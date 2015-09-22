<?php

namespace CirrusSearch;
use \ElasticaConnection;
use \Status;

/**
 * Base class with useful functions for communicating with Elasticsearch.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */
class ElasticsearchIntermediary {
	/**
	 * @var string|null the name or ip of the user for which we're performing this search or null in the case of
	 * requests kicked off by jobs
	 */
	private $user;
	/**
	 * @var float|null start time of current request or null if none is running
	 */
	private $requestStart = null;
	/**
	 * @var string|null description of the next request to be sent to Elasticsearch or null if not yet decided
	 */
	private $description = null;
	/**
	 * @var int how many millis a request through this intermediary needs to take before it counts as slow.
	 * 0 means none count as slow.
	 */
	private $slowMillis;

	/**
	 * Constructor.
	 *
	 * @param User|null $user user for which this search is being performed.  Attached to slow request logs.  Note that
	 * null isn't for anonymous users - those are still User objects and should be provided if possible.  Null is for
	 * when the action is being performed in some context where the user that caused it isn't available.  Like when an
	 * action is being performed during a job.
	 * @param float $slowSeconds how many seconds a request through this intermediary needs to take before it counts as
	 * slow.  0 means none count as slow.
	 */
	protected function __construct( $user, $slowSeconds ) {
		if ( $user ) {
			$this->user = 'User:' . $user->getName(); // name is the ip address of anonymous users
		} else {
			$this->user = 'nobody';
		}
		$this->slowMillis = round( 1000 * $slowSeconds );
	}

	/**
	 * Mark the start of a request to Elasticsearch.  Public so it can be called from pool counter methods.
	 *
	 * @param string $description name of the action being started
	 */
	public function start( $description ) {
		$this->description = $description;
		$this->requestStart = microtime( true );
	}

	/**
	 * Log a successful request and return the provided result in a good Status.  If you don't need the status
	 * just ignore the return.  Public so it can be called from pool counter methods.
	 *
	 * @param mixed $result result of the request.  defaults to null in case the request doesn't have a result
	 * @return \Status wrapping $result
	 */
	public function success( $result = null ) {
		$this->finishRequest();
		return Status::newGood( $result );
	}

	/**
	 * Log a failure and return an appropriate status.  Public so it can be called from pool counter methods.
	 *
	 * @param \Elastica\Exception\ExceptionInterface|null $exception if the request failed
	 * @return \Status representing a backend failure
	 */
	public function failure( $exception = null ) {
		$took = $this->finishRequest();
		$message = '';
		$status = Status::newFatal( 'cirrussearch-backend-error' );
		if ( $exception ) {
			$message = $exception->getMessage();
			$marker = 'ParseException[Cannot parse ';
			$markerLocation = strpos( $message, $marker );
			if ( $markerLocation === false ) {
				$message = 'Error message:  ' . $message;
			} else {
				// The important part of the parse error message comes before the next new line
				// so lets slurp it up and log it rather than the huge clump of error.
				$start = $markerLocation + strlen( $marker );
				$end = strpos( $message, "\n", $start );
				$parseError = substr( $message, $start, $end - $start );
				$message = 'Parse error on ' . $parseError;
				// Finally, return a different fatal status that we can check later on.
				$status = Status::newFatal( 'cirrussearch-parse-error' );
			}
		}
		wfLogWarning( "Search backend error during $this->description after $took.  $message" );
		return $status;
	}

	/**
	 * Unwrap a result that we expect to be a single value.
	 * @param mixed $data from Elastica result
	 * @return mixed the single result
	 */
	public static function singleValue( $result, $name ) {
		$data = $result->__get( $name );
		if ( $data === null ) {
			return null;
		}
		// Elasticsearch 0.90 returns single results as, well, single results
		if ( !is_array( $data ) ) {
			return $data;
		}
		// Elasticsearch 1.0 returns them as single node arrays
		$count = count( $data );
		if ( $count !== 1 ) {
			wfLogWarning( "Expected a single result for $name but got $count." );
		}
		return $data[ 0 ];
	}

	/**
	 * Does this status represent an Elasticsearch parse error?
	 * @param $status Status to check
	 * @return boolean is this a parse error?
	 */
	protected function isParseError( $status ) {
		foreach ( $status->getErrorsArray() as $errorMessage ) {
			if ( $errorMessage[ 0 ] === 'cirrussearch-parse-error' ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Log the completion of a request to Elasticsearch.
	 * @return int number of milliseconds it took to complete the request
	 */
	private function finishRequest() {
		if ( !$this->requestStart ) {
			wfLogWarning( 'finishRequest called without staring a request' );
			return;
		}
		// No need to check description because it must be set by $this->start.

		// Build the log message
		$took = round( ( microtime( true ) - $this->requestStart ) * 1000 );
		$logMessage = "$this->description took $took millis";

		// Extract the amount of time Elasticsearch reported the last request took if possible.
		$result = ElasticaConnection::getClient()->getLastResponse();
		if ( $result ) {
			$data = $result->getData();
			if ( isset( $data[ 'took' ] ) ) {
				$elasticTook = $data[ 'took' ];
				$logMessage .= " and $elasticTook Elasticsearch millis";
			}
		}

		// Now log and clear our state.
		wfDebugLog( 'CirrusSearchRequests', $logMessage );
		if ( $this->slowMillis && $took >= $this->slowMillis ) {
			if ( $this->user ) {
				$logMessage .= " for $this->user";
			}
			wfDebugLog( 'CirrusSearchSlowRequests', $logMessage );
		}
		$this->requestStart = null;
		return $took;
	}
}

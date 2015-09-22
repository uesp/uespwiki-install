<?php

namespace CirrusSearch;
use \JobQueueGroup;

/**
 * Performs the appropriate updates to Elasticsearch after a LinksUpdate is
 * completed.  The page itself is updated first then a second copy of this job
 * is queued to update linked articles if any links change.  The job can be
 * 'prioritized' via the 'prioritize' parameter which will switch it to a
 * different queue then the non-prioritized jobs.  Prioritized jobs will never
 * be deduplicated with non-prioritized jobs which is good because we can't
 * control which job is removed during deduplication.  In our case it'd only be
 * ok to remove the non-prioritized version.
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
class LinksUpdateJob extends Job {
	public function __construct( $title, $params, $id = 0 ) {
		parent::__construct( $title, $params, $id );

		if ( $this->isPrioritized() ) {
			$this->command .= 'Prioritized';
		}
		// Note that we have to keep the prioritized param or else when the job
		// is loaded it'll load under a different name/command/type which would
		// be confusing.
	}

	protected function doJob() {
		$updater = new Updater();
		$check = isset( $this->params['checkFreshness'] ) && $this->params['checkFreshness'];
		$updater->updateFromTitle( $this->title, $check );
		if ( count( $this->params[ 'addedLinks' ] ) > 0 ||
				count( $this->params[ 'removedLinks' ] ) > 0 ) {
			$params = array(
				'addedLinks' => $this->params[ 'addedLinks' ],
				'removedLinks' => $this->params[ 'removedLinks' ],
			);
			$jobQueueGroup = JobQueueGroup::singleton();
			$jobQueue = $jobQueueGroup->get( 'cirrusSearchLinksUpdateSecondary' );
			// If possible, delay the job execution by a few seconds so Elasticsearch
			// can refresh to contain what we just sent it.
			if ( $jobQueue->delayedJobsEnabled() ) {
				$params[ 'jobReleaseTimestamp' ] = time() + 3;
			}
			$jobQueueGroup->push(
				new LinksUpdateSecondaryJob( $this->title, $params ) );
		}
	}

	/**
	 * @return is this job prioritized?
	 */
	private function isPrioritized() {
		return isset( $this->params[ 'prioritize' ] ) && $this->params[ 'prioritize' ];
	}
}

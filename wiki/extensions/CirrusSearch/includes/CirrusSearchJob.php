<?php
/**
 * Abstract job class used by all CirrusSearch*Job classes
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
abstract class CirrusSearchJob extends Job {
	public function __construct( $title, $params, $id = 0 ) {
		// eg: CirrusSearchDeletePagesJob -> cirrusSearchDeletePages
		$jobName = lcfirst( str_replace( 'Job', '', get_class( $this ) ) );
		parent::__construct( $jobName, $title, $params, $id );
	}

	/**
	 * Some boilerplate stuff for all jobs goes here
	 */
	public function run() {
		global $wgDisableSearchUpdate, $wgPoolCounterConf;

		if ( $wgDisableSearchUpdate ) {
			return;
		}

		// Make sure we don't flood the pool counter.  This is safe since this is only used
		// to batch update wikis and we don't want to subject those to the pool counter.
		$backupPoolCounterSearch = $wgPoolCounterConf['CirrusSearch-Search'];
		unset( $wgPoolCounterConf['CirrusSearch-Search'] );

		$this->doJob();

		// Restore the pool counter settings in case other jobs need them
		$wgPoolCounterConf['CirrusSearch-Search'] = $backupPoolCounterSearch;
	}

	/**
	 * Actually perform the labor of the job
	 */
	abstract protected function doJob();
}

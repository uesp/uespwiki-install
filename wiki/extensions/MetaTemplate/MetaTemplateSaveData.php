<?php

// I probably should create a maintenance function that scans the pages in mt_save_set and makes sure
// all revision IDs are up-to-date... but when/how should it be called?  really shouldn't be particularly often....
// ... or with each save, randomly pick one other line to check?... still too often probably
// ... with each save, have a 10% chance of randomly picking another line?
// randomly picking lines seems to have too much overhead and no guarantee that a given line will ever get
// checked

// It's the only way to be sure to clear out obsolete data (not that it will end up being used anywhere,
// because any attempt to read the data will trigger cleardata)

class MetaTemplateSaveData {
	protected static $_titles = array();
	protected $_title;
	protected $_parser;
	protected $_currset = '';
	protected $_data = array();
	protected $_savedone=false;
	
	public function __construct(&$title, &$parser) {
		global $wgTitle, $wgHooks, $wgRequest;
		$this->_title = $title;
		$this->_parser = $parser;
		$this->_currset = '';
		$this->_data = array();
		
// ParserAfterTidy works on save and auto-update -- called after each individual article is processed
// ArticleSaveComplete works on save but NOT auto-update ... but ArticleSaveComplete has the correct new revision ID
// New approach: use parser's revision ID to determine which to call
//   no revision ID, or revision ID is not the latest one, means that the article will have to be saved
		if (is_null($this->_parser->mRevisionId) || $this->_parser->mRevisionId!=$this->_title->getLatestRevID()) {
// This is somewhat kludgy, and I'm not 100% sure it will work for all combinations of requests and job queues
//		if ($wgTitle==$title && (substr($action=$wgRequest->getVal('action'),0,4)=='edit' || $action=='submit'))
			$wgHooks['ArticleSaveComplete'][] = array($this, 'savedata');
		}
		else {
			$wgHooks['ParserAfterTidy'][] = array($this, 'savedata');
		}
	}
	
	static public function newFromTitle($title, $parser) {
		$id = $title->getArticleID();
		if (!array_key_exists($id, self::$_titles))
			self::$_titles[$id] = new MetaTemplateSaveData($title, $parser);
		return self::$_titles[$id];
	}
	
	static public function adddata( $title, $parser, $array, $subset ) {
		$object = self::newFromTitle($title, $parser);
		$object->doAdddata($array, $subset);
	}
	
	public function doAdddata($array, $subset) {
		if ($subset)
			$this->_currset = $subset;
		foreach ($array as $key => $value) {
			$this->_data[$this->_currset][$key] = $value;
		}
	}
	
	// if rev_id is provided, then it's the one rev_id that should be kept
	// (ideally should perhaps do rev_id lookup as part of query, but it doesn't matter if rev_id
	//  is a bit out of date... the purpose is to make sure that any completely out of date data gets cleaned up)
	// needs to also handle clearing multiple set_ids for same page/subset
	// (otherwise, it could be called over and over again when loads are done)
	static public function cleardata ( $title, $rev_id=NULL ) {
		$dbw = wfGetDB( DB_MASTER );
		
		if (is_object($title))
			$page_id = $title->getArticleID();
		else
			$page_id = $title;
		$conds = array( 'mt_set_page_id='.$page_id );
		if( !is_null ($rev_id ) )
			$conds[] = 'mt_set_rev_id<'.$rev_id;
			
// work around to handle DB memory issues
   		$result = $dbw->select( 'mt_save_set',
			                'mt_set_id',
			                $conds,
			                __METHOD__);
		if ($result) {
			$doCommit = true;
			
			try {
				$dbw->begin();
			} catch ( DBUnexpectedError $e ) {
				 $doCommit = false;
			}
			
			while( $row=$dbw->fetchRow( $result ) ) {
			       $rowconds = array('mt_save_id='.$row['mt_set_id']);
			       $dbw->delete('mt_save_data', $rowconds);
			}
			if ($doCommit) $dbw->commit();
		}
//		$dbw->deleteJoin( 'mt_save_data', 'mt_save_set', 'mt_save_id', 'mt_set_id', $conds );
		$dbw->delete( 'mt_save_set', $conds );
		
		// to be safe: I don't want these deletes to be run after I insert any new data
		// (although might not be necessary now that I'm not clearing and rewriting data)
		// also needs to be done before next round of deletes
		// With this extra commit image deletion causes an DBUnexpectedError from line 2661 of /home/uesp/www/w/includes/db/Database.php:
		// starting in MW 1.27. Commenting this line out fixes the issue.
		//$dbw->commit();
		
		// if I didn't clear the title out completely, now check to make sure that no
		// subset names are duplicated (could happen with simultaneous DB updates for same page)
		if (isset($rev_id)) {
			$delids = array();
			$donesets = array();
			$res = $dbw->select( 'mt_save_set',
				array('mt_set_subset', 'mt_set_id'),
				array('mt_set_page_id' => $page_id),
				__METHOD__,
				array('ORDER BY' => 'mt_set_rev_id DESC, mt_set_id DESC'));
			while ($row=$dbw->fetchRow($res)) {
				if (!isset($donesets[$row['mt_set_subset']])) {
					$donesets[$row['mt_set_subset']] = $row['mt_set_id'];
				}
				else {
					$delids[] = $row['mt_set_id'];
				}
			}
			if (count($delids))
				self::clearsets( $delids );
		}
	}
	
	// to delete a specific set_id from mt_save_set and all its associated values from mt_save_data
	// used in cases of duplicate entries for same subset, and also in cases where a single subset
	// needs to be removed from a page (but other subsets are still being kept)
	static public function clearsets( $set_ids ) {
		if (empty($set_ids))
			return;
		$dbw = wfGetDB(DB_MASTER);
		$dbw->delete('mt_save_data', array('mt_save_id' => $set_ids));
		$dbw->delete('mt_save_set', array('mt_set_id' => $set_ids));
	}
	
	public function savedata( ) {
		if ( wfReadOnly() ) return true;

		if( !count($this->_data) )
			return true;
		
		$revision_id = $this->_title->getLatestRevID();
// implies newly created article being called from ParserAfterTidy (which shouldn't happen, but just to be safe...)
// force processing after ArticleSaveComplete instead
		if (is_null($revision_id)) {
			$wgHooks['ArticleSaveComplete'][] = array($this, 'savedata');
			return true;
		}
		$page_id = $this->_title->getArticleID();
		$prttitle = $this->_title->getPrefixedText();
		
		// can't just clear data at this point and rewrite it all because causes too many problems with deadlocks
		// also, based on usage, the chances are that none of the data is being changed 
		//self::cleardata( $this->_title );
		// updating algorithm is based on assumption that its unlikely any data is actually been changed, therefore
		// * it's best to read the existing DB data before making any DB updates/inserts
		// * the chances are that we're going to need to read all the data for this save_set,
		//   so best to read it all at once instead of one entry at a time
		// * best to use read-only DB object until/unless it's clear that we'll need to write
		$db = wfGetDB(DB_MASTER); // Changed from DB_SLAVE to test odd occurrences of data not saving
		$dbw = NULL;
		
		// 'order by' is to ensure that if there are duplicates, I'll always delete the out-of-date revision, or else
		// the lowest numbered one
		$res = $db->select('mt_save_set', array('mt_set_id', 'mt_set_rev_id', 'mt_set_subset'), array('mt_set_page_id' => $page_id), 'MetaTemplateSaveData-savedata', array('ORDER BY' => 'mt_set_rev_id DESC, mt_set_id DESC'));
		$oldsets = array();
		$delids = array();
		while ($row = $db->fetchRow($res)) {
			if (isset($oldsets[$row['mt_set_subset']]))
				$delids[] = $row['mt_set_id'];
			else
				$oldsets[$row['mt_set_subset']] = array('mt_set_id' => $row['mt_set_id'], 'mt_set_rev_id' => $row['mt_set_rev_id']);
		}
		
		// what about simultaneous processing of same page??  since I know it happens (although hopefully generally on null-type edits)
		// should only produce errors if attemt to insert records twice
		// ... should order of DB writes be tweaked 
		foreach ($this->_data as $subset => $subdata) {
			$olddata = NULL;
			$inserts = array();
			if (isset($oldsets[$subset])) {
				if ($oldsets[$subset]['mt_set_rev_id']>$revision_id) {
					// set exists, and is more up-to-date than this call (unlikely, but theoretically possible with simultaneous processing)
					unset($oldsets[$subset]);
					continue;
				}
				elseif ($oldsets[$subset]['mt_set_rev_id']==$revision_id) {
					// rev_id hasn't changed, suggesting I could just skip the subset at this point
					// but there could be changes inherited from templates (new variables to save, etc.)
					$setid = $oldsets[$subset]['mt_set_id'];
				}
				else {
					// set exists, but rev_id has changed (page has been edited)
					if (!isset($dbw))
						$dbw = wfGetDB(DB_MASTER);
					$setid = $oldsets[$subset]['mt_set_id'];
					$dbw->update('mt_save_set', array('mt_set_rev_id' => $revision_id), array('mt_set_id' => $setid));
				}
				unset($oldsets[$subset]);
			}
			else {
				// newly created set
				if (!isset($dbw))
					$dbw = wfGetDB(DB_MASTER);
				// could theoretically get two inserts happening simultaneously here
				// can't be prevented using replace, because the values I'm inserting aren't indices
				// instead, I need to be sure that duplicates won't mess up any routines reading data
				// and handle cleaning up the duplicate next time this routine is called
				$dbw->insert('mt_save_set', array('mt_set_page_id' => $page_id,
				                                  'mt_set_rev_id' => $revision_id,
				                                  'mt_set_subset' => $subset));
				$setid = $dbw->insertId();
				$olddata = array();
			}
			if (is_null($olddata)) {
				$res = $db->select('mt_save_data', array('mt_save_varname', 'mt_save_value', 'mt_save_parsed'), array('mt_save_id' => $setid));
				while ($row=$db->fetchRow($res)) {
					$olddata[$row['mt_save_varname']] = array('mt_save_value' => $row['mt_save_value'], 'mt_save_parsed' => $row['mt_save_parsed']);
				}
			}
			
			// addslashes is not needed here: default wiki processing is already taking care of add/strip somewhere in
			// the process (tested with quotes and slash)
			foreach ( $subdata as $key => $vdata ) {
				$value = $vdata['value'];
				
				// Do not save any UNIQ..QINU marker names
				// They can't be expanded on #load because context has been lost
				// And leave it end users to figure out whether dropping some of the content is causing any problems
				// In some cases, may be perfectly OK to drop it (e.g., cleanspace tags that only contain data initalization)
				// RH70 2019-03-24: Regex was no longer working - replaced with built-in parser function.
				// $value = preg_replace("/\x7fUNIQ.*?QINU\x7f/", '', $value);
				$value = $this->_parser->killMarkers( $value );
				
				if (array_key_exists('parsed', $vdata))
					$parsed = $vdata['parsed'];
				else
					$parsed = true;
				if (!isset($olddata[$key])) {
					$inserts[] = array( 'mt_save_id' => $setid,
					                    'mt_save_varname' => $key,
					                    'mt_save_value' => $value,
					                    'mt_save_parsed' => $parsed);
				}
				else {
					if ($olddata[$key]['mt_save_value']!=$value || $olddata[$key]['mt_save_parsed']!=$parsed) {
						if (!isset($dbw))
							$dbw = wfGetDB(DB_MASTER);
						// updates can't be done in a batch... unless I delete then insert them all
						// but I'm assuming that it's most likely only value needs to be updated, in which case
						// it's most efficient to simply make updates one value at a time
						$dbw->update('mt_save_data',
						             array('mt_save_value' => $value,
						                   'mt_save_parsed' => $parsed),
						             array('mt_save_id' => $setid,
						                   'mt_save_varname' => $key));
					}
					unset ($olddata[$key]);
				}
			}
			if (count($olddata)) {
				if (!isset($dbw))
					$dbw = wfGetDB(DB_MASTER);
				$dbw->delete('mt_save_data', array('mt_save_id' => $setid,
				                                   'mt_save_varname' => array_keys($olddata)));
			}
			if (count($inserts)) {
				if (!isset($dbw))
					$dbw = wfGetDB(DB_MASTER);
				// use replace instead of insert just in case there's simultaneous processing going on
				// second param isn't used by mysql, but provide it just in case another DB is used 
				$dbw->replace('mt_save_data', array('mt_save_id', 'mt_save_varname'), $inserts);
			}
		}
		
		if (count($oldsets) || count($delids)) {
			foreach ($oldsets as $subset => $subdata)
				$delids[] = $subdata['mt_set_id'];
			self::clearsets( $delids );
		}
		global $wgJobRunRate;
		// same frequency algorithm used by Wiki.php to determine whether or not to do a job
		if ($wgJobRunRate > 0) {
			if( $wgJobRunRate < 1 ) {
				$max = mt_getrandmax();
				if( mt_rand( 0, $max ) > $max * $wgJobRunRate )
					$n = 0;
				else
					$n = 1;
			} else {
				$n = intval( $wgJobRunRate );
			}
			if ($n) {
				self::clearoldsets($n);
			}
		}
		
		$this->_data = array();
		$this->_currset = '';
		return true;
	}
	
	static public function OnDelete( &$article ) {
		
		if ( wfReadOnly() ) return true;
		
		$title = $article->getTitle();
		self::cleardata( $title );
		return true;
	}
	
	static public function OnMove( &$oldtitle, &$newtitle, &$user, $pageid, $redirectid ) {
	
 	        if ( wfReadOnly() ) return true;

		$dbw = wfGetDB( DB_MASTER );
		$revid = $newtitle->getLatestRevID();
		
		$conds = array( 'mt_set_page_id' => $oldtitle->getArticleID() );
		$dbw->update( 'mt_save_set',
		              array( 'mt_set_page_id' => $newtitle->getArticleID(),
		                     'mt_set_rev_id' => $revid ),
		              $conds);
		return true;
	}
	
	// function to check whether there are any out-of-date saved sets
	// only needs to be run occasionally
	static public function clearoldsets ($limit=1) {
		// do original query on slave, since it's likely it will be a null result and no writing needs to be done
		$db = wfGetDB(DB_MASTER); // Changed from DB_SLAVE to test odd occurrences of data not saving
		if (!empty($limit))
			$opts = array('LIMIT' => $limit);
		else
			$opts = array();
		$res = $db->select('mt_save_set INNER JOIN page ON (mt_set_page_id=page_id AND mt_set_rev_id<page_latest)',
		                   array('mt_set_id', 'mt_set_page_id', 'mt_set_rev_id', 'page_latest'),
		                   '', 'MetaTemplateSaveData-clearoldset', $opts);
		if (!$db->numRows($res))
			return;
		while ($row=$db->fetchRow($res)) {
			self::cleardata($row['mt_set_page_id'], $row['page_latest']);
		}
	}
	
	static public function OnPurge( &$article ) {

	        if ( wfReadOnly() ) return true;

		$title = $article->getTitle();
		self::cleardata( $title );
		return true;
	}
}

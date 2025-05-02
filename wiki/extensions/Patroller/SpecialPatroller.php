<?php
/**
 * Patroller
 * Patroller MediaWiki hooks
 *
 * @author: Rob Church <robchur@gmail.com>, Kris Blair (Developaws)
 * @copyright: 2006-2008 Rob Church, 2015-2017 Kris Blair
 * @license: GPL General Public Licence 2.0
 * @package: Patroller
 * @link: https://mediawiki.org/wiki/Extension:Patroller
 */

class SpecialPatroller extends SpecialPage {
	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct( 'Patrol', 'patroller' );
	}

	public function doesWrites() {
		return true;
	}

	/**
	 * Execution
	 *
	 * @param array $par Parameters passed to the page
	 * @return void
	 */
	public function execute( $par ) {
		global $wgUser, $wgRequest, $wgOut;

		$this->setHeaders();

		// Check permissions
		if ( !$wgUser->isAllowed( 'patroller' ) ) {
			throw new PermissionsError( 'patroller' );
		}

		// Keep out blocked users
		if ( $wgUser->isBlocked() ) {
			throw new UserBlockedError( $wgUser->getBlock() );
		}

		// Prune old assignments if needed
		if ( 0 == mt_rand( 0, 499 ) ) {
			$this->pruneAssignments();
		}

		// See if something needs to be done
		if ( $wgRequest->wasPosted() && $wgUser->matchEditToken( $wgRequest->getText( 'wpToken' ) ) ) {
			$rcid = $wgRequest->getIntOrNull( 'wpRcId' );
			if ( $rcid ) {
				if ( $wgRequest->getCheck( 'wpPatrolEndorse' ) ) {
					// Mark the change patrolled
					if ( !$wgUser->isBlocked( false ) ) {
						RecentChange::markPatrolled( $rcid );
						$wgOut->setSubtitle( wfMessage( 'patrol-endorsed-ok' )->escaped() );
					} else {
						$wgOut->setSubtitle( wfMessage( 'patrol-endorsed-failed' )->escaped() );
					}
				} elseif ( $wgRequest->getCheck( 'wpPatrolRevert' ) ) {
					// Revert the change
					$edit = $this->loadChange( $rcid );
					$msg = $this->revert( $edit, $this->revertReason( $wgRequest ) ) ? 'ok' : 'failed';
					$wgOut->setSubtitle( wfMessage( 'patrol-reverted-' . $msg )->escaped() );
				} elseif ( $wgRequest->getCheck( 'wpPatrolSkip' ) ) {
					// Do nothing
					$wgOut->setSubtitle( wfMessage( 'patrol-skipped-ok' )->escaped() );
				}
			}
		}

		// If a token was passed, but the check box value was not, then the user
		// wants to pause or stop patrolling
		if ( $wgRequest->getCheck( 'wpToken' ) && !$wgRequest->getCheck( 'wpAnother' ) ) {
			$skin = $this->getSkin();
			$self = SpecialPage::getTitleFor( 'Patrol' );
			$link = Linker::link(
				$self,
				wfMessage( 'patrol-resume' )->escaped(),
				[],
				[],
				[ 'known' ]
			);
			$wgOut->addHTML( wfMessage( 'patrol-stopped', $link )->escaped() );
			return;
		}

		// Pop an edit off recentchanges
		$haveEdit = false;
		while ( !$haveEdit ) {
			$edit = $this->fetchChange( $wgUser );
			if ( $edit ) {
				// Attempt to assign it
				if ( $this->assignChange( $edit ) ) {
					$haveEdit = true;
					$this->showDiffDetails( $edit );
					$wgOut->addHTML( '<br><hr>' );
					$this->showDiff( $edit );
					$wgOut->addHTML( '<br><hr>' );
					$this->showControls( $edit );
				}
			} else {
				// Can't find a suitable edit
				$haveEdit = true; // Don't keep going, there's nothing to find
				$wgOut->addWikiText( wfMessage( 'patrol-nonefound' )->text() );
			}
		}
	}

	/**
	 * Produce a stub recent changes listing for a single diff.
	 *
	 * @param RecentChange &$edit Diff. to show the listing for
	 */
	private function showDiffDetails( &$edit ) {
		global $wgOut;
		$edit->counter = 1;
		$edit->mAttribs['rc_patrolled'] = 1;
		$list = ChangesList::newFromContext( RequestContext::GetMain() );
		$wgOut->addHTML(
			$list->beginRecentChangesList() .
			$list->recentChangesLine( $edit ) .
			$list->endRecentChangesList()
		);
	}

	/**
	 * Output a trimmed down diff view corresponding to a particular change
	 *
	 * @param RecentChange &$edit Recent change to produce a diff for
	 */
	private function showDiff( &$edit ) {
		$diff = new DifferenceEngine(
			$edit->getTitle(),
			$edit->mAttribs['rc_last_oldid'],
			$edit->mAttribs['rc_this_oldid']
		);
		$diff->showDiff( '', '' );
	}

	/**
	 * Output a bunch of controls to let the user endorse, revert and skip changes
	 *
	 * @param RecentChange &$edit RecentChange being dealt with
	 */
	private function showControls( &$edit ) {
		global $wgUser, $wgOut;
		$self = SpecialPage::getTitleFor( 'Patrol' );
		$form = Html::openElement( 'form', [
			'method' => 'post',
			'action' => $self->getLocalUrl()
		] );
		$form .= Html::openElement( 'table' );
		$form .= Html::openElement( 'tr' );
		$form .= Html::openElement( 'td', [
			'align' => 'right'
		] );
		$form .= Html::submitButton( wfMessage( 'patrol-endorse' )->escaped(), [
			'name' => 'wpPatrolEndorse'
		] );
		$form .= Html::closeElement( 'td' );
		$form .= Html::openElement( 'td' ) . Html::closeElement( 'td' );
		$form .= Html::closeElement( 'tr' );
		$form .= Html::openElement( 'tr' );
		$form .= Html::openElement( 'td', [
			'align' => 'right'
		] );
		$form .= Html::submitButton( wfMessage( 'patrol-revert' )->escaped(), [
			'name' => 'wpPatrolRevert'
		] );
		$form .= Html::closeElement( 'td' );
		$form .= Html::openElement( 'td' );
		$form .= Html::label( wfMessage( 'patrol-revert-reason' )->escaped(), 'reason' ) . '&#160;';
		$form .= $this->revertReasonsDropdown() . ' / ' . Html::input( 'wpPatrolRevertReason' );
		$form .= Html::closeElement( 'td' );
		$form .= Html::closeElement( 'tr' );
		$form .= Html::openElement( 'tr' );
		$form .= Html::openElement( 'td', [
			'align' => 'right'
		] );
		$form .= Html::submitButton( wfMessage( 'patrol-skip' )->escaped(), [
			'name' => 'wpPatrolSkip'
		] );
		$form .= Html::closeElement( 'td' );
		$form .= Html::closeElement( 'tr' );
		$form .= Html::openElement( 'tr' );
		$form .= Html::openElement( 'td' );
		$form .= Html::check( 'wpAnother', true );
		$form .= Html::closeElement( 'td' );
		$form .= Html::openElement( 'td' );
		$form .= wfMessage( 'patrol-another' )->escaped();
		$form .= Html::closeElement( 'td' );
		$form .= Html::closeElement( 'tr' );
		$form .= Html::closeElement( 'table' );
		$form .= Html::Hidden( 'wpRcId', $edit->mAttribs['rc_id'] );
		$form .= Html::Hidden( 'wpToken', $wgUser->getEditToken() );
		$form .= Html::closeElement( 'form' );
		$wgOut->addHTML( $form );
	}

	/**
	 * Fetch a recent change which
	 *   - the user doing the patrolling didn't cause
	 *   - wasn't due to a bot
	 *   - hasn't been patrolled
	 *   - isn't assigned to a user
	 *
	 * @param User &$user User to suppress edits for
	 * @return false|RecentChange
	 */
	private function fetchChange( &$user ) {
		$dbr = wfGetDB( DB_REPLICA );
		$uid = $user->getId();
		extract( $dbr->tableNames( 'recentchanges', 'patrollers', 'page' ) );
		$res = $dbr->select(
			[ $page, $recentchanges ],
			'*',
			[
				'ptr_timestamp IS NULL',
				'rc_namespace = page_namespace',
				'rc_title = page_title',
				'rc_this_oldid = page_latest',
				'rc_user != ' . $uid,
				'rc_bot'		=> '0',
				'rc_patrolled'	=> '0',
				'rc_type'		=> '0'
			],
			__METHOD__,
			[
				'LIMIT'	=> '0,1'
			],
			[
				$patrollers => [
					'LEFT JOIN',
					[
						'rc_id = ptr_change'
					]
				]
			]
		);
		if ( $res->numRows() > 0 ) {
			$row = $res->fetchObject();
			return RecentChange::newFromRow( $row, $row->rc_last_oldid );
		}
		return false;
	}

	/**
	 * Fetch a particular recent change given the rc_id value
	 *
	 * @param int $rcid rc_id value of the row to fetch
	 * @return bool|RecentChange
	 */
	private function loadChange( $rcid ) {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'recentchanges',
			'*',
			[
				'rc_id' => $rcid
			],
			'Patroller::loadChange'
		);
		if ( $res->numRows() > 0 ) {
			$row = $dbr->fetchObject( $res );
			return RecentChange::newFromRow( $row );
		}
		return false;
	}

	/**
	 * Assign the patrolling of a particular change, so other users don't pull
	 * it up, duplicating effort
	 *
	 * @param RecentChange &$edit RecentChange item to assign
	 * @return bool If rows were changed
	 */
	private function assignChange( &$edit ) {
		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->insert(
			'patrollers',
			[
				'ptr_change'	=> $edit->mAttribs['rc_id'],
				'ptr_timestamp'	=> $dbw->timestamp()
			],
			__METHOD__,
			'IGNORE'
		);
		return (bool)$dbw->affectedRows();
	}

	/**
	 * Remove the assignment for a particular change, to let another user handle it
	 *
	 * @param int $rcid rc_id value
	 *
	 * @todo Use it or lose it
	 */
	private function unassignChange( $rcid ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'patrollers',
			[
				'ptr_change' => $rcid
			],
			__METHOD__
		);
	}

	/**
	 * Prune old assignments from the table so edits aren't
	 * hidden forever because a user wandered off, and to
	 * keep the table size down as regards old assignments
	 */
	private function pruneAssignments() {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'patrollers',
			[
				'ptr_timestamp < ' . $dbw->timestamp( time() - 120 )
			],
			__METHOD__
		);
	}

	/**
	 * Revert a change, setting the page back to the "old" version
	 *
	 * @param RecentChange &$edit RecentChange to revert
	 * @param string $comment Comment to use when reverting
	 * @return bool Change was reverted
	 */
	private function revert( &$edit, $comment = '' ) {
		global $wgUser;
		if ( !$wgUser->isBlocked( false ) ) { // Check block against master
			$dbw = wfGetDB( DB_MASTER );
			$title = $edit->getTitle();
			// Prepare the comment
			$comment = wfMessage( 'patrol-reverting', $comment )->inContentLanguage()->text();
			// Find the old revision
			$old = Revision::newFromId( $edit->mAttribs['rc_last_oldid'] );
			// Be certain we're not overwriting a more recent change
			// If we would, ignore it, and silently consider this change patrolled
			$latest = (int)$dbw->selectField(
				'page',
				'page_latest',
				[
					'page_id' => $title->getArticleID()
				],
				__METHOD__
			);
			if ( $edit->mAttribs['rc_this_oldid'] == $latest ) {
				// Revert the edit; keep the reversion itself out of recent changes
				wfDebugLog( 'patroller', 'Reverting "' . $title->getPrefixedText() . '" to r' . $old->getId() );
				$page = WikiPage::factory( $title );
				$page->doEditContent(
					$old->getContent(),
					$comment,
					EDIT_UPDATE & EDIT_MINOR & EDIT_SUPPRESS_RC
				);
			}
			// Mark the edit patrolled so it doesn't bother us again
			RecentChange::markPatrolled( $edit->mAttribs['rc_id'] );
			return true;
		}
		return false;
	}

	/**
	 * Make a nice little drop-down box containing all the pre-defined revert
	 * reasons for simplified selection
	 *
	 * @return string Reasons
	 */
	private function revertReasonsDropdown() {
		$msg = wfMessage( 'patrol-reasons' )->inContentLanguage()->text();
		if ( $msg == '-' || $msg == '&lt;patrol-reasons&gt;' ) {
			return '';
		}
		$reasons = [];
		$lines = explode( "\n", $msg );
		foreach ( $lines as $line ) {
			if ( substr( $line, 0, 1 ) == '*' ) {
				$reasons[] = trim( $line, '* ' );
			}
		}
		if ( count( $reasons ) > 0 ) {
			$box = Html::openElement( 'select', [
				'name' => 'wpPatrolRevertReasonCommon'
			] );
			foreach ( $reasons as $reason ) {
				$box .= Html::element( 'option', [
					'value' => $reason
				], $reason );
			}
			$box .= Html::closeElement( 'select' );
			return $box;
		}
		return '';
	}

	/**
	 * Determine which of the two "revert reason" form fields to use;
	 * the pre-defined reasons, or the nice custom text box
	 *
	 * @param WebRequest &$request WebRequest object to test
	 * @return string Revert reason
	 */
	private function revertReason( &$request ) {
		$custom = $request->getText( 'wpPatrolRevertReason' );
		return trim( $custom ) != '' ? $custom : $request->getText( 'wpPatrolRevertReasonCommon' );
	}
}

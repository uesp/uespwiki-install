<?php

/**
 * The default view used in Special:AbuseFilter
 */
class AbuseFilterViewList extends AbuseFilterView {
	function show() {
		global $wgAbuseFilterCentralDB, $wgAbuseFilterIsCentral;

		$out = $this->getOutput();
		$request = $this->getRequest();

		// Status info...
		$this->showStatus();

		$out->addWikiMsg( 'abusefilter-intro' );

		// New filter button
		if ( $this->canEdit() ) {
			$title = $this->getTitle( 'new' );
			$link = $this->linkRenderer->makeLink( $title, $this->msg( 'abusefilter-new' )->text() );
			$links = Xml::tags( 'p', null, $link ) . "\n";
			$out->addHTML( $links );
		}

		// Options.
		$conds = [];
		$deleted = $request->getVal( 'deletedfilters' );
		$hidedisabled = $request->getBool( 'hidedisabled' );
		$defaultscope = 'all';
		if ( isset( $wgAbuseFilterCentralDB ) && !$wgAbuseFilterIsCentral ) {
			// Show on remote wikis as default only local filters
			$defaultscope = 'local';
		}
		$scope = $request->getVal( 'rulescope', $defaultscope );

		if ( $deleted == 'show' ) {
			# Nothing
		} elseif ( $deleted == 'only' ) {
			$conds['af_deleted'] = 1;
		} else { # hide, or anything else.
			$conds['af_deleted'] = 0;
			$deleted = 'hide';
		}
		if ( $hidedisabled ) {
			$conds['af_deleted'] = 0;
			$conds['af_enabled'] = 1;
		}

		if ( $scope == 'local' ) {
			$conds['af_global'] = 0;
		} elseif ( $scope == 'global' ) {
			$conds['af_global'] = 1;
		}

		$this->showList( $conds, compact( 'deleted', 'hidedisabled', 'scope' ) );
	}

	function showList( $conds = [ 'af_deleted' => 0 ], $optarray = [] ) {
		global $wgAbuseFilterCentralDB, $wgAbuseFilterIsCentral;

		$output = '';
		$output .= Xml::element( 'h2', null,
			$this->msg( 'abusefilter-list' )->parse() );

		$pager = new AbuseFilterPager( $this, $conds, $this->linkRenderer );

		$deleted = $optarray['deleted'];
		$hidedisabled = $optarray['hidedisabled'];
		$scope = $optarray['scope'];

		# Options form
		$fields = [];
		$fields['abusefilter-list-options-deleted'] =
			Xml::radioLabel(
				$this->msg( 'abusefilter-list-options-deleted-show' )->text(),
				'deletedfilters',
				'show',
				'mw-abusefilter-deletedfilters-show',
				$deleted == 'show'
			) .
			Xml::radioLabel(
				$this->msg( 'abusefilter-list-options-deleted-hide' )->text(),
				'deletedfilters',
				'hide',
				'mw-abusefilter-deletedfilters-hide',
				$deleted == 'hide'
			) .
			Xml::radioLabel(
				$this->msg( 'abusefilter-list-options-deleted-only' )->text(),
				'deletedfilters',
				'only',
				'mw-abusefilter-deletedfilters-only',
				$deleted == 'only'
			);

		if ( isset( $wgAbuseFilterCentralDB ) ) {
			$fields['abusefilter-list-options-scope'] =
				Xml::radioLabel(
					$this->msg( 'abusefilter-list-options-scope-local' )->text(),
					'rulescope',
					'local',
					'mw-abusefilter-rulescope-local',
					$scope == 'local'
				) .
				Xml::radioLabel(
					$this->msg( 'abusefilter-list-options-scope-global' )->text(),
					'rulescope',
					'global',
					'mw-abusefilter-rulescope-global',
					$scope == 'global'
				);

			if ( $wgAbuseFilterIsCentral ) {
				// For central wiki: add third scope option
				$fields['abusefilter-list-options-scope'] .=
					Xml::radioLabel(
						$this->msg( 'abusefilter-list-options-scope-all' )->text(),
						'rulescope',
						'all',
						'mw-abusefilter-rulescope-all',
						$scope == 'all'
				);
			}
		}

		$fields['abusefilter-list-options-disabled'] =
			Xml::checkLabel(
				$this->msg( 'abusefilter-list-options-hidedisabled' )->text(),
				'hidedisabled',
				'mw-abusefilter-disabledfilters-hide',
				$hidedisabled
			);
		$fields['abusefilter-list-limit'] = $pager->getLimitSelect();

		$options = Xml::buildForm( $fields, 'abusefilter-list-options-submit' );
		$options .= Html::hidden( 'title', $this->getTitle()->getPrefixedDBkey() );
		$options = Xml::tags( 'form',
			[
				'method' => 'get',
				'action' => $this->getTitle()->getFullURL()
			],
			$options
		);
		$options = Xml::fieldset( $this->msg( 'abusefilter-list-options' )->text(), $options );

		$output .= $options;

		if ( isset( $wgAbuseFilterCentralDB ) && !$wgAbuseFilterIsCentral && $scope == 'global' ) {
			$globalPager = new GlobalAbuseFilterPager( $this, $conds, $this->linkRenderer );
			$output .=
				$globalPager->getNavigationBar() .
				$globalPager->getBody() .
				$globalPager->getNavigationBar();
		} else {
			$output .=
				$pager->getNavigationBar() .
				$pager->getBody() .
				$pager->getNavigationBar();
		}

		$this->getOutput()->addHTML( $output );
	}

	function showStatus() {
		global $wgAbuseFilterConditionLimit, $wgAbuseFilterValidGroups;

		$stash = ObjectCache::getMainStashInstance();
		$overflow_count = (int)$stash->get( AbuseFilter::filterLimitReachedKey() );
		$match_count = (int)$stash->get( AbuseFilter::filterMatchesKey() );
		$total_count = 0;
		foreach ( $wgAbuseFilterValidGroups as $group ) {
			$total_count += (int)$stash->get( AbuseFilter::filterUsedKey( $group ) );
		}

		if ( $total_count > 0 ) {
			$overflow_percent = sprintf( "%.2f", 100 * $overflow_count / $total_count );
			$match_percent = sprintf( "%.2f", 100 * $match_count / $total_count );

			$status = $this->msg( 'abusefilter-status' )
				->numParams(
					$total_count,
					$overflow_count,
					$overflow_percent,
					$wgAbuseFilterConditionLimit,
					$match_count,
					$match_percent
				)->parse();

			$status = Xml::tags( 'div', [ 'class' => 'mw-abusefilter-status' ], $status );
			$this->getOutput()->addHTML( $status );
		}
	}
}

/**
 * Class to build paginated filter list
 */
// Probably no need to autoload this class, as it will only be called from the class above.
class AbuseFilterPager extends TablePager {

	/**
	 * @var \MediaWiki\Linker\LinkRenderer
	 */
	protected $linkRenderer;

	function __construct( $page, $conds, $linkRenderer ) {
		$this->mPage = $page;
		$this->mConds = $conds;
		$this->linkRenderer = $linkRenderer;
		parent::__construct( $this->mPage->getContext() );
	}

	function getQueryInfo() {
		return [
			'tables' => [ 'abuse_filter' ],
			'fields' => [
				'af_id',
				'af_enabled',
				'af_deleted',
				'af_global',
				'af_public_comments',
				'af_hidden',
				'af_hit_count',
				'af_timestamp',
				'af_user_text',
				'af_user',
				'af_actions',
				'af_group',
			],
			'conds' => $this->mConds,
		];
	}

	function getFieldNames() {
		static $headers = null;

		if ( !empty( $headers ) ) {
			return $headers;
		}

		$headers = [
			'af_id' => 'abusefilter-list-id',
			'af_public_comments' => 'abusefilter-list-public',
			'af_actions' => 'abusefilter-list-consequences',
			'af_enabled' => 'abusefilter-list-status',
			'af_timestamp' => 'abusefilter-list-lastmodified',
			'af_hidden' => 'abusefilter-list-visibility',
		];

		if ( $this->mPage->getUser()->isAllowed( 'abusefilter-log-detail' ) ) {
			$headers['af_hit_count'] = 'abusefilter-list-hitcount';
		}

		global $wgAbuseFilterValidGroups;
		if ( count( $wgAbuseFilterValidGroups ) > 1 ) {
			$headers['af_group'] = 'abusefilter-list-group';
		}

		foreach ( $headers as &$msg ) {
			$msg = $this->msg( $msg )->text();
		}

		return $headers;
	}

	function formatValue( $name, $value ) {
		$lang = $this->getLanguage();
		$row = $this->mCurrentRow;

		switch ( $name ) {
			case 'af_id':
				return $this->linkRenderer->makeLink(
					SpecialPage::getTitleFor( 'AbuseFilter', intval( $value ) ),
					$lang->formatNum( intval( $value ) )
				);
			case 'af_public_comments':
				return $this->linkRenderer->makeLink(
					SpecialPage::getTitleFor( 'AbuseFilter', intval( $row->af_id ) ),
					$value
				);
			case 'af_actions':
				$actions = explode( ',', $value );
				$displayActions = [];
				foreach ( $actions as $action ) {
					$displayActions[] = AbuseFilter::getActionDisplay( $action );
				}
				return htmlspecialchars( $lang->commaList( $displayActions ) );
			case 'af_enabled':
				$statuses = [];
				if ( $row->af_deleted ) {
					$statuses[] = $this->msg( 'abusefilter-deleted' )->parse();
				} elseif ( $row->af_enabled ) {
					$statuses[] = $this->msg( 'abusefilter-enabled' )->parse();
				} else {
					$statuses[] = $this->msg( 'abusefilter-disabled' )->parse();
				}

				global $wgAbuseFilterIsCentral;
				if ( $row->af_global && $wgAbuseFilterIsCentral ) {
					$statuses[] = $this->msg( 'abusefilter-status-global' )->parse();
				}

				return $lang->commaList( $statuses );
			case 'af_hidden':
				$msg = $value ? 'abusefilter-hidden' : 'abusefilter-unhidden';
				return $this->msg( $msg )->parse();
			case 'af_hit_count':
				if ( SpecialAbuseLog::canSeeDetails( $row->af_id, $row->af_hidden ) ) {
					$count_display = $this->msg( 'abusefilter-hitcount' )
						->numParams( $value )->parse();
					$link = $this->linkRenderer->makeKnownLink(
						SpecialPage::getTitleFor( 'AbuseLog' ),
						$count_display,
						[],
						[ 'wpSearchFilter' => $row->af_id ]
					);
				} else {
					$link = "";
				}
				return $link;
			case 'af_timestamp':
				$userLink =
					Linker::userLink(
						$row->af_user,
						$row->af_user_text
					) .
					Linker::userToolLinks(
						$row->af_user,
						$row->af_user_text
					);
				$user = $row->af_user_text;
				return $this->msg( 'abusefilter-edit-lastmod-text' )
					->rawParams( $lang->timeanddate( $value, true ),
						$userLink,
						$lang->date( $value, true ),
						$lang->time( $value, true ),
						$user
				)->parse();
			case 'af_group':
				return AbuseFilter::nameGroup( $value );
				break;
			default:
				throw new MWException( "Unknown row type $name!" );
		}
	}

	function getDefaultSort() {
		return 'af_id';
	}

	function getRowClass( $row ) {
		if ( $row->af_enabled ) {
			return 'mw-abusefilter-list-enabled';
		} elseif ( $row->af_deleted ) {
			return 'mw-abusefilter-list-deleted';
		} else {
			return 'mw-abusefilter-list-disabled';
		}
	}

	function isFieldSortable( $name ) {
		$sortable_fields = [
			'af_id',
			'af_enabled',
			'af_throttled',
			'af_user_text',
			'af_timestamp',
			'af_hidden',
			'af_group',
		];
		if ( $this->mPage->getUser()->isAllowed( 'abusefilter-log-detail' ) ) {
			$sortable_fields[] = 'af_hit_count';
		}
		return in_array( $name, $sortable_fields );
	}
}

/**
 * Class to build paginated filter list for wikis using global abuse filters
 */
class GlobalAbuseFilterPager extends AbuseFilterPager {
	function __construct( $page, $conds, $linkRenderer ) {
		parent::__construct( $page, $conds, $linkRenderer );
		global $wgAbuseFilterCentralDB;
		$this->mDb = wfGetDB( DB_REPLICA, [], $wgAbuseFilterCentralDB );
	}

	function formatValue( $name, $value ) {
		$lang = $this->getLanguage();
		$row = $this->mCurrentRow;

		switch ( $name ) {
			case 'af_id':
				return $lang->formatNum( intval( $value ) );
			case 'af_public_comments':
				return $this->getOutput()->parseInline( $value );
			case 'af_actions':
				$actions = explode( ',', $value );
				$displayActions = [];
				foreach ( $actions as $action ) {
					$displayActions[] = AbuseFilter::getActionDisplay( $action );
				}
				return htmlspecialchars( $lang->commaList( $displayActions ) );
			case 'af_enabled':
				$statuses = [];
				if ( $row->af_deleted ) {
					$statuses[] = $this->msg( 'abusefilter-deleted' )->parse();
				} elseif ( $row->af_enabled ) {
					$statuses[] = $this->msg( 'abusefilter-enabled' )->parse();
				} else {
					$statuses[] = $this->msg( 'abusefilter-disabled' )->parse();
				}
				if ( $row->af_global ) {
					$statuses[] = $this->msg( 'abusefilter-status-global' )->parse();
				}

				return $lang->commaList( $statuses );
			case 'af_hidden':
				$msg = $value ? 'abusefilter-hidden' : 'abusefilter-unhidden';
				return $this->msg( $msg, 'parseinline' )->parse();
			case 'af_hit_count':
				// If the rule is hidden, don't show it, even to priviledged local admins
				if ( $row->af_hidden ) {
					return '';
				}
				return $this->msg( 'abusefilter-hitcount' )->numParams( $value )->parse();
			case 'af_timestamp':
				$user = $row->af_user_text;
				return $this->msg(
					'abusefilter-edit-lastmod-text',
					$lang->timeanddate( $value, true ),
					$user,
					$lang->date( $value, true ),
					$lang->time( $value, true ),
					$user
				)->parse();
			case 'af_group':
				// If this is global, local name probably doesn't exist, but try
				return AbuseFilter::nameGroup( $value );
				break;
			default:
				throw new MWException( "Unknown row type $name!" );
		}
	}
}

<?php
/**
 * MobileFrontend.hooks.php
 */

/**
 * Hook handlers for MobileFrontend extension
 *
 * Hook handler method names should be in the form of:
 *	on<HookName>()
 * For intance, the hook handler for the 'RequestContextCreateSkin' would be called:
 *	onRequestContextCreateSkin()
 */
class MobileFrontendHooks {

	/**
	 * LinksUpdate hook handler - saves a count of h2 elements that occur in the WikiPage
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/LinksUpdate
	 *
	 * @param LinksUpdate $lu
	 * @return bool
	 */
	public static function onLinksUpdate( LinksUpdate $lu ) {
		if ( $lu->getTitle()->isTalkPage() ) {
			$parserOutput = $lu->getParserOutput();
			$sections = $parserOutput->getSections();
			$numTopics = 0;
			foreach ( $sections as $section ) {
				if ( $section['toclevel'] == 1 ) {
					$numTopics += 1;
				}
			}
			if ( $numTopics ) {
				$lu->mProperties['page_top_level_section_count'] = $numTopics;
			}
		}

		return true;
	}

	/**
	 * Enables the global booleans $wgHTMLFormAllowTableFormat and $wgUseMediaWikiUIEverywhere
	 * for mobile users.
	 */
	private static function enableMediaWikiUI() {
		// FIXME: Temporary variables, will be deprecated in core in the future
		global $wgHTMLFormAllowTableFormat, $wgUseMediaWikiUIEverywhere;

		$mobileContext = MobileContext::singleton();

		if ( $mobileContext->shouldDisplayMobileView() && !$mobileContext->isBlacklistedPage() ) {
			// Force non-table based layouts (see bug 63428)
			$wgHTMLFormAllowTableFormat = false;
			// Turn on MediaWiki UI styles so special pages with form are styled.
			// FIXME: Remove when this becomes the default.
			$wgUseMediaWikiUIEverywhere = true;
		}
	}

	/**
	 * RequestContextCreateSkin hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/RequestContextCreateSkin
	 *
	 * @param IContextSource $context
	 * @param Skin $skin
	 * @return bool
	 */
	public static function onRequestContextCreateSkin( $context, &$skin ) {
		// FIXME: This shouldn't be a global, it should be possible for other extensions
		// to set this via a static variable or set function in ULS
		global $wgULSPosition;

		$mobileContext = MobileContext::singleton();

		$mobileContext->doToggling();
		if ( !$mobileContext->shouldDisplayMobileView()
			|| $mobileContext->isBlacklistedPage()
		) {
			return true;
		}

		// enable wgUseMediaWikiUIEverywhere
		self::enableMediaWikiUI();

		// FIXME: Remove hack around Universal Language selector bug 57091
		$wgULSPosition = 'none';

		// Handle any X-Analytics header values in the request by adding them
		// as log items. X-Analytics header values are serialized key=value
		// pairs, separated by ';', used for analytics purposes.
		if ( $xanalytics = $mobileContext->getRequest()->getHeader( 'X-Analytics' ) ) {
			$xanalytics_arr = explode( ';', $xanalytics );
			if ( count( $xanalytics_arr ) > 1 ) {
				foreach ( $xanalytics_arr as $xanalytics_item ) {
					$mobileContext->addAnalyticsLogItemFromXAnalytics( $xanalytics_item );
				}
			} else {
				$mobileContext->addAnalyticsLogItemFromXAnalytics( $xanalytics );
			}
		}

		// log whether user is using beta/stable
		$mobileContext->logMobileMode();

		$skinName = $mobileContext->getMFConfig()->get( 'MFDefaultSkinClass' );
		$betaSkinName = $skinName . 'Beta';
		// Force beta for test mode to sure all modules can run
		$name = $context->getTitle()->getDBkey();
		$inTestMode =
			$name === SpecialPage::getTitleFor( 'JavaScriptTest', 'qunit' )->getDBkey();
		// FIXME: remove the migration code below at some point.
		// alpha no more, fallback to beta
		if ( $mobileContext->getMobileMode() === 'alpha' ) {
			$mobileContext->setMobileMode( 'beta' );
		}
		if ( $mobileContext->isBetaGroupMember() && class_exists( $betaSkinName ) ) {
			$skinName = $betaSkinName;
		}
		$skin = new $skinName( $context );

		return false;
	}

	/**
	 * MediaWikiPerformAction hook handler (enable mwui for all pages)
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/MediaWikiPerformAction
	 *
	 * @param OutputPage $output
	 * @param Article $article
	 * @param Title $title
	 * @param User $user
	 * @param RequestContext $request
	 * @param MediaWiki $wiki
	 * @return bool
	 */
	public static function onMediaWikiPerformAction( $output, $article, $title,
		$user, $request, $wiki
	) {
		self::enableMediaWikiUI();

		// don't prevent performAction to do anything
		return true;
	}

	/**
	 * SkinTemplateOutputPageBeforeExec hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateOutputPageBeforeExec
	 *
	 * Adds a link to view the current page in 'mobile view' to the desktop footer.
	 *
	 * @param SkinTemplate $skin
	 * @param QuickTemplate $tpl
	 * @return bool
	 */
	public static function onSkinTemplateOutputPageBeforeExec( &$skin, &$tpl ) {
		$title = $skin->getTitle();
		$context = MobileContext::singleton();

		if ( !$context->isBlacklistedPage() ) {
			$footerlinks = $tpl->data['footerlinks'];
			$args = $skin->getRequest()->getQueryValues();
			// avoid title being set twice
			unset( $args['title'] );
			unset( $args['useformat'] );
			$args['mobileaction'] = 'toggle_view_mobile';

			$mobileViewUrl = $title->getFullURL( $args );
			$mobileViewUrl = MobileContext::singleton()->getMobileUrl( $mobileViewUrl );

			$link = Html::element( 'a',
				array( 'href' => $mobileViewUrl, 'class' => 'noprint stopMobileRedirectToggle' ),
				wfMessage( 'mobile-frontend-view' )->text()
			);
			$tpl->set( 'mobileview', $link );
			$footerlinks['places'][] = 'mobileview';
			$tpl->set( 'footerlinks', $footerlinks );
		}

		return true;
	}

	/**
	 * BeforePageRedirect hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageRedirect
	 *
	 * Ensures URLs are handled properly for select special pages.
	 * @param OutputPage $out
	 * @param string $redirect
	 * @param string $code
	 * @return bool
	 */
	public static function onBeforePageRedirect( $out, &$redirect, &$code ) {
		$context = MobileContext::singleton();
		$shouldDisplayMobileView = $context->shouldDisplayMobileView();
		if ( !$shouldDisplayMobileView ) {
			return true;
		}

		// Bug 43123: force mobile URLs only for local redirects
		if ( $context->isLocalUrl( $redirect ) ) {
			$out->addVaryHeader( 'X-Subdomain' );
			$out->addVaryHeader( 'X-CS' );
			$redirect = $context->getMobileUrl( $redirect );
		}

		return true;
	}

	/**
	 * DiffViewHeader hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/DiffViewHeader
	 *
	 * Redirect Diff page to mobile version if appropriate
	 *
	 * @param DifferenceEngine $diff DifferenceEngine object that's calling
	 * @param Revision $oldRev Revision object of the "old" revision (may be null/invalid)
	 * @param Revision $newRev Revision object of the "new" revision
	 * @return bool
	 */
	public static function onDiffViewHeader( $diff, $oldRev, $newRev ) {
		$context = MobileContext::singleton();

		// Only do redirects to MobileDiff if user is in mobile view and it's not a special page
		if ( $context->shouldDisplayMobileView()
			&& !$diff->getContext()->getTitle()->isSpecialPage()
		) {
			$output = $context->getOutput();
			$newRevId = $newRev->getId();

			// The MobileDiff page currently only supports showing a single revision, so
			// only redirect to MobileDiff if we are sure this isn't a multi-revision diff.
			if ( $oldRev ) {
				// Get the revision immediately before the new revision
				$prevRev = $newRev->getPrevious();
				if ( $prevRev ) {
					$prevRevId = $prevRev->getId();
					$oldRevId = $oldRev->getId();
					if ( $prevRevId === $oldRevId ) {
						$output->redirect( SpecialPage::getTitleFor( 'MobileDiff', $newRevId )->getFullURL() );
					}
				}
			} else {
				$output->redirect( SpecialPage::getTitleFor( 'MobileDiff', $newRevId )->getFullURL() );
			}
		}

		return true;
	}

	/**
	 * ResourceLoaderTestModules hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderTestModules
	 *
	 * @param array $testModules
	 * @param ResourceLoader $resourceLoader
	 * @return bool
	 */
	public static function onResourceLoaderTestModules( array &$testModules,
		ResourceLoader &$resourceLoader
	) {
		// FIXME: Global core variable don't use it.
		global $wgResourceModules;
		$testFiles = array();
		$dependencies = array();
		$localBasePath = dirname( __DIR__ );

		// find test files for every RL module
		foreach ( $wgResourceModules as $key => $module ) {
			$hasTests = false;
			if ( substr( $key, 0, 7 ) === 'mobile.' && isset( $module['scripts'] ) ) {
				foreach ( $module['scripts'] as $script ) {
					$testFile = 'tests/' . dirname( $script ) . '/test_' . basename( $script );
					// For resources folder
					$testFile = str_replace( 'tests/resources/', 'tests/qunit/', $testFile );
					// if a test file exists for a given JS file, add it
					if ( file_exists( $localBasePath . '/' . $testFile ) ) {
						$testFiles[] = $testFile;
						$hasTests = true;
					}
				}

				// if test files exist for given module, create a corresponding test module
				if ( $hasTests ) {
					$dependencies[] = $key;
				}
			}
		}

		$testModule = array(
			'dependencies' => $dependencies,
			'templates' => array(
				'section.hogan' => 'tests/qunit/tests.mobilefrontend/section.hogan',
				'issues.hogan' => 'tests/qunit/tests.mobilefrontend/issues.hogan',
			),
			'localBasePath' => $localBasePath,
			'remoteExtPath' => 'MobileFrontend',
			'targets' => array( 'mobile', 'desktop' ),
			'scripts' => $testFiles,
		);

		// Expose templates module
		$testModules['qunit']["tests.mobilefrontend"] = $testModule;

		return true;
	}

	/**
	 * GetCacheVaryCookies hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GetCacheVaryCookies
	 *
	 * @param OutputPage $out
	 * @param array $cookies
	 * @return bool
	 */
	public static function onGetCacheVaryCookies( $out, &$cookies ) {
		$context = MobileContext::singleton();
		$mobileUrlTemplate = $context->getMobileUrlTemplate();

		// Enables mobile cookies on wikis w/o mobile domain
		$cookies[] = MobileContext::USEFORMAT_COOKIE_NAME;
		// Don't redirect to mobile if user had explicitly opted out of it
		$cookies[] = 'stopMobileRedirect';

		if ( $context->shouldDisplayMobileView() || !$mobileUrlTemplate ) {
			$cookies[] = 'optin'; // beta cookie
			$cookies[] = 'disableImages';
		}
		// Redirect people who want so from HTTP to HTTPS. Ideally, should be
		// only for HTTP but we don't vary on protocol.
		$cookies[] = 'forceHTTPS';
		return true;
	}

	/**
	 * ResourceLoaderGetConfigVars hook handler
	 * This should be used for variables which vary with the html
	 * and for variables this should work cross skin including anonymous users
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderGetConfigVars
	 *
	 * @param array $vars
	 * @return boolean
	 */
	public static function onResourceLoaderGetConfigVars( &$vars ) {
		$context = MobileContext::singleton();
		$config = $context->getMFConfig();

		// Get the licensing agreement that is displayed in the uploading interface.
		$wgMFUploadLicense = SkinMinerva::getLicense( 'upload' );
		$vars += array(
			'wgMFNearbyEndpoint' => $config->get( 'MFNearbyEndpoint' ),
			'wgMFThumbnailSizes' => array(
				'tiny' =>  MobilePage::TINY_IMAGE_WIDTH,
				'small' =>  MobilePage::SMALL_IMAGE_WIDTH,
			),
			'wgMFContentNamespace' => $config->get( 'MFContentNamespace' ),
			'wgMFEditorOptions' => $config->get( 'MFEditorOptions' ),
			'wgMFLicense' => SkinMinerva::getLicense( 'editor' ),
			'wgMFUploadLicenseLink' => $wgMFUploadLicense['link'],
		);

		// add CodeMirror specific things, if it is installed (for CodeMirror editor)
		if ( class_exists( 'CodeMirrorHooks' ) ) {
			$vars += CodeMirrorHooks::getGlobalVariables( MobileContext::singleton() );
			$vars['wgMFCodeMirror'] = true;
		}

		return true;
	}

	/**
	 * Hook for SpecialPage_initList in SpecialPageFactory.
	 *
	 * @param array $list list of special page classes
	 * @return bool hook return value
	 */
	public static function onSpecialPage_initList( &$list ) {
		$ctx = MobileContext::singleton();
		// Perform substitutions of pages that are unsuitable for mobile
		// FIXME: Upstream these changes to core.
		if ( $ctx->shouldDisplayMobileView() ) {
			// Replace the standard watchlist view with our custom one
			$list['Watchlist'] = 'SpecialMobileWatchlist';
			$list['EditWatchlist'] = 'SpecialMobileEditWatchlist';
			$list['Preferences'] = 'SpecialMobilePreferences';

			/* Special:MobileContributions redefines Special:History in
			 * such a way that for Special:Contributions/Foo, Foo is a
			 * username (in Special:History/Foo, Foo is a page name).
			 * Redirect people here as this is essential
			 * Special:Contributions without the bells and whistles.
			 */
			$list['Contributions'] = 'SpecialMobileContributions';
		}
		// add Special:Nearby only, if Nearby is activated
		if ( $ctx->getMFConfig()->get( 'MFNearby' ) ) {
			$list['Nearby'] = 'SpecialNearby';
		}
		return true;
	}

	/**
	 * ListDefinedTags and ChangeTagsListActive hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ListDefinedTags
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ChangeTagsListActive
	 *
	 * @param array $tags
	 * @return bool
	 */
	public static function onListDefinedTags( &$tags ) {
		$tags[] = 'mobile edit';
		$tags[] = 'mobile web edit';
		return true;
	}

	/**
	 * RecentChange_save hook handler that tags mobile changes
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/RecentChange_save
	 *
	 * @param RecentChange $rc
	 * @return bool
	 */
	public static function onRecentChange_save( RecentChange $rc ) {
		$context = MobileContext::singleton();
		$userAgent = $context->getRequest()->getHeader( "User-agent" );
		$logType = $rc->getAttribute( 'rc_log_type' );
		// Only log edits and uploads
		if ( $context->shouldDisplayMobileView() && ( $logType === 'upload' || is_null( $logType ) ) ) {
			$rcId = $rc->getAttribute( 'rc_id' );
			$revId = $rc->getAttribute( 'rc_this_oldid' );
			$logId = $rc->getAttribute( 'rc_logid' );
			ChangeTags::addTags( 'mobile edit', $rcId, $revId, $logId );
			// Tag as mobile web edit specifically, if it isn't coming from the apps
			if ( strpos( $userAgent, 'WikipediaApp/' ) !== 0 ) {
				ChangeTags::addTags( 'mobile web edit', $rcId, $revId, $logId );
			}
		}
		return true;
	}

	/**
	 * AbuseFilter-GenerateUserVars hook handler that adds a user_mobile variable.
	 * Altering the variables generated for a specific user
	 *
	 * @see hooks.txt in AbuseFilter extension
	 * @param AbuseFilterVariableHolder $vars object to add vars to
	 * @param User $user object
	 * @return bool
	 */
	public static function onAbuseFilterGenerateUserVars( $vars, $user ) {
		$context = MobileContext::singleton();

		if ( $context->shouldDisplayMobileView() ) {
			$vars->setVar( 'user_mobile', true );
		} else {
			$vars->setVar( 'user_mobile', false );
		}

		return true;
	}

	/**
	 * AbuseFilter-builder hook handler that adds user_mobile variable to list
	 *  of valid vars
	 *
	 * @param array $builder Array in AbuseFilter::getBuilderValues to add to.
	 * @return bool
	 */
	public static function onAbuseFilterBuilder( &$builder ) {
		$builder['vars']['user_mobile'] = 'user-mobile';
		return true;
	}


	/**
	 * Invocation of hook SpecialPageBeforeExecute
	 *
	 * We use this hook to ensure that login/account creation pages
	 * are redirected to HTTPS if they are not accessed via HTTPS and
	 * $wgSecureLogin == true - but only when using the
	 * mobile site.
	 *
	 * @param SpecialPage $special
	 * @param string $subpage
	 * @return bool
	 */
	public static function onSpecialPageBeforeExecute( SpecialPage $special, $subpage ) {
		$mobileContext = MobileContext::singleton();
		$isMobileView = $mobileContext->shouldDisplayMobileView();
		$context = $special->getContext();
		$out = $context->getOutput();
		$secureLogin = $context->getConfig()->get( 'SecureLogin' );
		$request = $special->getContext()->getRequest();
		$skin = $out->getSkin()->getSkinName();

		$name = $special->getName();

		// Ensure desktop version of Special:Preferences page gets mobile targeted modules
		// FIXME: Upstream to core (?)
		if ( $skin === 'minerva' ) {
			if ( $name === 'Preferences' ) {
				$out->addModules( 'skins.minerva.special.preferences.scripts' );
			}

			// Add default warning message to Special:UserLogin and Special:UserCreate
			// if no warning message set.
			if (
				$name === 'Userlogin' &&
				!$request->getVal( 'warning', null ) &&
				!$context->getUser()->isLoggedIn()
			) {
				$request->setVal( 'warning', 'mobile-frontend-generic-login-new' );
			}
		}

		if ( $isMobileView ) {
			if ( $name === 'Search' ) {
				$out->addModuleStyles( 'skins.minerva.special.search.styles' );
			} elseif ( $name === 'Userlogin' ) {
				$out->addModuleStyles( 'skins.minerva.special.userlogin.styles' );
				$out->addModules( 'mobile.special.userlogin.scripts' );
				// make sure we're on https if we're supposed to be and currently aren't.
				// most of this is lifted from https redirect code in SpecialUserlogin::execute()
				// also, checking for 'https' in $wgServer is a little funky, but this is what
				// is done on the WMF cluster (see config in CommonSettings.php)
				if ( $secureLogin && WebRequest::detectProtocol() != 'https' ) {
					// get the https url and redirect
					$query = $special->getContext()->getRequest()->getQueryValues();
					if ( isset( $query['title'] ) )  {
						unset( $query['title'] );
					}
					$url = $mobileContext->getMobileUrl(
						$special->getFullTitle()->getFullURL( $query ),
						true
					);
					$special->getContext()->getOutput()->redirect( $url );
				}
			}
		}

		return true;
	}

	/**
	 * UserLoginComplete hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/UserLoginComplete
	 *
	 * Used here to handle watchlist actions made by anons to be handled after
	 * login or account creation.
	 *
	 * @param User $currentUser
	 * @param string $injected_html
	 * @return bool
	 */
	public static function onUserLoginComplete( &$currentUser, &$injected_html ) {
		$context = MobileContext::singleton();
		if ( !$context->shouldDisplayMobileView() ) {
			return true;
		}

		// If 'watch' is set from the login form, watch the requested article
		$watch = $context->getRequest()->getVal( 'watch' );
		if ( !is_null( $watch ) ) {
			$title = Title::newFromText( $watch );
			// protect against watching special pages (these cannot be watched!)
			if ( !is_null( $title ) && !$title->isSpecialPage() ) {
				WatchAction::doWatch( $title, $currentUser );
			}
		}
		return true;
	}

	/**
	 * Decide if the login/usercreate page should be overwritten by a mobile only
	 * special specialpage. If not, do some changes to the template.
	 * @param QuickTemplate $tpl Login or Usercreate template
	 * @param String $mode Is this function called in context of UserCreate or UserLogin?
	 */
	public static function changeUserLoginCreateForm( &$tpl ) {
		$context = MobileContext::singleton();
		// otherwise just(tm) add a logoheader, if there is any
		$mfLogo = $context->getMFConfig()
			->get( 'MobileFrontendLogo' );

		// do nothing in desktop mode
		if ( $context->shouldDisplayMobileView() && $mfLogo ) {
			$tpl->extend(
				'formheader',
				Html::openElement(
					'div',
					array( 'class' => 'watermark' )
				) .
				Html::element( 'img',
					array(
						'src' => $mfLogo,
						'alt' => '',
					)
				) .
				Html::closeElement( 'div' )
			);
		}
	}

	/**
	 * UserLoginForm hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/UserLoginForm
	 *
	 * @param QuickTemplate $template Login form template object
	 * @return bool
	 */
	public static function onUserLoginForm( &$template ) {
		self::changeUserLoginCreateForm( $template );
		return true;
	}

	/**
	 * UserCreateForm hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/UserCreateForm
	 *
	 * @param QuickTemplate $template Account creation form template object
	 * @return bool
	 */
	public static function onUserCreateForm( &$template ) {
		self::changeUserLoginCreateForm( $template );
		return true;
	}

	/**
	 * BeforePageDisplay hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 *
	 * @param OutputPage $out
	 * @param Skin $sk
	 * @return bool
	 */
	public static function onBeforePageDisplay( &$out, &$sk ) {
		$context = MobileContext::singleton();
		$config = $context->getMFConfig();
		$mfEnableXAnalyticsLogging = $config->get( 'MFEnableXAnalyticsLogging' );
		$mfAppPackageId = $config->get( 'MFAppPackageId' );
		$mfAppScheme = $config->get( 'MFAppScheme' );
		$mfNoIndexPages = $config->get( 'MFNoindexPages' );
		$mfMobileUrlTemplate = $context->getMobileUrlTemplate();
		$tabletSize = $config->get( 'MFDeviceWidthTablet' );

		$title = $sk->getTitle();
		$request = $context->getRequest();
		# Add deep link to a mobile app specified by $wgMFAppScheme
		if ( ( $mfAppPackageId !== false ) && ( $title->isContentPage() )
			&& ( $request->getRawQueryString() === '' )
		) {
			$fullUrl = $title->getFullURL();
			$mobileUrl = $context->getMobileUrl( $fullUrl );
			$path = preg_replace( "/^([a-z]+:)?(\/)*/", '', $mobileUrl, 1 );

			$scheme = 'http';
			if ( $mfAppScheme !== false ) {
				$scheme = $mfAppScheme;
			} else {
				$protocol = $request->getProtocol();
				if ( $protocol != '' ) {
					$scheme = $protocol;
				}
			}

			$hreflink = 'android-app://' . $mfAppPackageId . '/' . $scheme . '/' . $path;
			$out->addLink( array( 'rel' => 'alternate', 'href' => $hreflink ) );
		}

		// an canonical/alternate link is only useful, if the mobile and desktop URL are different
		// and $wgMFNoindexPages needs to be true
		if ( $mfMobileUrlTemplate && $mfNoIndexPages ) {
			if ( !$context->shouldDisplayMobileView() ) {
				// add alternate link to desktop sites - bug T91183
				$desktopUrl = $title->getFullUrl();
				$link = array(
					'rel' => 'alternate',
					'media' => 'only screen and (max-width: ' . $tabletSize . 'px)',
					'href' => $context->getMobileUrl( $desktopUrl ),
				);
			} else {
				// add canonical link to mobile pages, instead of noindex - bug T91183
				$link = array(
					'rel' => 'canonical',
					'href' => $title->getFullUrl(),
				);
			}
			$out->addLink( $link );
		}

		// Set X-Analytics HTTP response header if necessary
		if ( $context->shouldDisplayMobileView() ) {
			$analyticsHeader = ( $mfEnableXAnalyticsLogging ? $context->getXAnalyticsHeader() : false );
			if ( $analyticsHeader ) {
				$resp = $out->getRequest()->response();
				$resp->header( $analyticsHeader );
			}

			// in mobile view: always add vary header
			$out->addVaryHeader( 'Cookie' );
		}

		return true;
	}

	/**
	 * CustomEditor hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/CustomEditor
	 *
	 * @param Article $article
	 * @param User $user
	 * @return bool
	 */
	public static function onCustomEditor( $article, $user ) {
		$context = MobileContext::singleton();

		// redirect to mobile editor instead of showing desktop editor
		if ( $context->shouldDisplayMobileView() ) {
			$output = $context->getOutput();
			$data = $output->getRequest()->getValues();
			// Unset these to avoid a redirect loop but make sure we pass other
			// parameters to edit e.g. undo actions
			unset( $data['action'] );
			unset( $data['title'] );

			$output->redirect( SpecialPage::getTitleFor( 'MobileEditor', $article->getTitle() )
				->getFullURL( $data ) );
			return false;
		}

		return true;
	}

	/**
	 * GetPreferences hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GetPreferences
	 *
	 * @param User $user
	 * @param array $preferences
	 *
	 * @return bool
	 */
	public static function onGetPreferences( $user, &$preferences ) {
		$mfEnableMinervaBetaFeature = MobileContext::singleton()->getMFConfig()
			->get( 'MFEnableMinervaBetaFeature' );
		$definition = array(
			'type' => 'api',
			'default' => '',
		);
		$preferences[SpecialMobileWatchlist::FILTER_OPTION_NAME] = $definition;
		$preferences[SpecialMobileWatchlist::VIEW_OPTION_NAME] = $definition;

		// Remove the Minerva skin from the preferences unless Minerva has been enabled in
		// BetaFeatures.
		if ( !class_exists( 'BetaFeatures' )
			|| !BetaFeatures::isFeatureEnabled( $user, 'betafeatures-minerva' )
			|| !$mfEnableMinervaBetaFeature
		) {
			// Preference key/values are backwards. The value is the name of the skin. The
			// key is the text+links to display.
			if ( !empty( $preferences['skin']['options'] ) ) {
				$key = array_search( 'minerva', $preferences['skin']['options'] );
				unset( $preferences['skin']['options'][$key] );
			}
		}

		return true;
	}

	/**
	 * GetBetaFeaturePreferences hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GetPreferences
	 *
	 * @param User $user
	 * @param array $preferences
	 *
	 * @return bool
	 */
	public static function onGetBetaFeaturePreferences( $user, &$preferences ) {
		$context = MobileContext::singleton();
		$extensionAssetsPath = $context->getConfig()->get( 'ExtensionAssetsPath' );
		$mfEnableMinervaBetaFeature = $context->getMFConfig()->get( 'MFEnableMinervaBetaFeature' );

		if ( $mfEnableMinervaBetaFeature ) {
			// Enable the mobile skin on desktop
			$preferences['betafeatures-minerva'] = array(
				'label-message' => 'beta-feature-minerva',
				'desc-message' => 'beta-feature-minerva-description',
				'info-link' => '//www.mediawiki.org/wiki/Beta_Features/Minerva',
				'discussion-link' => '//www.mediawiki.org/wiki/Talk:Beta_Features/Minerva',
				'screenshot' => array(
					'ltr' => "$extensionAssetsPath/MobileFrontend/images/BetaFeatures/minerva-ltr.svg",
					'rtl' => "$extensionAssetsPath/MobileFrontend/images/BetaFeatures/minerva-rtl.svg",
				),
			);
		}

		return true;
	}

	/**
	 * Gadgets::allowLegacy hook handler
	 *
	 * @return bool
	 */
	public static function onAllowLegacyGadgets() {
		return !MobileContext::singleton()->shouldDisplayMobileView();
	}

	/**
	 * UnitTestsList hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/UnitTestsList
	 *
	 * @param array $files
	 * @return bool
	 */
	public static function onUnitTestsList( &$files ) {
		$files[] = __DIR__ . '/../tests/phpunit';

		return true;
	}

	/**
	 * CentralAuthLoginRedirectData hook handler
	 * Saves mobile host so that the CentralAuth wiki could redirect back properly
	 *
	 * @see CentralAuthHooks::doCentralLoginRedirect in CentralAuth extension
	 * @param CentralAuthUser $centralUser
	 * @param array $data
	 *
	 * @return bool
	 */
	public static function onCentralAuthLoginRedirectData( $centralUser, &$data ) {
		$context = MobileContext::singleton();
		$server = $context->getConfig()->get( 'Server' );
		if ( $context->shouldDisplayMobileView() ) {
			$data['mobileServer'] = $context->getMobileUrl( $server );
		}
		return true;
	}

	/**
	 * CentralAuthSilentLoginRedirect hook handler
	 * Points redirects from CentralAuth wiki to mobile domain if user has logged in from it
	 * @see SpecialCentralLogin in CentralAuth extension
	 * @param CentralAuthUser $centralUser
	 * @param string $url to redirect to
	 * @param array $info token information
	 *
	 * @return bool
	 */
	public static function onCentralAuthSilentLoginRedirect( $centralUser, &$url, $info ) {
		if ( isset( $info['mobileServer'] ) ) {
			$mobileUrlParsed = wfParseUrl( $info['mobileServer'] );
			$urlParsed = wfParseUrl( $url );
			$urlParsed['host'] = $mobileUrlParsed['host'];
			$url = wfAssembleUrl( $urlParsed );
		}
		return true;
	}

	/**
	 * ResourceLoaderRegisterModules hook handler
	 *
	 * Registers the mobile.loggingSchemas module
	 * without a dependency on the EventLogging schema modules so that calls to the various log
	 * functions are effectively NOPs.  And registers VisualEditor and Echo related modules.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderRegisterModules
	 *
	 * @param ResourceLoader &$resourceLoader The ResourceLoader object
	 * @return bool Always true
	 */
	public static function onResourceLoaderRegisterModules( ResourceLoader &$resourceLoader ) {
		self::registerMobileLoggingSchemasModule( $resourceLoader );
		$config = MobileContext::singleton()->getMFConfig();

		// add VisualEditor related modules only, if VisualEditor seems to be installed - T85007
		if ( class_exists( 'VisualEditorHooks' ) ) {
			$resourceLoader->register( $config->get( 'MobileVEModules' ) );
		}

		// add Echo, if it's installed
		if ( class_exists( 'MWEchoNotifUser' ) ) {
			$resourceLoader->register( $config->get( 'MobileEchoModules' ) );
		};

		return true;
	}

	/**
	 * ResourceLoaderGetLessVars hook handler
	 *
	 * Add the context-based less variables.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderGetLessVars
	 * @param array &$lessVars Variables already added
	 */
	public static function onResourceLoaderGetLessVars( &$lessVars ) {
		$config = MobileContext::singleton()->getMFConfig();
		$lessVars = array_merge( $lessVars,
			array(
				'wgMFDeviceWidthTablet' => "{$config->get( 'MFDeviceWidthTablet' )}px",
				'wgMFDeviceWidthMobileSmall' => "{$config->get( 'MFDeviceWidthMobileSmall' )}px",
				'wgMFThumbnailTiny' =>  MobilePage::TINY_IMAGE_WIDTH . 'px',
				'wgMFThumbnailSmall' =>  MobilePage::SMALL_IMAGE_WIDTH . 'px'
			)
		);
	}

	/**
	 * Returns an array of schema names mapped to a schema revision ID
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderGetLessVars
	 * @param array Array of schema names associated to revision IDs
	 */
	private static function getEventLoggingSchemas() {
		return array(
			'MobileWebBrowse' => 12119641,
			'MobileWebDiffClickTracking' => 10720373,
			'MobileWebMainMenuClickTracking' => 11568715,
			'MobileWebSearch' => 12054448,
			'MobileWebUIClickTracking' => 10742159,
			'MobileWebWatching' => 11761466,
			'MobileWebWatchlistClickTracking' => 10720361,
		);
	}

	/**
	 * EventLoggingRegisterSchemas hook handler.
	 *
	 * Registers our EventLogging schemas so that they can be converted to
	 * ResourceLoaderSchemaModules by the EventLogging extension as the
	 * mobile.loggingSchemas module.
	 *
	 * If the module has already been registered in
	 * onResourceLoaderRegisterModules, then it is overwritten.
	 *
	 * @param array $schemas The schemas currently registered with the EventLogging
	 *  extension
	 * @return bool Always true
	 */
	public static function onEventLoggingRegisterSchemas( &$schemas ) {
		$schemas += self::getEventLoggingSchemas();
		return true;
	}

	/**
	 * Registers the mobile.loggingSchemas module with optional dependency on the
	 * EventLogging schema modules registered in onEventLoggingRegisterSchemas
	 *
	 * @param ResourceLoader &$resourceLoader The ResourceLoader object
	 */
	private static function registerMobileLoggingSchemasModule( $resourceLoader ) {
		$mfResourceFileModuleBoilerplate = MobileContext::singleton()
			->getMFConfig()->get( 'MFResourceFileModuleBoilerplate' );

		$scripts = array(
			'resources/mobile.loggingSchemas/SchemaEdit.js',
			'resources/mobile.loggingSchemas/SchemaMobileWeb.js',
			'resources/mobile.loggingSchemas/SchemaMobileWebClickTracking.js',
			'resources/mobile.loggingSchemas/SchemaMobileWebWatching.js',
			'resources/mobile.loggingSchemas/SchemaMobileWebSearch.js',
			'resources/mobile.loggingSchemas/SchemaMobileWebBrowse.js',
		);

		$schemaModules = array();
		if ( class_exists( 'EventLogging' ) ) {
			$schemaModules = array_map(
				function ( $schema ) {
					return "schema.{$schema}";
				},
				array_keys( self::getEventLoggingSchemas() )
			);
		}

		$loggingSchemasModule = $mfResourceFileModuleBoilerplate + array(
			'dependencies' => array_merge( array(
				'mobile.startup',
				'mobile.settings',
			), $schemaModules ),
			'scripts' => $scripts,
		);

		$resourceLoader->register( array( 'mobile.loggingSchemas' => $loggingSchemasModule ) );
	}

	/**
	 * OutputPageParserOutput hook handler
	 * Disables TOC in output before it grabs HTML
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageParserOutput
	 *
	 * @param OutputPage $outputPage
	 * @param ParserOutput $po
	 * @return bool
	 */
	public static function onOutputPageParserOutput( $outputPage, ParserOutput $po ) {
		global $wgMFWikibaseImageCategory;

		$context = MobileContext::singleton();
		$mfUseWikibaseDescription = $context->getMFConfig()->get( 'MFUseWikibaseDescription' );

		if ( $context->shouldDisplayMobileView() ) {
			$outputPage->enableTOC( false );
			$outputPage->setProperty( 'MinervaTOC', $po->getTOCHTML() !== '' );

			if ( $mfUseWikibaseDescription && $context->isBetaGroupMember() ) {
				$item = $po->getProperty( 'wikibase_item' );
				if ( $item ) {
					$desc = ExtMobileFrontend::getWikibaseDescription( $item );
					$category =  ExtMobileFrontend::getWikibasePropertyValue( $item, $wgMFWikibaseImageCategory );
					if ( $desc ) {
						$outputPage->setProperty( 'wgMFDescription', $desc );
					}
					if ( $category ) {
						$outputPage->setProperty( 'wgMFImagesCategory', $category );
					}
				}
			}
		}
		return true;
	}

	/**
	 * HTMLFileCache::useFileCache hook handler
	 * Disables file caching for mobile pageviews
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/HTMLFileCache::useFileCache
	 *
	 * @return bool
	 */
	public static function onHTMLFileCache_useFileCache() {
		return !MobileContext::singleton()->shouldDisplayMobileView();
	}

	/**
	 * LoginFormValidErrorMessages hook handler to promote MF specific error message be valid.
	 *
	 * @param array $messages Array of already added messages
	 */
	public static function onLoginFormValidErrorMessages( &$messages ) {
		$messages = array_merge( $messages,
			array(
				'mobile-frontend-watchlist-signup-action', // watchstart sign up CTA
				'mobile-frontend-watchlist-purpose', // Watchlist and watchstar sign in CTA
				'mobile-frontend-donate-image-anon', // Uploads link
				'mobile-frontend-edit-login-action', // Edit button sign in CTA
				'mobile-frontend-edit-signup-action', // Edit button sign-up CTA
				'mobile-frontend-donate-image-login-action',
				'mobile-frontend-generic-login-new', // default message
			)
		);
	}
}

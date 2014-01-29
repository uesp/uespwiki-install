<?php
/**
 * MonoBook nouveau
 *
 * Translated from gwicke's previous TAL template version to remove
 * dependency on PHPTAL.
 *
 * @todo document
 * @file
 * @ingroup Skins
 */

if( !defined( 'MEDIAWIKI' ) )
	die( -1 );

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @todo document
 * @ingroup Skins
 */
class SkinMonoBook extends SkinTemplate {
	/** Using monobook. */
	var $skinname = 'monobook', $stylename = 'monobook',
		$template = 'MonoBookTemplate', $useHeadElement = true;

	/**
	 * @param $out OutputPage
	 */
	function setupSkinUserCss( OutputPage $out ) {
		global $wgHandheldStyle;
		parent::setupSkinUserCss( $out );

		$out->addModuleStyles( 'skins.monobook' );

		// Ugh. Can't do this properly because $wgHandheldStyle may be a URL
		if( $wgHandheldStyle ) {
			// Currently in testing... try 'chick/main.css'
			$out->addStyle( $wgHandheldStyle, 'handheld' );
		}

		// TODO: Migrate all of these
		$out->addStyle( 'monobook/IE60Fixes.css', 'screen', 'IE 6' );
		$out->addStyle( 'monobook/IE70Fixes.css', 'screen', 'IE 7' );

	}
}

/**
 * @todo document
 * @ingroup Skins
 */
class MonoBookTemplate extends BaseTemplate {

	/**
	 * Template filter callback for MonoBook skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 *
	 * @access private
	 */
	function execute() {
		global $wgUser;
		// Suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();

		$this->html( 'headelement' );
?><div id="globalWrapper">
<div id="column-content"><div id="content">

<!-- BEGIN UESP -->
<?php
        if ( ! $wgUser->isLoggedIn() ) {
?>
        <!-- WikiLeaderboardTop -->
<div class='center' style='width:728px; height:105px; overflow: hidden; margin-left: auto; margin-right: auto;'>

<form method="post" target="_blank" id="form_topad" action="http://www.uesp.net/adreport.php">
<input name="UespAdContent" type="hidden" value="unknown">
<input name="UespAdId" type="hidden" value="div-gpt-ad-1344720487368-2" />

<div id='div-gpt-ad-1344720487368-2' style='width:728px; height:90px;'>
<script type='text/javascript'>
googletag.cmd.push(function() { googletag.display('div-gpt-ad-1344720487368-2');
 });
</script>
</div>
<div style='float: left;'>
<small><a href='/wiki/UESPWiki:Site_Support'>What is this Ad?</a></small>
</div>
<div style='float: right;'>
<small><a href="javascript:submitReportAdForm('div-gpt-ad-1344720487368-2', 'form_topad')">Report Ad</a></small>
</div>
</form>
</div>

<?php
        }
?>
<!-- END UESP -->
	<a id="top"></a>
	<?php if($this->data['sitenotice']) { ?><div id="siteNotice"><?php $this->html('sitenotice') ?></div><?php } ?>

	<h1 id="firstHeading" class="firstHeading"><span dir="auto"><?php $this->html('title') ?></span></h1>
	<div id="bodyContent" class="mw-body">
		<div id="siteSub"><?php $this->msg('tagline') ?></div>
		<div id="contentSub"<?php $this->html('userlangattributes') ?>><?php $this->html('subtitle') ?></div>
<?php if($this->data['undelete']) { ?>
		<div id="contentSub2"><?php $this->html('undelete') ?></div>
<?php } ?><?php if($this->data['newtalk'] ) { ?>
		<div class="usermessage"><?php $this->html('newtalk')  ?></div>
<?php } ?><?php if($this->data['showjumplinks']) { ?>
		<div id="jump-to-nav" class="mw-jump"><?php $this->msg('jumpto') ?> <a href="#column-one"><?php $this->msg('jumptonavigation') ?></a>, <a href="#searchInput"><?php $this->msg('jumptosearch') ?></a></div>
<?php } ?>
		<!-- start content -->
<?php $this->html('bodytext') ?>
		<?php if($this->data['catlinks']) { $this->html('catlinks'); } ?>
		<!-- end content -->
		<?php if($this->data['dataAfterContent']) { $this->html ('dataAfterContent'); } ?>
		<div class="visualClear"></div>
	</div>
</div>
<!-- BEGIN UESP -->
<?php wfRunHooks( 'MonoBookPageBottom', array( &$this ) ); ?>
<!-- End UESP -->
</div>
<div id="column-one"<?php $this->html('userlangattributes')  ?>>
<?php $this->cactions(); ?>
	<div class="portlet" id="p-personal">
		<h5><?php $this->msg('personaltools') ?></h5>
		<div class="pBody">
			<ul<?php $this->html('userlangattributes') ?>>
<?php		foreach($this->getPersonalTools() as $key => $item) { ?>
				<?php echo $this->makeListItem($key, $item); ?>

<?php		} ?>
			</ul>
		</div>
	</div>
	<div class="portlet" id="p-logo">
<?php
			echo Html::element( 'a', array(
				'href' => $this->data['nav_urls']['mainpage']['href'],
				'style' => "background-image: url({$this->data['logopath']});" )
				+ Linker::tooltipAndAccesskeyAttribs('p-logo') ); ?>

	</div>
<?php
	$this->renderPortals( $this->data['sidebar'] );

//BEGIN UESP
        if ( ! $wgUser->isLoggedIn() ) {
?>

	<div id="p-googlead" class="portlet" style="overflow: hidden; width: 162px; height: 625px; ">
		<form method="post" target="_blank" id="form_sidead" action="http://www.uesp.net/adreport.php">
		<input name="UespAdContent" type="hidden" value="unknown" />
		<input name="UespAdId" type="hidden" value="div-gpt-ad-1344720487368-3" />
		<div style="float: left;">
		        <small>&nbsp;<a href="/wiki/UESPWiki:Site_Support">What is this Ad?</a></small>
		</div>
		<div style="float: right;">
			<small><a href="javascript:submitReportAdForm('div-gpt-ad-1344720487368-3', 'form_sidead')">Report Ad</a></small>
		</div>
		<!-- WikiMiddleWideSkyscraper -->
		<div class='center' id='div-gpt-ad-1344720487368-3' style='width:160px; height:600px; margin-right: auto; margin-left: auto;'>
			<script type='text/javascript'>
googletag.cmd.push(function() { googletag.display('div-gpt-ad-1344720487368-3'); });
			</script>
		</div>
		</form>
        </div>
<?php
        }
//END UESP
?>
</div><!-- end of the left (by default at least) column -->
<div class="visualClear"></div>
<?php
	$validFooterIcons = $this->getFooterIcons( "icononly" );
	$validFooterLinks = $this->getFooterLinks( "flat" ); // Additional footer links

	if ( count( $validFooterIcons ) + count( $validFooterLinks ) > 0 ) { ?>
<div id="footer"<?php $this->html('userlangattributes') ?>>
<?php
		$footerEnd = '</div>';
	} else {
		$footerEnd = '';
	}
	foreach ( $validFooterIcons as $blockName => $footerIcons ) { ?>
	<div id="f-<?php echo htmlspecialchars($blockName); ?>ico">
<?php foreach ( $footerIcons as $icon ) { ?>
		<?php echo $this->getSkin()->makeFooterIcon( $icon ); ?>

<?php }
?>
	</div>
<?php }

		if ( count( $validFooterLinks ) > 0 ) {
?>	<ul id="f-list">
<?php
			foreach( $validFooterLinks as $aLink ) { ?>
		<li id="<?php echo $aLink ?>"><?php $this->html($aLink) ?></li>
<?php
			}
?>
	</ul>
<?php	}
echo $footerEnd;
?>

</div>
<?php
		$this->printTrail();
		echo Html::closeElement( 'body' );
		echo Html::closeElement( 'html' );
		wfRestoreWarnings();
	} // end of execute() method

	/*************************************************************************************************/

	protected function renderPortals( $sidebar ) {
		if ( !isset( $sidebar['SEARCH'] ) ) $sidebar['SEARCH'] = true;
		if ( !isset( $sidebar['TOOLBOX'] ) ) $sidebar['TOOLBOX'] = true;
		if ( !isset( $sidebar['LANGUAGES'] ) ) $sidebar['LANGUAGES'] = true;

		foreach( $sidebar as $boxName => $content ) {
			if ( $content === false )
				continue;

			if ( $boxName == 'SEARCH' ) {
				$this->searchBox();
			} elseif ( $boxName == 'TOOLBOX' ) {
				$this->toolbox();
			} elseif ( $boxName == 'LANGUAGES' ) {
				$this->languageBox();
			} else {
				$this->customBox( $boxName, $content );
			}
		}
	}

	function searchBox() {
		global $wgUseTwoButtonsSearchForm;
?>
	<div id="p-search" class="portlet">
		<h5><label for="searchInput"><?php $this->msg('search') ?></label></h5>
		<div id="searchBody" class="pBody">
			<form action="<?php $this->text('wgScript') ?>" id="searchform">
				<input type='hidden' name="title" value="<?php $this->text('searchtitle') ?>"/>
				<?php echo $this->makeSearchInput(array( "id" => "searchInput", "type" => "" )); ?>
<!-- Begin UESP -->
	<button id="searchButton" title="Search UESP for this text" name="button" type="submit">
		<img width="12" height="13" alt="Search" src="/w/extensions/UespCustomCode/files/search-icon.png">
	</button>
<!-- End UESP -->
			</form>
		</div>
	</div>

<!-- BEGIN UESP -->
        <div id="p-googleplus" class="portlet" style="border: 1px solid #AAAAAA; background:#FBEFD5; margin: 0px; padding: 3px; width: 153px; overflow: hidden;">
        <div class="g-plusone" data-annotation="inline" data-width="139"></div>
<script type="text/javascript">
  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>
        </div>
<!-- END UESP -->
<?php
	}

	/**
	 * Prints the cactions bar.
	 * Shared between MonoBook and Modern
	 */
	function cactions() {
?>
	<div id="p-cactions" class="portlet">
		<h5><?php $this->msg('views') ?></h5>
		<div class="pBody">
			<ul><?php
				foreach($this->data['content_actions'] as $key => $tab) {
					echo '
				' . $this->makeListItem( $key, $tab );
				} ?>

			</ul>
		</div>
	</div>
<?php
	}
	/*************************************************************************************************/
	function toolbox() {
?>
	<div class="portlet" id="p-tb">
		<h5><?php $this->msg('toolbox') ?></h5>
		<div class="pBody">
			<ul>
<?php
		foreach ( $this->getToolbox() as $key => $tbitem ) { ?>
				<?php echo $this->makeListItem($key, $tbitem); ?>

<?php
		}
		wfRunHooks( 'MonoBookTemplateToolboxEnd', array( &$this ) );
		wfRunHooks( 'SkinTemplateToolboxEnd', array( &$this, true ) );
?>
			</ul>
		</div>
	</div>
<?php
	}

	/*************************************************************************************************/
	function languageBox() {
		if( $this->data['language_urls'] ) {
?>
	<div id="p-lang" class="portlet">
		<h5<?php $this->html('userlangattributes') ?>><?php $this->msg('otherlanguages') ?></h5>
		<div class="pBody">
			<ul>
<?php		foreach($this->data['language_urls'] as $key => $langlink) { ?>
				<?php echo $this->makeListItem($key, $langlink); ?>

<?php		} ?>
			</ul>
		</div>
	</div>
<?php
		}
	}

	/*************************************************************************************************/
	function customBox( $bar, $cont ) {
		$portletAttribs = array( 'class' => 'generated-sidebar portlet', 'id' => Sanitizer::escapeId( "p-$bar" ) );
		$tooltip = Linker::titleAttrib( "p-$bar" );
		if ( $tooltip !== false ) {
			$portletAttribs['title'] = $tooltip;
		}
		echo '	' . Html::openElement( 'div', $portletAttribs );
?>

		<h5><?php $msg = wfMessage( $bar ); echo htmlspecialchars( $msg->exists() ? $msg->text() : $bar ); ?></h5>
		<div class='pBody'>
<?php   if ( is_array( $cont ) ) { ?>
			<ul>
<?php 			foreach($cont as $key => $val) { 
				if( function_exists('efSiteCustomCode') ) {
					if (substr($val['text'],0,1)=='*') {
						if (!$isindent)
							echo "\n<ul>\n";
						$isindent = true;
						$val['text'] = trim(substr($val['text'],1));
					}
					elseif ($isindent) {
						$isindent = false;
						echo "\n</ul>\n";
					}
				}
?>
				<?php echo $this->makeListItem($key, $val); ?>

<?php			} 
			if ($isindent) echo "\n</ul>\n";			
?>
			</ul>
<?php   } else {
			# allow raw HTML block to be defined by extensions
			print $cont;
		}
?>
		</div>
	</div>
<?php
	}
} // end of class



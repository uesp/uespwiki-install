<?php
/**
 * UespMonoBook as copied from MonoBook.
 *
 * Translated from gwicke's previous TAL template version to remove
 * dependency on PHPTAL.
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
 *
 * @todo document
 * @file
 * @ingroup Skins
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( -1 );
}

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @todo document
 * @ingroup Skins
 */
class SkinUespMonoBook extends SkinTemplate {
	/** Using uespmonobook. */
	var $skinname = 'uespmonobook', $stylename = 'uespmonobook',
		$template = 'UespMonoBookTemplate', $useHeadElement = true;

	/**
	 * @param $out OutputPage
	 */
	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );

		$out->addModuleStyles( 'skins.uespmonobook' );

		// TODO: Migrate all of these
		$out->addStyle( 'uespmonobook/IE60Fixes.css', 'screen', 'IE 6' );
		$out->addStyle( 'uespmonobook/IE70Fixes.css', 'screen', 'IE 7' );

	}
}


/**
 * @todo document
 * @ingroup Skins
 */
class UespMonoBookTemplate extends MonoBookTemplate
{

	/**
	 * Template filter callback for UespMonoBook skin.
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
	<div id="column-content"><div id="content" class="mw-body-primary" role="main">
	<!-- BEGIN UESP -->
<?php
        if ( ! $wgUser->isLoggedIn() ) {
?>
        <!-- WikiLeaderboardTop -->
<div class='center' style='margin-left: auto; margin-right: auto;'>
<div id='div-gpt-ad-1344720487368-2' style='width:728px; height:90px;'>
<script type='text/javascript'>
googletag.cmd.push(function() { googletag.display('div-gpt-ad-1344720487368-2');
 });
</script>
</div>
</div>

<?php
        }
?>
<!-- END UESP -->
		<a id="top"></a>
		<?php if ( $this->data['sitenotice'] ) { ?><div id="siteNotice"><?php $this->html( 'sitenotice' ) ?></div><?php } ?>
	
		<h1 id="firstHeading" class="firstHeading" lang="<?php
			$this->data['pageLanguage'] = $this->getSkin()->getTitle()->getPageViewLanguage()->getHtmlCode();
			$this->text( 'pageLanguage' );
		?>"><span dir="auto"><?php $this->html( 'title' ) ?></span></h1>
		<div id="bodyContent" class="mw-body">
			<div id="siteSub"><?php $this->msg( 'tagline' ) ?></div>
			<div id="contentSub"<?php $this->html( 'userlangattributes' ) ?>><?php $this->html( 'subtitle' ) ?></div>
	<?php if ( $this->data['undelete'] ) { ?>
			<div id="contentSub2"><?php $this->html( 'undelete' ) ?></div>
	<?php } ?><?php if ( $this->data['newtalk'] ) { ?>
			<div class="usermessage"><?php $this->html( 'newtalk' ) ?></div>
	<?php } ?>
			<div id="jump-to-nav" class="mw-jump"><?php $this->msg( 'jumpto' ) ?> <a href="#column-one"><?php $this->msg( 'jumptonavigation' ) ?></a><?php $this->msg( 'comma-separator' ) ?><a href="#searchInput"><?php $this->msg( 'jumptosearch' ) ?></a></div>
	
			<!-- start content -->
	<?php $this->html( 'bodytext' ) ?>
			<?php if ( $this->data['catlinks'] ) { $this->html( 'catlinks' ); } ?>
			<!-- end content -->
			<?php if ( $this->data['dataAfterContent'] ) { $this->html( 'dataAfterContent' ); } ?>
			<div class="visualClear"></div>
		</div>
	</div>
	<!-- BEGIN UESP -->
	<?php wfRunHooks( 'MonoBookPageBottom', array( &$this ) ); ?>
	<!-- End UESP -->
	</div>
	<div id="column-one"<?php $this->html( 'userlangattributes' ) ?>>
		<h2><?php $this->msg( 'navigation-heading' ) ?></h2>
	<?php $this->cactions(); ?>
		<div class="portlet" id="p-personal" role="navigation">
			<h3><?php $this->msg( 'personaltools' ) ?></h3>
			<div class="pBody">
				<ul<?php $this->html( 'userlangattributes' ) ?>>
	<?php		foreach ( $this->getPersonalTools() as $key => $item ) { ?>
					<?php echo $this->makeListItem( $key, $item ); ?>
	
	<?php		} ?>
				</ul>
			</div>
		</div>
		<div class="portlet" id="p-logo" role="banner">
	<?php
				echo Html::element( 'a', array(
					'href' => $this->data['nav_urls']['mainpage']['href'],
					'style' => "background-image: url({$this->data['logopath']});" )
					+ Linker::tooltipAndAccesskeyAttribs( 'p-logo' ) ); ?>
	
		</div>
	<?php
		$this->renderPortals( $this->data['sidebar'] );
		//BEGIN UESP
		if ( ! $wgUser->isLoggedIn() ) {
			?>
		
			<div id="p-googlead" class="portlet" style="">
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
	<div id="footer" role="contentinfo"<?php $this->html( 'userlangattributes' ) ?>>
	<?php
			$footerEnd = '</div>';
		} else {
			$footerEnd = '';
		}
		foreach ( $validFooterIcons as $blockName => $footerIcons ) { ?>
		<div id="f-<?php echo htmlspecialchars( $blockName ); ?>ico">
	<?php foreach ( $footerIcons as $icon ) { ?>
			<?php echo $this->getSkin()->makeFooterIcon( $icon ); ?>
	
	<?php }
	?>
		</div>
	<?php }
	
			if ( count( $validFooterLinks ) > 0 ) {
	?>	<ul id="f-list">
	<?php
				foreach ( $validFooterLinks as $aLink ) { ?>
			<li id="<?php echo $aLink ?>"><?php $this->html( $aLink ) ?></li>
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
		
		
	function searchBox() {
?>
	<div id="p-search" class="portlet" role="search">
		<h3><label for="searchInput"><?php $this->msg( 'search' ) ?></label></h3>
		<div id="searchBody" class="pBody">
			<form action="<?php $this->text( 'wgScript' ) ?>" id="searchform">
				<input type='hidden' name="title" value="<?php $this->text( 'searchtitle' ) ?>"/>
				<input name="search" title="Search UESPWiki [f]" accesskey="f" id="searchInput" />
				<button id="searchButton" title="Search UESP for this text" name="button" type="submit">
					<img width="12" height="13" alt="Search" src="/w/extensions/UespCustomCode/files/search-icon.png">
				</button>
			</form>
		</div>
	</div>
	
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
<?php
	}
	
	
	function customBox( $bar, $cont ) {
		$portletAttribs = array( 'class' => 'generated-sidebar portlet', 'id' => Sanitizer::escapeId( "p-$bar" ) );
		$tooltip = Linker::titleAttrib( "p-$bar" );
		if ( $tooltip !== false ) {
			$portletAttribs['title'] = $tooltip;
		}
		echo '	' . Html::openElement( 'div', $portletAttribs );
		?>
	
			<h5><label><?php $msg = wfMessage( $bar ); echo htmlspecialchars( $msg->exists() ? $msg->text() : $bar ); ?></label></h5>
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



<?php

/**
 * NOTE: This is an old file that is no longer used. See SkinUespMonoBook.php and UespMonoBookTemplate.php instead. 
 */

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

require_once "skins/MonoBook/MonoBookTemplate.php";

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
		error_log("uesp monobook");
		$out->addModuleStyles( array('mediawiki.skinning.elements', 'mediawiki.skinning.content', 'mediawiki.skinning.interface', 'skins.uespmonobook' ));

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
		global $wgOut;
		
		// Suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();
		
		$this->html( 'headelement' );
		?><div id="globalWrapper">
	<div id="column-content"><div id="content" class="mw-body-primary" role="main">
	<!-- BEGIN UESP Top Ad-->
<?php
        //if ( ! $wgUser->isLoggedIn() ) {
        if (true) {
?>
		<div id='topad'><div class='center' id='uespTopBannerAd'>
			<div id='uesp_D_1'></div>
		</div></div>
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
	<!-- BEGIN UESP Bottom Ad -->
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
		//BEGIN UESP Sidebar Ad
		//if ( ! $wgUser->isLoggedIn() ) {
		if (true) {
			?>
			<div>
				<div id='uesp_D_2'></div>
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
<!-- BEGIN UESP -->
	<div id='cdm-zone-end'></div>
	
<!-- Begin comScore -->
<script>
	var _comscore = _comscore || [];
	_comscore.push({ c1: "2", c2: "6035118" });
	(function() {
	   var s = document.createElement("script"), el = document.getElementsByTagName("script")[0]; s.async = true;
	   s.src = (document.location.protocol == "https:" ? "https://sb" : "http://b") + ".scorecardresearch.com/beacon.js";
	   el.parentNode.insertBefore(s, el);
	})();
</script>
<noscript>
   <img src="https://sb.scorecardresearch.com/p?c1=2&amp;c2=6035118&amp;cv=2.0&amp;cj=1" />
</noscript>
<!-- End comScore -->

<!-- Nielsen Online SiteCensus -->
       <div><img src="//secure-us.imrworldwide.com/cgi-bin/m?ci=us-603339h&amp;cg=0&amp;cc=1&amp;ts=noscript" width="1" height="1" alt="" /></div>
<!-- End Nielsen Online SiteCensus -->
<!-- END UESP -->
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
	
			<h3><label><?php $msg = wfMessage( $bar ); echo htmlspecialchars( $msg->exists() ? $msg->text() : $bar ); ?></label></h3>
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



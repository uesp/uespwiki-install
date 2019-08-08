<?php
/**
 * Vector - Modern version of MonoBook with fresh look and many usability
 * improvements.
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
 * @file
 * @ingroup Skins
 */

/**
 * QuickTemplate class for Vector skin
 * @ingroup Skins
 */
require_once "skins/Vector/VectorTemplate.php";

class UespVectorTemplate extends VectorTemplate {
	function execute() {
		$sn = $this->get('sitenotice');
		$this->set('sitenotice', "<div id='topad'><div class='center' id='uespTopBannerAd' style='margin:0 auto;'><div id='cdm-zone-01'></div></div></div>$sn");
		parent::execute();
	}
	
	function printTrail() {
		?>
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
   <img src="http://b.scorecardresearch.com/p?c1=2&amp;c2=6035118&amp;cv=2.0&amp;cj=1" />
</noscript>
<!-- End comScore -->

<!-- Nielsen Online SiteCensus -->
       <div><img src="//secure-us.imrworldwide.com/cgi-bin/m?ci=us-603339h&amp;cg=0&amp;cc=1&amp;ts=noscript" width="1" height="1" alt="" /></div>
<!-- End Nielsen Online SiteCensus -->
<!-- END UESP --><?php
		parent::printTrail();
	}
	protected function renderPortal( $name, $content, $msg = null, $hook = null ) {
		if ( $msg === null ) {
			$msg = $name;
		}
		$msgObj = wfMessage( $msg );
		?>
		<div class="portal" role="navigation" id='<?php
		echo Sanitizer::escapeId( "p-$name" )
		?>'<?php
		echo Linker::tooltip( 'p-' . $name )
		?> aria-labelledby='<?php echo Sanitizer::escapeId( "p-$name-label" ) ?>'>
			<h3<?php
			$this->html( 'userlangattributes' )
			?> id='<?php
			echo Sanitizer::escapeId( "p-$name-label" )
			?>'><?php
				echo htmlspecialchars( $msgObj->exists() ? $msgObj->text() : $msg );
				?></h3>

			<div class="body">
				<?php
				if ( is_array( $content ) ) {
					?>
					<ul>
						<?php
						$isindent = false;
						foreach ( $content as $key => $val ) {
							if (isset($val['text']) && substr($val['text'],0,1)=='*') {
								if (!$isindent)
									echo "\n<ul style=\"padding-left:0.75em;\">\n";
								$isindent = true;
								$val['text'] = trim(substr($val['text'],1));
							}
							elseif ($isindent) {
								$isindent = false;
								echo "\n</ul>\n";
							}

							echo $this->makeListItem( $key, $val );
						}
						if ( $hook !== null ) {
							wfRunHooks( $hook, array( &$this, true ) );
						}
						?>
					</ul>
				<?php
				} else {
					?>
					<?php
					echo $content; /* Allow raw HTML block to be defined by extensions */
				}

				$this->renderAfterPortlet( $name );
				?>
			</div>
		</div>
	<?php
	}

	// Same fugly hack as UespMonobook for footer ad, just moved to a different place.
	function renderPortals( $sidebar ) {
		parent::renderPortals( $sidebar );
		?>
		<div class='portlet' style='margin:0 auto;'>
			<div id='cdm-zone-07'></div>
		</div><div class='visualClear'></div></div></div><div style='padding-left:11em; width:300px; height:250px; margin:0 auto;'><div id='cdm-zone-02'></div></div><div><div>
<?php
	}
}

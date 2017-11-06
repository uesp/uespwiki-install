<?php

/*
 * Assorted functions designed to be called within Monobook.php in order to customize the page layout
 * (add boxes to sidebar, tweak search box, add google ad boxes, etc.

 * These functions are writing text to a string, then printing the string in preparation for
 * (hopeful) future version of code where Monobook text is no longer written directly to output
 */
class SiteMonobook {
	public static function GoogleAdBottom() {
		global $egSiteEnableGoogleAds, $wgUser, $wgTitle;
		if (!$egSiteEnableGoogleAds)
			return true;
?>

<!-- Wiki_Bottom_Rectangle -->
<div class='center' style="margin-left: auto; margin-right: auto; margin-top: 10px;">
<?php
	if ( $wgTitle->getPrefixedText() == 'Special:WebChat') {
		// no ads
	}
        else if ( ! $wgUser->isLoggedIn() ) {
?>
<!-- WikiBottomAnonymous -->
<div id='div-gpt-ad-1344720487368-1' style='width:300px; height:250px;'>
<script type='text/javascript'>
googletag.cmd.push(function() { googletag.display('div-gpt-ad-1344720487368-1');
 });
</script>
</div>
<?php
        } else {
?>

<!-- Wiki_Bottom_Rectangle -->
<div id='div-gpt-ad-1344720487368-0' style='width:300px; height:250px;'>
<script type='text/javascript'>
googletag.cmd.push(function() { googletag.display('div-gpt-ad-1344720487368-0');
 });
</script>
</div>
<?php
        }
?>
</div>
<?php	
		return true;
	}

	public static function GoogleSearchSidebar() {
		$text = <<< End_GoogleSearch
	<div class="portlet" id="p-googlesearch">
		<h5>Google Search</h5>
		<div class="pBody">
Begin SiteSearch Google
<form method="get" action="//www.google.com/custom" target="_top">
<table border="0" bgcolor="#FBEFD5">
<tr><td nowrap="nowrap" valign="top" align="left">
<input type="hidden" name="domains" value="uesp.net"></input>
<label for="sbi" style="display: none">Enter your search terms</label>
<input type="text" name="q" size="17" maxlength="255" value="" id="sbi"></input>
</td></tr>
<tr>
<td nowrap="nowrap">
<table>
<tr>
<td>
<input type="radio" name="sitesearch" value="" checked id="ss0" style="margin-left:0px;"></input>
<label for="ss0" title="Search the Web"><font size="-1" color="#000000">Web</font></label></td>
<td>
<input type="radio" name="sitesearch" value="uesp.net" id="ss1"></input>
<label for="ss1" title="Search uesp.net"><font size="-1" color="#000000">uesp.net</font></label></td>
</tr>
</table>
<label for="sbb" style="display: none">Submit search form</label>
<input type="submit" name="sa" value="Google Search" id="sbb"></input>
<input type="hidden" name="client" value="pub-3886949899853833"></input>
<input type="hidden" name="forid" value="1"></input>
<input type="hidden" name="channel" value="1320313013"></input>
<input type="hidden" name="ie" value="ISO-8859-1"></input>
<input type="hidden" name="oe" value="ISO-8859-1"></input>
<input type="hidden" name="safe" value="active"></input>
<input type="hidden" name="cof" value="GALT:#008000;GL:1;DIV:#CCCCCC;VLC:663399;AH:center;BGC:FBEFD5;LBGC:FCFFF0;ALC:0000ff;LC:0000ff;T:000000;GFNT:0000FF;GIMP:0000FF;LH:50;LW:189;L://www.uesp.net/w/images/Mainpage-logo.png;S://www.uesp.net;FORID:1"></input>
<input type="hidden" name="hl" value="en"></input>
</td></tr></table>
</form>
End SiteSearch Google
		</div>
	</div>

End_GoogleSearch;
		echo $text;
		return true;
	}

	public static function GoogleAdSidebar() {
		global $egSiteEnableGoogleAds;
		if (!$egSiteEnableGoogleAds)
			return true;
		
		return true;
	}

	public static function SearchButtonsSidebar( ) {
		global $wgTitle, $wgUser, $wgDefaultUserOptions;
		global $egCustomSiteID;
		$prefix = strtolower($egCustomSiteID);
//		<input type='submit' name="helpsearch" class="searchButton" id="mw-searchHelpButton" value="?" /> */
		
		$nsnumvalue = $wgTitle->getNamespace()-($wgTitle->getNamespace()%2);
		$searchredirs = $wgUser->getOption($prefix.'searchredirects', $wgDefaultUserOptions[$prefix.'searchredirects']);
		$searchtitle = $wgUser->getOption($prefix.'searchtitles', $wgDefaultUserOptions[$prefix.'searchtitles']);
		$extrabutton = wfMessage($prefix.'extrasearchbutton')->text();
		if (!$extrabutton)
			$text = '';
		elseif ($extrabutton == '?' || $extrabutton == 'Help') {
			$text = "\t\t<input type='submit' name=\"helpsearch\" class=\"searchButton\" id=\"mw-searchHelpButton\" value=\"{$extrabutton}\" />\n";
		}
		else {
			$text = "\t\t<input type='submit' name=\"more\" class=\"searchButton\" id=\"mw-searchOptionsButton\" value=\"{$extrabutton}\" />\n";
		}
		$text .= <<< End_Buttons
		<input type='hidden' name="nsnum" value="$nsnumvalue" />
		<input type='hidden' name="redirs" value="$searchredirs" />
		<input type='hidden' name="searchtitles" value="$searchtitle" />

End_Buttons;
		echo $text;
		return true;
	}

/* This was previously at very end of </body> section, but commented out */
	public static function UrchinTracker() 
	{
		return true;
	}
}

<?php

/**
 * Adds a simple PayPal button/form for quickly making a donation to the site.
 *
 * @author Dave Humphrey <uesp@sympatico.ca>
 * @copyright Public domain
 * @license Public domain
 * @package MediaWikiExtensions
 * @version 0.1
 */

/**
 * Register the Inputbox extension with MediaWiki
 */ 
$wgExtensionFunctions[] = 'registerUespPayPalExtension';
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'UespPayPal',
	'author' => 'Dave Humphrey',
	'url' => 'http://www.uesp.net/',
);


/**
 * Sets the tag that this extension looks for and the function by which it
 * operates
 */
function registerUespPayPalExtension()
{
    global $wgParser;
    $wgParser->setHook('uesppaypal', 'renderUespPayPal');
}


/**
 * Renders a the site PayPal button
 */
function renderUespPayPal($input, $params, &$parser)
{
	foreach ($params as $key=>$value) { 
		$args .= "<li>$key = *$value*</li>\n";
	}

	$Result = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Make payments with PayPal - it%27s fast, free and secure!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form>';
	return ($Result);
}

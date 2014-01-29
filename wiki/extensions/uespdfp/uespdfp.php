<?php



$wgResourceModules['ext.uespdfp'] = array(
	'scripts' => 'ext.uespdfp.js',
	'localBasePath' => dirname(__FILE__) . '/modules',
	'remoteExtPath' => 'uespdfp/modules',
	'position' => 'top',
	'group' => 'ext.uespdfp', 
);

$wgExtensionCredits['other'][] = array(
         'path' => __FILE__,
         'name' => 'UESP DFP',
         'author' =>  'Dave Humphrey',
	 'description' => 'DFP JavaScript',
         'version' => '0.1',
);

$wgHooks['BeforePageDisplay'][] = 'UespDfp_beforePageDisplay';


function UespDfp_beforePageDisplay( $out, $skin ) 
{
	$out->addModules('ext.uespdfp' );
        return true;
}


?>

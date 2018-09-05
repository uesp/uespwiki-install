<?php
// If this is run directly from the web die as this is not a valid entry point.
if ( !defined( 'MEDIAWIKI' ) ) die( 'Invalid entry point.' );

// Extension credits.
$wgExtensionCredits[ 'other' ][] = array(
	'path'           => __FILE__,
	'name'           => 'qwebirChat',
	'descriptionmsg' => 'qwebirChat-desc',
	'author'         => '[[User:Jak Atackka|Jak Atackka]]', 
	'version'        => '1.1'
);

// Register special page.
$wgSpecialPages['WebChat'] = 'WebChat';
$wgSpecialPageGroups['WebChat'] = 'wiki';
// Extension messages.
$wgExtensionMessagesFiles['WebChat'] =  dirname( __FILE__).'/qwebirChat.i18n.php';

//Add "chat" tab to every article
//$wgHooks['SkinTemplateNavigation::Universal'][] = 'WebChat::addChatTab';

class WebChat extends SpecialPage {

	function __construct() {
		parent::__construct( 'WebChat', 'qwebirChat' );
	}

	function execute( $par ) {
		global $wgOut;
		
		$this->setHeaders();
		$wgOut->addWikiMsg( 'qwebirChat-header' );

		$wgOut->addHTML( Xml::openElement( 'iframe', array(
			'width'     => '100%',
			'height'    => '600px',
			'border'    => '0',
			'src'       => 'http://irc.uesp.net:9090/?channels=uespwiki&uio=MTE9MzE28',
		) ) . Xml::closeElement( 'iframe' ) );
	}
	
	public static function addChatTab( $skin, &$content_actions){ 
		$title = $skin->getTitle();
		if ($title->getNamespace() !== NS_SPECIAL ) {
			$content_actions['view']['chat'] = Array( 
				'class' => false,
				'text' => 'chat', 
				'href' => 'http://www.uesp.net/wiki/Special:WebChat', 
			);
		}
		return true; 
	} 
}

?>

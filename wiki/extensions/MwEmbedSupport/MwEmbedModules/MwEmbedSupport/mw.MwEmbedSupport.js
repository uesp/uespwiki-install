// Add support for html5 / mwEmbed elements to IE
// For discussion and comments, see: http://remysharp.com/2009/01/07/html5-enabling-script/
'video audio source track'.replace(/\w+/g,function( n ){ document.createElement( n ) } );

/**
 * MwEmbedSupport includes shared mwEmbed utilities that either
 * wrap core mediawiki functionality or support legacy mwEmbed module code
 *
 * @license
 * mwEmbed
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * @copyright (C) 2010 Kaltura
 * @author Michael Dale ( michael.dale at kaltura.com )
 *
 * @url http://www.kaltura.org/project/HTML5_Video_Media_JavaScript_Library
 *
 * Libraries used include code license in headers
 *
 * @dependencies
 */

( function( mw, $ ) {

	/**
	 * mw.ready method
	 * @deprecated
	 */
	mw.ready = function( callback ) {
		mw.log( "Error mw.ready has been deprecated." )
		// use the native jQuery dom ready call:
		$(callback);
	};

	mw.setConfig = function( name, value ){
		mediaWiki.config.set( name, value );
	};
	mw.getConfig = function( name, value ){
		return mediaWiki.config.get( name, value );
	};
	mw.setDefaultConfig = function( name, value ){
		if( mediaWiki.config.get( name ) === null ){
			mediaWiki.config.set( name, value );
		}
	};
	mw.mergeConfig = function( name, value ){
		if( mediaWiki.config.get( name ) != null ){
			var value = $.extend( {}, mediaWiki.config.get( name ), value );
		}
		return mw.setConfig( name, value );
	};
	/**
	 * Set any pre-mwEmbed embed configuration
	 */
	if( typeof window.preMwEmbedConfig != 'undefined') {
		mw.setConfig( window.preMwEmbedConfig );
	}


	/**
	 * Aliased load function
	 */
	mw.load = function( resources, callback ){
		mediaWiki.loader.using( resources, callback, function(){
			// failed to load
			mw.log("Failed to load resources:"  + resources );
		});
	};

	mw.getEmbedPlayerPath = function(){
		if(  mediaWiki.config.get( 'wgExtensionAssetsPath' ) ){
			return mediaWiki.config.get( 'wgExtensionAssetsPath' ) + '/TimedMediaHandler/MwEmbedModules/EmbedPlayer'
		} else if ( mediaWiki.config.get( 'wgLoadScript' ) ){
			return mw.getMwEmbedPath() + 'modules/EmbedPlayer'
		}
	};

	/**
	 * legacy support to get the mwEmbed resource path:
	 */
	mw.getMwEmbedPath = function(){
		// check for wgExtensionAssetsPath path ( running in mediaWiki instance )
		if ( mediaWiki.config.get( 'wgLoadScript' ) ){
			return mediaWiki.config.get( 'wgLoadScript' ).replace('load.php', '');
		}
		return false;
	};

	/**
	 * Merge in a configuration value:
	 */
	mw.mergeConfig = function( name, value ){
		if( typeof name == 'object' ) {
			$.each( name, function( inx, val) {
				mw.mergeConfig( inx, val );
			});
			return ;
		}
		var existingValue = mediaWiki.config.get( name );
		if( !existingValue || typeof existingValue == 'string'){
			mw.setConfig( name, value );
			return ;
		}
		if( typeof mediaWiki.config.get( name ) == 'object' ){
			if( $.isArray( existingValue) && $.isArray( value ) ){
				for( var i =0; i <  value.length ; i ++ ){
					existingValue.push( value[i] );
				}
				mw.setConfig( name, $.uniqueArray( existingValue ) );
			} else {
				mw.setConfig( name, $.extend( {}, existingValue, value) );
			}
			return ;
		}
	};

	/**
	 * Simple inheritance. We will move to something like
	 * http://javascriptmvc.com/docs.html#&who=jQuery.Class
	 * in the near future. This is just a stop gap.
	 */
	mw.inherit = function( _this, inhertParent ){
		for ( var method in inhertParent ) {
			if ( _this[ method ] ) {
				_this['parent_' + method] = inhertParent[method];
			} else {
				_this[ method ] = inhertParent[method];
			}
		}
	};


	/**
	 * Utility Functions
	 */

	/**
	 * Checks if a string is a url ( parsed success by mw.Uri )
	 * @param {String}
	 * 		Url url version to be checked with mw.Uri
	 */
	mw.isUrl = function( url ){
		try {
			new mw.Uri( url );
			return true;
		} catch ( e ){
			// no error
		}
		return false;
	};

	/**
	 * A version comparison utility function Handles version of types
	 * {Major}.{MinorN}.{Patch}
	 *
	 * Note this just handles version numbers not patch letters.
	 *
	 * @param {String}
	 *            minVersion Minimum version needed
	 * @param {String}
	 *            clientVersion Client version to be checked
	 *
	 * @return true if the version is at least of minVersion false if the
	 *         version is less than minVersion
	 */
	mw.versionIsAtLeast = function( minVersion, clientVersion ) {
		var minVersionParts = minVersion.split('.');
		var clientVersionParts = clientVersion.split('.');
		for( var i =0; i < minVersionParts.length; i++ ) {
			if( parseInt( clientVersionParts[i] ) > parseInt( minVersionParts[i] ) ) {
				return true;
			}
			if( parseInt( clientVersionParts[i] ) < parseInt( minVersionParts[i] ) ) {
				return false;
			}
		}
		// Same version:
		return true;
	};


	/**
	 * addLoaderDialog small helper for displaying a loading dialog
	 *
	 * @param {String}
	 *            dialogHtml text Html of the loader msg
	 */
	mw.addLoaderDialog = function( dialogHtml ) {
		if( !dialogHtml ){
			dialogHtml = mw.msg('mwe-loading');
		}
		$dialog = mw.addDialog({
			'title' : dialogHtml,
			'content' : dialogHtml + '<br>' +
				$('<div />')
				.loadingSpinner()
				.html()
		});
		return $dialog;
	};



	/**
	 * Add a dialog window:
	 *
	 * @param {Object} with following keys:
	 *            title: {String} Title string for the dialog
	 *            content: {String} to be inserted in msg box
	 *            buttons: {Object} A button object for the dialog Can be a string
	 *            				for the close button
	 * 			  any jquery.ui.dialog option
	 */
	mw.addDialog = function ( options ) {
		// Remove any other dialog
		$( '#mweDialog' ).remove();

		if( !options){
			options = {};
		}

		// Extend the default options with provided options
		var options = $.extend({
			'bgiframe': true,
			'draggable': true,
			'resizable': false,
			'modal': true
		}, options );

		if( ! options.title || ! options.content ){
			mw.log("Error: mwEmbed addDialog missing required options ( title, content ) ");
		}

		// Append the dialog div on top:
		$( 'body' ).append(
			$('<div />')
			.attr( {
				'id' : "mweDialog",
				'title' : options.title
			})
			.css({
				'display': 'none'
			})
			.append( options.content )
		);

		// Build the uiRequest
		var uiRequest = [ 'jquery.ui.dialog' ];
		if( options.draggable ){
			uiRequest.push( 'jquery.ui.draggable' );
		}
		if( options.resizable ){
			uiRequest.push( 'jquery.ui.resizable' );
		}

		// Special button string
		if ( typeof options.buttons == 'string' ) {
			var buttonMsg = options.buttons;
			buttons = { };
			options.buttons[ buttonMsg ] = function() {
				$( this ).dialog( 'close' );
			};
		}

		// Load the dialog resources
		mw.load( uiRequest, function() {
			$( '#mweDialog' ).dialog( options );
		} );
		return $( '#mweDialog' );
	};

	/**
	 * Close the loader dialog created with addLoaderDialog
	 */
	mw.closeLoaderDialog = function() {
		$( '#mweDialog' ).dialog( 'destroy' ).remove();
	};

	// An event once mwEmbedSupport is Ready,
	$( mw ).trigger( 'MwEmbedSupportReady' );

	/**
	 * Convert Hexadecimal string to HTML color code
	 *
	 * @param {String} Color code in hexadecimal notation
	 */
	mw.getHexColor = function( color ) {
		if( typeof color == 'string' && color.substr(0,2) == "0x" ) {
			return color.replace('0x', '#');
		} else {
			color = parseInt( color );
			color = color.toString(16);
			var len = 6 - color.length;
			if( len > 0 ) {
				var pre = '';
				for( var i=0; i<len; i++) {
					pre += '0';
				}
				color = pre + color;
			}
			return '#' + color;
		}
	};

	/*
	 * Send beacon ( used by ads and analytics plugins )
	 * @param {String} Beacon URL to load
	 */
	mw.sendBeaconUrl = function( beaconUrl ){
		var beacon = new Image();
		beacon.src = beaconUrl;
	};

} )( mediaWiki, jQuery );

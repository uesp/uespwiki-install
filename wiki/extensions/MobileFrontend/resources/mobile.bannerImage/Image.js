( function ( M, $ ) {

	var browser = M.require( 'mobile.browser/browser' );

	/**
	 * An image that can be loaded.
	 *
	 * @class Image
	 * @param {String} src
	 * @param {Number} width
	 * @param {String} height
	 * @param {String} fileName The name of the file from which the thumbnail was created
	 */
	function Image( src, width, height, fileName ) {
		this.src = src;
		this.width = width;
		this.height = height;
		this.fileName = fileName;
	}

	/**
	 * Try to load image and resolve or fail when it loads / or not.
	 * @returns {jQuery.Deferred}
	 */
	Image.prototype.load = function () {
		var loaded = $.Deferred(),
			self = this;
		// Try to load it
		// There is an issue with reliably knowing if the
		// original image is wider than the thumbnail.
		// If so, that image will fail to load.
		$( '<img>' )
			.attr( 'src', this.src )
			.load( function () {
				if ( browser.isWideScreen() && $( this ).width() < 768 ) {
					loaded.reject();
				} else {
					loaded.resolve( self );
				}
				$( this ).remove();
			} )
			.error( function () {
				$( this ).remove();
				loaded.reject();
			} )
			.appendTo( $( 'body' ) )
			.hide();
		return loaded;
	};

	M.define( 'mobile.bannerImage/Image', Image )
		.deprecate( 'modules/bannerImage/Image' );

}( mw.mobileFrontend, jQuery ) );

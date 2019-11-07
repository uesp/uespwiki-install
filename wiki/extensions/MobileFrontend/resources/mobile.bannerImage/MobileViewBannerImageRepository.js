 ( function ( M, $ ) {

	var Class = M.require( 'mobile.oo/Class' ),
		Image = M.require( 'mobile.bannerImage/Image' ),
		MobileViewBannerImageRepository;

	/**
	 * Uses the mobileview API to get images that can be used as banner images for a page.
	 *
	 * @class
	 */
	MobileViewBannerImageRepository = Class.extend( {

		/**
		 * @constructor
		 * @param {mw.Api} api A MediaWiki API client
		 * @param {String} title The title of the page
		 */
		initialize: function ( api, title ) {
			this.api = api;
			this.title = title;
			this.cache = {};
		},

		/**
		 * Gets an image that can be used as a banner image.
		 *
		 * @param {Number} targetWidth The target width of the image
		 * @return {jQuery.Promise} A promise that resolves with an Image object if the MediaWiki API
		 *  request was successful or rejects otherwise.
		 */
		getImage: function ( targetWidth ) {
			var deferred = $.Deferred(),
				promise = deferred.promise(),
				self = this;

			if ( self.cache.hasOwnProperty( targetWidth ) ) {
				deferred.resolve( self.cache[targetWidth] );

				return promise;
			}

			self.api.get( {
				action: 'mobileview',
				prop: 'thumb|image',
				page: self.title,
				thumbwidth: targetWidth
			} )
			.then( function ( data ) {
				var thumb,
					image;

				// Page doesn't exist.
				if ( data.hasOwnProperty( 'error' ) ) {
					deferred.reject( data.error );

					return;
				}

				// Page exists but doesn't have thumbnail or the main image.
				if (
					!data.mobileview.hasOwnProperty( 'thumb' ) ||
					!data.mobileview.hasOwnProperty( 'image' )
				) {
					// TODO: Should this reject with an error?
					deferred.reject();

					return;
				}

				thumb = data.mobileview.thumb;
				image = new Image(
					thumb.url,
					thumb.width,
					thumb.height,
					data.mobileview.image.file
				);

				self.cache[targetWidth] = image;

				deferred.resolve( image );
			} )
			.fail( deferred.reject );

			return promise;
		}
	} );

	M.define( 'mobile.bannerImage/MobileViewBannerImageRepository', MobileViewBannerImageRepository )
		.deprecate( 'modules/bannerImage/MobileViewBannerImageRepository' );

}( mw.mobileFrontend, jQuery ) );

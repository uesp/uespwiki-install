( function ( M, $ ) {
	var WatchList,
		WatchstarPageList = M.require( 'mobile.pagelist.scripts/WatchstarPageList' ),
		InfiniteScroll = M.require( 'mobile.infiniteScroll/InfiniteScroll' ),
		WatchListGateway = M.require( 'mobile.watchlist/WatchListGateway' );

	/**
	 * An extension of the PageList which preloads pages as all being watched.
	 * @extends PageList
	 * @class WatchList
	 * @uses InfiniteScroll
	 */
	WatchList = WatchstarPageList.extend( {
		events: {
			'mousedown a.title': 'onTitleClick'
		},
		/** @inheritdoc */
		initialize: function ( options ) {
			var lastTitle;

			// Set up infinite scroll helper and listen to events
			this.infiniteScroll = new InfiniteScroll();
			this.infiniteScroll.on( 'load', $.proxy( this, '_loadPages' ) );

			if ( options.el ) {
				lastTitle = this.getLastTitle( options.el );
			}
			this.gateway = new WatchListGateway( options.api, lastTitle );

			WatchstarPageList.prototype.initialize.apply( this, arguments );
		},
		/** @inheritdoc */
		preRender: function () {
			this.infiniteScroll.disable();
			this.infiniteScroll.setElement( this.$el );
		},
		/**
		 * Retrieve pages where all pages are watched.
		 *
		 * @method
		 * @param {Array} ids a list of page ids
		 * @return {jQuery.Deferred}
		 */
		getPages: function ( ids ) {
			return this.api.load( ids, true );
		},
		/**
		 * Also sets a watch uploads funnel.
		 * @inheritdoc
		 */
		postRender: function () {
			WatchstarPageList.prototype.postRender.apply( this );
			this.infiniteScroll.enable();
		},
		/**
		 * Save title clicks
		 */
		onTitleClick: function () {
			// name funnel for watchlists to catch subsequent uploads
			$.cookie( 'mwUploadsFunnel', 'watchlist', {
				expires: new Date( new Date().getTime() + 60000 )
			} );
		},
		/**
		 * Loads pages from the api and triggers render.
		 * Infinite scroll is re-enabled in postRender.
		 */
		_loadPages: function () {
			var self = this;
			this.gateway.load().done( function ( pages ) {
				$.each( pages, function ( i, page ) {
					self.appendPage( page );
				} );
				self.render();
			} );
		},

		/**
		 * Appends a list item
		 * @param {Object} page
		 */
		appendPage: function ( page ) {
			this.$el.append( this.templatePartials.item.render( page ) );
		},

		/**
		 * Get the last title from the rendered HTML.
		 * Used for initializing the API
		 * @param {jQuery.Object} $el Dom element of the list
		 */
		getLastTitle: function ( $el ) {
			return $el.find( 'li:last' ).attr( 'title' );
		}

	} );

	M.define( 'mobile.watchlist/WatchList', WatchList )
		.deprecate( 'modules/watchlist/WatchList' );

}( mw.mobileFrontend, jQuery ) );

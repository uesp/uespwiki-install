( function ( M, $ ) {

	var Page,
		View = M.require( 'mobile.view/View' ),
		Section = M.require( 'mobile.startup/Section' ),
		Thumbnail = M.require( 'mobile.startup/Thumbnail' );

	/**
	 * Mobile page view object
	 *
	 * @class Page
	 * @uses Section
	 * @extends View
	 */
	Page = View.extend( {
		/**
		 * @cfg {Object} defaults Default options hash.
		 * @cfg {Number} defaults.id Page ID. The default value of 0 represents a new page.
		 * Be sure to override it to avoid side effects.
		 * @cfg {String} defaults.title Title of the page. It includes prefix where needed and
		 * is human readable, e.g. Talk:The man who lived.
		 * @cfg {String} defaults.displayTitle Title of the page that's displayed. Falls back
		 * to defaults.title if no value is provided.
		 * @cfg {Number} defaults.namespaceNumber the number of the namespace the page belongs to
		 * @cfg {Object} defaults.protection List of permissions as returned by API,
		 * e.g. [{ edit: ['*'] }]
		 * @cfg {Array} defaults.sections Array of {Section} objects.
		 * @cfg {Boolean} defaults.isMainPage Whether the page is the Main Page.
		 * @cfg {Boolean} defaults.isMissing Whether the page exists in the wiki.
		 * @cfg {String} defaults.hash Window location hash.
		 * @cfg {Object} defaults.thumbnail thumbnail definition corresponding to page image
		 * @cfg {Boolean} defaults.thumbnail.isLandscape whether the image is in landscape format
		 * @cfg {Number} defaults.thumbnail.width of image in pixels.
		 * @cfg {Number} defaults.thumbnail.height of image in pixels.
		 * @cfg {String} defaults.thumbnail.source url for image
		 */
		defaults: {
			id: 0,
			title: '',
			displayTitle: '',
			namespaceNumber: 0,
			protection: {
				edit: [ '*' ]
			},
			sections: [],
			isMissing: false,
			isMainPage: false,
			hash: window.location.hash,
			url: undefined,
			thumbnail: {
				isLandscape: undefined,
				source: undefined,
				width: undefined,
				height: undefined
			}
		},
		/**
		 * @inheritdoc
		 */
		isBorderBox: false,
		/**
		 * @inheritdoc
		 */
		initialize: function ( options ) {
			var thumb;

			// Fallback if no displayTitle provided
			options.displayTitle = options.displayTitle || options.title;
			options.languageUrl = mw.util.getUrl( 'Special:MobileLanguages/' + options.title );
			View.prototype.initialize.apply( this, arguments );
			// allow usage in templates.
			// FIXME: Should View map all options to properties?
			this.title = options.title;
			this.displayTitle = options.displayTitle || options.title;
			this.thumbnail = options.thumbnail;
			this.url = options.url || mw.util.getUrl( options.title );
			this.id = options.id;
			this.isMissing = options.isMissing;
			thumb = this.thumbnail;
			if ( thumb && thumb.width ) {
				this.thumbnail.isLandscape = thumb.width > thumb.height;
			}
			this.wikidataDescription = options.wikidataDescription;
		},

		/**
		 * Determine if current page is in a specified namespace
		 * @method
		 * @param {String} namespace Name of namespace
		 * @return {Boolean}
		 */
		inNamespace: function ( namespace ) {
			return this.options.namespaceNumber === mw.config.get( 'wgNamespaceIds' )[namespace];
		},

		/**
		 * Get the lead section of the page view.
		 * @method
		 * @return {jQuery.Object}
		 */
		getLeadSectionElement: function () {
			return this.$( '> div' ).eq( 0 );
		},

		/**
		 * Determines if content model is wikitext
		 * @method
		 * @return {Boolean}
		 */
		isWikiText: function () {
			return mw.config.get( 'wgPageContentModel' ) === 'wikitext';
		},

		/**
		 * Checks whether the current page is the main page
		 * @method
		 * @return {Boolean}
		 */
		isMainPage: function () {
			return this.options.isMainPage;
		},
		/**
		 * Checks whether the current page is watched
		 * @method
		 * @return {Boolean}
		 */
		isWatched: function () {
			return this.options.isWatched;
		},

		/**
		 * Checks whether the given user can edit the page.
		 * @method
		 * @param {mw.user} user Object representing a user
		 * @return {Boolean}
		 */
		isEditable: function ( user ) {
			// see: https://www.mediawiki.org/wiki/Manual:Interface/JavaScript#Page-specific
			var editable = mw.config.get( 'wgIsProbablyEditable' );

			// for logged in users check if they are blocked from editing this page
			if ( editable && !user.isAnon() ) {
				editable = !mw.config.get( 'wgMFUserBlockInfo' );
			}

			return editable;
		},

		/**
		 * Return the latest revision id for this page
		 * @method
		 * @return {Number}
		 */
		getRevisionId: function () {
			return this.options.revId;
		},

		/**
		 * Return prefixed page title
		 * @method
		 * @return {String}
		 */
		getTitle: function () {
			return this.options.title;
		},

		/**
		 * Return page id
		 * @method
		 * @return {Number}
		 */
		getId: function () {
			return this.options.id;
		},

		/**
		 * return namespace id
		 * @method
		 * @return {Number} namespace Number
		 */
		getNamespaceId: function () {
			var nsId,
				args = this.options.title.split( ':' );

			if ( args[1] ) {
				nsId = mw.config.get( 'wgNamespaceIds' )[ args[0].toLowerCase().replace( ' ', '_' ) ] || 0;
			} else {
				nsId = 0;
			}
			return nsId;
		},

		/**
		 * Determines if current page is a talk page
		 * @method
		 * @return {Boolean} Whether the page is a talk page or not
		 */
		isTalkPage: function () {
			var ns = this.getNamespaceId();
			// all talk pages are odd Numbers (except the case of special pages)
			return ns > 0 && ns % 2 === 1;
		},

		/**
		 * @inheritdoc
		 */
		preRender: function () {
			var self = this;
			this.sections = [];
			this._sectionLookup = {};
			this.title = this.options.title;

			$.each( this.options.sections, function () {
				var section = new Section( this );
				self.sections.push( section );
				self._sectionLookup[section.id] = section;
			} );
		},

		/**
		 * @inheritdoc
		 */
		postRender: function () {
			var self = this;
			// Restore anchor position after everything on page has been loaded.
			// Otherwise, images that load after a while will push the anchor
			// from the top of the viewport.
			if ( this.options.hash ) {
				$( window ).on( 'load', function () {
					window.location.hash = self.options.hash;
				} );
			}
		},

		/**
		 * Return reference section
		 * @method
		 */
		getReferenceSection: function () {
			return this._referenceLookup;
		},

		/**
		 * Return all the thumbnails in the article
		 * @method
		 * @return {Thumbnail[]}
		 */
		getThumbnails: function () {
			var thumbs = [];

			if ( !this._thumbs ) {
				this.$el.find( 'a.image, a.thumbimage' ).each( function () {
					var $a = $( this ),
						match = $a.attr( 'href' ).match( /[^\/]+$/ );

					if ( match ) {
						thumbs.push(
							new Thumbnail( {
								el: $a,
								filename: decodeURIComponent( match[0] )
							} )
						);
					}
				} );
				this._thumbs = thumbs;
			}
			return this._thumbs;
		},

		/**
		 * FIXME: Change function signature to take the anchor of the heading
		 * @method
		 * @param {String} id of the section
		 * @return {Section}
		 */
		getSection: function ( id ) {
			return this._sectionLookup[ id ];
		},

		/**
		 * @method
		 * @return {Array}
		 */
		getSections: function () {
			return this.sections;
		},

		/**
		 * Returns a jQuery object representing all redlinks on the page.
		 */
		getRedLinks: function () {
			return this.$( '.new' );
		}
	} );

	M.define( 'mobile.startup/Page', Page ).deprecate( 'Page' );

}( mw.mobileFrontend, jQuery ) );

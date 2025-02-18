( function ( M, $ ) {
	var Page = M.require( 'mobile.startup/Page' ),
		extendSearchParams = M.require( 'mobile.search.util/extendSearchParams' );

	/**
	 * @class SearchGateway
	 * @uses mw.Api
	 * @param {mw.Api} api
	 */
	function SearchGateway( api ) {
		this.api = api;
		this.searchCache = {};
		this.generator = mw.config.get( 'wgMFSearchGenerator' );
	}

	SearchGateway.prototype = {
		/**
		 * The namespace to search in.
		 * @type {number}
		 */
		searchNamespace: '0|102|104|106|108|110|112|114|116|118|120|122|124|126|128|130|132|134|136|138|140|142|144|146|148|150|152|154|156|158|160|162|164|166|168|170|172|174|176|178|184',

		/**
		 * Get the data used to do the search query api call.
		 * @method
		 * @param {string} query to search for
		 * @return {Object}
		 */
		getApiData: function ( query ) {
			var prefix = this.generator.prefix,
				data = extendSearchParams( 'search', {
					generator: this.generator.name
				} );

			data.redirects = '';

			data['g' + prefix + 'search'] = query;
			data['g' + prefix + 'namespace'] = this.searchNamespace;
			data['g' + prefix + 'limit'] = 15;

			// If PageImages is being used configure further.
			if ( data.pilimit ) {
				data.pilimit = 15;
				data.pithumbsize = mw.config.get( 'wgMFThumbnailSizes' ).tiny;
			}
			return data;
		},

		/**
		 * Escapes regular expression wildcards (metacharacters) by adding a \\ prefix
		 * @param {string} str a string
		 * @return {Object} a regular expression that can be used to search for that str
		 * @private
		 */
		_createSearchRegEx: function ( str ) {
			// '\[' can be unescaped, but leave it balanced with '`]'
			// eslint-disable-next-line no-useless-escape
			str = str.replace( /[-\[\]{}()*+?.,\\^$|#\s]/g, '\\$&' );
			return new RegExp( '^(' + str + ')', 'ig' );
		},

		/**
		 * Takes a label potentially beginning with term
		 * and highlights term if it is present with strong
		 * @param {string} label a piece of text
		 * @param {string} term a string to search for from the start
		 * @return {string} safe html string with matched terms encapsulated in strong tags
		 * @private
		 */
		_highlightSearchTerm: function ( label, term ) {
			label = $( '<span>' ).text( label ).html();
			term = $( '<span>' ).text( term ).html();

			return label.replace( this._createSearchRegEx( term ), '<strong>$1</strong>' );
		},

		/**
		 * Return data used for creating {Page} objects
		 * @param {string} query to search for
		 * @param {Object} pageInfo from the API
		 * @return {Object} data needed to create a {Page}
		 * @private
		 */
		_getPage: function ( query, pageInfo ) {
			var page = Page.newFromJSON( pageInfo );

			// If displaytext is set in the generator result (eg. by Wikibase), use that as display title.
			// Otherwise default to the page's title.
			// FIXME: Given that displayTitle could have html in it be safe and just highlight text.
			// Note that highlightSearchTerm does full HTML escaping before highlighting.
			page.displayTitle = this._highlightSearchTerm(
				pageInfo.displaytext ? pageInfo.displaytext : page.title,
				query
			);
			page.index = pageInfo.index;

			return page;
		},

		/**
		 * Process the data returned by the api call.
		 * @param {string} query to search for
		 * @param {Object} data from api
		 * @return {Array}
		 * @private
		 */
		_processData: function ( query, data ) {
			var self = this,
				results = [];

			if ( data.query ) {

				results = data.query.pages || {};
				results = Object.keys( results ).map( function ( id ) {
					return self._getPage( query, results[ id ] );
				} );
				// sort in order of index
				results.sort( function ( a, b ) {
					return a.index < b.index ? -1 : 1;
				} );
			}

			return results;
		},

		/**
		 * Perform a search for the given query.
		 * @method
		 * @param {string} query to search for
		 * @return {jQuery.Deferred}
		 */
		search: function ( query ) {
			var result = $.Deferred(),
				request,
				self = this;

			if ( !this.isCached( query ) ) {
				request = this.api.get( this.getApiData( query ) )
					.done( function ( data ) {
						// resolve the Deferred object
						result.resolve( {
							query: query,
							results: self._processData( query, data )
						} );
					} )
					.fail( function () {
						// reset cached result, it maybe contains no value
						self.searchCache[query] = undefined;
						// reject
						result.reject();
					} );

				// cache the result to prevent the execution of one search query twice in one session
				this.searchCache[query] = result.promise( {
					abort: request.abort
				} );
			}

			return this.searchCache[query];
		},

		/**
		 * Check if the search has already been performed in given session.
		 * @method
		 * @param {string} query
		 * @return {boolean}
		 */
		isCached: function ( query ) {
			return Boolean( this.searchCache[ query ] );
		}
	};

	M.define( 'mobile.search.api/SearchGateway', SearchGateway );

}( mw.mobileFrontend, jQuery ) );

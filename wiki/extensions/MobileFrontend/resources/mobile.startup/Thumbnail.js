( function ( M ) {

	var
		View = M.require( 'mobile.view/View' ),
		Thumbnail;

	/**
	 * Representation of a thumbnail
	 *
	 * @class Thumbnail
	 * @extends View
	 */
	Thumbnail = View.extend( {
		/**
		 * @cfg {Object} defaults options
		 * @cfg {String} defaults.filename uri decoded filename including File: prefix associated with thumbnail
		 */
		defaults: {
			filename: undefined
		},
		/** @inheritdoc */
		postRender: function () {
			this.options.description = this.$el.siblings( '.thumbcaption' ).text();
		},
		/**
		 * Obtain description for thumbnail
		 * @return {String}
		 */
		getDescription: function () {
			return this.options.description;
		},
		/**
		 * Return the page title for the thumbnail
		 * @return {String}
		 */
		getFileName: function () {
			return this.options.filename;
		}
	} );

	M.define( 'mobile.startup/Thumbnail', Thumbnail ).deprecate( 'Thumbnail' );

}( mw.mobileFrontend ) );

( function ( M ) {
	var Overlay = M.require( 'mobile.overlays/Overlay' ),
		PhotoList = M.require( 'mobile.gallery/PhotoList' ),
		CommonsCategoryOverlay;

	/**
	 * Overlay for displaying page issues
	 * @class CommonsCategoryOverlay
	 * @extends Overlay
	 */
	CommonsCategoryOverlay = Overlay.extend( {
		/** @inheritdoc */
		postRender: function () {
			Overlay.prototype.postRender.apply( this );
			this.clearSpinner();
			new PhotoList( {
				category: this.options.title
			} ).appendTo( this.$( '.overlay-content' ) );
		}
	} );
	M.define( 'mobile.commonsCategory/CommonsCategoryOverlay', CommonsCategoryOverlay )
		.deprecate( 'modules/commonsCategory/CommonsCategoryOverlay' );
}( mw.mobileFrontend ) );

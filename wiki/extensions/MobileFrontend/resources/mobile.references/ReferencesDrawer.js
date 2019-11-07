( function ( M, $ ) {
	var ReferencesDrawer,
		Drawer = M.require( 'mobile.drawers/Drawer' ),
		Icon = M.require( 'mobile.startup/Icon' ),
		SchemaMobileWebClickTracking = M.require( 'mobile.loggingSchemas/SchemaMobileWebClickTracking' ),
		uiSchema = new SchemaMobileWebClickTracking( {}, 'MobileWebUIClickTracking' );

	/**
	 * Drawer for references
	 * @class ReferencesDrawer
	 * @extends Drawer
	 * @uses Icon
	 */
	ReferencesDrawer = Drawer.extend( {
		/**
		 * @cfg {Object} defaults Default options hash.
		 * @cfg {String} defaults.cancelButton HTML of the button that closes the drawer.
		 */
		defaults: $.extend( {}, Drawer.prototype.defaults, {
			cancelButton: new Icon( {
				name: 'close-gray',
				additionalClassNames: 'cancel',
				label: mw.msg( 'mobile-frontend-overlay-close' )
			} ).toHtmlString(),
			citation: new Icon( {
				name: 'citation',
				additionalClassNames: 'text',
				hasText: true,
				label: mw.msg( 'mobile-frontend-references-citation' )
			} ).toHtmlString()
		} ),
		/** @inheritdoc */
		show: function () {
			uiSchema.log( {
				name: 'reference'
			} );
			return Drawer.prototype.show.apply( this, arguments );
		},
		className: 'drawer position-fixed text references',
		template: mw.template.get( 'mobile.references', 'Drawer.hogan' ),
		/**
		 * @inheritdoc
		 */
		closeOnScroll: false,
		/**
		 * @inheritdoc
		 */
		postRender: function () {
			var windowHeight = $( window ).height();

			Drawer.prototype.postRender.apply( this );

			// make sure the drawer doesn't take up more than 50% of the viewport height
			if ( windowHeight / 2 < 400 ) {
				this.$el.css( 'max-height', windowHeight / 2 );
			}

			this.on( 'show', $.proxy( this, 'onShow' ) );
			this.on( 'hide', $.proxy( this, 'onHide' ) );
		},
		/**
		 * Make body not scrollable
		 */
		onShow: function () {
			$( 'body' ).addClass( 'drawer-enabled' );
		},
		/**
		 * Restore body scroll
		 */
		onHide: function () {
			$( 'body' ).removeClass( 'drawer-enabled' );
		}
	} );

	M.define( 'mobile.references/ReferencesDrawer', ReferencesDrawer )
		.deprecate( 'modules/references/ReferencesDrawer' );
}( mw.mobileFrontend, jQuery ) );

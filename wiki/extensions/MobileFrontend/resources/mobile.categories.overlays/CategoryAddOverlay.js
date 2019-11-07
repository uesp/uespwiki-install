( function ( M, $ ) {

	var CategoryAddOverlay,
		Overlay = M.require( 'mobile.overlays/Overlay' ),
		CategoryApi = M.require( 'mobile.categories.overlays/CategoryApi' ),
		CategoryLookupInputWidget = M.require( 'mobile.categories.overlays/CategoryLookupInputWidget' ),
		icons = M.require( 'mobile.startup/icons' ),
		toast = M.require( 'mobile.toast/toast' );

	/**
	 * Displays the list of categories for a page
	 * @class CategoryAddOverlay
	 * @extends Overlay
	 * @uses CategoryApi
	 */
	CategoryAddOverlay = Overlay.extend( {
		/**
		 * @inheritdoc
		 * @cfg {Object} defaults Default options hash.
		 * @cfg {String} defaults.waitMsg Text that displays while a page edit is being saved.
		 * @cfg {String} defaults.waitIcon HTML of the icon that displays while a page edit
		 * is being saved.
		 */
		defaults: {
			headerButtonsListClassName: 'overlay-action',
			waitMsg: mw.msg( 'mobile-frontend-categories-add-wait' ),
			waitIcon: icons.spinner().toHtmlString()
		},
		/**
		 * @inheritdoc
		 */
		events: $.extend( {}, Overlay.prototype.events, {
			'click .save': 'onSaveClick',
			'click .suggestion': 'onCategoryClick'
		} ),
		/**
		 * @inheritdoc
		 */
		className: 'category-overlay overlay',
		/**
		 * @inheritdoc
		 */
		template: mw.template.get( 'mobile.categories.overlays', 'CategoryAddOverlay.hogan' ),
		/**
		 * @inheritdoc
		 */
		templatePartials: {
			header: mw.template.get( 'mobile.categories.overlays', 'CategoryAddOverlayHeader.hogan' ),
			saveHeader: mw.template.get( 'mobile.editor.common', 'saveHeader.hogan' )
		},

		/**
		 * @inheritdoc
		 */
		initialize: function ( options ) {
			options.heading = mw.msg( 'mobile-frontend-categories-add-heading', options.title );
			Overlay.prototype.initialize.apply( this, arguments );
		},

		/**
		 * @inheritdoc
		 */
		postRender: function () {
			var input;

			Overlay.prototype.postRender.apply( this );

			this.$suggestions = this.$( '.category-suggestions' );
			this.$saveButton = this.$( '.save' );
			this.wgCategories = this.options.categories;
			this.title = this.options.title;

			this.api = new CategoryApi();
			input = new CategoryLookupInputWidget( {
				api: this.api,
				suggestions: this.$suggestions,
				categories: this.wgCategories,
				saveButton: this.$saveButton
			} );
			this.$( '.category-add-input' ).append(
				input.$element
			);
		},

		/**
		 * Handle a click on an added category
		 * @method
		 * @param {jQuery.Event} ev
		 */
		onCategoryClick: function ( ev ) {
			$( ev.target ).closest( '.suggestion' ).detach();
			if ( this.$( '.suggestion' ).length > 0 ) {
				this.$saveButton.prop( 'disabled', false );
			} else {
				this.$saveButton.prop( 'disabled', true );
			}
		},

		/**
		 * Handle the click on the save button. Builds a string of new categories
		 * and add it to the article.
		 */
		onSaveClick: function () {
			var newCategories = '',
				self = this;

			// show the loading spinner and disable the safe button
			this.showHidden( '.saving-header' );

			// add wikitext to add to the page
			$.each( $( '.suggestion' ), function () {
				var data = $( this ).data( 'title' );

				if ( data ) {
					// add the new categories in wikitext markup
					newCategories += '[[' + data + ']] ';
				}
			} );

			// if there are no categories added, don't do anything (the user shouldn't see the save button)
			if ( newCategories.length === 0 ) {
				toast.show( mw.msg( 'mobile-frontend-categories-nodata' ), 'toast error' );
			} else {
				// save the new categories
				this.api.save( this.title, newCategories ).done( function () {
					M.emit( 'category-added' );
					window.location.hash = '#/categories';
				} ).fail( function () {
					self.showHidden( '.initial-header' );
					self.$safeButton.prop( 'disabled', false );
					// FIXME: Should be a better error message
					toast.show( mw.msg( 'mobile-frontend-categories-nodata' ), 'toast error' );
				} );
			}
		}
	} );

	M.define( 'mobile.categories.overlays/CategoryAddOverlay', CategoryAddOverlay )
		.deprecate( 'categories/CategoryAddOverlay' );

}( mw.mobileFrontend, jQuery ) );

( function ( M, $ ) {

	var BetaOptinPanel,
		Button = M.require( 'mobile.startup/Button' ),
		Panel = M.require( 'mobile.startup/Panel' );

	/**
	 * @class BetaOptinPanel
	 * @extends Panel
	 */
	BetaOptinPanel = Panel.extend( {
		className: 'panel panel-inline visible',
		templatePartials: {
			button: Button.prototype.template
		},
		template: mw.template.get( 'mobile.betaoptin', 'Panel.hogan' ),
		defaults: {
			postUrl: undefined,
			editToken: mw.user.tokens.get( 'editToken' ),
			enableImages: mw.config.get( 'wgImagesDisabled' ) ? 0 : 1,
			text: mw.msg( 'mobile-frontend-panel-betaoptin-msg' ),
			buttons: [
				new Button( {
					constructive: true,
					additionalClassNames: 'optin',
					label: mw.msg( 'mobile-frontend-panel-ok' )
				} ).options,
				new Button( {
					additionalClassNames: 'cancel',
					label: mw.msg( 'mobile-frontend-panel-cancel' )
				} ).options
			]
		},
		events: $.extend( {}, Panel.prototype.events, {
			'click .optin': 'onOptin'
		} ),
		/**
		 * Cancel event handler
		 * @param {jQuery.Event} ev
		 */
		onOptin: function ( ev ) {
			$( ev.currentTarget ).closest( 'form' ).submit();
		}
	} );

	M.define( 'mobile.betaoptin/BetaOptinPanel', BetaOptinPanel );

}( mw.mobileFrontend, jQuery ) );

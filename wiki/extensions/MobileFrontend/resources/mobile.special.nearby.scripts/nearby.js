( function ( M, $ ) {
	var Icon = M.require( 'mobile.startup/Icon' ),
		endpoint = mw.config.get( 'wgMFNearbyEndpoint' ),
		router = M.require( 'mobile.startup/router' ),
		Nearby = M.require( 'mobile.nearby/Nearby' );

	$( function () {
		var
			nearby,
			options = {
				el: $( '#mw-mf-nearby' ),
				funnel: 'nearby'
			},
			$btn = $( '#secondary-button' ).parent(),
			icon,
			$iconContainer,
			$icon;

		// Remove user button
		if ( $btn.length ) {
			$btn.remove();
		}

		// Create refresh button on the header
		icon = new Icon( {
			name: 'refresh',
			id: 'secondary-button',
			additionalClassNames: 'main-header-button',
			tagName: 'a',
			title: mw.msg( 'mobile-frontend-nearby-refresh' ),
			label: mw.msg( 'mobile-frontend-nearby-refresh' )
		} );
		$iconContainer = $( '<div>' );

		$icon = icon.$el.on( 'click', refreshCurrentLocation )
			.appendTo( $iconContainer );

		$iconContainer.appendTo( '.header' );

		/**
		 * Initialize or instantiate Nearby with options
		 * @method
		 * @ignore
		 * @param {Object} options
		 */
		function refresh( options ) {
			if ( nearby ) {
				nearby.initialize( options );
			} else {
				if ( endpoint ) {
					mw.loader.using( 'mobile.foreignApi' ).done( function () {
						var JSONPForeignApi = M.require( 'mobile.foreignApi/JSONPForeignApi' );
						options.api = new JSONPForeignApi( endpoint );
						nearby = new Nearby( options );
					} );
				} else {
					options.api = new mw.Api();
					nearby = new Nearby( options );
				}
			}
		}

		// Routing on the nearby view

		/*
		 * #/coords/lat,long
		 */
		router.route( /^\/coord\/(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/, function ( lat, lon ) {
			$icon.hide();
			// Search with coordinates
			refresh( $.extend( {}, options, {
				latitude: lat,
				longitude: lon
			} ) );
		} );

		/*
		 * #/page/PageTitle
		 */
		router.route( /^\/page\/(.+)$/, function ( pageTitle ) {
			$icon.hide();
			refresh( $.extend( {}, options, {
				pageTitle: pageTitle
			} ) );
		} );

		/**
		 * Refresh the current view using browser geolocation api
		 * @ignore
		 */
		function refreshCurrentLocation() {
			$icon.show();
			refresh( $.extend( {}, options, {
				useCurrentLocation: true
			} ) );
		}

		/*
		 * Anything else search with current location
		 * FIXME: The regex has to negate the rest of the routes because every time we
		 * define a route with router.route that route gets matched against the
		 * current hash.
		 */
		router.route( /^(?!.coord|.page).*$/, refreshCurrentLocation );

	} );

}( mw.mobileFrontend, jQuery ) );

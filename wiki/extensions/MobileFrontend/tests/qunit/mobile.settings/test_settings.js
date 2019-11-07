( function ( M, $ ) {

	var settings = M.require( 'mobile.settings/settings' );

	QUnit.module( 'MobileFrontend settings', {
		setup: function () {
			var cookieCache = {},
				localStorageCache = {};

			// Stub cookie and localStorage
			this.sandbox.stub( $, 'cookie', function ( key, value ) {
				if ( value !== undefined ) {
					// setter
					cookieCache[key] = value;
				} else {
					// getter
					// $.cookie returns null for missing items
					return cookieCache[key] || null;
				}
			} );
			this.sandbox.stub( $, 'removeCookie', function ( key ) {
				delete cookieCache[key];
			} );

			this.sandbox.stub( localStorage, 'setItem', function ( key, value ) {
				localStorageCache[key] = value;
			} );
			this.sandbox.stub( localStorage, 'getItem', function ( key ) {
				// localStorage returns null for missing items
				return localStorageCache[key] || null;
			} );
			this.sandbox.stub( localStorage, 'removeItem', function ( key ) {
				delete localStorageCache[key];
			} );
		},
		tearDown: function () {
			this.sandbox.restore();
		}
	} );

	QUnit.skip( 'check cookies', 2, function ( assert ) {
		assert.strictEqual(
			settings.cookiesEnabled(),
			true,
			'Cookies are enabled.'
		);

		$.cookie.restore();
		this.sandbox.stub( $, 'cookie' );
		assert.strictEqual(
			settings.cookiesEnabled(),
			false,
			'Cookies are disabled.'
		);
	} );

	QUnit.skip( 'localstorage', 3, function ( assert ) {
		assert.strictEqual(
			settings.get( 'test_key' ),
			null,
			'Initially test_key is not in settings.'
		);

		settings.save( 'test_key', 'yep' );
		assert.strictEqual(
			settings.get( 'test_key', 'yep' ),
			'yep',
			'test_key has the correct value.'
		);

		settings.remove( 'test_key' );
		assert.strictEqual(
			settings.get( 'test_key' ),
			null,
			'test_key has been correctly removed.'
		);
	} );

	QUnit.skip( 'cookie fallback', 3, function ( assert ) {
		assert.strictEqual(
			settings.get( 'test_key', true ),
			null,
			'Initially test_key is not in settings.'
		);

		settings.save( 'test_key', 'yep', true );
		assert.strictEqual(
			settings.get( 'test_key', true ),
			'yep',
			'test_key has the correct value.'
		);

		settings.remove( 'test_key', true );
		assert.strictEqual(
			settings.get( 'test_key', true ),
			null,
			'test_key has been correctly removed.'
		);
	} );

	QUnit.module( 'MobileFrontend settings - localStorage', {
		setup: function () {
			var localStorageCache = {};

			this.sandbox.stub( mw.storage, 'isLocalStorageSupported', true );
			this.sandbox.stub( localStorage, 'setItem' ).throws();
			this.sandbox.stub( localStorage, 'getItem', function ( key ) {
				// localStorage returns null for missing items
				return localStorageCache[key] || null;
			} );
			this.sandbox.stub( localStorage, 'removeItem', function ( key ) {
				delete localStorageCache[key];
			} );

		},
		tearDown: function () {
			this.sandbox.restore();
		}
	} );
	QUnit.skip( 'without cookies or localStorage', 3, function ( assert ) {
		assert.strictEqual(
			settings.save( 'test_key', 'yep' ),
			false,
			'Returns false when unable to save.' );

		assert.strictEqual(
			settings.get( 'test_key' ),
			null,
			'Initially test_key is not in settings.'
		);

		settings.remove( 'test_key' );
		assert.strictEqual(
			settings.get( 'test_key' ),
			null,
			'test_key has been correctly removed (even if it didn\'t exist in the first place'
		);
	} );

}( mw.mobileFrontend, jQuery ) );

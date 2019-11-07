( function ( $, M ) {

	var SearchApi = M.require( 'mobile.search.api/SearchApi' ),
		api;

	QUnit.module( 'MobileFrontend: SearchApi', {
		setup: function () {
			api = new SearchApi();
			this.sandbox.stub( SearchApi.prototype, 'get', function () {
				return $.Deferred().resolve( {
					warnings: {
						query: {
							'*': 'Formatting of continuation data will be changing soon. To continue using the current formatting, use the "rawcontinue" parameter. To begin using the new format, pass an empty string for "continue" in the initial query.'
						}
					},
					query: {
						redirects: [
							{
								from: 'Barack obama',
								to: 'Barack Obama'
							},
							{
								from: 'Barack',
								to: 'Claude Monet'
							}
						],
						pages: {
							2: {
								pageid: 2,
								ns: 0,
								title: 'Claude Monet',
								thumbnail: {
									source: 'http://127.0.0.1:8080/images/thumb/5/54/Claude_Monet%2C_Impression%2C_soleil_levant.jpg/80px-Claude_Monet%2C_Impression%2C_soleil_levant.jpg',
									width: 80,
									height: 62
								}
							},
							60: {
								pageid: 60,
								ns: 0,
								title: 'Barack Obama',
								thumbnail: {
									source: 'http://127.0.0.1:8080/images/thumb/8/8d/President_Barack_Obama.jpg/64px-President_Barack_Obama.jpg',
									width: 64,
									height: 80
								}
							}
						},
						prefixsearch: [
							{
								ns: 0,
								title: 'Barack',
								pageid: 245
							},
							{
								ns: 0,
								title: 'Barack Obama',
								pageid: 60
							},
							{
								ns: 0,
								title: 'Barack obama',
								pageid: 244
							}
						]
					}
				} );
			} );
		}
	} );

	QUnit.test( '._highlightSearchTerm', 14, function ( assert ) {
		var data = [
			[ 'Hello World', 'Hel', '<strong>Hel</strong>lo World' ],
			[ 'Hello kitty', 'el', 'Hello kitty' ], // not at start
			[ 'Hello worl', 'hel', '<strong>Hel</strong>lo worl' ],
			[ 'Belle & Sebastian', 'Belle & S', '<strong>Belle &amp; S</strong>ebastian' ],
			[ 'Belle & the Beast', 'Belle &amp;', 'Belle &amp; the Beast' ],
			[ 'with ? in it', 'with ?', '<strong>with ?</strong> in it' ], // not at start
			[ 'Title with ? in it', 'with ?', 'Title with ? in it' ], // not at start
			[ 'AT&T', 'a', '<strong>A</strong>T&amp;T' ],
			[ 'AT&T', 'at&', '<strong>AT&amp;</strong>T' ],
			[ '<tag', '&lt;tag', '&lt;tag' ],
			[ '& this is a weird title', '&', '<strong>&amp;</strong> this is a weird title' ],
			[ '& this is a weird title', '&a', '&amp; this is a weird title' ],
			[ '&lt;t', '<t', '&amp;lt;t' ],
			[ '<script>alert("FAIL")</script> should be safe',
				'<script>alert("FAIL"', '<strong>&lt;script&gt;alert("FAIL"</strong>)&lt;/script&gt; should be safe' ]
		];

		$.each( data, function ( i, item ) {
			assert.strictEqual( api._highlightSearchTerm( item[ 0 ], item[ 1 ] ), item[ 2 ], 'highlightSearchTerm test ' + i );
		} );
	} );

	QUnit.test( 'show redirect targets', 6, function ( assert ) {
		api.search( 'barack' ).done( function ( response ) {
			assert.strictEqual( response.query, 'barack' );
			assert.strictEqual( response.results.length, 2 );
			assert.strictEqual( response.results[ 0 ].displayTitle, 'Claude Monet' );
			assert.strictEqual( response.results[ 0 ].thumbnail.width, 80 );
			assert.strictEqual( response.results[ 1 ].displayTitle, '<strong>Barack</strong> Obama' );
			assert.strictEqual( response.results[ 1 ].title, 'Barack Obama' );
		} );
	} );

}( jQuery, mw.mobileFrontend ) );

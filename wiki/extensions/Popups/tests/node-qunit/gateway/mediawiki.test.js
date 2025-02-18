import { createModel } from '../../../src/preview/model';
import createMediaWikiApiGateway from '../../../src/gateway/mediawiki';

var DEFAULT_CONSTANTS = {
		THUMBNAIL_SIZE: 300,
		EXTRACT_LENGTH: 525
	},
	MEDIAWIKI_API_RESPONSE = {
		query: {
			pages: [
				{
					contentmodel: 'wikitext',
					extract: 'Richard Paul "Rick" Astley (/\u02c8r\u026ak \u02c8\u00e6stli/; born 6 February 1966) is an English singer, songwriter, musician, and radio personality. His 1987 song, "Never Gonna Give You Up" was a No. 1 hit single in 25 countries. By the time of his retirement in 1993, Astley had sold approximately 40 million records worldwide.\nAstley made a comeback in 2007, becoming an Internet phenomenon when his video "Never Gonna Give You Up" became integral to the meme known as "rickrolling". Astley was voted "Best Act Ever" by Internet users at the...',
					lastrevid: 748725726,
					length: 32132,
					fullurl: 'https://en.wikipedia.org/wiki/Rick_Astley',
					editurl: 'https://en.wikipedia.org/w/index.php?title=Rick_Astley&action=edit',
					canonicalurl: 'https://en.wikipedia.org/wiki/Rick_Astley',
					ns: 0,
					pageid: 447541,
					pagelanguage: 'en',
					pagelanguagedir: 'ltr',
					pagelanguagehtmlcode: 'en',
					revisions: [ {
						timestamp: '2016-11-10T00:14:14Z'
					} ],
					thumbnail: {
						height: 300,
						source: 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6d/Rick_Astley_-_Pepsifest_2009.jpg/200px-Rick_Astley_-_Pepsifest_2009.jpg',
						width: 200
					},
					title: 'Rick Astley',
					touched: '2016-11-10T00:14:14Z'
				}
			]
		}
	},
	MEDIAWIKI_API_RESPONSE_PREVIEW_MODEL = createModel(
		'Rick Astley',
		'https://en.wikipedia.org/wiki/Rick_Astley',
		'en',
		'ltr',
		[ document.createTextNode( 'Richard Paul "Rick" Astley is an English singer, songwriter, musician, and radio personality. His 1987 song, "Never Gonna Give You Up" was a No. 1 hit single in 25 countries. By the time of his retirement in 1993, Astley had sold approximately 40 million records worldwide.\nAstley made a comeback in 2007, becoming an Internet phenomenon when his video "Never Gonna Give You Up" became integral to the meme known as "rickrolling". Astley was voted "Best Act Ever" by Internet users at the' ) ],
		{
			height: 300,
			source: 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6d/Rick_Astley_-_Pepsifest_2009.jpg/200px-Rick_Astley_-_Pepsifest_2009.jpg',
			width: 200
		}
	);

QUnit.module( 'ext.popups/gateway/mediawiki', {
	beforeEach: function () {
		window.mediaWiki.RegExp = {
			escape: this.sandbox.spy( function ( str ) {
				return str.replace( /([\\{}()|.?*+\-\^$\[\]])/g, '\\$1' );
			} )
		};
	},
	afterEach: function () {
		window.mediaWiki.RegExp = null;
	}
} );

QUnit.test( 'MediaWiki API gateway is called with correct arguments', function ( assert ) {
	var spy = this.sandbox.spy(),
		api = {
			get: spy
		},
		gateway = createMediaWikiApiGateway( api, DEFAULT_CONSTANTS ),
		expectedOptions = {
			action: 'query',
			prop: 'info|extracts|pageimages|revisions|info',
			formatversion: 2,
			redirects: true,
			exintro: true,
			exchars: 525,
			explaintext: true,
			piprop: 'thumbnail',
			pithumbsize: DEFAULT_CONSTANTS.THUMBNAIL_SIZE,
			pilicense: 'any',
			rvprop: 'timestamp',
			inprop: 'url',
			titles: 'Test Title',
			smaxage: 300,
			maxage: 300,
			uselang: 'content'
		},
		expectedHeaders = {
			headers: {
				'X-Analytics': 'preview=1'
			}
		};

	gateway.fetch( 'Test Title' );

	assert.deepEqual( spy.getCall( 0 ).args[ 0 ], expectedOptions, 'options' );
	assert.deepEqual( spy.getCall( 0 ).args[ 1 ], expectedHeaders, 'headers' );
} );

QUnit.test( 'MediaWiki API gateway is correctly extracting the page data from the response ', function ( assert ) {
	var api = {
			get: this.sandbox.stub()
		},
		gateway = createMediaWikiApiGateway( api, DEFAULT_CONSTANTS ),
		errorCases = [
			{},
			{
				query: {}
			},
			{
				query: {
					pages: []
				}
			}
		],
		successCases = [
			[
				{
					query: {
						pages: [ { someData: 'Yes' } ]
					}
				},
				{
					someData: 'Yes'
				}
			]
		];

	assert.expect( errorCases.length + successCases.length );

	$.each( errorCases, function ( _, data ) {
		assert.throws( function () {
			gateway.extractPageFromResponse( data );
		} );
	} );

	$.each( successCases, function ( _, data ) {
		assert.deepEqual(
			gateway.extractPageFromResponse( data[ 0 ] ),
			data[ 1 ]
		);
	} );
} );

QUnit.test( 'MediaWiki API gateway is correctly converting the page data to a model', function ( assert ) {
	var gateway = createMediaWikiApiGateway(),
		page = gateway.extractPageFromResponse( MEDIAWIKI_API_RESPONSE );

	assert.deepEqual(
		gateway.convertPageToModel( gateway.formatPlainTextExtract( page ) ),
		MEDIAWIKI_API_RESPONSE_PREVIEW_MODEL
	);
} );

QUnit.test( 'MediaWiki API gateway handles the API failure', function ( assert ) {
	var p,
		deferred = $.Deferred(),
		api = {
			get: this.sandbox.stub().returns( deferred.promise() )
		},
		gateway = createMediaWikiApiGateway( api, DEFAULT_CONSTANTS );

	p = gateway.getPageSummary( 'Test Title' ).catch( function () {
		assert.ok( true );
	} );

	deferred.reject();
	return p;
} );

QUnit.test( 'MediaWiki API gateway returns the correct data ', function ( assert ) {
	var api = {
			get: this.sandbox.stub().returns(
				$.Deferred().resolve( MEDIAWIKI_API_RESPONSE ).promise()
			)
		},
		gateway = createMediaWikiApiGateway( api, DEFAULT_CONSTANTS );

	return gateway.getPageSummary( 'Test Title' ).then( function ( result ) {
		assert.deepEqual( result, MEDIAWIKI_API_RESPONSE_PREVIEW_MODEL );
	} );
} );

QUnit.test( 'MediaWiki API gateway handles missing pages ', function ( assert ) {
	var response = {
			query: {
				pages: [ {
					canonicalurl: 'http://dev.wiki.local.wmftest.net:8080/wiki/Missing_page',
					contentmodel: 'wikitext',
					editurl: 'http://dev.wiki.local.wmftest.net:8080/w/index.php?title=Missing_page&action=edit',
					fullurl: 'http://dev.wiki.local.wmftest.net:8080/wiki/Missing_page',
					missing: true,
					ns: 0,
					pagelanguage: 'en',
					pagelanguagedir: 'ltr',
					pagelanguagehtmlcode: 'en',
					title: 'Missing page'
				} ]
			}
		},
		model = createModel(
			'Missing page',
			'http://dev.wiki.local.wmftest.net:8080/wiki/Missing_page',
			'en',
			'ltr',
			undefined,
			undefined
		),
		api = {
			get: this.sandbox.stub().returns(
				$.Deferred().resolve( response ).promise()
			)
		},
		gateway = createMediaWikiApiGateway( api, DEFAULT_CONSTANTS );

	return gateway.getPageSummary( 'Test Title' ).then( function ( result ) {
		assert.deepEqual( result, model );
	} );
} );

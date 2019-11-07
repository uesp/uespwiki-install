/*!
 * Grunt file
 *
 * @package MobileFrontend
 */

/*jshint node:true, strict:false*/
module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-mkdir' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-jscs' );
	grunt.loadNpmTasks( 'grunt-qunit-istanbul' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-notify' );
	grunt.loadNpmTasks( 'grunt-banana-checker' );

	grunt.initConfig( {
		URL: process.env.MEDIAWIKI_URL || 'http://127.0.0.1:8080/w/index.php/',
		QUNIT_DEBUG: ( process.env.QUNIT_DEBUG && '&debug=true' || '' ),
		QUNIT_FILTER: ( process.env.QUNIT_FILTER && '&filter=' + process.env.QUNIT_FILTER ) || '',
		QUNIT_MODULE: ( process.env.QUNIT_MODULE && '&module=' + process.env.QUNIT_MODULE ) || '',
		files: {
			js: 'resources/**/*.js',
			jsTests: 'tests/qunit/**/*.js'
		},
		mkdir: {
			docs: {
				options: {
					create: [ 'docs' ]
				}
			}
		},
		jshint: {
			options: {
				jshintrc: true
			},
			all: [
				'<%= files.js %>',
				'<%= files.jsTests %>'
			]
		},
		jscs: {
			main: [
				'<%= files.js %>'
			],
			test: {
				options: {
					config: 'tests/.jscsrc.js'
				},
				files: {
					src: '<%= files.jsTests %>'
				}
			}
		},
		qunit: {
			all: {
				options: {
					timeout: 20000,
					urls: [
						'<%= URL %>Special:JavaScriptTest/qunit?useformat=mobile' +
						'<%= QUNIT_DEBUG %><%= QUNIT_FILTER %><%= QUNIT_MODULE %>'
					]
				}
			},
			cov: {
				options: {
					timeout: 20000,
					urls: [
						'<%= URL %>Special:JavaScriptTest/qunit?debug=true&useformat=mobile' +
						'<%= QUNIT_FILTER %><%= QUNIT_MODULE %>'
					],
					coverage: {
						linesThresholdPct: 65,
						statementsThresholdPct: 65,
						functionsThresholdPct: 61,
						branchesThresholdPct: 52,
						prefixUrl: 'w/', // Prefix url on the server
						baseUrl: '../../', // Path to assets from the server (extensions/Mobile...)
						src: [
							'<%= files.js %>'
						],
						instrumentedFiles: 'tests/report/tmp',
						htmlReport: 'tests/report'
					}
				}
			}
		},
		watch: {
			lint: {
				files: [ '<%= files.js %>', '<%= files.jsTests %>' ],
				tasks: [ 'lint' ]
			},
			scripts: {
				files: [ '<%= files.js %>', '<%= files.jsTests %>' ],
				tasks: [ 'test' ]
			},
			configFiles: {
				files: [ 'Gruntfile.js' ],
				options: {
					reload: true
				}
			}
		},
		banana: {
			all: 'i18n/'
		}
	} );

	grunt.registerTask( 'lint', [ 'jshint', 'jscs', 'banana' ] );

	// grunt test will be run by npm test which will be run by Jenkins
	// Do not execute qunit here, or other tasks that require full mediawiki
	// running.
	grunt.registerTask( 'test', [ 'lint' ] );

	grunt.registerTask( 'default', [ 'test' ] );
};

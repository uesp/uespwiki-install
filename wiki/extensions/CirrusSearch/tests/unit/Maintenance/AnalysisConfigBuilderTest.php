<?php

namespace CirrusSearch\Tests\Maintenance;

use CirrusSearch\Maintenance\AnalysisConfigBuilder;
use CirrusSearch\Test\HashSearchConfig;
use CirrusSearch\CirrusTestCase;

/**
 * @group CirrusSearch
 * @covers CirrusSearch\Maintenance\AnalysisConfigBuilder
 */
class AnalysisConfigBuilderTest extends CirrusTestCase {
	/** @dataProvider provideASCIIFoldingFilters */
	public function testASCIIFoldingFix( array $input, array $expected ) {
		$config = new HashSearchConfig([]);
		$plugins = ['extra', 'analysis-icu'];
		$builder = new AnalysisConfigBuilder( 'en', $plugins, $config );
		$result = $builder->fixAsciiFolding( $input );

		$this->assertEquals( $expected['analyzer'], $result['analyzer'] );
		if ( isset( $expected['filter'] ) ) {
			$this->assertEquals( $expected['filter'], $result['filter'] );
		} else {
			$this->assertFalse( isset ( $result['filter'] ) );
		}
	}

	/** @dataProvider provideICUFoldingFilters */
	public function testICUFolding( array $input, array $expected ) {
		$config = new HashSearchConfig( ['CirrusSearchUseIcuFolding' => 'yes' ] );
		$plugins = ['extra', 'analysis-icu'];
		$builder = new AnalysisConfigBuilder( 'unknown_language', $plugins, $config );
		$result = $builder->enableICUFolding( $input );
		$this->assertEquals( $expected['analyzer'], $result['analyzer'] );

		// Test default
		$config = new HashSearchConfig( ['CirrusSearchUseIcuFolding' => 'default' ] );
		$plugins = ['extra', 'analysis-icu'];
		$builder = new AnalysisConfigBuilder( 'en', $plugins, $config );
		$result = $builder->enableICUFolding( $input );
		$this->assertEquals( $expected['analyzer'], $result['analyzer'] );

		// test BC code
		$config = new HashSearchConfig( ['CirrusSearchUseIcuFolding' => true ] );
		$plugins = ['extra', 'analysis-icu'];
		$builder = new AnalysisConfigBuilder( 'en', $plugins, $config );
		$this->assertTrue( $builder->isIcuFolding() );
		$this->assertEquals( $expected['analyzer'], $result['analyzer'] );

		// Test that we can force disabling ICU folding
		$config = new HashSearchConfig( ['CirrusSearchUseIcuFolding' => 'no' ] );
		$plugins = ['extra', 'analysis-icu'];
		$builder = new AnalysisConfigBuilder( 'en', $plugins, $config );
		$this->assertFalse( $builder->isIcuFolding() );

		// Test that ICU folding cannot be enable without the required plugins
		$config = new HashSearchConfig( ['CirrusSearchUseIcuFolding' => 'yes' ] );
		$plugins = ['analysis-icu'];
		$builder = new AnalysisConfigBuilder( 'en', $plugins, $config );
		$this->assertFalse( $builder->isIcuFolding() );
	}

	/** @dataProvider provideICUTokenizer */
	public function testICUTokinizer( array $input, array $expected ) {
		$config = new HashSearchConfig( ['CirrusSearchUseIcuTokenizer' => 'yes'] );
		$plugins = ['extra', 'analysis-icu'];
		$builder = new AnalysisConfigBuilder( 'en', $plugins, $config );
		$result = $builder->enableICUTokenizer( $input );
		$this->assertEquals( $expected['analyzer'], $result['analyzer'] );
	}

	public static function provideASCIIFoldingFilters() {
		return [
			'only custom is updated' => [
				[
					'analyzer' => [
						'french' => [
							'type' => 'french',
							'filter' => ['asciifolding_preserve']
						]
					],
				],
				[
					'analyzer' => [
						'french' => [
							'type' => 'french',
							'filter' => ['asciifolding_preserve']
						]
					],
				],
			],
			'only asciifolding_preserve is updated' => [
				[
					'analyzer' => [
						'french' => [
							'type' => 'custom',
							'filter' => ['asciifolding']
						]
					],
				],
				[
					'analyzer' => [
						'french' => [
							'type' => 'custom',
							'filter' => ['asciifolding']
						],
					],
				],
			],
			'dedup filter is appended after asciifolding_preserve' => [
				[
					'filter' => [
						'asciifolding_preserve' => [
							'type' => 'asciifolding',
							'preserve_original' => true,
						],
					],
					'analyzer' => [
						'french' => [
							'type' => 'custom',
							'filter' => [
								'asciifolding_preserve',
								'french_stemmer',
							],
						],
					],
				],
				[
					'filter' => [
						'asciifolding_preserve' => [
							'type' => 'asciifolding',
							'preserve_original' => true,
						],
						'dedup_asciifolding' => [
							'type' => 'unique',
							'only_on_same_position' => true,
						],
					],
					'analyzer' => [
						'french' => [
							'type' => 'custom',
							'filter' => [
								'asciifolding_preserve',
								'dedup_asciifolding',
								'french_stemmer',
							],
						],
					],
				],
			],
		];
	}

	public static function provideICUFoldingFilters() {
		return [
			'only custom is updated' => [
				[
					'analyzer' => [
						'french' => [
							'type' => 'french',
							'filter' => ['asciifolding']
						]
					],
				],
				[
					'analyzer' => [
						'french' => [
							'type' => 'french',
							'filter' => ['asciifolding']
						]
					],
				],
			],
			'simple' => [
				[
					'analyzer' => [
						'plain' => [
							'type' => 'custom',
							'filter' => [
								'icu_normalizer',
								'asciifolding',
								'kstem'
							],
						],
					],
				],
				[
					'analyzer' => [
						'plain' => [
							'type' => 'custom',
							'filter' => [
								'icu_normalizer',
								'icu_folding',
								'kstem'
							],
						],
					],
				],
			],
			'preserve' => [
				[
					'analyzer' => [
						'plain' => [
							'type' => 'custom',
							'filter' => [
								'icu_normalizer',
								'asciifolding_preserve',
								'kstem'
							],
						],
					],
				],
				[
					'analyzer' => [
						'plain' => [
							'type' => 'custom',
							'filter' => [
								'icu_normalizer',
								'preserve_original_recorder',
								'icu_folding',
								'preserve_original',
								'kstem'
							],
						],
					],
				],
			],
			'preserve no lowercase before' => [
				[
					'analyzer' => [
						'plain' => [
							'type' => 'custom',
							'filter' => [
								'random_filter',
								'asciifolding_preserve',
								'icu_normalizer',
								'kstem'
							],
						],
					],
				],
				[
					'analyzer' => [
						'plain' => [
							'type' => 'custom',
							'filter' => [
								'random_filter',
								'icu_nfkc_normalization',
								'preserve_original_recorder',
								'icu_folding',
								'preserve_original',
								'icu_normalizer',
								'kstem'
							],
						],
					],
				],
			],
			'icu_folding is explicitly added to plain analyzers' => [
				[
					'analyzer' => [
						'random_analyzer' => [
							'type' => 'custom',
							'filter' => [
								'random_filter',
							],
						],
						'plain' => [
							'type' => 'custom',
							'filter' => [
								'random_filter',
							],
						],
						'source_text_plain' => [
							'type' => 'custom',
							'filter' => [
								'random_filter',
								'icu_normalizer',
							],
						],
					],
				],
				[
					'analyzer' => [
						'random_analyzer' => [
							'type' => 'custom',
							'filter' => [
								'random_filter',
							],
						],
						'plain' => [
							'type' => 'custom',
							'filter' => [
								'random_filter',
								'icu_nfkc_normalization',
								'preserve_original_recorder',
								'icu_folding',
								'preserve_original',
							],
						],
						'source_text_plain' => [
							'type' => 'custom',
							'filter' => [
								'random_filter',
								'icu_normalizer',
							],
						],
					],
				],
			],
		];
	}

	public static function provideICUTokenizer() {
		return [
			'only custom is updated' => [
				[
					'analyzer' => [
						'french' => [
							'type' => 'french',
							'filter' => ['random']
						]
					],
				],
				[
					'analyzer' => [
						'french' => [
							'type' => 'french',
							'filter' => ['random']
						]
					],
				],
			],
			'only custom is updated' => [
				[
					'analyzer' => [
						'chinese' => [
							'type' => 'custom',
							'tokenizer' => 'standard',
							'filter' => ['random']
						]
					],
				],
				[
					'analyzer' => [
						'chinese' => [
							'type' => 'custom',
							'tokenizer' => 'icu_tokenizer',
							'filter' => ['random']
						]
					],
				],
			],
		];
	}

	public function provideLanguageAnalysis() {
		$tests = [];
		foreach ( glob( __DIR__ . '/../fixtures/languageAnalysis/*.config' ) as $testFile ) {
			$testName = substr( basename( $testFile ), 0, -7 );
			$extraConfig = json_decode( file_get_contents( $testFile ), true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				throw new \RuntimeException( "Failed decoding fixture config: $testFile" );
			}
			if ( isset( $extraConfig['LangCode'] ) ) {
				$langCode = $extraConfig['LangCode'];
			} else {
				$langCode = $testName;
			}
			$expectedFile = dirname( $testFile ) . "/$testName.expected";
			if ( file_exists( $expectedFile ) ) {
				$expected = json_decode( file_get_contents( $expectedFile ), true );
				if ( json_last_error() !== JSON_ERROR_NONE ) {
					throw new \RuntimeException( "Failed decoding fixture: $expectedFile" );
				}
			} else {
				$expected = $expectedFile;
			}
			$tests[$testName] = [$expected, $langCode, $extraConfig];
		}
		return $tests;
	}

	/**
	 * Test various language specific analysers against fixtures, to make
	 *  the results of generation obvious and tracked in git
	 *
	 * @dataProvider provideLanguageAnalysis
	 */
	public function testLanguageAnalysis( $expected, $langCode, array $extraConfig ) {
		$config = new HashSearchConfig( $extraConfig + [
			'CirrusSearchSimilarityProfile' => 'default',
		]);
		$plugins = [
			'analysis-stempel', 'analysis-kuromoji',
			'analysis-smartcn', 'elasticsearch-analysis-hebrew',
			'analysis-ukrainian',
		];
		$builder = new AnalysisConfigBuilder( $langCode, $plugins, $config );
		if ( is_string( $expected ) ) {
			// generate fixture
			$fixture = json_encode( $builder->buildConfig(), JSON_PRETTY_PRINT );
			file_put_contents( $expected, $fixture );
			$this->markTestSkipped( "Generated new fixture" );
		} else {
			$this->assertEquals( $expected, $builder->buildConfig() );
		}
	}
}

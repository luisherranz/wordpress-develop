<?php

/**
 * Unit tests covering WP_HTML_Sanitizer conformance against the
 * Web Platform Tests sanitizer-api suite.
 *
 * This test suite runs the html5lib-format test corpora from the WPT
 * `sanitizer-api` directory against WP_HTML_Sanitizer: each case's input is
 * sanitized into an HTML string, the string is re-parsed with the HTML
 * Processor, and the resulting tree is compared against the case's expected
 * tree.
 *
 * Comparing the *re-parsed* output is the correct conformance question for a
 * string-returning sanitizer: consumers of the sanitized string obtain the
 * re-parsed tree. Two normalizations account for representational differences
 * between a serialized string and a live DOM (see ::normalize_tree_dump()).
 *
 * See the README file at DIR_TESTDATA / web-platform-tests / sanitizer-api
 * for details on the third-party suite.
 *
 * @package WordPress
 * @subpackage HTML-API
 *
 * @since 7.1.0
 *
 * @group html-api
 * @group html-api-sanitizer
 */
class Tests_HtmlApi_WpHtmlSanitizerWebPlatformTests extends WP_UnitTestCase {
	/**
	 * Per-file harness conventions carried over from the WPT `.html` files
	 * the corpora originate from. See the fixture README.
	 */
	const FILE_MODES = array(
		'sanitizer-javascript-url' => array( 'config' => array() ),
		'html5lib-basics'          => array( 'safe' => false ),
		'sethtml-unsafety'         => array( 'safe' => false ),
	);

	/**
	 * Verifies WP_HTML_Sanitizer output against a Web Platform Tests case.
	 *
	 * @dataProvider data_web_platform_sanitizer_tests
	 *
	 * @param string            $fragment_context Context element for fragment parsing, e.g. "body".
	 * @param string            $html             Input HTML.
	 * @param array|string|null $config           Sanitizer configuration, the string "default", or
	 *                                            null for the operation default.
	 * @param bool              $safe             Whether to run the safe operation.
	 * @param string|null       $expected_error   Expected error name for invalid configurations, or null.
	 * @param string            $expected_tree    Expected tree in html5lib format.
	 */
	public function test_sanitize( string $fragment_context, string $html, $config, bool $safe, ?string $expected_error, string $expected_tree ) {
		if ( 'body' !== $fragment_context ) {
			$this->markTestSkipped( "Unimplemented: WP_HTML_Processor::create_fragment() only supports the BODY context, not <{$fragment_context}>." );
		}

		try {
			$sanitized = WP_HTML_Sanitizer::sanitize_with_config( $html, $config, $safe );
		} catch ( InvalidArgumentException $invalid_config ) {
			if ( null !== $expected_error ) {
				// The invalid configuration was rejected, as expected.
				$this->assertTrue( true );
				return;
			}
			throw $invalid_config;
		}

		if ( null !== $expected_error ) {
			$this->fail( "Expected the configuration to be rejected with {$expected_error}, but it was accepted." );
		}

		if ( null === $sanitized ) {
			$this->markTestSkipped( 'Unsupported markup: the HTML Processor cannot represent this input and the sanitizer failed closed.' );
		}

		$actual_tree = self::build_tree_dump( $sanitized );

		$this->assertNotNull(
			$actual_tree,
			"Sanitized output failed to re-parse: {$sanitized}"
		);

		$this->assertSame(
			self::normalize_tree_dump( $expected_tree ),
			self::normalize_tree_dump( $actual_tree ),
			"Sanitized output was: {$sanitized}"
		);
	}

	/**
	 * Data provider.
	 *
	 * @return Generator
	 */
	public function data_web_platform_sanitizer_tests() {
		$test_dir = DIR_TESTDATA . '/web-platform-tests/sanitizer-api/';

		$handle = opendir( $test_dir );
		while ( false !== ( $entry = readdir( $handle ) ) ) {
			if ( ! str_ends_with( $entry, '.dat' ) ) {
				continue;
			}

			$test_name = preg_replace( '/\.(sub\.)?dat$/', '', $entry );
			$mode      = isset( self::FILE_MODES[ $test_name ] ) ? self::FILE_MODES[ $test_name ] : array();

			foreach ( self::parse_test_cases( file_get_contents( $test_dir . $entry ) ) as $index => $case ) {
				$config = isset( $mode['config'] ) ? $mode['config'] : null;
				if ( null !== $case['config'] ) {
					$config = json_decode( $case['config'], true );
					if ( null === $config ) {
						// A config which is not valid JSON encodes a test of invalid input.
						$config = array( 'elements' => array( array() ) );
					}
				}

				$description = strlen( $case['data'] ) > 60 ? substr( $case['data'], 0, 57 ) . '…' : $case['data'];
				$description = str_replace( "\n", '⏎', $description );

				yield "{$test_name}/case{$index} {$description}" => array(
					$case['fragment'],
					$case['data'],
					$config,
					isset( $mode['safe'] ) ? $mode['safe'] : true,
					$case['error'],
					$case['document'],
				);
			}
		}
		closedir( $handle );
	}

	/**
	 * Parses html5lib-format test cases with sanitizer-api extensions
	 * (#config and #error sections).
	 *
	 * @param string $content Contents of a `.dat` fixture file.
	 * @return array[] List of cases with data, config, document, fragment, and error keys.
	 */
	private static function parse_test_cases( string $content ): array {
		$cases   = array();
		$current = null;
		$section = null;

		foreach ( explode( "\n", $content ) as $line ) {
			if ( '#data' === $line ) {
				if ( null !== $current ) {
					$cases[] = $current;
				}
				$current = array(
					'data'     => '',
					'config'   => null,
					'document' => '',
					'fragment' => 'body',
					'error'    => null,
				);
				$section = 'data';
				continue;
			}

			if ( null === $current ) {
				continue;
			}

			switch ( $line ) {
				case '#config':
					$section           = 'config';
					$current['config'] = '';
					continue 2;
				case '#document':
					$section = 'document';
					continue 2;
				case '#document-fragment':
					$section = 'fragment';
					continue 2;
				case '#errors':
					$section = 'ignored';
					continue 2;
				case '#error':
					$section          = 'error';
					$current['error'] = '';
					continue 2;
			}

			switch ( $section ) {
				case 'data':
					$current['data'] .= ( '' === $current['data'] ? '' : "\n" ) . $line;
					break;
				case 'config':
					$current['config'] .= $line . "\n";
					break;
				case 'document':
					$current['document'] .= $line . "\n";
					break;
				case 'fragment':
					if ( '' !== trim( $line ) ) {
						$current['fragment'] = trim( $line );
					}
					break;
				case 'error':
					if ( '' !== trim( $line ) ) {
						$current['error'] = trim( $line );
					}
					break;
			}
		}

		if ( null !== $current ) {
			$cases[] = $current;
		}

		return $cases;
	}

	/**
	 * Parses HTML in BODY fragment context and dumps the tree in html5lib format.
	 *
	 * @param string $html HTML to parse.
	 * @return string|null Tree dump, or null if the input cannot be represented.
	 */
	private static function build_tree_dump( string $html ): ?string {
		$processor = WP_HTML_Processor::create_fragment( $html );
		if ( null === $processor ) {
			return null;
		}

		$dump = '';

		$ns_prefix = array(
			'html' => '',
			'svg'  => 'svg ',
			'math' => 'math ',
		);

		while ( $processor->next_token() ) {
			$type = $processor->get_token_type();
			// Depth relative to the BODY context: breadcrumbs start with HTML > BODY.
			$indent_depth = $processor->get_current_depth() - ( '#tag' === $type && $processor->is_tag_closer() ? 1 : 3 );
			$pad          = '| ' . str_repeat( '  ', max( 0, $indent_depth ) );

			switch ( $type ) {
				case '#tag':
					if ( $processor->is_tag_closer() ) {
						break;
					}
					$namespace = $processor->get_namespace();
					$name      = 'html' === $namespace
						? strtolower( $processor->get_tag() )
						: $processor->get_qualified_tag_name();
					$dump     .= "{$pad}<{$ns_prefix[ $namespace ]}{$name}>\n";

					$attribute_names = $processor->get_attribute_names_with_prefix( '' );
					foreach ( null === $attribute_names ? array() : $attribute_names as $attr_name ) {
						$qualified = $processor->get_qualified_attribute_name( $attr_name );
						$value     = $processor->get_attribute( $attr_name );
						$value     = true === $value ? '' : $value;
						$dump     .= "{$pad}  {$qualified}=\"{$value}\"\n";
					}

					// Self-contained elements carry their text inside the token.
					if ( 'html' === $namespace ) {
						$tag = strtoupper( $name );
						if ( in_array( $tag, array( 'IFRAME', 'NOEMBED', 'NOFRAMES', 'SCRIPT', 'STYLE', 'TEXTAREA', 'TITLE', 'XMP' ), true ) ) {
							$text = $processor->get_modifiable_text();
							if ( '' !== $text ) {
								$dump .= "{$pad}  \"{$text}\"\n";
							}
						}
					}
					break;

				case '#text':
				case '#cdata-section':
					$dump .= "{$pad}\"{$processor->get_modifiable_text()}\"\n";
					break;

				case '#comment':
				case '#funky-comment':
					$dump .= "{$pad}<!--{$processor->get_full_comment_text()}-->\n";
					break;
			}
		}

		if ( null !== $processor->get_last_error() ) {
			return null;
		}

		return $dump;
	}

	/**
	 * Canonicalizes an html5lib tree dump so that token-level and DOM-level
	 * dumps of equivalent trees compare equal:
	 *
	 * - Template "content" nodes are dropped, splicing their children up a
	 *   level: the HTML Processor's token walk yields template contents
	 *   directly under TEMPLATE.
	 * - Adjacent sibling text nodes are merged: a serialized string cannot
	 *   distinguish adjacent text nodes, and re-parsing merges them.
	 * - Attribute lines under each element are sorted: attribute order is not
	 *   significant for tree equality (Node.isEqualNode ignores it).
	 * - Odd indentation levels round up, mirroring the reference WPT parser,
	 *   whose `for (i = 0; i < indent / 2; i++)` loop rounds fractional
	 *   levels up (a few WPT fixtures use odd indents).
	 *
	 * @param string $dump Tree dump in html5lib format.
	 * @return string Canonicalized dump.
	 */
	private static function normalize_tree_dump( string $dump ): string {
		$nodes          = array();
		$content_levels = array();

		foreach ( explode( "\n", $dump ) as $line ) {
			$line = rtrim( $line );
			if ( '' === $line || ! str_starts_with( $line, '| ' ) ) {
				continue;
			}
			$body   = substr( $line, 2 );
			$indent = strlen( $body ) - strlen( ltrim( $body ) );
			$level  = (int) ceil( $indent / 2 );
			$body   = ltrim( $body );

			while ( count( $content_levels ) > 0 && $level <= end( $content_levels ) ) {
				array_pop( $content_levels );
			}

			if ( 'content' === $body ) {
				$content_levels[] = $level;
				continue;
			}

			$level  -= count( $content_levels );
			$is_text = 1 === preg_match( '/^".*"$/s', $body );
			$is_attr = ! $is_text && 1 === preg_match( '/^[^<"].*="/', $body );

			$nodes[] = array( $level, $body, $is_attr, $is_text );
		}

		// Merge adjacent sibling text nodes.
		$merged = array();
		foreach ( $nodes as $node ) {
			$last_index = count( $merged ) - 1;
			if ( $node[3] && $last_index >= 0 && $merged[ $last_index ][3] && $merged[ $last_index ][0] === $node[0] ) {
				$merged[ $last_index ][1] = '"' . substr( $merged[ $last_index ][1], 1, -1 ) . substr( $node[1], 1, -1 ) . '"';
				continue;
			}
			$merged[] = $node;
		}

		// Sort attribute runs.
		$result = array();
		$i      = 0;
		$n      = count( $merged );
		while ( $i < $n ) {
			if ( ! $merged[ $i ][2] ) {
				$result[] = $merged[ $i ];
				++$i;
				continue;
			}
			$run = array();
			while ( $i < $n && $merged[ $i ][2] ) {
				$run[] = $merged[ $i ];
				++$i;
			}
			usort(
				$run,
				static function ( $a, $b ) {
					return strcmp( $a[1], $b[1] );
				}
			);
			foreach ( $run as $attribute ) {
				$result[] = $attribute;
			}
		}

		$lines = array();
		foreach ( $result as $node ) {
			$lines[] = '| ' . str_repeat( '  ', max( 0, $node[0] ) ) . $node[1];
		}
		return implode( "\n", $lines );
	}
}

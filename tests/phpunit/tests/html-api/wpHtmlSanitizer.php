<?php

/**
 * Unit tests covering WP_HTML_Sanitizer functionality.
 *
 * The expectations in this file are ported from the JavaScript-based tests of
 * the Web Platform Tests `sanitizer-api` suite (sanitizer-names,
 * sanitizer-boolean-defaults, sanitizer-svg-animate, sanitizer-unknown,
 * sanitizer-removeUnsafe, sanitizer-config, sethtml-safety), plus feature
 * tests for the token-walk sanitization mechanics. The html5lib-format
 * corpora run separately in Tests_HtmlApi_WpHtmlSanitizerWebPlatformTests.
 *
 * @package WordPress
 * @subpackage HTML-API
 *
 * @since 7.1.0
 *
 * @group html-api
 * @group html-api-sanitizer
 */
class Tests_HtmlApi_WpHtmlSanitizer extends WP_UnitTestCase {
	const SVG_NS    = WP_HTML_Sanitizer::SVG_NS;
	const MATHML_NS = WP_HTML_Sanitizer::MATHML_NS;
	const XLINK_NS  = WP_HTML_Sanitizer::XLINK_NS;

	/**
	 * Ensures basic filtering mechanics produce the expected markup.
	 *
	 * @dataProvider data_basic_filtering
	 *
	 * @param array  $config   Sanitizer configuration.
	 * @param string $html     Input HTML.
	 * @param string $expected Expected sanitized output.
	 * @param bool   $safe     Whether to run the safe operation.
	 */
	public function test_basic_filtering( array $config, string $html, string $expected, bool $safe = true ) {
		$this->assertSame( $expected, WP_HTML_Sanitizer::sanitize_with_config( $html, $config, $safe ) );
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_basic_filtering() {
		return array(
			'removeElements removes subtree'           => array(
				array( 'removeElements' => array( 'script' ) ),
				'<p>hello<script>alert(1)</script> world</p>',
				'<p>hello world</p>',
			),
			'replaceWithChildrenElements unwraps'      => array(
				array( 'replaceWithChildrenElements' => array( 'div' ) ),
				'<div class="x"><p>a</p><div><span>b</span></div></div>',
				'<p>a</p><span>b</span>',
			),
			'elements allow-list removes with subtree' => array(
				array( 'elements' => array( 'p', 'b' ) ),
				'<p>keep <b>bold</b> <u>drop</u></p><section><p>inner</p></section>',
				'<p>keep <b>bold</b> </p>',
			),
			'attributes allow-list'                    => array(
				array( 'attributes' => array( 'href', 'title' ) ),
				'<a href="/x" onclick="evil()" title="t" data-a="1">link</a>',
				'<a href="/x" title="t">link</a>',
			),
			'per-element attributes'                   => array(
				array(
					'elements' => array(
						array(
							'name'       => 'a',
							'attributes' => array( 'href' ),
						),
						'p',
					),
				),
				'<p><a href="/x" title="t">link</a></p>',
				'<p><a href="/x">link</a></p>',
			),
			'removeAttributes'                         => array(
				array( 'removeAttributes' => array( 'style' ) ),
				'<p style="color:red" class="k">x</p>',
				'<p class="k">x</p>',
			),
			'comments kept when true'                  => array(
				array( 'comments' => true ),
				'<!-- keep me --><p>x</p>',
				'<!-- keep me --><p>x</p>',
			),
			'comments dropped when false'              => array(
				array( 'comments' => false ),
				'<!-- drop me --><p>x</p>',
				'<p>x</p>',
			),
			'unwrapping style re-homes text escaped'   => array(
				array( 'replaceWithChildrenElements' => array( 'style' ) ),
				'<div><style>a<b{color:red}</style></div>',
				'<div>a&lt;b{color:red}</div>',
			),
			'template contents are filtered'           => array(
				array( 'removeElements' => array( 'script' ) ),
				'<template><script>x</script><p>keep</p></template>',
				'<template><p>keep</p></template>',
			),
			'removal spans implied closers'            => array(
				array( 'removeElements' => array( 'li' ) ),
				'<ul><li>one<li>two</ul>',
				'<ul></ul>',
			),
			'svg subtree removal'                      => array(
				array(
					'removeElements' => array(
						array(
							'name'      => 'script',
							'namespace' => self::SVG_NS,
						),
					),
				),
				'<svg><circle r="1"/><script>evil()</script><text>ok</text></svg>',
				'<svg><circle r="1" /><text>ok</text></svg>',
			),
			'safe baseline removes script and iframe even when allowed' => array(
				array( 'elements' => array( 'p', 'script', 'iframe' ) ),
				'<p>a</p><script>x</script><iframe src="//evil"></iframe>',
				'<p>a</p>',
			),
			'unsafe keeps script when config allows'   => array(
				array(),
				'<script>keep()</script>',
				'<script>keep()</script>',
				false,
			),
			'event handlers dropped in safe mode even when allow-listed' => array(
				array( 'attributes' => array( 'src', 'onclick', 'one', 'alt' ) ),
				'<img src="x" onclick="2+2" one="two" alt="ok">',
				'<img src="x" one="two" alt="ok">',
			),
			'javascript: URLs removed from navigating attributes' => array(
				array(),
				'<a href="  JavaScript:alert(1)">x</a><a href="https://ok.example/">y</a>',
				'<a>x</a><a href="https://ok.example/">y</a>',
			),
			'javascript: URL with encoded obfuscation' => array(
				array(),
				'<a href="jav&#x09;ascript:alert(2)">y</a>',
				'<a>y</a>',
			),
			'javascript: URL on non-navigating attribute survives' => array(
				array(),
				'<a nothref="javascript:alert(1)">x</a>',
				'<a nothref="javascript:alert(1)">x</a>',
			),
			'MathML href is a navigating attribute'    => array(
				array(),
				'<math><mrow href="javascript:alert(1)"><mi>x</mi></mrow></math>',
				'<math><mrow><mi>x</mi></mrow></math>',
			),
			'base is removed by the safe baseline even when allowed' => array(
				array( 'elements' => array( 'div', 'base' ) ),
				'<div><base href="https://example.org/"></base></div>',
				'<div></div>',
			),
			'base is preserved by unsafe sanitization' => array(
				array( 'elements' => array( 'div', 'base' ) ),
				'<div><base href="https://example.org/"></base></div>',
				'<div><base href="https://example.org/"></div>',
				false,
			),
		);
	}

	/**
	 * Ensures event handler content attributes are removed by safe
	 * sanitization even when a configuration explicitly allows them, across
	 * the HTML GlobalEventHandlers/WindowEventHandlers set and the live
	 * cross-specification handlers the HTML baseline does not cover.
	 *
	 * @dataProvider data_event_handler_attributes
	 *
	 * @param string $attribute An event handler content attribute name.
	 */
	public function test_event_handlers_are_stripped_even_when_allowed( string $attribute ) {
		$html = "<a {$attribute}=\"code()\" title=\"ok\">x</a>";

		$this->assertSame(
			'<a title="ok">x</a>',
			WP_HTML_Sanitizer::sanitize_with_config( $html, array( 'attributes' => array( $attribute, 'title' ) ), true ),
			"Safe sanitization did not remove the '{$attribute}' event handler attribute."
		);
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_event_handler_attributes() {
		$attributes = array(
			// Common HTML handlers.
			'onclick',
			'onerror',
			'onload',
			// HTML handlers omitted by the specification's published
			// event-handler-content-attributes list.
			'onabort',
			'oncommand',
			'onreadystatechange',
			'onshow',
			'onvisibilitychange',
			// Live handlers defined by other specifications.
			'onanimationstart',
			'ontransitionend',
			'onpointerdown',
			'ontouchstart',
			'onwebkitanimationstart',
			'onbegin',
		);

		$cases = array();
		foreach ( $attributes as $attribute ) {
			$cases[ $attribute ] = array( $attribute );
		}
		return $cases;
	}

	/**
	 * Ensures a non-handler attribute whose name merely begins with "on" is
	 * not treated as an event handler.
	 */
	public function test_non_handler_on_prefixed_attribute_survives() {
		$this->assertSame(
			'<a one="1">x</a>',
			WP_HTML_Sanitizer::sanitize_with_config( '<a one="1">x</a>', array( 'attributes' => array( 'one' ) ), true )
		);
	}

	/**
	 * Ensures sanitization fails closed on inputs the HTML Processor cannot
	 * represent.
	 *
	 * @dataProvider data_unsupported_inputs
	 *
	 * @param string $html Input which the HTML Processor cannot represent.
	 */
	public function test_fails_closed_on_unsupported_input( string $html ) {
		$this->assertNull( WP_HTML_Sanitizer::sanitize_with_config( $html ) );
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_unsupported_inputs() {
		return array(
			'foster parenting'                 => array( '<table><div>foster</div></table>' ),
			'in-table text'                    => array( '<table>text</table>' ),
			'formatting reconstruction rewind' => array( '<p><b>bold<p>still bold' ),
			'adoption agency common ancestor'  => array( '<b><i onclick="alert(1)"><div></b>x' ),
			'plaintext'                        => array( '<plaintext>anything' ),
		);
	}

	/**
	 * Ensures element and attribute names are matched case-sensitively and
	 * namespace-qualified: elements default to the HTML namespace while
	 * attributes default to no namespace.
	 *
	 * Ported from WPT sanitizer-names.html.
	 *
	 * @dataProvider data_namespaced_names
	 *
	 * @param array  $config   Sanitizer configuration.
	 * @param string $html     Input HTML.
	 * @param string $expected Expected sanitized output.
	 */
	public function test_namespaced_names( array $config, string $html, string $expected ) {
		$this->assertSame( $expected, WP_HTML_Sanitizer::sanitize_with_config( $html, $config ) );
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_namespaced_names() {
		$attr_options = static function ( $attribute ) {
			return array(
				'attributes' => array( $attribute ),
				'elements'   => array(
					'p',
					array(
						'name'      => 'svg',
						'namespace' => self::SVG_NS,
					),
				),
			);
		};

		return array(
			'HTML-namespace "svg" does not match the SVG element' => array(
				array( 'elements' => array( 'svg' ) ),
				'<svg>Hello</svg>',
				'',
			),
			'SVG-namespace svg matches'                   => array(
				array(
					'elements' => array(
						array(
							'name'      => 'svg',
							'namespace' => self::SVG_NS,
						),
					),
				),
				'<svg>Hello</svg>',
				'<svg>Hello</svg>',
			),
			'uppercase names never match lowercased HTML' => array(
				array( 'removeElements' => array( 'I', 'DL' ) ),
				'<dl>test</dl>',
				'<dl>test</dl>',
			),
			'xlink:href on HTML element is a no-namespace attribute' => array(
				$attr_options( array( 'name' => 'xlink:href' ) ),
				'<p xlink:href="bla"></p>',
				'<p xlink:href="bla"></p>',
			),
			'XLink-namespace href does not match HTML xlink:href' => array(
				$attr_options(
					array(
						'name'      => 'href',
						'namespace' => self::XLINK_NS,
					)
				),
				'<p xlink:href="bla"></p>',
				'<p></p>',
			),
			'XLink-namespace href matches adjusted SVG xlink:href' => array(
				$attr_options(
					array(
						'name'      => 'href',
						'namespace' => self::XLINK_NS,
					)
				),
				'<svg xlink:href="bla"></svg>',
				'<svg xlink:href="bla"></svg>',
			),
			'no-namespace xlink:href does not match adjusted SVG attribute' => array(
				$attr_options( array( 'name' => 'xlink:href' ) ),
				'<svg xlink:href="bla"></svg>',
				'<svg></svg>',
			),
			'mixed-case SVG element names are preserved and matched' => array(
				array(
					'elements' => array(
						array(
							'name'      => 'svg',
							'namespace' => self::SVG_NS,
						),
						array(
							'name'      => 'feBlend',
							'namespace' => self::SVG_NS,
						),
					),
				),
				'<svg><feBlend></feBlend></svg>',
				'<svg><feBlend></feBlend></svg>',
			),
		);
	}

	/**
	 * Ensures unset comments and dataAttributes resolve per the operation for
	 * raw configurations, and at construction time for Sanitizer instances.
	 *
	 * Ported from WPT sanitizer-boolean-defaults.html.
	 */
	public function test_boolean_defaults() {
		$comment = '<!--bla--><p>x</p>';
		$data    = '<div data-foo="bar"></div>';

		// Safe operation with a raw empty config drops comments; unsafe keeps them.
		$this->assertStringNotContainsString( '<!--', WP_HTML_Sanitizer::sanitize_with_config( $comment, array(), true ) );
		$this->assertStringContainsString( '<!--', WP_HTML_Sanitizer::sanitize_with_config( $comment, array(), false ) );

		// A constructed Sanitizer resolves booleans at construction with true.
		$constructed = new WP_HTML_Sanitizer( array() );
		$this->assertStringContainsString( '<!--', $constructed->sanitize( $comment, true ) );

		// Explicit values are respected regardless of operation.
		$this->assertStringContainsString( '<!--', WP_HTML_Sanitizer::sanitize_with_config( $comment, array( 'comments' => true ), true ) );
		$this->assertStringNotContainsString( '<!--', WP_HTML_Sanitizer::sanitize_with_config( $comment, array( 'comments' => false ), false ) );

		// dataAttributes defaulting only applies with an attributes allow-list.
		$this->assertStringNotContainsString( 'data-foo', WP_HTML_Sanitizer::sanitize_with_config( $data, array( 'attributes' => array() ), true ) );
		$this->assertStringContainsString( 'data-foo', WP_HTML_Sanitizer::sanitize_with_config( $data, array( 'attributes' => array() ), false ) );

		$constructed_attrs = new WP_HTML_Sanitizer( array( 'attributes' => array() ) );
		$this->assertStringContainsString( 'data-foo', $constructed_attrs->sanitize( $data, true ) );

		$this->assertStringContainsString(
			'data-foo',
			WP_HTML_Sanitizer::sanitize_with_config(
				$data,
				array(
					'attributes'     => array(),
					'dataAttributes' => true,
				),
				true
			)
		);
		$this->assertStringNotContainsString(
			'data-foo',
			WP_HTML_Sanitizer::sanitize_with_config(
				$data,
				array(
					'attributes'     => array(),
					'dataAttributes' => false,
				),
				false
			)
		);

		// The default configuration drops data attributes.
		$this->assertStringNotContainsString( 'data-foo', WP_HTML_Sanitizer::sanitize_with_config( $data ) );
	}

	/**
	 * Ensures the SVG animation attributeName treatment matches the safe and
	 * unsafe operations.
	 *
	 * Ported from WPT sanitizer-svg-animate.html.
	 *
	 * @dataProvider data_svg_animate
	 *
	 * @param string $attribute_name The attributeName value under test.
	 * @param bool   $expect_retain  Whether safe sanitization retains the attribute.
	 */
	public function test_svg_animate_attribute_name( string $attribute_name, bool $expect_retain ) {
		$config = array(
			'elements'   => array(
				array(
					'name'      => 'svg',
					'namespace' => self::SVG_NS,
				),
				array(
					'name'      => 'a',
					'namespace' => self::SVG_NS,
				),
				array(
					'name'      => 'set',
					'namespace' => self::SVG_NS,
				),
			),
			'attributes' => array( 'href', 'hreflang', 'xlink:href', 'attributeName', 'to', 'begin' ),
		);

		$html = "<svg><a href=\"about:blank\"><set attributeName=\"{$attribute_name}\" to=\"https://example.org/\" begin=\"0s\">";

		$safe = WP_HTML_Sanitizer::sanitize_with_config( $html, $config, true );
		$this->assertSame(
			$expect_retain,
			str_contains( $safe, 'attributeName' ),
			$expect_retain
				? 'Safe sanitization should have retained a harmless attributeName.'
				: 'Safe sanitization should have removed a URL-targeting attributeName.'
		);

		$unsafe = WP_HTML_Sanitizer::sanitize_with_config( $html, $config, false );
		$this->assertStringContainsString( 'attributeName', $unsafe, 'Unsafe sanitization should always retain attributeName.' );
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_svg_animate() {
		return array(
			'href is removed'                      => array( 'href', false ),
			'xlink:href is removed'                => array( 'xlink:href', false ),
			'unknown prefix ylink:href is removed' => array( 'ylink:href', false ),
			'hreflang is kept'                     => array( 'hreflang', true ),
			'unknown target xref is kept'          => array( 'xref', true ),
			'multiple colons are kept'             => array( 'xlink:href:x', true ),
			'padded " href " is kept'              => array( ' href ', true ),
		);
	}

	/**
	 * Ensures unknown and custom element or attribute names participate in
	 * the lists like any other name.
	 *
	 * Ported from WPT sanitizer-unknown.html.
	 */
	public function test_unknown_names() {
		$this->assertSame(
			'',
			WP_HTML_Sanitizer::sanitize_with_config( '<hello><world>', array( 'elements' => array( 'b', 'em' ) ) )
		);
		$this->assertSame(
			'<hello><world></world></hello>',
			WP_HTML_Sanitizer::sanitize_with_config( '<hello><world>', array( 'elements' => array( 'hello', 'world' ) ) )
		);
		$this->assertSame(
			'<b></b>',
			WP_HTML_Sanitizer::sanitize_with_config( '<b hello="1" world>', array( 'attributes' => array( 'name', 'href' ) ) )
		);
		$this->assertSame(
			'<b hello="1" world>x</b>',
			WP_HTML_Sanitizer::sanitize_with_config( '<b hello="1" world>x</b>', array( 'attributes' => array( 'hello', 'world' ) ) )
		);
	}

	/**
	 * Ensures remove_unsafe() opts unsafe sanitization into the baseline
	 * element and event handler removals, without enabling javascript: URL
	 * removal, which is exclusive to safe sanitization.
	 *
	 * Ported from WPT sanitizer-removeUnsafe.html.
	 */
	public function test_remove_unsafe() {
		$sanitizer = new WP_HTML_Sanitizer( array() );
		$sanitizer->remove_unsafe();

		$this->assertSame(
			'<p onclick="x">a</p><script>b</script>',
			( new WP_HTML_Sanitizer( array() ) )->sanitize( '<p onclick="x">a</p><script>b</script>', false ),
			'Without remove_unsafe(), unsafe sanitization keeps everything.'
		);

		$this->assertSame(
			'<p>a</p>',
			$sanitizer->sanitize( '<p onclick="x">a</p><script>b</script>', false ),
			'remove_unsafe() should remove baseline elements and event handlers in unsafe sanitization.'
		);

		$this->assertSame(
			'<a href="javascript:alert(1)">x</a>',
			$sanitizer->sanitize( '<a href="javascript:alert(1)">x</a>', false ),
			'remove_unsafe() should not enable javascript: URL removal.'
		);
	}

	/**
	 * Ensures invalid configurations are rejected, mirroring the TypeError
	 * behavior of the browser API.
	 *
	 * Ported from WPT sanitizer-config.html.
	 *
	 * @dataProvider data_invalid_configurations
	 *
	 * @param array $config Invalid configuration.
	 */
	public function test_invalid_configurations_are_rejected( array $config ) {
		$this->expectException( InvalidArgumentException::class );
		new WP_HTML_Sanitizer( $config );
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_invalid_configurations() {
		return array(
			'elements + removeElements'                    => array(
				array(
					'elements'       => array(),
					'removeElements' => array(),
				),
			),
			'attributes + removeAttributes'                => array(
				array(
					'attributes'       => array(),
					'removeAttributes' => array(),
				),
			),
			'dataAttributes + removeAttributes'            => array(
				array(
					'removeAttributes' => array(),
					'dataAttributes'   => true,
				),
			),
			'duplicate elements'                           => array(
				array( 'elements' => array( 'abc', 'abc' ) ),
			),
			'duplicate via namespace defaulting'           => array(
				array(
					'elements' => array(
						'abc',
						array(
							'name'      => 'abc',
							'namespace' => 'http://www.w3.org/1999/xhtml',
						),
					),
				),
			),
			'duplicate attributes via null namespace'      => array(
				array(
					'attributes' => array(
						'abc',
						array(
							'name'      => 'abc',
							'namespace' => null,
						),
					),
				),
			),
			'non-replaceable html'                         => array(
				array( 'replaceWithChildrenElements' => array( 'html' ) ),
			),
			'non-replaceable svg'                          => array(
				array(
					'replaceWithChildrenElements' => array(
						array(
							'name'      => 'svg',
							'namespace' => self::SVG_NS,
						),
					),
				),
			),
			'elements ∩ replaceWithChildrenElements'       => array(
				array(
					'elements'                    => array( 'abc' ),
					'replaceWithChildrenElements' => array( 'abc' ),
				),
			),
			'element attributes ∩ global attributes'       => array(
				array(
					'attributes' => array( 'style' ),
					'elements'   => array(
						array(
							'name'       => 'div',
							'attributes' => array( 'style' ),
						),
					),
				),
			),
			'element removeAttributes ⊄ global attributes' => array(
				array(
					'attributes' => array( 'class' ),
					'elements'   => array(
						array(
							'name'             => 'div',
							'removeAttributes' => array( 'title' ),
						),
					),
				),
			),
			'data attribute with dataAttributes true'      => array(
				array(
					'attributes'     => array( 'data-bar' ),
					'dataAttributes' => true,
				),
			),
		);
	}

	/**
	 * Ensures WebIDL-style scalar coercion of list entries.
	 *
	 * Ported from WPT sethtml-tree-construction.sub.dat.
	 */
	public function test_scalar_entries_are_stringified() {
		$this->assertSame(
			'<div>balabala</div>',
			WP_HTML_Sanitizer::sanitize_with_config(
				'<div>balabala<i>test</i></div><test>t</test>',
				array( 'removeElements' => array( 123, 'test', 'i' ) )
			)
		);
	}
}

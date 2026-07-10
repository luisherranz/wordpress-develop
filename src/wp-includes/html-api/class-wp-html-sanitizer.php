<?php
/**
 * HTML API: WP_HTML_Sanitizer class.
 *
 * PROTOTYPE: This is a prototype implementation of the HTML Sanitizer API
 * (WHATWG HTML §8.6 "HTML sanitization") built on top of the HTML Processor.
 * It is implemented as a filtered re-serialization of the HTML Processor's
 * token stream, which is equivalent to the sanitize algorithm's DOM walk for
 * every input the HTML Processor supports.
 *
 * When the HTML Processor cannot represent an input (foster parenting,
 * complex adoption-agency cases, PLAINTEXT, and a few other constructs),
 * sanitization fails closed: `sanitize()` returns `null` and never returns
 * unsafe or partial output.
 *
 * @see https://html.spec.whatwg.org/multipage/dynamic-markup-insertion.html
 *
 * @package WordPress
 * @subpackage HTML-API
 * @since 7.1.0
 */

/**
 * Core class implementing the HTML Sanitizer API on top of the HTML Processor.
 *
 * This class models the `Sanitizer` interface and `SanitizerConfig` dictionary
 * from the HTML Sanitizer API. Element and attribute names are matched
 * case-sensitively and namespace-qualified, following the specification:
 * elements default to the HTML namespace while attributes default to no
 * namespace.
 *
 * Example:
 *
 *     $sanitizer = new WP_HTML_Sanitizer(); // Built-in safe default configuration.
 *     $safe_html = $sanitizer->sanitize( $untrusted_html );
 *
 *     $sanitizer = new WP_HTML_Sanitizer(
 *         array(
 *             'elements'   => array( 'p', 'b', array( 'name' => 'a', 'attributes' => array( 'href' ) ) ),
 *             'comments'   => false,
 *         )
 *     );
 *     $safe_html = $sanitizer->sanitize( $untrusted_html );
 *
 * @since 7.1.0
 */
class WP_HTML_Sanitizer {
	/**
	 * The HTML namespace.
	 *
	 * @since 7.1.0
	 */
	const HTML_NS = 'http://www.w3.org/1999/xhtml';

	/**
	 * The SVG namespace.
	 *
	 * @since 7.1.0
	 */
	const SVG_NS = 'http://www.w3.org/2000/svg';

	/**
	 * The MathML namespace.
	 *
	 * @since 7.1.0
	 */
	const MATHML_NS = 'http://www.w3.org/1998/Math/MathML';

	/**
	 * The XLink namespace.
	 *
	 * @since 7.1.0
	 */
	const XLINK_NS = 'http://www.w3.org/1999/xlink';

	/**
	 * The XML namespace.
	 *
	 * @since 7.1.0
	 */
	const XML_NS = 'http://www.w3.org/XML/1998/namespace';

	/**
	 * The XMLNS namespace.
	 *
	 * @since 7.1.0
	 */
	const XMLNS_NS = 'http://www.w3.org/2000/xmlns/';

	/**
	 * Namespace keys used by the HTML API mapped to full namespace URLs.
	 *
	 * @since 7.1.0
	 */
	const PARSER_NS = array(
		'html' => self::HTML_NS,
		'svg'  => self::SVG_NS,
		'math' => self::MATHML_NS,
	);

	/**
	 * Built-in safe baseline configuration: element removals.
	 *
	 * These elements are removed by safe sanitization regardless of the
	 * configured lists, and folded into the configuration by remove_unsafe().
	 * The list consists of the HTML elements whose specification definitions
	 * mark them as sanitizer-unsafe, plus the obsolete `frame` element and the
	 * SVG `script` and `use` elements.
	 *
	 * These constants are living-standard data and must be regenerated when the
	 * pinned revision changes. Pinned to WHATWG HTML commit
	 * c38efee5f866693d0310f8ce689588a7fe75e8f4 (2026-07-09), which marked the
	 * HTML `base` element as sanitizer-unsafe. The `base` categorization is
	 * volatile: it was "Uncategorized with navigating URL attributes" until
	 * that commit, so any refresh must re-check it against the pinned source
	 * rather than a cached copy or the WICG builtins mirror (which can lag).
	 *
	 * @see https://html.spec.whatwg.org/multipage/dynamic-markup-insertion.html#built-in-safe-baseline-configuration
	 *
	 * @since 7.1.0
	 */
	const BASELINE_REMOVE_ELEMENTS = array(
		self::HTML_NS . ' base'   => true,
		self::HTML_NS . ' embed'  => true,
		self::HTML_NS . ' frame'  => true,
		self::HTML_NS . ' iframe' => true,
		self::HTML_NS . ' object' => true,
		self::HTML_NS . ' script' => true,
		self::SVG_NS . ' script'  => true,
		self::SVG_NS . ' use'     => true,
	);

	/**
	 * Built-in non-replaceable elements list.
	 *
	 * These elements may not appear in `replaceWithChildrenElements`.
	 *
	 * @see https://html.spec.whatwg.org/multipage/dynamic-markup-insertion.html#built-in-non-replaceable-elements-list
	 *
	 * @since 7.1.0
	 */
	const NON_REPLACEABLE_ELEMENTS = array(
		self::HTML_NS . ' html'   => true,
		self::SVG_NS . ' svg'     => true,
		self::MATHML_NS . ' math' => true,
	);

	/**
	 * Built-in navigating URL attributes list.
	 *
	 * Per-element attributes which navigate and therefore have `javascript:`
	 * URL values removed by safe sanitization. Maps element identity to a
	 * list of attribute identities. MathML is special-cased in code: `href`
	 * navigates on every MathML element.
	 *
	 * @see https://html.spec.whatwg.org/multipage/dynamic-markup-insertion.html#built-in-navigating-url-attributes-list
	 *
	 * @since 7.1.0
	 */
	const NAVIGATING_URL_ATTRIBUTES = array(
		self::HTML_NS . ' a'      => array( ' href' ),
		self::HTML_NS . ' area'   => array( ' href' ),
		self::HTML_NS . ' form'   => array( ' action' ),
		self::HTML_NS . ' button' => array( ' formaction' ),
		self::HTML_NS . ' input'  => array( ' formaction' ),
		self::SVG_NS . ' a'       => array( ' href', self::XLINK_NS . ' href' ),
	);

	/**
	 * SVG animation elements whose `attributeName` attribute may target a URL
	 * attribute. `animateMotion` is not included because it does not animate
	 * attributes by name.
	 *
	 * @see https://html.spec.whatwg.org/multipage/dynamic-markup-insertion.html#built-in-animating-url-attributes-list
	 *
	 * @since 7.1.0
	 */
	const SVG_ANIMATION_ELEMENTS = array(
		self::SVG_NS . ' animate'          => true,
		self::SVG_NS . ' animateTransform' => true,
		self::SVG_NS . ' set'              => true,
	);

	/**
	 * Event handler content attributes, all in no namespace.
	 *
	 * The safe baseline removes event handler content attributes. The
	 * specification's "remove unsafe" algorithm defers to an
	 * implementation-defined set of these, and the HTML Sanitizer conformance
	 * tests likewise only require that each removed attribute name begin with
	 * "on" (they do not pin the exact set). A bare "on*" prefix rule is still
	 * wrong, because non-handler attributes such as "one" must survive.
	 *
	 * This list is therefore a deliberate, safety-oriented superset:
	 *
	 *  - The HTML specification's GlobalEventHandlers and WindowEventHandlers
	 *    content attributes, the complete set. Note this is broader than the
	 *    specification's published event-handler-content-attributes list, which
	 *    omits several real HTML handlers (for example onabort, oncommand,
	 *    onreadystatechange, onselectstart, onshow, and onvisibilitychange);
	 *    matching only that list would leave those unremoved when a
	 *    configuration explicitly allowed them.
	 *  - Live event handler content attributes defined by other specifications
	 *    that the HTML baseline does not cover but which execute script in
	 *    browsers: CSS Animations and Transitions (onanimation*, ontransition*,
	 *    and the legacy onwebkit* aliases), Pointer Events (onpointer*,
	 *    ongotpointercapture, onlostpointercapture), Touch Events (ontouch*),
	 *    and SVG animation timing (onbegin, onend, onrepeat).
	 *
	 * These are only consulted when a configuration would otherwise allow an
	 * attribute; the default configuration allows none of them.
	 *
	 * @since 7.1.0
	 */
	const EVENT_HANDLER_ATTRIBUTES = array(
		'onabort'                    => true,
		'onafterprint'               => true,
		'onanimationcancel'          => true,
		'onanimationend'             => true,
		'onanimationiteration'       => true,
		'onanimationstart'           => true,
		'onauxclick'                 => true,
		'onbeforecopy'               => true,
		'onbeforecut'                => true,
		'onbeforeinput'              => true,
		'onbeforematch'              => true,
		'onbeforepaste'              => true,
		'onbeforeprint'              => true,
		'onbeforetoggle'             => true,
		'onbeforeunload'             => true,
		'onbegin'                    => true,
		'onblur'                     => true,
		'oncancel'                   => true,
		'oncanplay'                  => true,
		'oncanplaythrough'           => true,
		'onchange'                   => true,
		'onclick'                    => true,
		'onclose'                    => true,
		'oncommand'                  => true,
		'oncontextlost'              => true,
		'oncontextmenu'              => true,
		'oncontextrestored'          => true,
		'oncopy'                     => true,
		'oncuechange'                => true,
		'oncut'                      => true,
		'ondblclick'                 => true,
		'ondrag'                     => true,
		'ondragend'                  => true,
		'ondragenter'                => true,
		'ondragleave'                => true,
		'ondragover'                 => true,
		'ondragstart'                => true,
		'ondrop'                     => true,
		'ondurationchange'           => true,
		'onemptied'                  => true,
		'onend'                      => true,
		'onended'                    => true,
		'onerror'                    => true,
		'onfocus'                    => true,
		'onfocusin'                  => true,
		'onfocusout'                 => true,
		'onformdata'                 => true,
		'ongotpointercapture'        => true,
		'onhashchange'               => true,
		'oninput'                    => true,
		'oninvalid'                  => true,
		'onkeydown'                  => true,
		'onkeypress'                 => true,
		'onkeyup'                    => true,
		'onlanguagechange'           => true,
		'onload'                     => true,
		'onloadeddata'               => true,
		'onloadedmetadata'           => true,
		'onloadstart'                => true,
		'onlostpointercapture'       => true,
		'onmessage'                  => true,
		'onmessageerror'             => true,
		'onmousedown'                => true,
		'onmouseenter'               => true,
		'onmouseleave'               => true,
		'onmousemove'                => true,
		'onmouseout'                 => true,
		'onmouseover'                => true,
		'onmouseup'                  => true,
		'onmousewheel'               => true,
		'onoffline'                  => true,
		'ononline'                   => true,
		'onpagehide'                 => true,
		'onpagereveal'               => true,
		'onpageshow'                 => true,
		'onpageswap'                 => true,
		'onpaste'                    => true,
		'onpause'                    => true,
		'onplay'                     => true,
		'onplaying'                  => true,
		'onpointercancel'            => true,
		'onpointerdown'              => true,
		'onpointerenter'             => true,
		'onpointerleave'             => true,
		'onpointermove'              => true,
		'onpointerout'               => true,
		'onpointerover'              => true,
		'onpointerrawupdate'         => true,
		'onpointerup'                => true,
		'onpopstate'                 => true,
		'onprogress'                 => true,
		'onratechange'               => true,
		'onreadystatechange'         => true,
		'onrejectionhandled'         => true,
		'onrepeat'                   => true,
		'onreset'                    => true,
		'onresize'                   => true,
		'onscroll'                   => true,
		'onscrollend'                => true,
		'onscrollsnapchange'         => true,
		'onscrollsnapchanging'       => true,
		'onsearch'                   => true,
		'onsecuritypolicyviolation'  => true,
		'onseeked'                   => true,
		'onseeking'                  => true,
		'onselect'                   => true,
		'onselectionchange'          => true,
		'onselectstart'              => true,
		'onshow'                     => true,
		'onslotchange'               => true,
		'onstalled'                  => true,
		'onstorage'                  => true,
		'onsubmit'                   => true,
		'onsuspend'                  => true,
		'ontimeupdate'               => true,
		'ontoggle'                   => true,
		'ontouchcancel'              => true,
		'ontouchend'                 => true,
		'ontouchmove'                => true,
		'ontouchstart'               => true,
		'ontransitioncancel'         => true,
		'ontransitionend'            => true,
		'ontransitionrun'            => true,
		'ontransitionstart'          => true,
		'onunhandledrejection'       => true,
		'onunload'                   => true,
		'onvisibilitychange'         => true,
		'onvolumechange'             => true,
		'onwaiting'                  => true,
		'onwebkitanimationend'       => true,
		'onwebkitanimationiteration' => true,
		'onwebkitanimationstart'     => true,
		'onwebkittransitionend'      => true,
		'onwheel'                    => true,
	);

	/**
	 * Elements in the allow-list, or `null` when no allow-list exists.
	 *
	 * Element and attribute identities are strings: "{namespace-url} {local-name}"
	 * for elements, "{namespace-url-or-empty} {local-name}" for attributes
	 * (attributes with no namespace have an empty namespace part, so their
	 * identity starts with a space).
	 *
	 * Maps element identity to `true` or to an array with per-element
	 * `attributes` or `remove_attributes` overrides.
	 *
	 * @since 7.1.0
	 * @var array|null
	 */
	private $elements = null;

	/**
	 * Elements in the remove-list, keyed by element identity.
	 *
	 * @since 7.1.0
	 * @var array
	 */
	private $remove_elements = array();

	/**
	 * Elements to replace with their children, keyed by element identity.
	 *
	 * @since 7.1.0
	 * @var array
	 */
	private $replace_with_children = array();

	/**
	 * Attributes in the allow-list, or `null` when no allow-list exists.
	 *
	 * @since 7.1.0
	 * @var array|null
	 */
	private $attributes = null;

	/**
	 * Attributes in the remove-list, keyed by attribute identity.
	 *
	 * @since 7.1.0
	 * @var array
	 */
	private $remove_attributes = array();

	/**
	 * Whether comments are preserved.
	 *
	 * @since 7.1.0
	 * @var bool
	 */
	private $comments = true;

	/**
	 * Whether `data-*` attributes are preserved when an attribute allow-list
	 * exists, or `null` when irrelevant (no attribute allow-list).
	 *
	 * @since 7.1.0
	 * @var bool|null
	 */
	private $data_attributes = null;

	/**
	 * Whether remove_unsafe() has requested event handler removal for
	 * unsafe sanitization.
	 *
	 * @since 7.1.0
	 * @var bool
	 */
	private $remove_event_handlers = false;

	/**
	 * Lazily-loaded built-in safe default configuration.
	 *
	 * @since 7.1.0
	 * @var array|null
	 */
	private static $default_configuration = null;

	/**
	 * Constructor.
	 *
	 * Canonicalizes and validates a sanitizer configuration, mirroring the
	 * specification's "canonicalize a configuration" and "check the validity"
	 * algorithms. Invalid configurations throw, mirroring the TypeError
	 * behavior of the browser API.
	 *
	 * @since 7.1.0
	 *
	 * @throws InvalidArgumentException When the configuration is invalid.
	 *
	 * @param array|string $config Optional. SanitizerConfig-shaped array, or 'default'
	 *                             for the built-in safe default configuration.
	 * @param bool $allow_comments_and_data_attributes Optional. Canonicalization flag
	 *                             for unset booleans. The specification's Sanitizer
	 *                             constructor always canonicalizes with `true`; raw
	 *                             dictionaries resolved by a safe operation use
	 *                             `false`. See ::sanitize_with_config(). Default true.
	 */
	public function __construct( $config = 'default', bool $allow_comments_and_data_attributes = true ) {
		if ( 'default' === $config ) {
			$config = self::get_default_configuration();
		}

		if ( ! is_array( $config ) ) {
			throw new InvalidArgumentException( 'Invalid sanitizer configuration.' );
		}

		// Mutually-exclusive keys.
		if ( isset( $config['elements'], $config['removeElements'] ) ) {
			throw new InvalidArgumentException( 'elements and removeElements are mutually exclusive.' );
		}
		if ( isset( $config['attributes'], $config['removeAttributes'] ) ) {
			throw new InvalidArgumentException( 'attributes and removeAttributes are mutually exclusive.' );
		}
		if ( isset( $config['removeAttributes'], $config['dataAttributes'] ) ) {
			throw new InvalidArgumentException( 'dataAttributes cannot be used with removeAttributes.' );
		}
		if ( isset( $config['processingInstructions'], $config['removeProcessingInstructions'] ) ) {
			throw new InvalidArgumentException( 'processingInstructions and removeProcessingInstructions are mutually exclusive.' );
		}

		$has_global_attributes = isset( $config['attributes'] );
		$has_data_attributes   = isset( $config['dataAttributes'] ) && true === $config['dataAttributes'];

		if ( isset( $config['elements'] ) ) {
			$this->elements = array();
			foreach ( $config['elements'] as $element ) {
				list( $key, $entry ) = $this->canonicalize_element( $element, true );
				if ( isset( $this->elements[ $key ] ) ) {
					throw new InvalidArgumentException( "Duplicate element '{$key}'." );
				}
				if ( is_array( $entry ) ) {
					$this->validate_per_element_attributes( $entry, $config, $has_global_attributes, $has_data_attributes );
				}
				$this->elements[ $key ] = $entry;
			}
		}

		if ( isset( $config['removeElements'] ) ) {
			foreach ( $config['removeElements'] as $element ) {
				list( $key ) = $this->canonicalize_element( $element, false );
				if ( isset( $this->remove_elements[ $key ] ) ) {
					throw new InvalidArgumentException( "Duplicate element '{$key}'." );
				}
				$this->remove_elements[ $key ] = true;
			}
		}

		if ( isset( $config['replaceWithChildrenElements'] ) ) {
			foreach ( $config['replaceWithChildrenElements'] as $element ) {
				list( $key ) = $this->canonicalize_element( $element, false );
				if ( isset( $this->replace_with_children[ $key ] ) ) {
					throw new InvalidArgumentException( "Duplicate element '{$key}'." );
				}
				if ( isset( self::NON_REPLACEABLE_ELEMENTS[ $key ] ) ) {
					throw new InvalidArgumentException( "Element '{$key}' is non-replaceable." );
				}
				if ( isset( $this->elements[ $key ] ) || isset( $this->remove_elements[ $key ] ) ) {
					throw new InvalidArgumentException( "Element '{$key}' also present in another element list." );
				}
				$this->replace_with_children[ $key ] = true;
			}
		}

		if ( $has_global_attributes ) {
			$this->attributes = array();
			foreach ( $config['attributes'] as $attribute ) {
				$key = $this->canonicalize_attribute( $attribute );
				if ( isset( $this->attributes[ $key ] ) ) {
					throw new InvalidArgumentException( "Duplicate attribute '{$key}'." );
				}
				if ( $has_data_attributes && $this->is_data_attribute_key( $key ) ) {
					throw new InvalidArgumentException( 'attributes must not contain data attributes when dataAttributes is true.' );
				}
				$this->attributes[ $key ] = true;
			}
		}

		if ( isset( $config['removeAttributes'] ) ) {
			foreach ( $config['removeAttributes'] as $attribute ) {
				$key = $this->canonicalize_attribute( $attribute );
				if ( isset( $this->remove_attributes[ $key ] ) ) {
					throw new InvalidArgumentException( "Duplicate attribute '{$key}'." );
				}
				$this->remove_attributes[ $key ] = true;
			}
		}

		/*
		 * Canonicalization resolves unset booleans immediately: `comments`
		 * always; `dataAttributes` only when an `attributes` allow-list
		 * exists (it is meaningless otherwise).
		 */
		$this->comments = array_key_exists( 'comments', $config )
			? (bool) $config['comments']
			: $allow_comments_and_data_attributes;
		if ( array_key_exists( 'dataAttributes', $config ) ) {
			$this->data_attributes = (bool) $config['dataAttributes'];
		} elseif ( $has_global_attributes ) {
			$this->data_attributes = $allow_comments_and_data_attributes;
		}
	}

	/**
	 * Adds an element to the allow-list, removing it from the other element lists.
	 *
	 * @since 7.1.0
	 *
	 * @param string|array $element Element name, or an array with `name` and
	 *                              optional `namespace`, `attributes`, and
	 *                              `removeAttributes` keys.
	 */
	public function allow_element( $element ): void {
		list( $key, $entry ) = $this->canonicalize_element( $element, true );
		if ( null === $this->elements ) {
			$this->elements = array();
		}
		$this->elements[ $key ] = $entry;
		unset( $this->remove_elements[ $key ], $this->replace_with_children[ $key ] );
	}

	/**
	 * Adds an element to the remove-list, removing it from the other element lists.
	 *
	 * @since 7.1.0
	 *
	 * @param string|array $element Element name, or an array with `name` and
	 *                              optional `namespace` keys.
	 */
	public function remove_element( $element ): void {
		list( $key )                   = $this->canonicalize_element( $element, false );
		$this->remove_elements[ $key ] = true;
		unset( $this->elements[ $key ], $this->replace_with_children[ $key ] );
	}

	/**
	 * Adds an element to the replace-with-children list, removing it from the
	 * other element lists. Non-replaceable elements (html, svg, math) are
	 * ignored.
	 *
	 * @since 7.1.0
	 *
	 * @param string|array $element Element name, or an array with `name` and
	 *                              optional `namespace` keys.
	 */
	public function replace_element_with_children( $element ): void {
		list( $key ) = $this->canonicalize_element( $element, false );
		if ( isset( self::NON_REPLACEABLE_ELEMENTS[ $key ] ) ) {
			return;
		}
		$this->replace_with_children[ $key ] = true;
		unset( $this->elements[ $key ], $this->remove_elements[ $key ] );
	}

	/**
	 * Adds an attribute to the allow-list, removing it from the remove-list.
	 *
	 * @since 7.1.0
	 *
	 * @param string|array $attribute Attribute name, or an array with `name`
	 *                                and optional `namespace` keys.
	 */
	public function allow_attribute( $attribute ): void {
		$key = $this->canonicalize_attribute( $attribute );
		if ( null === $this->attributes ) {
			$this->attributes = array();
		}
		$this->attributes[ $key ] = true;
		unset( $this->remove_attributes[ $key ] );
	}

	/**
	 * Adds an attribute to the remove-list, removing it from the allow-list.
	 *
	 * @since 7.1.0
	 *
	 * @param string|array $attribute Attribute name, or an array with `name`
	 *                                and optional `namespace` keys.
	 */
	public function remove_attribute( $attribute ): void {
		$key                             = $this->canonicalize_attribute( $attribute );
		$this->remove_attributes[ $key ] = true;
		unset( $this->attributes[ $key ] );
	}

	/**
	 * Sets whether comments are preserved.
	 *
	 * @since 7.1.0
	 *
	 * @param bool $allow Whether to preserve comments.
	 */
	public function set_comments( bool $allow ): void {
		$this->comments = $allow;
	}

	/**
	 * Sets whether `data-*` attributes are preserved when an attribute
	 * allow-list exists.
	 *
	 * @since 7.1.0
	 *
	 * @param bool $allow Whether to preserve data attributes.
	 */
	public function set_data_attributes( bool $allow ): void {
		$this->data_attributes = $allow;
	}

	/**
	 * Modifies this configuration to remove everything in the built-in safe
	 * baseline: unsafe elements and all event handler content attributes.
	 *
	 * Safe sanitization applies this baseline automatically; calling this
	 * method opts unsafe sanitization into the same element and event handler
	 * removals. It does not enable `javascript:` URL removal, which is
	 * exclusive to safe sanitization, as specified.
	 *
	 * @since 7.1.0
	 */
	public function remove_unsafe(): void {
		foreach ( self::BASELINE_REMOVE_ELEMENTS as $key => $unused ) {
			$this->remove_elements[ $key ] = true;
			unset( $this->elements[ $key ], $this->replace_with_children[ $key ] );
		}
		$this->remove_event_handlers = true;
	}

	/**
	 * Sanitizes an HTML fragment as found in BODY context.
	 *
	 * This is the analog of `Element.setHTML()` (safe) and
	 * `Element.setHTMLUnsafe()` (unsafe) for a body context element,
	 * returning the resulting markup instead of mutating a DOM tree.
	 *
	 * When safe, the built-in safe baseline is enforced on top of this
	 * configuration: baseline elements and event handler content attributes
	 * are removed even when explicitly allowed, and `javascript:` URLs are
	 * removed from navigating URL attributes.
	 *
	 * @since 7.1.0
	 *
	 * @param string $html Input HTML fragment.
	 * @param bool   $safe Optional. Whether to enforce the safe baseline. Default true.
	 * @return string|null Sanitized HTML, or `null` if the input cannot be
	 *                     represented by the HTML Processor (fails closed).
	 */
	public function sanitize( string $html, bool $safe = true ): ?string {
		$processor = WP_HTML_Processor::create_fragment( $html );
		if ( null === $processor ) {
			return null;
		}

		$output = '';

		/*
		 * Stack of decisions for open elements awaiting their tag closer.
		 * Each entry is array{ 0: 'keep'|'unwrap', 1: string qualified name }.
		 * Tag closers arrive in LIFO order, so a plain stack suffices.
		 */
		$open = array();

		while ( $processor->next_token() ) {
			$type = $processor->get_token_type();

			switch ( $type ) {
				case '#tag':
					if ( $processor->is_tag_closer() ) {
						$decision = array_pop( $open );
						if ( null !== $decision && 'keep' === $decision[0] ) {
							$output .= "</{$decision[1]}>";
						}
						break;
					}

					$element_namespace = $processor->get_namespace();
					$qualified_name    = 'html' === $element_namespace
						? strtolower( $processor->get_tag() )
						: $processor->get_qualified_tag_name();
					$key               = self::PARSER_NS[ $element_namespace ] . " {$qualified_name}";

					$decision = $this->decide_element( $key, $safe );

					if ( 'remove' === $decision ) {
						if ( $processor->expects_closer() ) {
							$this->skip_subtree( $processor );
						}
						break;
					}

					$is_self_contained = ! $processor->expects_closer();

					if ( 'unwrap' === $decision ) {
						if ( $is_self_contained ) {
							/*
							 * Replacing a self-contained element (SCRIPT, STYLE, …)
							 * with its children hoists its text content into the
							 * parent, where it is no longer raw text.
							 */
							$text = $processor->get_modifiable_text();
							if ( '' !== $text ) {
								$output .= htmlspecialchars( $text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8' );
							}
						} else {
							$open[] = array( 'unwrap', $qualified_name );
						}
						break;
					}

					$output .= $this->serialize_start_tag( $processor, $key, $qualified_name, $element_namespace, $safe );

					if ( $is_self_contained ) {
						$output .= $this->serialize_self_contained_interior( $processor, $element_namespace, $qualified_name );
					} else {
						$open[] = array( 'keep', $qualified_name );
					}
					break;

				case '#text':
				case '#cdata-section': // Only in foreign content; a text node in the DOM.
					$output .= htmlspecialchars( $processor->get_modifiable_text(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8' );
					break;

				case '#comment':
				case '#funky-comment':
					if ( $this->comments ) {
						$output .= "<!--{$processor->get_full_comment_text()}-->";
					}
					break;

				case '#doctype':
				case '#presumptuous-tag':
					// Doctypes cannot appear in fragments; presumptuous tags produce no node.
					break;
			}
		}

		if ( null !== $processor->get_last_error() ) {
			return null;
		}

		return $output;
	}

	/**
	 * Sanitizes an HTML fragment with a raw configuration array, resolving
	 * unset booleans against the safety of the operation.
	 *
	 * This is the analog of passing a raw dictionary as `options.sanitizer`
	 * to `Element.setHTML()`: unset `comments` and `dataAttributes` resolve
	 * to `false` for safe operations and `true` for unsafe ones, unlike
	 * configurations canonicalized by the constructor, which always resolve
	 * them to `true`.
	 *
	 * @since 7.1.0
	 *
	 * @param string            $html   Input HTML fragment.
	 * @param array|string|null $config Optional. Configuration array, 'default', or null
	 *                                  for the operation's default (the built-in safe
	 *                                  default configuration when safe, allow-all when
	 *                                  unsafe). Default null.
	 * @param bool              $safe   Optional. Whether to enforce the safe baseline. Default true.
	 * @return string|null Sanitized HTML, or `null` on unsupported input.
	 */
	public static function sanitize_with_config( string $html, $config = null, bool $safe = true ): ?string {
		if ( null === $config ) {
			$config = $safe ? 'default' : array();
		}
		$sanitizer = new self( $config, ! $safe );
		return $sanitizer->sanitize( $html, $safe );
	}

	/**
	 * Returns the built-in safe default configuration.
	 *
	 * @see https://html.spec.whatwg.org/multipage/dynamic-markup-insertion.html#built-in-safe-default-configuration
	 *
	 * @since 7.1.0
	 *
	 * @return array The default configuration in SanitizerConfig shape.
	 */
	public static function get_default_configuration(): array {
		if ( null === self::$default_configuration ) {
			self::$default_configuration = require __DIR__ . '/html-sanitizer-default-configuration.php';
		}
		return self::$default_configuration;
	}

	/**
	 * Decides what to do with an element.
	 *
	 * @since 7.1.0
	 *
	 * @param string $key  Element identity.
	 * @param bool   $safe Whether the safe baseline applies.
	 * @return string One of 'keep', 'remove', or 'unwrap'.
	 */
	private function decide_element( string $key, bool $safe ): string {
		if ( $safe && isset( self::BASELINE_REMOVE_ELEMENTS[ $key ] ) ) {
			return 'remove';
		}
		if ( isset( $this->remove_elements[ $key ] ) ) {
			return 'remove';
		}
		if ( isset( $this->replace_with_children[ $key ] ) ) {
			return 'unwrap';
		}
		if ( null !== $this->elements && ! isset( $this->elements[ $key ] ) ) {
			return 'remove';
		}
		return 'keep';
	}

	/**
	 * Decides whether to keep one attribute of the given element.
	 *
	 * Follows the specification's "is attribute allowed" semantics: the
	 * element's own `removeAttributes` is always subtracted; with a global
	 * allow-list the allowed set is the union of the global list, the
	 * element's own list, and (when enabled) data attributes; with remove-list
	 * semantics a per-element allow-list is an exclusive whitelist for that
	 * element.
	 *
	 * @since 7.1.0
	 *
	 * @param string $element_key Element identity.
	 * @param string $attr_key    Attribute identity.
	 * @param bool   $safe        Whether the safe baseline applies.
	 * @return bool Whether to keep the attribute.
	 */
	private function decide_attribute( string $element_key, string $attr_key, bool $safe ): bool {
		$local_name   = substr( $attr_key, strpos( $attr_key, ' ' ) + 1 );
		$no_namespace = ' ' === $attr_key[0];

		if ( ( $safe || $this->remove_event_handlers ) && $no_namespace && isset( self::EVENT_HANDLER_ATTRIBUTES[ $local_name ] ) ) {
			return false;
		}

		$element_entry = ( null !== $this->elements && isset( $this->elements[ $element_key ] ) )
			? $this->elements[ $element_key ]
			: true;

		// Per-element removeAttributes has the highest precedence.
		if ( is_array( $element_entry ) && isset( $element_entry['remove_attributes'][ $attr_key ] ) ) {
			return false;
		}

		if ( null !== $this->attributes ) {
			if ( isset( $this->attributes[ $attr_key ] ) ) {
				return true;
			}
			if ( is_array( $element_entry ) && isset( $element_entry['attributes'][ $attr_key ] ) ) {
				return true;
			}
			return true === $this->data_attributes
				&& $no_namespace
				&& str_starts_with( $local_name, 'data-' );
		}

		/*
		 * Remove-list semantics (including the implicit empty remove-list of
		 * canonicalized configurations): a per-element allow-list, when
		 * present, is an exclusive whitelist for that element.
		 */
		if ( is_array( $element_entry ) && isset( $element_entry['attributes'] ) && ! isset( $element_entry['attributes'][ $attr_key ] ) ) {
			return false;
		}

		return ! isset( $this->remove_attributes[ $attr_key ] );
	}

	/**
	 * Serializes a start tag, filtering attributes per the configuration.
	 *
	 * NOTE: This intentionally mirrors the emission rules of
	 * WP_HTML_Processor::serialize_token(). It cannot reuse that method
	 * because serialize_token() provides no attribute filtering and does not
	 * reflect enqueued attribute removals. A supported filtering hook in the
	 * HTML API would let this method be deleted; see the accompanying
	 * proposal.
	 *
	 * @since 7.1.0
	 *
	 * @param WP_HTML_Processor $processor      Processor at a tag-opener token.
	 * @param string            $element_key    Element identity.
	 * @param string            $qualified_name Qualified tag name for output.
	 * @param string            $element_namespace Parser namespace: 'html', 'svg', or 'math'.
	 * @param bool              $safe           Whether the safe baseline applies.
	 * @return string Serialized start tag.
	 */
	private function serialize_start_tag( WP_HTML_Processor $processor, string $element_key, string $qualified_name, string $element_namespace, bool $safe ): string {
		$html = "<{$qualified_name}";

		$names = $processor->get_attribute_names_with_prefix( '' );
		$names = null === $names ? array() : $names;
		$seen  = array();
		foreach ( $names as $name ) {
			$qualified_attr = $processor->get_qualified_attribute_name( $name );
			if ( isset( $seen[ $qualified_attr ] ) ) {
				continue;
			}
			$seen[ $qualified_attr ] = true;

			list( $attr_key, $serialized_name ) = $this->attribute_identity( $qualified_attr );

			if ( ! $this->decide_attribute( $element_key, $attr_key, $safe ) ) {
				continue;
			}

			$value = $processor->get_attribute( $name );

			if ( $safe && is_string( $value ) && $this->is_navigating_url_attribute( $element_key, $attr_key ) && self::is_javascript_url( $value ) ) {
				continue;
			}

			if (
				$safe &&
				is_string( $value ) &&
				' attributeName' === $attr_key &&
				isset( self::SVG_ANIMATION_ELEMENTS[ $element_key ] ) &&
				self::animates_url_attribute( $value )
			) {
				continue;
			}

			$html .= " {$serialized_name}";
			if ( is_string( $value ) ) {
				$html .= '="' . htmlspecialchars( $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5 ) . '"';
			}
		}

		if ( 'html' !== $element_namespace && $processor->has_self_closing_flag() ) {
			$html .= ' /';
		}

		$html .= '>';

		/*
		 * The HTML parser strips a leading newline immediately after the
		 * start tag of TEXTAREA, PRE, and LISTING elements; prepend one so
		 * the semantic content survives re-parsing.
		 */
		if ( 'html' === $element_namespace && in_array( $qualified_name, array( 'textarea', 'pre', 'listing' ), true ) ) {
			$html .= "\n";
		}

		return $html;
	}

	/**
	 * Emits interior text and the closer for self-contained HTML elements.
	 *
	 * The HTML Processor represents IFRAME, NOEMBED, NOFRAMES, SCRIPT, STYLE,
	 * TEXTAREA, TITLE, and XMP in HTML content as single tokens containing
	 * their text. Mirrors WP_HTML_Processor::serialize_token(): raw text for
	 * SCRIPT/STYLE/XMP, escaped text for TEXTAREA/TITLE, and dropped text for
	 * IFRAME/NOEMBED/NOFRAMES.
	 *
	 * @since 7.1.0
	 *
	 * @param WP_HTML_Processor $processor      Processor at the self-contained token.
	 * @param string            $element_namespace Parser namespace.
	 * @param string            $qualified_name Qualified tag name for output.
	 * @return string Interior text and closing tag, or empty string for void elements.
	 */
	private function serialize_self_contained_interior( WP_HTML_Processor $processor, string $element_namespace, string $qualified_name ): string {
		if (
			'html' !== $element_namespace ||
			! in_array( $qualified_name, array( 'iframe', 'noembed', 'noframes', 'script', 'style', 'textarea', 'title', 'xmp' ), true )
		) {
			return ''; // Void elements and foreign self-closers have no interior or closer here.
		}

		$text = $processor->get_modifiable_text();
		switch ( $qualified_name ) {
			case 'iframe':
			case 'noembed':
			case 'noframes':
				$text = '';
				break;
			case 'script':
			case 'style':
			case 'xmp':
				break;
			default:
				$text = htmlspecialchars( $text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8' );
		}

		return "{$text}</{$qualified_name}>";
	}

	/**
	 * Skips all tokens inside the current element, consuming its tag closer.
	 *
	 * Relies on the HTML Processor emitting a (possibly virtual) tag closer
	 * for every element which expects one: the first tag closer at a
	 * shallower depth than the opener is the opener's own closer.
	 *
	 * @since 7.1.0
	 *
	 * @param WP_HTML_Processor $processor Processor at a tag-opener token.
	 */
	private function skip_subtree( WP_HTML_Processor $processor ): void {
		$depth = $processor->get_current_depth();
		while ( $processor->next_token() ) {
			if (
				'#tag' === $processor->get_token_type() &&
				$processor->is_tag_closer() &&
				$processor->get_current_depth() < $depth
			) {
				return;
			}
		}
	}

	/**
	 * Canonicalizes an element entry into its identity and normalized form.
	 *
	 * The default namespace for elements is the HTML namespace. Scalar values
	 * are stringified, mirroring WebIDL coercion.
	 *
	 * @since 7.1.0
	 *
	 * @throws InvalidArgumentException When the entry is malformed.
	 *
	 * @param string|array $element         Element entry.
	 * @param bool         $with_attributes Whether per-element attribute lists are allowed.
	 * @return array{0: string, 1: true|array} Element identity and normalized entry.
	 */
	private function canonicalize_element( $element, bool $with_attributes ): array {
		if ( is_scalar( $element ) ) {
			$element = array( 'name' => (string) $element );
		}
		if ( ! isset( $element['name'] ) || ! is_string( $element['name'] ) ) {
			throw new InvalidArgumentException( 'Element entries require a name.' );
		}
		$namespace = isset( $element['namespace'] ) ? $element['namespace'] : self::HTML_NS;
		if ( null === $namespace || '' === $namespace ) {
			$namespace = self::HTML_NS;
		}
		$key = "{$namespace} {$element['name']}";

		$entry = true;
		if ( $with_attributes && ( isset( $element['attributes'] ) || isset( $element['removeAttributes'] ) ) ) {
			if ( isset( $element['attributes'], $element['removeAttributes'] ) ) {
				throw new InvalidArgumentException( 'Element attributes and removeAttributes are mutually exclusive.' );
			}
			$entry = array();
			if ( isset( $element['attributes'] ) ) {
				$entry['attributes'] = $this->canonicalize_attribute_list( $element['attributes'] );
			}
			if ( isset( $element['removeAttributes'] ) ) {
				$entry['remove_attributes'] = $this->canonicalize_attribute_list( $element['removeAttributes'] );
			}
		}

		return array( $key, $entry );
	}

	/**
	 * Canonicalizes a list of attribute entries into an identity-keyed set.
	 *
	 * @since 7.1.0
	 *
	 * @throws InvalidArgumentException On duplicates or malformed entries.
	 *
	 * @param array $attributes List of attribute entries.
	 * @return array Set keyed by attribute identity.
	 */
	private function canonicalize_attribute_list( array $attributes ): array {
		$set = array();
		foreach ( $attributes as $attribute ) {
			$key = $this->canonicalize_attribute( $attribute );
			if ( isset( $set[ $key ] ) ) {
				throw new InvalidArgumentException( "Duplicate attribute '{$key}'." );
			}
			$set[ $key ] = true;
		}
		return $set;
	}

	/**
	 * Canonicalizes an attribute entry into its identity.
	 *
	 * The default namespace for attributes is no namespace, represented by an
	 * empty namespace part (the identity starts with a space). Scalar values
	 * are stringified, mirroring WebIDL coercion.
	 *
	 * @since 7.1.0
	 *
	 * @throws InvalidArgumentException When the entry is malformed.
	 *
	 * @param string|array $attribute Attribute entry.
	 * @return string Attribute identity.
	 */
	private function canonicalize_attribute( $attribute ): string {
		if ( is_scalar( $attribute ) ) {
			$attribute = array( 'name' => (string) $attribute );
		}
		if ( ! isset( $attribute['name'] ) || ! is_string( $attribute['name'] ) ) {
			throw new InvalidArgumentException( 'Attribute entries require a name.' );
		}
		$namespace = isset( $attribute['namespace'] ) ? $attribute['namespace'] : null;
		if ( '' === $namespace ) {
			$namespace = null;
		}
		return ( null === $namespace ? '' : $namespace ) . " {$attribute['name']}";
	}

	/**
	 * Validates per-element attribute lists against the global configuration.
	 *
	 * @since 7.1.0
	 *
	 * @throws InvalidArgumentException On constraint violations.
	 *
	 * @param array $entry                 Normalized per-element entry.
	 * @param array $config                Raw configuration.
	 * @param bool  $has_global_attributes Whether a global attribute allow-list exists.
	 * @param bool  $has_data_attributes   Whether dataAttributes is true.
	 */
	private function validate_per_element_attributes( array $entry, array $config, bool $has_global_attributes, bool $has_data_attributes ): void {
		if ( isset( $config['removeAttributes'] ) && isset( $entry['attributes'], $entry['remove_attributes'] ) ) {
			throw new InvalidArgumentException( 'Per-element attributes and removeAttributes cannot both exist with global removeAttributes.' );
		}

		if ( isset( $entry['attributes'] ) ) {
			foreach ( $entry['attributes'] as $attr_key => $unused ) {
				if ( $has_global_attributes && $this->attribute_list_contains( $config['attributes'], $attr_key ) ) {
					throw new InvalidArgumentException( 'Per-element attributes must not intersect global attributes.' );
				}
				if ( isset( $config['removeAttributes'] ) && $this->attribute_list_contains( $config['removeAttributes'], $attr_key ) ) {
					throw new InvalidArgumentException( 'Per-element attributes must not intersect global removeAttributes.' );
				}
				if ( $has_data_attributes && $this->is_data_attribute_key( $attr_key ) ) {
					throw new InvalidArgumentException( 'Per-element attributes must not contain data attributes when dataAttributes is true.' );
				}
			}
		}

		if ( isset( $entry['remove_attributes'] ) ) {
			foreach ( $entry['remove_attributes'] as $attr_key => $unused ) {
				if ( $has_global_attributes && ! $this->attribute_list_contains( $config['attributes'], $attr_key ) ) {
					throw new InvalidArgumentException( 'Per-element removeAttributes must be a subset of global attributes.' );
				}
				if ( isset( $config['removeAttributes'] ) && $this->attribute_list_contains( $config['removeAttributes'], $attr_key ) ) {
					throw new InvalidArgumentException( 'Per-element removeAttributes must not intersect global removeAttributes.' );
				}
			}
		}
	}

	/**
	 * Whether an attribute identity denotes a custom data attribute.
	 *
	 * @since 7.1.0
	 *
	 * @param string $attr_key       Attribute identity.
	 * @return bool Whether the attribute is a `data-*` attribute in no namespace.
	 */
	private function is_data_attribute_key( string $attr_key ): bool {
		return ' ' === $attr_key[0] && str_starts_with( substr( $attr_key, 1 ), 'data-' );
	}

	/**
	 * Whether a raw attribute list contains an attribute identity.
	 *
	 * @since 7.1.0
	 *
	 * @param array  $attribute_list Raw attribute list from a configuration.
	 * @param string $attr_key       Attribute identity.
	 * @return bool Whether the list contains the attribute.
	 */
	private function attribute_list_contains( array $attribute_list, string $attr_key ): bool {
		foreach ( $attribute_list as $attribute ) {
			if ( $this->canonicalize_attribute( $attribute ) === $attr_key ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Computes the identity of an attribute from the parser's qualified name.
	 *
	 * In HTML content all attributes are in no namespace, including ones with
	 * colons in their names: `xlink:href` on an HTML element is a
	 * no-namespace attribute literally named "xlink:href". In foreign content
	 * the parser adjusts `xlink:*`, `xml:*`, and `xmlns[:xlink]` attributes
	 * into their namespaces, exposed through the qualified name's
	 * "prefix local-name" space-separated form.
	 *
	 * @see WP_HTML_Tag_Processor::get_qualified_attribute_name()
	 *
	 * @since 7.1.0
	 *
	 * @param string $qualified_attr Qualified attribute name from the parser.
	 * @return array{0: string, 1: string} Attribute identity and serialized name.
	 */
	private function attribute_identity( string $qualified_attr ): array {
		$space = strpos( $qualified_attr, ' ' );
		if ( false !== $space ) {
			$prefix     = substr( $qualified_attr, 0, $space );
			$local_name = substr( $qualified_attr, $space + 1 );
			switch ( $prefix ) {
				case 'xlink':
					$ns = self::XLINK_NS;
					break;
				case 'xml':
					$ns = self::XML_NS;
					break;
				case 'xmlns':
					$ns = self::XMLNS_NS;
					break;
				default:
					$ns = '';
			}
			return array( "{$ns} {$local_name}", "{$prefix}:{$local_name}" );
		}

		if ( 'xmlns' === $qualified_attr ) {
			return array( self::XMLNS_NS . ' xmlns', 'xmlns' );
		}

		return array( " {$qualified_attr}", $qualified_attr );
	}

	/**
	 * Whether an attribute of an element is in the built-in navigating URL
	 * attributes list.
	 *
	 * @since 7.1.0
	 *
	 * @param string $element_key Element identity.
	 * @param string $attr_key    Attribute identity.
	 * @return bool Whether the attribute navigates.
	 */
	private function is_navigating_url_attribute( string $element_key, string $attr_key ): bool {
		if ( str_starts_with( $element_key, self::MATHML_NS . ' ' ) ) {
			return ' href' === $attr_key;
		}
		$attrs = isset( self::NAVIGATING_URL_ATTRIBUTES[ $element_key ] )
			? self::NAVIGATING_URL_ATTRIBUTES[ $element_key ]
			: null;
		return null !== $attrs && in_array( $attr_key, $attrs, true );
	}

	/**
	 * Whether an SVG animation `attributeName` value targets a URL attribute.
	 *
	 * The name is split on its first colon into prefix and local name; any
	 * prefix with the exact local name "href" matches. There is no whitespace
	 * trimming (` href ` does not match) and extra colons in the local name
	 * do not match (`xlink:href:x` is kept), following the Web Platform Tests
	 * expectations.
	 *
	 * @since 7.1.0
	 *
	 * @param string $value The `attributeName` attribute value.
	 * @return bool Whether the value targets a URL attribute.
	 */
	private static function animates_url_attribute( string $value ): bool {
		$colon = strpos( $value, ':' );
		$local = false === $colon ? $value : substr( $value, $colon + 1 );
		return 'href' === $local;
	}

	/**
	 * Whether an attribute value is a `javascript:` URL.
	 *
	 * Mirrors scheme extraction in the WHATWG URL parser: leading and
	 * trailing C0 controls and spaces are stripped, ASCII tab and newline
	 * characters are removed, and the scheme is matched case-insensitively.
	 * Attribute values arrive fully decoded from the HTML Processor, so
	 * character-reference obfuscation is already neutralized.
	 *
	 * @since 7.1.0
	 *
	 * @param string $value Decoded attribute value.
	 * @return bool Whether the value is a `javascript:` URL.
	 */
	private static function is_javascript_url( string $value ): bool {
		$value = trim( $value, "\x00..\x20" );
		$value = str_replace( array( "\t", "\n", "\r" ), '', $value );
		return 0 === stripos( $value, 'javascript:' );
	}
}

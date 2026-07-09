<?php
/**
 * HTML API: Built-in safe default configuration for WP_HTML_Sanitizer.
 *
 * This file is derived from the HTML Sanitizer API specification's
 * built-in safe default configuration.
 *
 * @see https://html.spec.whatwg.org/multipage/dynamic-markup-insertion.html#built-in-safe-default-configuration
 * @see https://github.com/web-platform-tests/wpt/blob/master/sanitizer-api/sanitizer-default-config.html
 *
 * Do not edit by hand: regenerate from the specification when it changes.
 *
 * @package WordPress
 * @subpackage HTML-API
 * @since 7.1.0
 */

return array(
	'elements'               => array(
		array(
			'name'       => 'math',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'merror',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'mfrac',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'mi',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'mmultiscripts',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'mn',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'mo',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(
				array(
					'name'      => 'fence',
					'namespace' => null,
				),
				array(
					'name'      => 'form',
					'namespace' => null,
				),
				array(
					'name'      => 'largeop',
					'namespace' => null,
				),
				array(
					'name'      => 'lspace',
					'namespace' => null,
				),
				array(
					'name'      => 'maxsize',
					'namespace' => null,
				),
				array(
					'name'      => 'minsize',
					'namespace' => null,
				),
				array(
					'name'      => 'movablelimits',
					'namespace' => null,
				),
				array(
					'name'      => 'rspace',
					'namespace' => null,
				),
				array(
					'name'      => 'separator',
					'namespace' => null,
				),
				array(
					'name'      => 'stretchy',
					'namespace' => null,
				),
				array(
					'name'      => 'symmetric',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'mover',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(
				array(
					'name'      => 'accent',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'mpadded',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(
				array(
					'name'      => 'depth',
					'namespace' => null,
				),
				array(
					'name'      => 'height',
					'namespace' => null,
				),
				array(
					'name'      => 'lspace',
					'namespace' => null,
				),
				array(
					'name'      => 'voffset',
					'namespace' => null,
				),
				array(
					'name'      => 'width',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'mphantom',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'mprescripts',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'mroot',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'mrow',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'ms',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'mspace',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(
				array(
					'name'      => 'depth',
					'namespace' => null,
				),
				array(
					'name'      => 'height',
					'namespace' => null,
				),
				array(
					'name'      => 'width',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'msqrt',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'mstyle',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'msub',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'msubsup',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'msup',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'mtable',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'mtd',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(
				array(
					'name'      => 'columnspan',
					'namespace' => null,
				),
				array(
					'name'      => 'rowspan',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'mtext',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'mtr',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'munder',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(
				array(
					'name'      => 'accentunder',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'munderover',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(
				array(
					'name'      => 'accent',
					'namespace' => null,
				),
				array(
					'name'      => 'accentunder',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'semantics',
			'namespace'  => 'http://www.w3.org/1998/Math/MathML',
			'attributes' => array(),
		),
		array(
			'name'       => 'a',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(
				array(
					'name'      => 'href',
					'namespace' => null,
				),
				array(
					'name'      => 'hreflang',
					'namespace' => null,
				),
				array(
					'name'      => 'type',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'abbr',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'address',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'article',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'aside',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'b',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'bdi',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'bdo',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'blockquote',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(
				array(
					'name'      => 'cite',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'body',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'br',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'caption',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'cite',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'code',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'col',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(
				array(
					'name'      => 'span',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'colgroup',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(
				array(
					'name'      => 'span',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'data',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(
				array(
					'name'      => 'value',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'dd',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'del',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(
				array(
					'name'      => 'cite',
					'namespace' => null,
				),
				array(
					'name'      => 'datetime',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'dfn',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'div',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'dl',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'dt',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'em',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'figcaption',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'figure',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'footer',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'h1',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'h2',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'h3',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'h4',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'h5',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'h6',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'head',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'header',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'hgroup',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'hr',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'html',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'i',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'ins',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(
				array(
					'name'      => 'cite',
					'namespace' => null,
				),
				array(
					'name'      => 'datetime',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'kbd',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'li',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(
				array(
					'name'      => 'value',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'main',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'mark',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'menu',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'nav',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'ol',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(
				array(
					'name'      => 'reversed',
					'namespace' => null,
				),
				array(
					'name'      => 'start',
					'namespace' => null,
				),
				array(
					'name'      => 'type',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'p',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'pre',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'q',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'rp',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'rt',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'ruby',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 's',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'samp',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'search',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'section',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'small',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'span',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'strong',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'sub',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'sup',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'table',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'tbody',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'td',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(
				array(
					'name'      => 'colspan',
					'namespace' => null,
				),
				array(
					'name'      => 'headers',
					'namespace' => null,
				),
				array(
					'name'      => 'rowspan',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'tfoot',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'th',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(
				array(
					'name'      => 'abbr',
					'namespace' => null,
				),
				array(
					'name'      => 'colspan',
					'namespace' => null,
				),
				array(
					'name'      => 'headers',
					'namespace' => null,
				),
				array(
					'name'      => 'rowspan',
					'namespace' => null,
				),
				array(
					'name'      => 'scope',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'thead',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'time',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(
				array(
					'name'      => 'datetime',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'title',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'tr',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'u',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'ul',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'var',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'wbr',
			'namespace'  => 'http://www.w3.org/1999/xhtml',
			'attributes' => array(),
		),
		array(
			'name'       => 'a',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(
				array(
					'name'      => 'href',
					'namespace' => null,
				),
				array(
					'name'      => 'hreflang',
					'namespace' => null,
				),
				array(
					'name'      => 'type',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'circle',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(
				array(
					'name'      => 'cx',
					'namespace' => null,
				),
				array(
					'name'      => 'cy',
					'namespace' => null,
				),
				array(
					'name'      => 'pathLength',
					'namespace' => null,
				),
				array(
					'name'      => 'r',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'defs',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(),
		),
		array(
			'name'       => 'desc',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(),
		),
		array(
			'name'       => 'ellipse',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(
				array(
					'name'      => 'cx',
					'namespace' => null,
				),
				array(
					'name'      => 'cy',
					'namespace' => null,
				),
				array(
					'name'      => 'pathLength',
					'namespace' => null,
				),
				array(
					'name'      => 'rx',
					'namespace' => null,
				),
				array(
					'name'      => 'ry',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'foreignObject',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(
				array(
					'name'      => 'height',
					'namespace' => null,
				),
				array(
					'name'      => 'width',
					'namespace' => null,
				),
				array(
					'name'      => 'x',
					'namespace' => null,
				),
				array(
					'name'      => 'y',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'g',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(),
		),
		array(
			'name'       => 'line',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(
				array(
					'name'      => 'pathLength',
					'namespace' => null,
				),
				array(
					'name'      => 'x1',
					'namespace' => null,
				),
				array(
					'name'      => 'x2',
					'namespace' => null,
				),
				array(
					'name'      => 'y1',
					'namespace' => null,
				),
				array(
					'name'      => 'y2',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'marker',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(
				array(
					'name'      => 'markerHeight',
					'namespace' => null,
				),
				array(
					'name'      => 'markerUnits',
					'namespace' => null,
				),
				array(
					'name'      => 'markerWidth',
					'namespace' => null,
				),
				array(
					'name'      => 'orient',
					'namespace' => null,
				),
				array(
					'name'      => 'preserveAspectRatio',
					'namespace' => null,
				),
				array(
					'name'      => 'refX',
					'namespace' => null,
				),
				array(
					'name'      => 'refY',
					'namespace' => null,
				),
				array(
					'name'      => 'viewBox',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'metadata',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(),
		),
		array(
			'name'       => 'path',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(
				array(
					'name'      => 'd',
					'namespace' => null,
				),
				array(
					'name'      => 'pathLength',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'polygon',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(
				array(
					'name'      => 'pathLength',
					'namespace' => null,
				),
				array(
					'name'      => 'points',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'polyline',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(
				array(
					'name'      => 'pathLength',
					'namespace' => null,
				),
				array(
					'name'      => 'points',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'rect',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(
				array(
					'name'      => 'height',
					'namespace' => null,
				),
				array(
					'name'      => 'pathLength',
					'namespace' => null,
				),
				array(
					'name'      => 'rx',
					'namespace' => null,
				),
				array(
					'name'      => 'ry',
					'namespace' => null,
				),
				array(
					'name'      => 'width',
					'namespace' => null,
				),
				array(
					'name'      => 'x',
					'namespace' => null,
				),
				array(
					'name'      => 'y',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'svg',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(
				array(
					'name'      => 'height',
					'namespace' => null,
				),
				array(
					'name'      => 'preserveAspectRatio',
					'namespace' => null,
				),
				array(
					'name'      => 'viewBox',
					'namespace' => null,
				),
				array(
					'name'      => 'width',
					'namespace' => null,
				),
				array(
					'name'      => 'x',
					'namespace' => null,
				),
				array(
					'name'      => 'y',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'text',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(
				array(
					'name'      => 'dx',
					'namespace' => null,
				),
				array(
					'name'      => 'dy',
					'namespace' => null,
				),
				array(
					'name'      => 'lengthAdjust',
					'namespace' => null,
				),
				array(
					'name'      => 'rotate',
					'namespace' => null,
				),
				array(
					'name'      => 'textLength',
					'namespace' => null,
				),
				array(
					'name'      => 'x',
					'namespace' => null,
				),
				array(
					'name'      => 'y',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'textPath',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(
				array(
					'name'      => 'lengthAdjust',
					'namespace' => null,
				),
				array(
					'name'      => 'method',
					'namespace' => null,
				),
				array(
					'name'      => 'path',
					'namespace' => null,
				),
				array(
					'name'      => 'side',
					'namespace' => null,
				),
				array(
					'name'      => 'spacing',
					'namespace' => null,
				),
				array(
					'name'      => 'startOffset',
					'namespace' => null,
				),
				array(
					'name'      => 'textLength',
					'namespace' => null,
				),
			),
		),
		array(
			'name'       => 'title',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(),
		),
		array(
			'name'       => 'tspan',
			'namespace'  => 'http://www.w3.org/2000/svg',
			'attributes' => array(
				array(
					'name'      => 'dx',
					'namespace' => null,
				),
				array(
					'name'      => 'dy',
					'namespace' => null,
				),
				array(
					'name'      => 'lengthAdjust',
					'namespace' => null,
				),
				array(
					'name'      => 'rotate',
					'namespace' => null,
				),
				array(
					'name'      => 'textLength',
					'namespace' => null,
				),
				array(
					'name'      => 'x',
					'namespace' => null,
				),
				array(
					'name'      => 'y',
					'namespace' => null,
				),
			),
		),
	),
	'processingInstructions' => array(),
	'attributes'             => array(
		array(
			'name'      => 'alignment-baseline',
			'namespace' => null,
		),
		array(
			'name'      => 'baseline-shift',
			'namespace' => null,
		),
		array(
			'name'      => 'clip-path',
			'namespace' => null,
		),
		array(
			'name'      => 'clip-rule',
			'namespace' => null,
		),
		array(
			'name'      => 'color',
			'namespace' => null,
		),
		array(
			'name'      => 'color-interpolation',
			'namespace' => null,
		),
		array(
			'name'      => 'cursor',
			'namespace' => null,
		),
		array(
			'name'      => 'dir',
			'namespace' => null,
		),
		array(
			'name'      => 'direction',
			'namespace' => null,
		),
		array(
			'name'      => 'display',
			'namespace' => null,
		),
		array(
			'name'      => 'displaystyle',
			'namespace' => null,
		),
		array(
			'name'      => 'dominant-baseline',
			'namespace' => null,
		),
		array(
			'name'      => 'fill',
			'namespace' => null,
		),
		array(
			'name'      => 'fill-opacity',
			'namespace' => null,
		),
		array(
			'name'      => 'fill-rule',
			'namespace' => null,
		),
		array(
			'name'      => 'font-family',
			'namespace' => null,
		),
		array(
			'name'      => 'font-size',
			'namespace' => null,
		),
		array(
			'name'      => 'font-size-adjust',
			'namespace' => null,
		),
		array(
			'name'      => 'font-stretch',
			'namespace' => null,
		),
		array(
			'name'      => 'font-style',
			'namespace' => null,
		),
		array(
			'name'      => 'font-variant',
			'namespace' => null,
		),
		array(
			'name'      => 'font-weight',
			'namespace' => null,
		),
		array(
			'name'      => 'lang',
			'namespace' => null,
		),
		array(
			'name'      => 'letter-spacing',
			'namespace' => null,
		),
		array(
			'name'      => 'marker-end',
			'namespace' => null,
		),
		array(
			'name'      => 'marker-mid',
			'namespace' => null,
		),
		array(
			'name'      => 'marker-start',
			'namespace' => null,
		),
		array(
			'name'      => 'mathbackground',
			'namespace' => null,
		),
		array(
			'name'      => 'mathcolor',
			'namespace' => null,
		),
		array(
			'name'      => 'mathsize',
			'namespace' => null,
		),
		array(
			'name'      => 'opacity',
			'namespace' => null,
		),
		array(
			'name'      => 'paint-order',
			'namespace' => null,
		),
		array(
			'name'      => 'pointer-events',
			'namespace' => null,
		),
		array(
			'name'      => 'scriptlevel',
			'namespace' => null,
		),
		array(
			'name'      => 'shape-rendering',
			'namespace' => null,
		),
		array(
			'name'      => 'stop-color',
			'namespace' => null,
		),
		array(
			'name'      => 'stop-opacity',
			'namespace' => null,
		),
		array(
			'name'      => 'stroke',
			'namespace' => null,
		),
		array(
			'name'      => 'stroke-dasharray',
			'namespace' => null,
		),
		array(
			'name'      => 'stroke-dashoffset',
			'namespace' => null,
		),
		array(
			'name'      => 'stroke-linecap',
			'namespace' => null,
		),
		array(
			'name'      => 'stroke-linejoin',
			'namespace' => null,
		),
		array(
			'name'      => 'stroke-miterlimit',
			'namespace' => null,
		),
		array(
			'name'      => 'stroke-opacity',
			'namespace' => null,
		),
		array(
			'name'      => 'stroke-width',
			'namespace' => null,
		),
		array(
			'name'      => 'text-anchor',
			'namespace' => null,
		),
		array(
			'name'      => 'text-decoration',
			'namespace' => null,
		),
		array(
			'name'      => 'text-overflow',
			'namespace' => null,
		),
		array(
			'name'      => 'text-rendering',
			'namespace' => null,
		),
		array(
			'name'      => 'title',
			'namespace' => null,
		),
		array(
			'name'      => 'transform',
			'namespace' => null,
		),
		array(
			'name'      => 'transform-origin',
			'namespace' => null,
		),
		array(
			'name'      => 'unicode-bidi',
			'namespace' => null,
		),
		array(
			'name'      => 'vector-effect',
			'namespace' => null,
		),
		array(
			'name'      => 'visibility',
			'namespace' => null,
		),
		array(
			'name'      => 'white-space',
			'namespace' => null,
		),
		array(
			'name'      => 'word-spacing',
			'namespace' => null,
		),
		array(
			'name'      => 'writing-mode',
			'namespace' => null,
		),
	),
	'comments'               => false,
	'dataAttributes'         => false,
);

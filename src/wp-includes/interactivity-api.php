<?php
/**
 * Interactivity API: Functions and hooks
 *
 * @package WordPress
 * @subpackage Interactivity API
 */

/**
 * Temporary registration of the Interactivity API script modules until the
 * backport of the `WP_Interactivity_API` class is completed.
 *
 * https://github.com/WordPress/gutenberg/pull/58066
 */
function wp_interactivity_register_script_modules() {
	wp_register_script_module(
		'@wordpress/interactivity',
		includes_url( '/js/dist/interactivity.min.js' ),
		array()
	);

	wp_register_script_module(
		'@wordpress/interactivity-router',
		includes_url( '/js/dist/interactivity-router.min.js' ),
		array( '@wordpress/interactivity' ),
	);
}

add_action( 'init', 'wp_interactivity_register_script_modules' );

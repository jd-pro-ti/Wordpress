<?php
if ( ! function_exists( 'tecvisory_child_enqueue_style' ) ) {
	/**
	 * Load child theme style
	 */
	function tecvisory_child_style() {
		wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
		wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'parent-style' ) );
	}
	add_action( 'wp_enqueue_scripts', 'tecvisory_child_style' );
}

/**
 * Your code goes below.
 */

<?php
/**
 * NDTV theme setup.
 *
 * @package NDTV
 */

defined( 'ABSPATH' ) || exit;

define( 'NDTV_THEME_VERSION', '0.3.0' );

require_once get_template_directory() . '/inc/sections.php';

add_action(
	'after_setup_theme',
	static function () {
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/theme.css' );
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style( 'ndtv-theme', get_theme_file_uri( 'assets/css/theme.css' ), array(), NDTV_THEME_VERSION );

		if ( is_post_type_archive( 'ndtv_video' ) || is_singular( 'ndtv_video' ) ) {
			wp_enqueue_script(
				'ndtv-carousel',
				get_theme_file_uri( 'assets/js/carousel.js' ),
				array(),
				NDTV_THEME_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
	}
);

add_action(
	'init',
	static function () {
		register_block_pattern_category( 'ndtv', array( 'label' => __( 'NDTV', 'ndtv' ) ) );
	}
);

<?php
/**
 * Plugin Name:       NDTV Core
 * Description:       Video CPT synced from NDTV's video feed, NDTV Player block (HLS), story badges and demo seeder for the NDTV on WordPress VIP prototype.
 * Version:           0.3.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            WordPress VIP
 * License:           GPL-2.0-or-later
 * Text Domain:       ndtv-core
 *
 * @package NDTV_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'NDTV_CORE_VERSION', '0.3.0' );
define( 'NDTV_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'NDTV_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once NDTV_CORE_DIR . 'inc/post-types.php';
require_once NDTV_CORE_DIR . 'inc/remote-media.php';
require_once NDTV_CORE_DIR . 'inc/blocks.php';
require_once NDTV_CORE_DIR . 'inc/rails.php';
require_once NDTV_CORE_DIR . 'inc/seeder.php';
require_once NDTV_CORE_DIR . 'inc/video-sync.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once NDTV_CORE_DIR . 'inc/cli.php';
}

register_activation_hook(
	__FILE__,
	static function () {
		ndtv_core_register_content_types();
		flush_rewrite_rules( false );
	}
);

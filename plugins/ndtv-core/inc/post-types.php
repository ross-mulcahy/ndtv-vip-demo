<?php
/**
 * Video post type, video taxonomies and story/video meta.
 *
 * Video meta is registered with show_in_rest so an importer can sync it from
 * NDTV's video API: the files stay on NDTV's player backend, WordPress only
 * holds the metadata.
 *
 * @package NDTV_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'ndtv_core_register_content_types' );

/**
 * Register the ndtv_video CPT, its taxonomies and meta.
 */
function ndtv_core_register_content_types() {
	register_post_type(
		'ndtv_video',
		array(
			'labels'        => array(
				'name'          => __( 'Videos', 'ndtv-core' ),
				'singular_name' => __( 'Video', 'ndtv-core' ),
				'add_new_item'  => __( 'Add New Video', 'ndtv-core' ),
				'edit_item'     => __( 'Edit Video', 'ndtv-core' ),
				'all_items'     => __( 'All Videos', 'ndtv-core' ),
				'search_items'  => __( 'Search Videos', 'ndtv-core' ),
			),
			'public'        => true,
			'show_in_rest'  => true,
			'has_archive'   => 'video',
			'rewrite'       => array(
				'slug'       => 'video',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-video-alt3',
			'menu_position' => 6,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions', 'custom-fields' ),
			'taxonomies'    => array( 'post_tag' ),
			'template'      => array( array( 'ndtv/player', array( 'showTitle' => false ) ) ),
		)
	);

	// Video categories are deliberately separate from news categories.
	register_taxonomy(
		'video_category',
		'ndtv_video',
		array(
			'labels'            => array(
				'name'          => __( 'Video Categories', 'ndtv-core' ),
				'singular_name' => __( 'Video Category', 'ndtv-core' ),
			),
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => 'video-category',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'video_show',
		'ndtv_video',
		array(
			'labels'            => array(
				'name'          => __( 'Shows', 'ndtv-core' ),
				'singular_name' => __( 'Show', 'ndtv-core' ),
			),
			'hierarchical'      => false,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => 'video-show',
				'with_front' => false,
			),
		)
	);

	$video_meta = array(
		'player_id'   => array(
			'type'        => 'string',
			'description' => 'ID of the video on NDTV\'s player backend.',
		),
		'stream_url'  => array(
			'type'              => 'string',
			'description'       => 'HLS (.m3u8) or MP4 stream URL.',
			'sanitize_callback' => 'esc_url_raw',
		),
		'duration'    => array(
			'type'        => 'integer',
			'description' => 'Duration in seconds. 0 for live streams.',
			'default'     => 0,
		),
		'orientation' => array(
			'type'        => 'string',
			'description' => 'horizontal or vertical (9:16 shorts).',
			'default'     => 'horizontal',
			'enum'        => array( 'horizontal', 'vertical' ),
		),
		'kind'        => array(
			'type'        => 'string',
			'description' => 'vod or live.',
			'default'     => 'vod',
			'enum'        => array( 'vod', 'live' ),
		),
		'channel'     => array(
			'type'        => 'string',
			'description' => 'Channel the video belongs to, such as NDTV 24x7 or NDTV Profit.',
		),
		'mp4_url'      => array(
			'type'              => 'string',
			'description'       => 'Progressive MP4 rendition (media:filepath in NDTV\'s feed).',
			'sanitize_callback' => 'esc_url_raw',
		),
		'preview_url'  => array(
			'type'              => 'string',
			'description'       => 'Short muted MP4 played on tile hover (media:preview).',
			'sanitize_callback' => 'esc_url_raw',
		),
		'source_url'   => array(
			'type'              => 'string',
			'description'       => 'Canonical page on ndtv.com.',
			'sanitize_callback' => 'esc_url_raw',
		),
		'content_type' => array(
			'type'        => 'string',
			'description' => 'clip, full (episode) or live (media:content_type).',
		),
		'video_from'   => array(
			'type'        => 'string',
			'description' => 'Origin of the asset: TV or Digital.',
		),
		'related_ids'  => array(
			'type'        => 'string',
			'description' => 'Comma-separated NDTV player IDs the player suggests next.',
		),
		'synced_at'    => array(
			'type'        => 'string',
			'description' => 'When the NDTV sync last wrote this video (ISO 8601).',
		),
		'ndtv_views'  => array(
			'type'        => 'integer',
			'description' => 'View count used by the "top videos" rail. Parse.ly replaces this on VIP.',
			'default'     => 0,
		),
	);

	foreach ( $video_meta as $key => $args ) {
		$enum = $args['enum'] ?? null;
		unset( $args['enum'] );

		register_post_meta(
			'ndtv_video',
			$key,
			array_merge(
				array(
					'single'        => true,
					'show_in_rest'  => $enum ? array( 'schema' => array( 'enum' => $enum ) ) : true,
					'auth_callback' => static fn() => current_user_can( 'edit_posts' ),
				),
				$args
			)
		);
	}

	// Story meta: the red kicker badge ("3rd T20I:") and an optional scorecard link.
	register_post_meta(
		'post',
		'ndtv_kicker',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => static fn() => current_user_can( 'edit_posts' ),
		)
	);
	register_post_meta(
		'post',
		'ndtv_scorecard_url',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'esc_url_raw',
			'auth_callback'     => static fn() => current_user_can( 'edit_posts' ),
		)
	);
}

/**
 * Format a duration in seconds as m:ss or h:mm:ss.
 *
 * @param int $seconds Duration in seconds.
 * @return string
 */
function ndtv_core_format_duration( $seconds ) {
	$seconds = absint( $seconds );
	if ( ! $seconds ) {
		return '';
	}
	$h = intdiv( $seconds, 3600 );
	$m = intdiv( $seconds % 3600, 60 );
	$s = $seconds % 60;

	return $h ? sprintf( '%d:%02d:%02d', $h, $m, $s ) : sprintf( '%d:%02d', $m, $s );
}

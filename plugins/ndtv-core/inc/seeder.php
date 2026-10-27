<?php
/**
 * Demo content seeder. Called from the Playground blueprint (runPHP) and from
 * `wp ndtv seed`.
 *
 * @package NDTV_Core
 */

defined( 'ABSPATH' ) || exit;

const NDTV_CORE_SEED_VERSION = 3;
const NDTV_CORE_DEMO_META    = '_ndtv_demo';

/**
 * Seed the demo content.
 *
 * @param bool $force Delete previously seeded content and seed again.
 * @return array Counts of what was created, or an empty array if already seeded.
 */
function ndtv_core_seed( $force = false ) {
	if ( ! $force && (int) get_option( 'ndtv_core_seeded' ) >= NDTV_CORE_SEED_VERSION ) {
		return array();
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';

	if ( $force ) {
		ndtv_core_unseed();
	}

	$data = require NDTV_CORE_DIR . 'inc/seed-data.php';

	// Clear the WordPress sample content.
	foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page' ) as $slug => $type ) {
		$sample = get_page_by_path( $slug, OBJECT, $type );
		if ( $sample ) {
			wp_delete_post( $sample->ID, true );
		}
	}

	$authors  = ndtv_core_seed_authors( $data['authors'] );
	$images   = ndtv_core_seed_images();
	$sections = ndtv_core_seed_sections( $data['sections'] );
	$stories  = ndtv_core_seed_stories( $data, $authors, $sections, $images );
	$videos   = ndtv_core_seed_videos( $data );

	update_option( 'ndtv_core_seeded', NDTV_CORE_SEED_VERSION );
	flush_rewrite_rules( false );

	return array(
		'authors'  => count( $authors ),
		'images'   => array_sum( array_map( 'count', $images ) ),
		'sections' => count( $sections ),
		'stories'  => $stories,
		'videos'   => $videos,
	);
}

/**
 * Delete everything the seeder created.
 */
function ndtv_core_unseed() {
	$ids = get_posts(
		array(
			'post_type'      => array( 'post', 'ndtv_video', 'attachment' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => NDTV_CORE_DEMO_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);
	foreach ( $ids as $id ) {
		'attachment' === get_post_type( $id ) ? wp_delete_attachment( $id, true ) : wp_delete_post( $id, true );
	}
	delete_option( 'sticky_posts' );
	delete_option( 'ndtv_core_seeded' );
}

/**
 * Create the demo authors.
 *
 * @param array $rows Author rows.
 * @return array login => user ID.
 */
function ndtv_core_seed_authors( $rows ) {
	$ids = array();
	foreach ( $rows as list( $login, $name, $bio ) ) {
		$user = get_user_by( 'login', $login );
		if ( $user ) {
			$ids[ $login ] = $user->ID;
			continue;
		}
		list( $first, $last ) = explode( ' ', $name, 2 );

		$ids[ $login ] = wp_insert_user(
			array(
				'user_login'   => $login,
				'user_pass'    => wp_generate_password( 24 ),
				'user_email'   => $login . '@example.com',
				'display_name' => $name,
				'first_name'   => $first,
				'last_name'    => $last,
				'nickname'     => $name,
				'description'  => $bio,
				'role'         => 'author',
			)
		);
	}
	return $ids;
}

/**
 * Import the bundled placeholder story images as attachments.
 *
 * Video posters are not bundled: synced videos use NDTV's own posters on its
 * image CDN (see inc/remote-media.php).
 *
 * @return array{story: int[]}
 */
function ndtv_core_seed_images() {
	$out = array( 'story' => array() );
	foreach ( glob( NDTV_CORE_DIR . 'assets/img/story-*.jpg' ) as $path ) {
		$upload = wp_upload_bits( 'ndtv-demo-' . basename( $path ), null, file_get_contents( $path ) ); // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown
		if ( ! empty( $upload['error'] ) ) {
			continue;
		}
		$id = wp_insert_attachment(
			array(
				'post_mime_type' => 'image/jpeg',
				'post_title'     => __( 'Placeholder image', 'ndtv-core' ),
				'post_status'    => 'inherit',
			),
			$upload['file']
		);
		update_post_meta( $id, '_wp_attachment_image_alt', '' );
		update_post_meta( $id, NDTV_CORE_DEMO_META, 1 );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

		$out['story'][] = $id;
	}
	return $out;
}

/**
 * Create the news sections (categories).
 *
 * @param array $sections slug => [ name, author ].
 * @return array slug => term ID.
 */
function ndtv_core_seed_sections( $sections ) {
	$ids = array();
	foreach ( $sections as $slug => $section ) {
		$term = term_exists( $slug, 'category' );
		if ( ! $term ) {
			$term = wp_insert_term( $section[0], 'category', array( 'slug' => $slug ) );
		}
		$ids[ $slug ] = (int) $term['term_id'];
	}
	return $ids;
}

/**
 * Build block markup for a story body.
 *
 * @param string   $dek  Standfirst, used as the opening paragraph.
 * @param string[] $paras Body paragraphs.
 * @return string
 */
function ndtv_core_story_content( $dek, $paras ) {
	$blocks = array( $dek );
	array_push( $blocks, ...$paras );

	$html = '';
	foreach ( $blocks as $p ) {
		$html .= "<!-- wp:paragraph -->\n<p>" . esc_html( $p ) . "</p>\n<!-- /wp:paragraph -->\n\n";
	}
	$html .= "<!-- wp:paragraph {\"className\":\"ndtv-demo-note\"} -->\n<p class=\"ndtv-demo-note\"><em>" . esc_html__( 'Demo content: this story is placeholder copy written for the NDTV on WordPress VIP prototype.', 'ndtv-core' ) . "</em></p>\n<!-- /wp:paragraph -->";

	return $html;
}

/**
 * Create the news stories.
 *
 * @param array $data     Seed data.
 * @param array $authors  login => user ID.
 * @param array $sections slug => term ID.
 * @param array $images   Attachment IDs.
 * @return int Stories created.
 */
function ndtv_core_seed_stories( $data, $authors, $sections, $images ) {
	$now   = time();
	$count = 0;

	foreach ( $data['stories'] as $i => $row ) {
		list( $section, $kicker, $title, $dek ) = $row;
		$tags   = $row[4] ?? array();
		$extras = $row[5] ?? array();
		$paras  = $data['bodies'][ $section ] ?? $data['bodies']['default'];
		$date   = $now - ( $i * 40 + 10 ) * MINUTE_IN_SECONDS;

		$id = wp_insert_post(
			array(
				'post_type'     => 'post',
				'post_status'   => 'publish',
				'post_title'    => $title,
				'post_excerpt'  => $dek,
				'post_content'  => ndtv_core_story_content( $dek, $paras ),
				'post_author'   => $authors[ $data['sections'][ $section ][1] ],
				'post_category' => array( $sections[ $section ] ),
				'tags_input'    => $tags,
				'post_date'     => wp_date( 'Y-m-d H:i:s', $date ),
				'meta_input'    => array(
					NDTV_CORE_DEMO_META  => 1,
					'ndtv_kicker'        => $kicker,
					'ndtv_scorecard_url' => empty( $extras['scorecard'] ) ? '' : '#scorecard',
				),
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			continue;
		}
		if ( $images['story'] ) {
			set_post_thumbnail( $id, $images['story'][ $i % count( $images['story'] ) ] );
		}
		if ( ! empty( $extras['lead'] ) ) {
			stick_post( $id );
		}
		++$count;
	}
	return $count;
}

/**
 * Create the video categories, then pull the videos in through the NDTV sync.
 *
 * @param array $data Seed data.
 * @return int Videos created or updated.
 */
function ndtv_core_seed_videos( $data ) {
	foreach ( $data['video_cats'] as $name => $description ) {
		$slug = sanitize_title( $name );
		$term = term_exists( $slug, 'video_category' );
		if ( $term ) {
			wp_update_term( (int) $term['term_id'], 'video_category', array( 'description' => $description ) );
		} else {
			wp_insert_term(
				$name,
				'video_category',
				array(
					'slug'        => $slug,
					'description' => $description,
				)
			);
		}
	}

	$result = ndtv_core_sync_videos();
	return $result['created'] + $result['updated'];
}

/**
 * Find a video by its NDTV player ID. Used by the sync and theme patterns.
 *
 * @param string $player_id Player ID.
 * @return int Post ID or 0.
 */
function ndtv_core_video_by_player_id( $player_id ) {
	$ids = get_posts(
		array(
			'post_type'      => 'ndtv_video',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => 'player_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $player_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

/**
 * The newest horizontal, on-demand video. Used for the homepage featured player.
 *
 * @return int Post ID or 0.
 */
function ndtv_core_featured_video_id() {
	$ids = get_posts(
		ndtv_core_rail_query_vars(
			'latest',
			array(
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

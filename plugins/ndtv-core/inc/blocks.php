<?php
/**
 * Block registration and the post-title kicker filter.
 *
 * The editor script is plain ES5-style JS against the wp.* globals, so the
 * plugin has no build step and runs as-is on Playground.
 *
 * @package NDTV_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'ndtv_core_register_blocks' );

/**
 * Register the shared editor script and every block under blocks/.
 */
function ndtv_core_register_blocks() {
	wp_register_script(
		'ndtv-core-editor',
		NDTV_CORE_URL . 'assets/js/editor.js',
		array( 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-core-data', 'wp-data', 'wp-editor', 'wp-element', 'wp-i18n', 'wp-plugins', 'wp-server-side-render' ),
		NDTV_CORE_VERSION,
		true
	);

	foreach ( array( 'player', 'badge', 'market-strip' ) as $block ) {
		register_block_type( NDTV_CORE_DIR . 'blocks/' . $block );
	}
}

/**
 * Resolve a progressive (MP4) source for a video post.
 *
 * Synced NDTV videos carry their own MP4 rendition on NDTV's CDN. Live channels
 * have only HLS, which assets/js/player.js attaches with hls.js, so they get no
 * MP4. Videos with no stream at all (local uploads in the demo) fall back to a
 * bundled sample that matches their orientation.
 *
 * @param int $video_id ndtv_video post ID.
 * @return string
 */
function ndtv_core_video_src( $video_id ) {
	$mp4 = (string) get_post_meta( $video_id, 'mp4_url', true );
	if ( $mp4 ) {
		return $mp4;
	}

	$stream = (string) get_post_meta( $video_id, 'stream_url', true );
	if ( $stream && preg_match( '/\.mp4(\?|$)/i', $stream ) ) {
		return $stream;
	}
	if ( ndtv_core_hls_src( $video_id ) ) {
		return '';
	}

	$orientation = get_post_meta( $video_id, 'orientation', true ) === 'vertical' ? 'vertical' : 'horizontal';

	/**
	 * Filter the fallback source used when a video has no stream of its own.
	 *
	 * @param string $src      Fallback URL.
	 * @param int    $video_id Video post ID.
	 */
	return apply_filters( 'ndtv_core_fallback_video_src', NDTV_CORE_URL . "assets/video/sample-{$orientation}.mp4", $video_id );
}

/**
 * The HLS (.m3u8) stream for a video, if it has one.
 *
 * @param int $video_id ndtv_video post ID.
 * @return string
 */
function ndtv_core_hls_src( $video_id ) {
	$stream = (string) get_post_meta( $video_id, 'stream_url', true );
	return preg_match( '/\.m3u8(\?|$)/i', $stream ) ? $stream : '';
}

add_action( 'wp_enqueue_scripts', 'ndtv_core_enqueue_player_script' );

/**
 * Front-end player script: HLS for live streams and hover previews on video
 * tiles. It is small and loads hls.js only when a stream needs it.
 */
function ndtv_core_enqueue_player_script() {
	wp_enqueue_script(
		'ndtv-core-player',
		NDTV_CORE_URL . 'assets/js/player.js',
		array(),
		NDTV_CORE_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
	wp_localize_script(
		'ndtv-core-player',
		'ndtvPlayer',
		array( 'hlsUrl' => NDTV_CORE_URL . 'assets/vendor/hls.light.min.js?ver=1.5.17' )
	);
}

add_filter( 'render_block_core/post-featured-image', 'ndtv_core_featured_image_preview', 10, 3 );

/**
 * Mark video tiles that have a hover preview clip, as ndtv.com does.
 *
 * @param string   $content  Rendered block.
 * @param array    $block    Parsed block.
 * @param WP_Block $instance Block instance.
 * @return string
 */
function ndtv_core_featured_image_preview( $content, $block, $instance ) {
	$post_id = $instance->context['postId'] ?? 0;
	if ( ! $content || ! $post_id || 'ndtv_video' !== get_post_type( $post_id ) ) {
		return $content;
	}
	$preview = (string) get_post_meta( $post_id, 'preview_url', true );
	if ( ! $preview ) {
		return $content;
	}

	$tags = new WP_HTML_Tag_Processor( $content );
	if ( $tags->next_tag() ) {
		$tags->set_attribute( 'data-ndtv-preview', esc_url( $preview ) );
	}
	return $tags->get_updated_html();
}

add_filter( 'render_block_core/post-title', 'ndtv_core_post_title_kicker', 10, 3 );

/**
 * Prepend the red kicker badge to post titles and append the scorecard link.
 *
 * Done as a filter so templates keep using core/post-title.
 *
 * @param string   $content  Rendered block.
 * @param array    $block    Parsed block.
 * @param WP_Block $instance Block instance.
 * @return string
 */
function ndtv_core_post_title_kicker( $content, $block, $instance ) {
	$post_id = $instance->context['postId'] ?? 0;
	if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
		return $content;
	}
	if ( ! empty( $block['attrs']['className'] ) && str_contains( $block['attrs']['className'], 'no-kicker' ) ) {
		return $content;
	}

	$kicker    = get_post_meta( $post_id, 'ndtv_kicker', true );
	$scorecard = get_post_meta( $post_id, 'ndtv_scorecard_url', true );

	if ( $kicker ) {
		$badge   = '<span class="ndtv-kicker">' . esc_html( $kicker ) . '</span> ';
		$content = preg_replace( '/(<h[1-6][^>]*>|<p[^>]*>)/', '$1' . $badge, $content, 1 );
	}
	if ( $scorecard ) {
		$link    = ' <span class="ndtv-scorecard-sep">|</span> <a class="ndtv-scorecard" href="' . esc_url( $scorecard ) . '">' . esc_html__( 'Scorecard', 'ndtv-core' ) . '</a>';
		$content = preg_replace( '/(<\/h[1-6]>|<\/p>)\s*$/', $link . '$1', $content, 1 );
	}

	return $content;
}

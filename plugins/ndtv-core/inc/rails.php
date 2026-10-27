<?php
/**
 * Video rails: Query Loop variations over ndtv_video.
 *
 * A Query Loop opts in by carrying `ndtvRail` inside its `query` attribute
 * (the editor variations in assets/js/editor.js set it). The query object is
 * passed to post-template as block context, so the rail survives into
 * query_loop_block_query_vars without a custom block.
 *
 * @package NDTV_Core
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'query_loop_block_query_vars', 'ndtv_core_rail_query_loop_vars', 10, 2 );

/**
 * Apply the rail to a Query Loop's WP_Query vars.
 *
 * @param array    $vars  Query vars built by core.
 * @param WP_Block $block post-template block.
 * @return array
 */
function ndtv_core_rail_query_loop_vars( $vars, $block ) {
	$rail = $block->context['query']['ndtvRail'] ?? '';
	return $rail ? ndtv_core_rail_query_vars( $rail, $vars ) : $vars;
}

/**
 * Query vars for a named rail.
 *
 * - latest:  newest horizontal VOD.
 * - shorts:  vertical videos.
 * - live:    live channels.
 * - top:     most viewed. On VIP this reads Parse.ly; on the demo it sorts by
 *            the seeded `ndtv_views` meta.
 * - related: videos sharing tags with the story or video being viewed,
 *            topped up from the same video category, then from latest.
 *
 * @param string $rail Rail name.
 * @param array  $vars Base query vars.
 * @return array
 */
function ndtv_core_rail_query_vars( $rail, $vars ) {
	$vars['post_type']           = 'ndtv_video';
	$vars['ignore_sticky_posts'] = true;

	$horizontal_vod = array(
		'relation' => 'AND',
		array(
			'relation' => 'OR',
			array(
				'key'     => 'orientation',
				'value'   => 'vertical',
				'compare' => '!=',
			),
			array(
				'key'     => 'orientation',
				'compare' => 'NOT EXISTS',
			),
		),
		array(
			'relation' => 'OR',
			array(
				'key'     => 'kind',
				'value'   => 'live',
				'compare' => '!=',
			),
			array(
				'key'     => 'kind',
				'compare' => 'NOT EXISTS',
			),
		),
	);

	// Never list the video being watched in its own page's rails.
	if ( 'related' !== $rail && is_singular( 'ndtv_video' ) ) {
		$vars['post__not_in'] = array_merge( (array) ( $vars['post__not_in'] ?? array() ), array( get_queried_object_id() ) ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
	}

	switch ( $rail ) {
		case 'shorts':
			$vars['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'   => 'orientation',
					'value' => 'vertical',
				),
			);
			break;

		case 'live':
			$vars['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'   => 'kind',
					'value' => 'live',
				),
			);
			$vars['orderby'] = 'title';
			$vars['order']   = 'ASC';
			break;

		case 'top':
			/**
			 * Short-circuit the "top videos" rail, e.g. with post IDs from Parse.ly.
			 *
			 * @param int[]|null $ids  Ordered post IDs, or null to use the meta sort.
			 * @param array      $vars Query vars.
			 */
			$ids = apply_filters( 'ndtv_core_top_video_ids', null, $vars );
			if ( is_array( $ids ) ) {
				$vars['post__in'] = $ids ? $ids : array( 0 );
				$vars['orderby']  = 'post__in';
				break;
			}
			$vars['meta_query'] = $horizontal_vod; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$vars['meta_key']   = 'ndtv_views'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$vars['orderby']    = 'meta_value_num';
			$vars['order']      = 'DESC';
			break;

		case 'related':
			$vars = ndtv_core_related_query_vars( $vars, $horizontal_vod );
			break;

		case 'latest':
		default:
			$vars['meta_query'] = $horizontal_vod; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			break;
	}

	return $vars;
}

/**
 * Related videos for the current singular page, as an ordered post__in.
 *
 * Fills up to the rail's size in priority order: videos sharing a tag with
 * the current story or video, then videos in the same video category, then
 * the latest videos.
 *
 * @param array $vars           Base vars.
 * @param array $horizontal_vod Meta query for horizontal VOD.
 * @return array
 */
function ndtv_core_related_query_vars( $vars, $horizontal_vod ) {
	$current = is_singular() ? get_queried_object_id() : 0;
	$count   = max( 1, (int) ( $vars['posts_per_page'] ?? 4 ) );

	$vars['meta_query'] = $horizontal_vod; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	if ( ! $current ) {
		return $vars;
	}

	$ids  = array();
	$base = array(
		'post_type'      => 'ndtv_video',
		'posts_per_page' => $count,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => $horizontal_vod, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	);

	$tiers = array();
	$tags  = wp_get_post_terms( $current, 'post_tag', array( 'fields' => 'ids' ) );
	if ( ! is_wp_error( $tags ) && $tags ) {
		$tiers[] = array( 'post_tag', $tags );
	}
	if ( 'ndtv_video' === get_post_type( $current ) ) {
		$cats = wp_get_post_terms( $current, 'video_category', array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $cats ) && $cats ) {
			$tiers[] = array( 'video_category', $cats );
		}
	}
	$tiers[] = null; // Latest.

	foreach ( $tiers as $tier ) {
		if ( count( $ids ) >= $count ) {
			break;
		}
		$args                 = $base;
		$args['post__not_in'] = array_merge( array( $current ), $ids ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
		if ( $tier ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => $tier[0],
					'terms'    => $tier[1],
				),
			);
		}
		$ids = array_merge( $ids, get_posts( $args ) );
	}

	$vars['post__in'] = $ids ? array_slice( $ids, 0, $count ) : array( 0 );
	$vars['orderby']  = 'post__in';
	unset( $vars['meta_query'] );

	return $vars;
}

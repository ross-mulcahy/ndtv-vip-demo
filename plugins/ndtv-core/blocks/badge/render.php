<?php
/**
 * NDTV Badge render.
 *
 * @package NDTV_Core
 *
 * @var array    $attributes Block attributes.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$post_id = (int) ( $block->context['postId'] ?? 0 );
if ( ! $post_id ) {
	return;
}

$is_live = get_post_meta( $post_id, 'kind', true ) === 'live';
$label   = '';

switch ( $attributes['type'] ) {
	case 'duration':
		$label = $is_live ? __( 'LIVE', 'ndtv-core' ) : ndtv_core_format_duration( get_post_meta( $post_id, 'duration', true ) );
		break;
	case 'live':
		$label = $is_live ? __( 'LIVE', 'ndtv-core' ) : '';
		break;
	case 'play':
		$label = __( 'Play', 'ndtv-core' );
		break;
	case 'channel':
		$label = (string) get_post_meta( $post_id, 'channel', true );
		break;
	case 'section':
		$terms = get_the_terms( $post_id, 'ndtv_video' === get_post_type( $post_id ) ? 'video_category' : 'category' );
		$label = $terms && ! is_wp_error( $terms ) ? $terms[0]->name : '';
		break;
}

if ( '' === $label ) {
	return;
}

$classes = 'ndtv-badge is-' . $attributes['type'] . ( $is_live ? ' is-live' : '' );
printf(
	'<span %s>%s</span>',
	get_block_wrapper_attributes( array( 'class' => $classes ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	esc_html( $label )
);

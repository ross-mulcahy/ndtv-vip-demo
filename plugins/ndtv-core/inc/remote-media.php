<?php
/**
 * Remote media: attachments whose file stays on NDTV's image CDN.
 *
 * Synced videos keep their posters on c.ndtvimg.com, which resizes on the fly
 * with a `?downsize=W:H` query arg. Each poster is a normal attachment post (so
 * featured images, Query Loops, the media library and REST all work), but its
 * URLs point at the CDN instead of uploads/. This mirrors "sync the metadata,
 * don't move the files".
 *
 * @package NDTV_Core
 */

defined( 'ABSPATH' ) || exit;

const NDTV_CORE_REMOTE_META = '_ndtv_remote_url';

/**
 * Create (or reuse) an attachment for a remote image.
 *
 * @param string $url       Image URL on the CDN.
 * @param int    $parent_id Post the image belongs to.
 * @param string $title     Attachment title, also used as alt text.
 * @param int    $width     Original width.
 * @param int    $height    Original height.
 * @return int Attachment ID, or 0 on failure.
 */
function ndtv_core_remote_image( $url, $parent_id, $title, $width, $height ) {
	$url = esc_url_raw( $url );
	if ( ! $url ) {
		return 0;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => NDTV_CORE_REMOTE_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $url, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}

	$id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => $title,
			'post_status'    => 'inherit',
			'guid'           => $url,
		),
		false,
		$parent_id,
		true
	);
	if ( is_wp_error( $id ) ) {
		return 0;
	}

	update_post_meta( $id, NDTV_CORE_REMOTE_META, $url );
	update_post_meta( $id, '_wp_attachment_image_alt', $title );
	wp_update_attachment_metadata(
		$id,
		array(
			'width'  => (int) $width,
			'height' => (int) $height,
			'file'   => wp_basename( wp_parse_url( $url, PHP_URL_PATH ) ),
			'sizes'  => array(),
		)
	);

	return $id;
}

/**
 * Build a CDN URL for a given size. Only NDTV's image CDN understands `downsize`.
 *
 * @param string $url    Original URL.
 * @param int    $width  Target width.
 * @param int    $height Target height.
 * @return string
 */
function ndtv_core_remote_resize_url( $url, $width, $height ) {
	if ( 'c.ndtvimg.com' !== wp_parse_url( $url, PHP_URL_HOST ) ) {
		return $url;
	}
	return add_query_arg( 'downsize', $width . ':' . $height, $url );
}

add_filter( 'wp_get_attachment_url', 'ndtv_core_remote_attachment_url', 10, 2 );

/**
 * Point remote attachments at the CDN.
 *
 * @param string $url Attachment URL.
 * @param int    $id  Attachment ID.
 * @return string
 */
function ndtv_core_remote_attachment_url( $url, $id ) {
	$remote = get_post_meta( $id, NDTV_CORE_REMOTE_META, true );
	return $remote ? $remote : $url;
}

add_filter( 'image_downsize', 'ndtv_core_remote_image_downsize', 10, 3 );

/**
 * Serve every intermediate size from the CDN resizer, keeping the aspect ratio.
 *
 * @param array|false  $out  Short-circuit value.
 * @param int          $id   Attachment ID.
 * @param string|int[] $size Size name or [ width, height ].
 * @return array|false [ url, width, height, is_intermediate ].
 */
function ndtv_core_remote_image_downsize( $out, $id, $size ) {
	$remote = get_post_meta( $id, NDTV_CORE_REMOTE_META, true );
	if ( ! $remote ) {
		return $out;
	}

	$meta = wp_get_attachment_metadata( $id );
	$full = array( (int) ( $meta['width'] ?? 1280 ), (int) ( $meta['height'] ?? 720 ) );

	if ( is_array( $size ) ) {
		$width = (int) $size[0];
	} else {
		$sizes = wp_get_registered_image_subsizes();
		$width = isset( $sizes[ $size ] ) ? (int) $sizes[ $size ]['width'] : $full[0];
	}
	$width  = $width ? min( $width, $full[0] ) : $full[0];
	$height = (int) round( $width * $full[1] / max( 1, $full[0] ) );

	if ( $width >= $full[0] ) {
		return array( $remote, $full[0], $full[1], false );
	}
	return array( ndtv_core_remote_resize_url( $remote, $width, $height ), $width, $height, true );
}

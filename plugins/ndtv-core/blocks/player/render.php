<?php
/**
 * NDTV Player render.
 *
 * @package NDTV_Core
 *
 * @var array    $attributes Block attributes.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$video_id  = (int) $attributes['videoId'];
$player_id = (string) $attributes['playerId'];

if ( ! $video_id && ! empty( $block->context['postId'] ) && 'ndtv_video' === get_post_type( $block->context['postId'] ) ) {
	$video_id = (int) $block->context['postId'];
}

// A raw player ID with no post: look for a synced video that carries it.
if ( ! $video_id && $player_id ) {
	$found    = get_posts(
		array(
			'post_type'      => 'ndtv_video',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => 'player_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $player_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	$video_id = $found ? (int) $found[0] : 0;
}

if ( ! $video_id || 'ndtv_video' !== get_post_type( $video_id ) ) {
	return;
}

$player_id   = $player_id ? $player_id : (string) get_post_meta( $video_id, 'player_id', true );
$orientation = get_post_meta( $video_id, 'orientation', true ) === 'vertical' ? 'vertical' : 'horizontal';
$is_live     = get_post_meta( $video_id, 'kind', true ) === 'live';
$duration    = ndtv_core_format_duration( get_post_meta( $video_id, 'duration', true ) );
$stream      = (string) get_post_meta( $video_id, 'stream_url', true );
$channel     = (string) get_post_meta( $video_id, 'channel', true );
$poster      = get_the_post_thumbnail_url( $video_id, 'large' );
$src         = ndtv_core_video_src( $video_id );
$hls         = ndtv_core_hls_src( $video_id );
$autoplay    = $attributes['autoplay'] || $is_live;

$classes = array( 'ndtv-player', 'is-' . $orientation );
if ( $is_live ) {
	$classes[] = 'is-live';
}

$wrapper = get_block_wrapper_attributes(
	array(
		'class'          => implode( ' ', $classes ),
		'data-player-id' => $player_id,
		'data-stream'    => $stream,
		'data-source'    => (string) get_post_meta( $video_id, 'source_url', true ),
	)
);
?>
<figure <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="ndtv-player__frame">
		<video
			class="ndtv-player__video"
			<?php echo $src ? 'src="' . esc_url( $src ) . '"' : ''; ?>
			<?php echo $hls ? 'data-hls="' . esc_url( $hls ) . '"' : ''; ?>
			<?php echo $poster ? 'poster="' . esc_url( $poster ) . '"' : ''; ?>
			controls
			playsinline
			preload="none"
			<?php echo $autoplay ? 'autoplay muted loop' : ''; ?>
		></video>
		<span class="ndtv-player__bug" aria-hidden="true">NDTV</span>
		<?php if ( $is_live ) : ?>
			<span class="ndtv-player__live"><?php esc_html_e( 'LIVE', 'ndtv-core' ); ?></span>
		<?php elseif ( $duration ) : ?>
			<span class="ndtv-player__duration"><?php echo esc_html( $duration ); ?></span>
		<?php endif; ?>
	</div>
	<?php if ( $attributes['showTitle'] ) : ?>
		<figcaption class="ndtv-player__caption">
			<?php if ( $channel ) : ?>
				<span class="ndtv-player__channel"><?php echo esc_html( $channel ); ?></span>
			<?php endif; ?>
			<a href="<?php echo esc_url( get_permalink( $video_id ) ); ?>"><?php echo esc_html( get_the_title( $video_id ) ); ?></a>
		</figcaption>
	<?php endif; ?>
</figure>

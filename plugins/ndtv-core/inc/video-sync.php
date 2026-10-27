<?php
/**
 * NDTV video sync: maps items from NDTV's video feed onto the ndtv_video CPT.
 *
 * The input is NDTV's own feed shape, as served by ndtv.com/shorts/api/index1
 * ({"total", "results": [ MRSS-style items ]}), plus a few `player:*` fields
 * taken from the page player config (window.__html5playerdata). Files are never
 * copied: streams, MP4s, previews and posters stay on NDTV's CDNs.
 *
 * On Playground the feed is a snapshot bundled in data/ndtv-video-feed.json. On
 * VIP, hook `ndtv_core_video_feed` to fetch the live API on a cron schedule.
 *
 * @package NDTV_Core
 */

defined( 'ABSPATH' ) || exit;

const NDTV_CORE_SYNC_OPTION = 'ndtv_core_video_sync';

/**
 * Channel codes seen in the player config.
 */
const NDTV_CORE_CHANNELS = array(
	'1' => 'NDTV 24x7',
	'2' => 'NDTV India',
	'3' => 'NDTV Profit',
);

/**
 * Load the feed.
 *
 * @return array Feed items.
 */
function ndtv_core_video_feed() {
	/**
	 * Supply the video feed. Return null to use the bundled snapshot.
	 *
	 * In production this would call NDTV's video API (with its key from an
	 * environment constant) and return the decoded {"total","results"} body.
	 *
	 * @param array|null $feed Decoded feed.
	 */
	$feed = apply_filters( 'ndtv_core_video_feed', null );

	if ( null === $feed ) {
		$json = file_get_contents( NDTV_CORE_DIR . 'data/ndtv-video-feed.json' ); // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown
		$feed = json_decode( (string) $json, true );
	}

	return is_array( $feed['results'] ?? null ) ? $feed['results'] : array();
}

/**
 * Sync feed items into the video library. Matches existing videos on player ID.
 *
 * @param array|null $items Feed items, or null to load the feed.
 * @return array{created: int, updated: int, skipped: int}
 */
function ndtv_core_sync_videos( $items = null ) {
	$items  = null === $items ? ndtv_core_video_feed() : $items;
	$result = array(
		'created' => 0,
		'updated' => 0,
		'skipped' => 0,
	);

	foreach ( $items as $item ) {
		$mapped = ndtv_core_map_feed_item( $item );
		if ( ! $mapped ) {
			++$result['skipped'];
			continue;
		}

		$existing = ndtv_core_video_by_player_id( $mapped['meta']['player_id'] );
		$postarr  = $mapped['post'];
		if ( $existing ) {
			$postarr['ID'] = $existing;
		}

		$id = $existing ? wp_update_post( $postarr, true ) : wp_insert_post( $postarr, true );
		if ( is_wp_error( $id ) ) {
			++$result['skipped'];
			continue;
		}

		foreach ( $mapped['meta'] as $key => $value ) {
			update_post_meta( $id, $key, $value );
		}
		wp_set_object_terms( $id, $mapped['tags'], 'post_tag' );
		wp_set_object_terms( $id, $mapped['category'], 'video_category' );
		wp_set_object_terms( $id, $mapped['show'] ? array( $mapped['show'] ) : array(), 'video_show' );

		if ( $mapped['poster'] ) {
			$poster = ndtv_core_remote_image( $mapped['poster'][0], $id, $postarr['post_title'], $mapped['poster'][1], $mapped['poster'][2] );
			if ( $poster ) {
				update_post_meta( $poster, NDTV_CORE_DEMO_META, 1 );
				set_post_thumbnail( $id, $poster );
			}
		}

		++$result[ $existing ? 'updated' : 'created' ];
	}

	update_option(
		NDTV_CORE_SYNC_OPTION,
		array_merge( $result, array( 'time' => time() ) ),
		false
	);

	return $result;
}

/**
 * Map one feed item to post fields, meta, terms and a poster.
 *
 * @param array $item Feed item.
 * @return array|null
 */
function ndtv_core_map_feed_item( $item ) {
	$player_id = sanitize_text_field( (string) ( $item['id'] ?? '' ) );
	$title     = html_entity_decode( (string) ( $item['title'] ?? '' ), ENT_QUOTES, 'UTF-8' );
	if ( ! $player_id || ! $title ) {
		return null;
	}

	$is_live  = ! empty( $item['player:isLive'] );
	$vertical = ! empty( $item['vertical'] );
	$formats  = (array) ( $item['media:allfileformats'] ?? array() );
	$mp4      = (string) ( $item['media:filepath'] ?? ( $formats['http'] ?? '' ) );
	$hls      = (string) ( $formats['ios'] ?? '' );
	$link     = (string) ( $item['link'] ?? '' );
	$network  = (string) ( $item['media:network']['name'] ?? 'NDTV' );
	$channel  = NDTV_CORE_CHANNELS[ (string) ( $item['player:channel'] ?? '' ) ] ?? $network;

	// Keep NDTV's URL slug (title plus numeric ID) so /video/<slug>/ matches ndtv.com.
	$slug = $is_live ? sanitize_title( $title ) : sanitize_title( wp_basename( (string) wp_parse_url( $link, PHP_URL_PATH ) ) );

	$timestamp = $is_live || empty( $item['pubDate'] ) ? time() : strtotime( (string) $item['pubDate'] );

	$description = (string) ( $item['description'] ?? '' );
	$excerpt     = wp_trim_words( html_entity_decode( wp_strip_all_tags( $description ), ENT_QUOTES, 'UTF-8' ), 40 );

	// Shorts carry several 9:16 posters; the "V_Big_Thumb" rendition is the sharpest.
	$candidates = $vertical
		? array( $item['media:fullimage'] ?? '', $item['media:verticalimage'] ?? '', $item['media:thumbnail'] ?? '' )
		: array( $item['media:fullimage'] ?? '', $item['media:thumbnail'] ?? '' );
	$candidates = array_values( array_filter( array_map( 'strval', $candidates ) ) );
	$big        = array_values( preg_grep( '/V_Big_Thumb/', $candidates ) );
	$image      = $big ? $big[0] : ( $candidates[0] ?? '' );

	return array(
		'post'     => array(
			'post_type'    => 'ndtv_video',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_excerpt' => $excerpt,
			'post_content' => '<!-- wp:ndtv/player {"showTitle":false} /-->' . "\n\n" . ndtv_core_description_blocks( $description ),
			'post_author'  => ndtv_core_video_desk_user(),
			'post_date'    => wp_date( 'Y-m-d H:i:s', $timestamp ),
		),
		'meta'     => array(
			NDTV_CORE_DEMO_META => 1,
			'player_id'         => $player_id,
			'kind'              => $is_live ? 'live' : 'vod',
			'orientation'       => $vertical ? 'vertical' : 'horizontal',
			'duration'          => absint( $item['media:duration'] ?? 0 ),
			'stream_url'        => esc_url_raw( $hls ? $hls : $mp4 ),
			'mp4_url'           => esc_url_raw( $mp4 ),
			'preview_url'       => esc_url_raw( (string) ( $item['media:preview'] ?? '' ) ),
			'source_url'        => esc_url_raw( $link ),
			'channel'           => $channel,
			'content_type'      => sanitize_key( (string) ( $item['media:content_type'] ?? 'clip' ) ),
			'video_from'        => sanitize_text_field( (string) ( $item['player:video_from'] ?? '' ) ),
			'related_ids'       => implode( ',', array_map( 'sanitize_text_field', (array) ( $item['player:related'] ?? array() ) ) ),
			'synced_at'         => gmdate( 'c' ),
			// Deterministic stand-in for Parse.ly views; the "top" rail sorts on it.
			'ndtv_views'        => 5000 + crc32( $player_id ) % 95000,
		),
		'tags'     => ndtv_core_feed_keywords( (string) ( $item['media:keywords'] ?? '' ) ),
		'category' => $vertical ? 'shorts' : sanitize_title( (string) ( $item['media:category'] ?? 'news' ) ),
		'show'     => ndtv_core_feed_show( (string) ( $item['media:show'] ?? '' ) ),
		'poster'   => $image ? array( strtok( $image, '?' ), $vertical ? 720 : 1280, $vertical ? 1280 : 720 ) : null,
	);
}

/**
 * Turn the feed's HTML description into paragraph blocks.
 *
 * @param string $html Description HTML.
 * @return string Block markup.
 */
function ndtv_core_description_blocks( $html ) {
	$html = trim( str_replace( array( "\r", "\n" ), ' ', $html ) );
	if ( ! $html ) {
		return '';
	}

	preg_match_all( '#<p[^>]*>(.*?)</p>#is', $html, $matches );
	$paras = $matches[1] ? $matches[1] : array( $html );

	$out = '';
	foreach ( $paras as $para ) {
		$para = trim( wp_kses( $para, array( 'a' => array( 'href' => true ), 'strong' => array(), 'em' => array(), 'b' => array(), 'i' => array() ) ) );
		if ( '' === $para || '&nbsp;' === $para ) {
			continue;
		}
		$out .= "<!-- wp:paragraph -->\n<p>" . $para . "</p>\n<!-- /wp:paragraph -->\n\n";
	}
	return trim( $out );
}

/**
 * Feed keywords become tags. Internal asset IDs ("brazilelection_199034") and
 * run-together campaign codes ("NDTVGolfProamvideos26") are dropped.
 *
 * @param string $keywords Comma-separated keywords.
 * @return string[]
 */
function ndtv_core_feed_keywords( $keywords ) {
	$tags = array();
	foreach ( explode( ',', $keywords ) as $keyword ) {
		$keyword = trim( html_entity_decode( $keyword, ENT_QUOTES, 'UTF-8' ) );
		if ( '' === $keyword || strlen( $keyword ) > 40 || preg_match( '/_\d+$/', $keyword ) || ( ! str_contains( $keyword, ' ' ) && strlen( $keyword ) > 15 ) ) {
			continue;
		}
		$tags[ strtolower( $keyword ) ] = $keyword;
	}
	return array_slice( array_values( $tags ), 0, 6 );
}

/**
 * Feed show slugs become show names. Generic buckets are not shows.
 *
 * @param string $show Show slug from the feed.
 * @return string
 */
function ndtv_core_feed_show( $show ) {
	$show = sanitize_title( $show );
	if ( in_array( $show, array( '', 'news', 'other' ), true ) ) {
		return '';
	}
	return str_replace( 'Ndtv', 'NDTV', ucwords( str_replace( '-', ' ', $show ) ) );
}

/**
 * The byline for synced videos.
 *
 * @return int User ID.
 */
function ndtv_core_video_desk_user() {
	static $id = null;
	if ( null !== $id ) {
		return $id;
	}

	$user = get_user_by( 'login', 'ndtv-video-desk' );
	$id   = $user ? $user->ID : wp_insert_user(
		array(
			'user_login'   => 'ndtv-video-desk',
			'user_pass'    => wp_generate_password( 24 ),
			'user_email'   => 'ndtv-video-desk@example.com',
			'display_name' => 'NDTV Video Desk',
			'nickname'     => 'NDTV Video Desk',
			'role'         => 'author',
		)
	);
	$id = is_wp_error( $id ) ? 1 : (int) $id;

	return $id;
}

add_action( 'admin_menu', 'ndtv_core_sync_admin_menu' );

/**
 * Videos > NDTV Sync.
 */
function ndtv_core_sync_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=ndtv_video',
		__( 'NDTV Sync', 'ndtv-core' ),
		__( 'NDTV Sync', 'ndtv-core' ),
		'edit_others_posts',
		'ndtv-video-sync',
		'ndtv_core_sync_admin_page'
	);
}

add_action( 'admin_post_ndtv_core_sync_videos', 'ndtv_core_sync_admin_action' );

/**
 * Handle "Sync now".
 */
function ndtv_core_sync_admin_action() {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		wp_die( esc_html__( 'You are not allowed to sync videos.', 'ndtv-core' ) );
	}
	check_admin_referer( 'ndtv_core_sync_videos' );

	ndtv_core_sync_videos();

	wp_safe_redirect( admin_url( 'edit.php?post_type=ndtv_video&page=ndtv-video-sync&synced=1' ) );
	exit;
}

/**
 * Render the sync screen: source, last run and what the feed contains.
 */
function ndtv_core_sync_admin_page() {
	$items = ndtv_core_video_feed();
	$last  = get_option( NDTV_CORE_SYNC_OPTION );
	$live  = has_filter( 'ndtv_core_video_feed' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'NDTV video sync', 'ndtv-core' ); ?></h1>
		<?php if ( isset( $_GET['synced'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Sync complete.', 'ndtv-core' ); ?></p></div>
		<?php endif; ?>
		<p>
			<?php esc_html_e( 'Videos are synced from NDTV\'s video feed into the Videos library. Only metadata is stored in WordPress: HLS and MP4 streams, hover previews and posters stay on NDTV\'s CDNs. Existing videos are matched on their NDTV player ID and updated in place.', 'ndtv-core' ); ?>
		</p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Source', 'ndtv-core' ); ?></th>
				<td>
					<?php
					echo $live
						? esc_html__( 'Live feed (ndtv_core_video_feed filter)', 'ndtv-core' )
						: esc_html__( 'Bundled snapshot of ndtv.com (data/ndtv-video-feed.json). On VIP this is NDTV\'s video API, polled by cron.', 'ndtv-core' );
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Last sync', 'ndtv-core' ); ?></th>
				<td>
					<?php
					echo $last
						? esc_html(
							sprintf(
								/* translators: 1: date, 2: created, 3: updated, 4: skipped */
								__( '%1$s: %2$d created, %3$d updated, %4$d skipped', 'ndtv-core' ),
								wp_date( 'M j, Y g:i a', $last['time'] ),
								$last['created'],
								$last['updated'],
								$last['skipped']
							)
						)
						: esc_html__( 'Never', 'ndtv-core' );
					?>
				</td>
			</tr>
		</table>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="ndtv_core_sync_videos">
			<?php wp_nonce_field( 'ndtv_core_sync_videos' ); ?>
			<?php submit_button( __( 'Sync now', 'ndtv-core' ), 'primary', 'submit', false ); ?>
		</form>

		<h2><?php echo esc_html( sprintf( /* translators: %d: item count */ __( 'Feed items (%d)', 'ndtv-core' ), count( $items ) ) ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Player ID', 'ndtv-core' ); ?></th>
					<th><?php esc_html_e( 'Title', 'ndtv-core' ); ?></th>
					<th><?php esc_html_e( 'Type', 'ndtv-core' ); ?></th>
					<th><?php esc_html_e( 'Category', 'ndtv-core' ); ?></th>
					<th><?php esc_html_e( 'Duration', 'ndtv-core' ); ?></th>
					<th><?php esc_html_e( 'In library', 'ndtv-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $items as $item ) : ?>
					<?php
					$post_id = ndtv_core_video_by_player_id( (string) $item['id'] );
					$type    = ! empty( $item['player:isLive'] ) ? __( 'Live', 'ndtv-core' ) : ( ! empty( $item['vertical'] ) ? __( 'Short (9:16)', 'ndtv-core' ) : ucfirst( (string) ( $item['media:content_type'] ?? 'clip' ) ) );
					?>
					<tr>
						<td><code><?php echo esc_html( $item['id'] ); ?></code></td>
						<td><a href="<?php echo esc_url( $item['link'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( html_entity_decode( $item['title'], ENT_QUOTES, 'UTF-8' ) ); ?></a></td>
						<td><?php echo esc_html( $type ); ?></td>
						<td><?php echo esc_html( $item['media:category'] ?? '' ); ?></td>
						<td><?php echo esc_html( ndtv_core_format_duration( $item['media:duration'] ?? 0 ) ); ?></td>
						<td>
							<?php if ( $post_id ) : ?>
								<a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"><?php esc_html_e( 'Edit', 'ndtv-core' ); ?></a>
							<?php else : ?>
								&mdash;
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

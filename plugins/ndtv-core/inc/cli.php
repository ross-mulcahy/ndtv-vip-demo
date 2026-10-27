<?php
/**
 * WP-CLI commands.
 *
 * @package NDTV_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * NDTV demo commands.
 */
class NDTV_Core_CLI {

	/**
	 * Sync videos from NDTV's video feed into the Videos library.
	 *
	 * ## OPTIONS
	 *
	 * [--file=<path>]
	 * : A feed JSON file ({"results": [...]}) to use instead of the configured feed.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ndtv sync-videos
	 *     wp ndtv sync-videos --file=feed.json
	 *
	 * @subcommand sync-videos
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Flags.
	 */
	public function sync_videos( $args, $assoc_args ) {
		$items = null;
		if ( ! empty( $assoc_args['file'] ) ) {
			$feed  = json_decode( (string) file_get_contents( $assoc_args['file'] ), true ); // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown
			$items = $feed['results'] ?? null;
			if ( ! is_array( $items ) ) {
				WP_CLI::error( 'The file has no "results" array.' );
			}
		}
		$result = ndtv_core_sync_videos( $items );
		WP_CLI::success( sprintf( '%d created, %d updated, %d skipped.', $result['created'], $result['updated'], $result['skipped'] ) );
	}

	/**
	 * Seed the demo content.
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : Delete previously seeded content and seed again.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ndtv seed
	 *     wp ndtv seed --force
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Flags.
	 */
	public function seed( $args, $assoc_args ) {
		$result = ndtv_core_seed( ! empty( $assoc_args['force'] ) );
		if ( ! $result ) {
			WP_CLI::success( 'Already seeded. Use --force to reseed.' );
			return;
		}
		foreach ( $result as $what => $n ) {
			WP_CLI::log( sprintf( '%-9s %d', $what, $n ) );
		}
		WP_CLI::success( 'Demo content seeded.' );
	}

	/**
	 * Delete all seeded demo content.
	 */
	public function unseed() {
		ndtv_core_unseed();
		WP_CLI::success( 'Demo content removed.' );
	}
}

WP_CLI::add_command( 'ndtv', 'NDTV_Core_CLI' );

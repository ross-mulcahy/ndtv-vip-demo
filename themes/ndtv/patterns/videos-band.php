<?php
/**
 * Title: Videos band
 * Slug: ndtv/videos-band
 * Categories: ndtv
 * Description: Dark video panel with channel tabs, a featured player and a Must Watch list.
 *
 * @package NDTV
 */

$ndtv_featured = ndtv_featured_video_id();
$ndtv_video_url = home_url( '/video/' );
$ndtv_tabs      = array(
	'Featured'  => $ndtv_video_url,
	'NDTV'      => $ndtv_video_url,
	'India'     => $ndtv_video_url,
	'Profit'    => $ndtv_video_url,
	'Marathi'   => $ndtv_video_url,
	'MPCG'      => $ndtv_video_url,
	'Rajasthan' => $ndtv_video_url,
);
?>
<!-- wp:group {"className":"ndtv-videos","layout":{"type":"default"}} -->
<div class="wp-block-group ndtv-videos">
<!-- wp:group {"className":"ndtv-container","layout":{"type":"default"}} -->
<div class="wp-block-group ndtv-container">
<?php echo ndtv_section_head( 'Videos', $ndtv_video_url, $ndtv_tabs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<!-- wp:columns {"className":"ndtv-videos__body"} -->
<div class="wp-block-columns ndtv-videos__body">
<!-- wp:column {"width":"62%"} -->
<div class="wp-block-column" style="flex-basis:62%">
<?php if ( $ndtv_featured ) : ?>
<!-- wp:ndtv/player {"videoId":<?php echo (int) $ndtv_featured; ?>} /-->
<?php endif; ?>
</div>
<!-- /wp:column -->
<!-- wp:column {"width":"38%"} -->
<div class="wp-block-column" style="flex-basis:38%">
<!-- wp:heading {"level":3,"className":"ndtv-videos__subhead"} -->
<h3 class="wp-block-heading ndtv-videos__subhead">Must Watch</h3>
<!-- /wp:heading -->
<?php
echo ndtv_query( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	array(
		'perPage'  => 5,
		'postType' => 'ndtv_video',
		'ndtvRail' => 'top',
		'exclude'  => array_values( array_filter( array( $ndtv_featured ) ) ),
	),
	ndtv_tpl_row( 4, true ),
	'ndtv-rows'
);
?>
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

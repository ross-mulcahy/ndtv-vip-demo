<?php
/**
 * Title: Right rail
 * Slug: ndtv/right-rail
 * Categories: ndtv
 * Description: MPU ad, sponsored native, Watch Live, Other Stories, NDTV Micro shorts and Top Deals.
 *
 * @package NDTV
 */

$ndtv_live   = ndtv_video_id( 'LIVE_BG24x7' );
$ndtv_shorts = ndtv_term_id( 'shorts', 'video_category' );
?>
<!-- wp:group {"className":"ndtv-rail","layout":{"type":"default"}} -->
<div class="wp-block-group ndtv-rail">
<!-- wp:group {"className":"ndtv-ad ndtv-ad--mpu","layout":{"type":"default"}} -->
<div class="wp-block-group ndtv-ad ndtv-ad--mpu">
<!-- wp:paragraph {"className":"ndtv-ad__label"} -->
<p class="ndtv-ad__label">Advertisement</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"ndtv-ad__slot"} -->
<p class="ndtv-ad__slot">300 × 250<br>House ad slot</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"ndtv-native","layout":{"type":"default"}} -->
<div class="wp-block-group ndtv-native">
<!-- wp:paragraph {"className":"ndtv-native__title"} -->
<p class="ndtv-native__title"><a href="https://wpvip.com/">How newsrooms publish faster on enterprise WordPress</a></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"ndtv-native__meta"} -->
<p class="ndtv-native__meta">WordPress VIP | Sponsored</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<?php if ( $ndtv_live ) : ?>
<!-- wp:heading {"level":3,"className":"ndtv-rail__head is-live"} -->
<h3 class="wp-block-heading ndtv-rail__head is-live"><a href="<?php echo esc_url( home_url( '/video/' ) ); ?>">Watch Live</a></h3>
<!-- /wp:heading -->
<!-- wp:ndtv/player {"videoId":<?php echo (int) $ndtv_live; ?>} /-->
<?php endif; ?>

<!-- wp:heading {"level":3,"className":"ndtv-rail__head"} -->
<h3 class="wp-block-heading ndtv-rail__head"><a href="<?php echo esc_url( get_year_link( (int) wp_date( 'Y' ) ) ); ?>">Other Stories</a></h3>
<!-- /wp:heading -->
<?php
echo ndtv_query( array( 'perPage' => 4, 'offset' => 14, 'sticky' => 'exclude' ), ndtv_tpl_title( 4 ), 'ndtv-bullets' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

if ( $ndtv_shorts ) :
	?>
<!-- wp:heading {"level":3,"className":"ndtv-rail__head"} -->
<h3 class="wp-block-heading ndtv-rail__head"><a href="<?php echo esc_url( get_term_link( $ndtv_shorts ) ); ?>">NDTV Micro</a></h3>
<!-- /wp:heading -->
	<?php
	echo ndtv_query( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		array(
			'perPage'  => 3,
			'postType' => 'ndtv_video',
			'ndtvRail' => 'shorts',
		),
		ndtv_tpl_video( '9/16', 4 ),
		'ndtv-micro',
		array(
			'type'        => 'grid',
			'columnCount' => 3,
		)
	);
endif;
?>

<!-- wp:heading {"level":3,"className":"ndtv-rail__head"} -->
<h3 class="wp-block-heading ndtv-rail__head">Top Deals</h3>
<!-- /wp:heading -->
<!-- wp:list {"className":"ndtv-deals"} -->
<ul class="wp-block-list ndtv-deals">
<!-- wp:list-item -->
<li><strong>Wireless earbuds</strong> with noise cancelling <span>40% off</span></li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li><strong>Air purifier</strong> for rooms up to 400 sq ft <span>₹3,000 off</span></li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li><strong>5G phone</strong> with 128 GB storage <span>Festive price</span></li>
<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->
<!-- wp:paragraph {"className":"ndtv-deals__note"} -->
<p class="ndtv-deals__note">Placeholder affiliate unit</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

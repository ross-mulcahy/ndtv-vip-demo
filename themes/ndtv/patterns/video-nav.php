<?php
/**
 * Title: Video left rail
 * Slug: ndtv/video-nav
 * Categories: ndtv
 * Inserter: no
 *
 * @package NDTV
 */

$ndtv_hub   = home_url( '/video/' );
$ndtv_items = array(
	'live'       => array( 'Live TV', $ndtv_hub . '#live' ),
	'latest'     => array( 'Latest Videos', $ndtv_hub . '#latest' ),
	'shorts'     => array( 'Shorts', $ndtv_hub . '#shorts' ),
	'categories' => array( 'Categories', $ndtv_hub . '#categories' ),
	'shows'      => array( 'Shows', $ndtv_hub . '#shows' ),
	'top'        => array( 'Top Videos', $ndtv_hub . '#top' ),
);
$ndtv_features = get_term_by( 'slug', 'features', 'video_category' );
if ( $ndtv_features ) {
	$ndtv_items['classics'] = array( 'NDTV Classics', get_term_link( $ndtv_features ) );
}
$ndtv_links = '';
foreach ( $ndtv_items as $ndtv_key => list( $ndtv_label, $ndtv_url ) ) {
	$ndtv_links .= '<a class="is-' . esc_attr( $ndtv_key ) . '" href="' . esc_url( $ndtv_url ) . '">' . esc_html( $ndtv_label ) . '</a>';
}
?>
<!-- wp:group {"className":"ndtv-vnav","layout":{"type":"default"}} -->
<div class="wp-block-group ndtv-vnav">
<!-- wp:search {"label":"Search videos","showLabel":false,"placeholder":"Search Videos","buttonText":"Search","buttonPosition":"button-inside","buttonUseIcon":true,"query":{"post_type":"ndtv_video"},"className":"ndtv-vsearch"} /-->
<!-- wp:heading {"className":"ndtv-vnav__title"} -->
<h2 class="wp-block-heading ndtv-vnav__title">Videos</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"ndtv-vnav__links"} -->
<p class="ndtv-vnav__links"><?php echo $ndtv_links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"ndtv-vnav__ad"} -->
<p class="ndtv-vnav__ad">Advertisement</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

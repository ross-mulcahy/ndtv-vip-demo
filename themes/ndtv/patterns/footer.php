<?php
/**
 * Title: Footer
 * Slug: ndtv/footer
 * Categories: ndtv, footer
 * Block Types: core/template-part/footer
 * Inserter: no
 *
 * @package NDTV
 */

$ndtv_sections = array( 'india', 'world', 'business', 'sports', 'entertainment', 'lifestyle', 'health', 'tech', 'education', 'auto', 'science', 'offbeat' );
$ndtv_links    = '';
foreach ( $ndtv_sections as $ndtv_slug ) {
	$ndtv_links .= '<a href="' . esc_url( home_url( '/category/' . $ndtv_slug . '/' ) ) . '">' . esc_html( ucfirst( $ndtv_slug ) ) . '</a>';
}
$ndtv_sites = array( 'NDTV India', 'NDTV Profit', 'NDTV Marathi', 'NDTV MPCG', 'NDTV Rajasthan', 'NDTV Good Times', 'NDTV Food', 'NDTV Auto' );
?>
<!-- wp:group {"className":"ndtv-footer","layout":{"type":"default"}} -->
<div class="wp-block-group ndtv-footer">
<!-- wp:group {"className":"ndtv-container","layout":{"type":"default"}} -->
<div class="wp-block-group ndtv-container">
<!-- wp:paragraph {"className":"ndtv-footer__sections"} -->
<p class="ndtv-footer__sections"><?php echo $ndtv_links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":4,"className":"ndtv-footer__head","anchor":"network"} -->
<h4 class="wp-block-heading ndtv-footer__head" id="network">Group Sites</h4>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"ndtv-footer__sites"} -->
<p class="ndtv-footer__sites"><?php echo esc_html( implode( ' · ', $ndtv_sites ) ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"ndtv-footer__legal"} -->
<p class="ndtv-footer__legal">NDTV on WordPress VIP: a prototype. Every headline, story and video on this site is placeholder content written for the demo.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

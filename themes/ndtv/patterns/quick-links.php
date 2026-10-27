<?php
/**
 * Title: Quick links
 * Slug: ndtv/quick-links
 * Categories: ndtv
 * Inserter: no
 *
 * @package NDTV
 */

$ndtv_links = array(
	'India vs New Zealand LIVE' => home_url( '/tag/cricket/' ),
	'Monsoon Watch'             => home_url( '/tag/monsoon/' ),
	'Delhi AQI'                 => home_url( '/tag/air-quality/' ),
	'Gold Rate Today'           => home_url( '/tag/gold/' ),
	'Diwali 2026'               => home_url( '/tag/festivals/' ),
	'AI'                        => home_url( '/tag/ai/' ),
);
$ndtv_html  = '<span class="ndtv-quicklinks__label">Quick Links</span>';
foreach ( $ndtv_links as $ndtv_label => $ndtv_url ) {
	$ndtv_html .= '<a href="' . esc_url( $ndtv_url ) . '">' . esc_html( $ndtv_label ) . '</a>';
}
?>
<!-- wp:group {"className":"ndtv-quicklinks","layout":{"type":"default"}} -->
<div class="wp-block-group ndtv-quicklinks">
<!-- wp:paragraph {"className":"ndtv-container ndtv-quicklinks__row"} -->
<p class="ndtv-container ndtv-quicklinks__row"><?php echo $ndtv_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

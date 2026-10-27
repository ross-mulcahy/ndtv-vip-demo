<?php
/**
 * Title: Masthead
 * Slug: ndtv/masthead
 * Categories: ndtv, header
 * Block Types: core/template-part/header
 * Inserter: no
 *
 * @package NDTV
 */

$ndtv_nav = array(
	array( 'Live TV', home_url( '/video/#live' ), 'is-live' ),
	array( 'Latest', get_year_link( (int) wp_date( 'Y' ) ), '' ),
	array( 'India', home_url( '/category/india/' ), '' ),
	array( 'World', home_url( '/category/world/' ), '' ),
	array( 'Business', home_url( '/category/business/' ), '' ),
	array( 'Sports', home_url( '/category/sports/' ), '' ),
	array( 'Videos', home_url( '/video/' ), '' ),
	array( 'Education', home_url( '/category/education/' ), '' ),
);
?>
<!-- wp:group {"className":"ndtv-masthead","layout":{"type":"default"}} -->
<div class="wp-block-group ndtv-masthead">
<!-- wp:group {"className":"ndtv-container ndtv-masthead__bar","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group ndtv-container ndtv-masthead__bar">
<!-- wp:html -->
<a class="ndtv-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="NDTV home"><svg viewBox="0 0 100 40" width="100" height="40" aria-hidden="true"><rect width="100" height="40" fill="#E21B22"/><text x="50" y="28" text-anchor="middle" fill="#fff" font-family="Inter, Arial, sans-serif" font-weight="800" font-size="22" letter-spacing="1">NDTV</text></svg></a>
<!-- /wp:html -->
<!-- wp:navigation {"className":"ndtv-nav","overlayMenu":"mobile","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<?php foreach ( $ndtv_nav as list( $ndtv_label, $ndtv_url, $ndtv_class ) ) : ?>
<!-- wp:navigation-link <?php echo wp_json_encode( array_filter( array( 'label' => $ndtv_label, 'url' => $ndtv_url, 'kind' => 'custom', 'isTopLevelLink' => true, 'className' => $ndtv_class ) ), JSON_UNESCAPED_SLASHES ); ?> /-->
<?php endforeach; ?>
<!-- /wp:navigation -->
<!-- wp:ndtv/market-strip /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

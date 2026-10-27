<?php
/**
 * Title: Video hub
 * Slug: ndtv/video-hub
 * Categories: ndtv
 * Description: The /video/ landing page: latest carousel, live channels, shorts, top videos, category bands and shows.
 * Inserter: no
 *
 * @package NDTV
 */

$ndtv_grid4    = array( 'type' => 'grid', 'columnCount' => 4 );
$ndtv_cats     = get_terms( array( 'taxonomy' => 'video_category', 'hide_empty' => true ) );
$ndtv_cats     = is_wp_error( $ndtv_cats ) ? array() : array_values( array_filter( $ndtv_cats, static fn( $t ) => 'shorts' !== $t->slug ) );
$ndtv_order    = array( 'news', 'sports', 'business', 'entertainment', 'auto', 'exclusive', 'features' );
usort( $ndtv_cats, static fn( $a, $b ) => array_search( $a->slug, $ndtv_order, true ) <=> array_search( $b->slug, $ndtv_order, true ) );
$ndtv_shows    = get_terms( array( 'taxonomy' => 'video_show', 'hide_empty' => true ) );
$ndtv_shows    = is_wp_error( $ndtv_shows ) ? array() : $ndtv_shows;
$ndtv_shorts   = get_term_by( 'slug', 'shorts', 'video_category' );

$ndtv_tabs = '<span class="ndtv-vtabs__label">Categories</span>';
if ( $ndtv_shorts ) {
	$ndtv_tabs .= '<a href="#shorts">Shorts</a>';
}
foreach ( $ndtv_cats as $ndtv_cat ) {
	$ndtv_tabs .= '<a href="#vc-' . esc_attr( $ndtv_cat->slug ) . '">' . esc_html( $ndtv_cat->name ) . '</a>';
}

/**
 * Section heading for the hub.
 *
 * @param string $title Title.
 * @param string $url   Optional link.
 * @return string
 */
$ndtv_head = static function ( $title, $url = '' ) {
	$inner = $url ? '<a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a>' : esc_html( $title );
	return "<!-- wp:heading {\"className\":\"ndtv-vhead\"} -->\n<h2 class=\"wp-block-heading ndtv-vhead\">" . $inner . "</h2>\n<!-- /wp:heading -->\n";
};

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- block markup built from escaped parts.
?>
<!-- wp:group {"className":"ndtv-vhub","layout":{"type":"default"}} -->
<div class="wp-block-group ndtv-vhub">
<!-- wp:heading {"level":1,"className":"ndtv-vhub__title"} -->
<h1 class="wp-block-heading ndtv-vhub__title">NDTV Videos</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"ndtv-vhub__intro"} -->
<p class="ndtv-vhub__intro">News, explainers, shows and shorts from across the NDTV network.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"anchor":"latest","className":"ndtv-vsection","layout":{"type":"default"}} -->
<div id="latest" class="wp-block-group ndtv-vsection">
<?php echo $ndtv_head( 'Latest Videos', get_post_type_archive_link( 'ndtv_video' ) . '#latest' ); ?>
<?php echo ndtv_video_rail( 'latest', 10, 'ndtv-carousel' ); ?>
</div>
<!-- /wp:group -->

<!-- wp:group {"anchor":"live","className":"ndtv-vsection ndtv-channels","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div id="live" class="wp-block-group ndtv-vsection ndtv-channels">
<!-- wp:heading {"className":"ndtv-channels__title"} -->
<h2 class="wp-block-heading ndtv-channels__title">NDTV<br>Channels</h2>
<!-- /wp:heading -->
<?php echo ndtv_video_rail( 'live', 6, 'ndtv-channels__list' ); ?>
</div>
<!-- /wp:group -->

<!-- wp:group {"anchor":"shorts","className":"ndtv-vsection","layout":{"type":"default"}} -->
<div id="shorts" class="wp-block-group ndtv-vsection">
<?php echo $ndtv_head( 'Shorts', $ndtv_shorts ? get_term_link( $ndtv_shorts ) : '' ); ?>
<?php echo ndtv_video_rail( 'shorts', 12, 'ndtv-carousel' ); ?>
</div>
<!-- /wp:group -->

<!-- wp:group {"anchor":"top","className":"ndtv-vsection","layout":{"type":"default"}} -->
<div id="top" class="wp-block-group ndtv-vsection">
<?php echo $ndtv_head( 'Top Videos' ); ?>
<!-- wp:paragraph {"className":"ndtv-vnote"} -->
<p class="ndtv-vnote">Most viewed. On VIP this rail reads Parse.ly; the demo sorts by a seeded view count.</p>
<!-- /wp:paragraph -->
<?php echo ndtv_video_rail( 'top', 4, 'ndtv-vgrid', $ndtv_grid4 ); ?>
</div>
<!-- /wp:group -->

<!-- wp:group {"anchor":"categories","className":"ndtv-vsection","layout":{"type":"default"}} -->
<div id="categories" class="wp-block-group ndtv-vsection">
<!-- wp:paragraph {"className":"ndtv-vtabs"} -->
<p class="ndtv-vtabs"><?php echo $ndtv_tabs; ?></p>
<!-- /wp:paragraph -->
<?php foreach ( $ndtv_cats as $ndtv_cat ) : ?>
<!-- wp:group {"anchor":"vc-<?php echo esc_attr( $ndtv_cat->slug ); ?>","className":"ndtv-vband","layout":{"type":"default"}} -->
<div id="vc-<?php echo esc_attr( $ndtv_cat->slug ); ?>" class="wp-block-group ndtv-vband">
<!-- wp:group {"className":"ndtv-vband__head","layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"}} -->
<div class="wp-block-group ndtv-vband__head">
<!-- wp:heading {"className":"ndtv-vband__title"} -->
<h2 class="wp-block-heading ndtv-vband__title"><?php echo esc_html( $ndtv_cat->name ); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"ndtv-vband__desc"} -->
<p class="ndtv-vband__desc"><?php echo esc_html( $ndtv_cat->description ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"className":"ndtv-vband__more"} -->
<div class="wp-block-buttons ndtv-vband__more">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( get_term_link( $ndtv_cat ) ); ?>">More <?php echo esc_html( $ndtv_cat->name ); ?> Videos</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
<?php
	echo ndtv_query(
		array(
			'perPage'  => 4,
			'postType' => 'ndtv_video',
			'taxQuery' => array( 'video_category' => array( (int) $ndtv_cat->term_id ) ),
		),
		ndtv_tpl_video( '16/9', 3 ),
		'ndtv-vgrid',
		$ndtv_grid4
	);
?>
</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->

<?php if ( $ndtv_shows ) : ?>
<!-- wp:group {"anchor":"shows","className":"ndtv-vsection","layout":{"type":"default"}} -->
<div id="shows" class="wp-block-group ndtv-vsection">
<?php echo $ndtv_head( 'Shows' ); ?>
<!-- wp:group {"className":"ndtv-shows","layout":{"type":"grid","minimumColumnWidth":"14rem"}} -->
<div class="wp-block-group ndtv-shows">
<?php foreach ( $ndtv_shows as $ndtv_show ) : ?>
<!-- wp:paragraph {"className":"ndtv-show"} -->
<p class="ndtv-show"><a href="<?php echo esc_url( get_term_link( $ndtv_show ) ); ?>"><strong><?php echo esc_html( $ndtv_show->name ); ?></strong></a><span><?php echo esc_html( $ndtv_show->description ); ?></span><em><?php echo esc_html( sprintf( _n( '%d episode', '%d episodes', $ndtv_show->count, 'ndtv' ), $ndtv_show->count ) ); ?></em></p>
<!-- /wp:paragraph -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<?php endif; ?>
</div>
<!-- /wp:group -->

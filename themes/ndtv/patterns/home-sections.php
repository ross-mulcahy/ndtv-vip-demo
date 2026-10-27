<?php
/**
 * Title: Home section blocks
 * Slug: ndtv/home-sections
 * Categories: ndtv
 * Description: Section blocks two per row, then a four-up row.
 *
 * @package NDTV
 */

$ndtv_sports_tabs = array(
	'Featured' => home_url( '/category/sports/' ),
	'Cricket'  => home_url( '/tag/cricket/' ),
	'Football' => home_url( '/tag/football/' ),
	'Tennis'   => home_url( '/tag/tennis/' ),
);

$ndtv_pairs = array(
	array( ndtv_section( 'entertainment' ), ndtv_section( 'lifestyle' ) ),
	array( ndtv_section( 'sports', '', $ndtv_sports_tabs ), ndtv_section( 'business', 'Profit' ) ),
	array( ndtv_section( 'health' ), ndtv_section( 'education' ) ),
	array( ndtv_section( 'auto' ), ndtv_section( 'tech' ) ),
);

foreach ( $ndtv_pairs as $ndtv_pair ) {
	echo ndtv_columns( $ndtv_pair, 'ndtv-pair' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

echo ndtv_columns( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	array(
		ndtv_section( 'india', '', array(), true ),
		ndtv_section( 'world', '', array(), true ),
		ndtv_section( 'science', '', array(), true ),
		ndtv_section( 'offbeat', '', array(), true ),
	),
	'ndtv-quad'
);

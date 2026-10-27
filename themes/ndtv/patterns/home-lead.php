<?php
/**
 * Title: Home lead zone
 * Slug: ndtv/home-lead
 * Categories: ndtv
 * Description: Sticky lead story plus two columns of three stories.
 *
 * @package NDTV
 */

$ndtv_stack = ndtv_tpl_card( 3 );
echo ndtv_columns( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	array(
		ndtv_query( array( 'perPage' => 1, 'sticky' => 'only' ), ndtv_tpl_card( 2, '16/9', 'ndtv-lead__title' ), 'ndtv-lead__story' ),
		ndtv_query( array( 'perPage' => 3, 'sticky' => 'exclude' ), $ndtv_stack, 'ndtv-stack' ),
		ndtv_query( array( 'perPage' => 3, 'offset' => 3, 'sticky' => 'exclude' ), $ndtv_stack, 'ndtv-stack' ),
	),
	'ndtv-lead'
);

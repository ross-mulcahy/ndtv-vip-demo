<?php
/**
 * Title: Home four-up row
 * Slug: ndtv/home-four-up
 * Categories: ndtv
 *
 * @package NDTV
 */

echo ndtv_query( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	array(
		'perPage' => 8,
		'offset'  => 6,
		'sticky'  => 'exclude',
	),
	ndtv_tpl_card( 3 ),
	'ndtv-four-up',
	array(
		'type'        => 'grid',
		'columnCount' => 4,
	)
);

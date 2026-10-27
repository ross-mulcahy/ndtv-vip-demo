<?php
/**
 * Markup helpers for the homepage patterns.
 *
 * Query Loop blocks need term and post IDs, which only exist once the seeder
 * has run, so the patterns are PHP and build their block markup at render
 * time with these helpers.
 *
 * @package NDTV
 */

defined( 'ABSPATH' ) || exit;

/**
 * Term ID by slug, or 0.
 *
 * @param string $slug     Term slug.
 * @param string $taxonomy Taxonomy.
 * @return int
 */
function ndtv_term_id( $slug, $taxonomy = 'category' ) {
	$term = get_term_by( 'slug', $slug, $taxonomy );
	return $term ? (int) $term->term_id : 0;
}

/**
 * Seeded video ID by player ID, or 0 when the plugin is inactive.
 *
 * @param string $player_id Player ID.
 * @return int
 */
function ndtv_video_id( $player_id ) {
	return function_exists( 'ndtv_core_video_by_player_id' ) ? ndtv_core_video_by_player_id( $player_id ) : 0;
}

/**
 * The newest on-demand video, for featured players.
 *
 * @return int Post ID or 0.
 */
function ndtv_featured_video_id() {
	return function_exists( 'ndtv_core_featured_video_id' ) ? ndtv_core_featured_video_id() : 0;
}

/**
 * Serialize a block comment delimiter's attributes.
 *
 * @param array $attrs Attributes.
 * @return string Leading space plus JSON, or empty.
 */
function ndtv_block_attrs( $attrs ) {
	return $attrs ? ' ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) : '';
}

/**
 * A complete Query Loop block.
 *
 * @param array  $query     Query overrides (perPage, offset, postType, sticky, taxQuery, exclude).
 * @param string $template  Inner blocks for each post.
 * @param string $class     Extra class on the query wrapper.
 * @param array  $layout    post-template layout, e.g. a grid.
 * @return string
 */
function ndtv_query( $query, $template, $class = '', $layout = array() ) {
	static $query_id = 100;
	++$query_id;

	$query = array_merge(
		array(
			'perPage'  => 3,
			'pages'    => 0,
			'offset'   => 0,
			'postType' => 'post',
			'order'    => 'desc',
			'orderBy'  => 'date',
			'author'   => '',
			'search'   => '',
			'exclude'  => array(),
			'sticky'   => '',
			'inherit'  => false,
		),
		$query
	);

	$attrs = array(
		'queryId' => $query_id,
		'query'   => $query,
	);
	if ( $class ) {
		$attrs['className'] = $class;
	}

	$tpl_attrs = $layout ? array( 'layout' => $layout ) : array();

	return '<!-- wp:query' . ndtv_block_attrs( $attrs ) . " -->\n"
		. '<div class="wp-block-query' . ( $class ? ' ' . esc_attr( $class ) : '' ) . "\">\n"
		. '<!-- wp:post-template' . ndtv_block_attrs( $tpl_attrs ) . " -->\n"
		. $template . "\n"
		. "<!-- /wp:post-template -->\n"
		. "</div>\n"
		. "<!-- /wp:query -->\n";
}

/**
 * Story card: image above a title.
 *
 * @param int    $level  Heading level.
 * @param string $ratio  Image aspect ratio.
 * @param string $title_class Extra title class.
 * @return string
 */
function ndtv_tpl_card( $level = 3, $ratio = '16/9', $title_class = '' ) {
	$title = array(
		'level'  => $level,
		'isLink' => true,
	);
	if ( $title_class ) {
		$title['className'] = $title_class;
	}
	return '<!-- wp:post-featured-image' . ndtv_block_attrs(
		array(
			'isLink'      => true,
			'aspectRatio' => $ratio,
			'sizeSlug'    => 'large',
		)
	) . " /-->\n<!-- wp:post-title" . ndtv_block_attrs( $title ) . ' /-->';
}

/**
 * Title-only list item.
 *
 * @param int $level Heading level.
 * @return string
 */
function ndtv_tpl_title( $level = 3 ) {
	return '<!-- wp:post-title' . ndtv_block_attrs(
		array(
			'level'  => $level,
			'isLink' => true,
		)
	) . ' /-->';
}

/**
 * Row with a small thumbnail on the left and the title on the right.
 *
 * @param int  $level Heading level.
 * @param bool $video Overlay a duration badge (video loops).
 * @return string
 */
function ndtv_tpl_row( $level = 3, $video = false ) {
	$badge = $video ? "\n<!-- wp:ndtv/badge {\"type\":\"duration\"} /-->" : '';
	return "<!-- wp:group {\"className\":\"ndtv-row\",\"layout\":{\"type\":\"flex\",\"flexWrap\":\"nowrap\",\"verticalAlignment\":\"top\"}} -->\n"
		. "<div class=\"wp-block-group ndtv-row\">\n"
		. "<!-- wp:group {\"className\":\"ndtv-thumb\",\"layout\":{\"type\":\"default\"}} -->\n"
		. "<div class=\"wp-block-group ndtv-thumb\">\n"
		. '<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","sizeSlug":"medium"} /-->'
		. $badge . "\n</div>\n<!-- /wp:group -->\n"
		. ndtv_tpl_title( $level ) . "\n"
		. "</div>\n<!-- /wp:group -->";
}

/**
 * Video tile: thumbnail with duration or LIVE badge, then the title.
 *
 * @param string $ratio Image aspect ratio.
 * @param int    $level Heading level.
 * @param bool   $date  Show the publish date under the title.
 * @return string
 */
function ndtv_tpl_video( $ratio = '16/9', $level = 3, $date = false ) {
	return "<!-- wp:group {\"className\":\"ndtv-thumb\",\"layout\":{\"type\":\"default\"}} -->\n"
		. "<div class=\"wp-block-group ndtv-thumb\">\n"
		. '<!-- wp:post-featured-image' . ndtv_block_attrs(
			array(
				'isLink'      => true,
				'aspectRatio' => $ratio,
				'sizeSlug'    => 'medium_large',
			)
		) . " /-->\n"
		. "<!-- wp:ndtv/badge {\"type\":\"duration\"} /-->\n"
		. "</div>\n<!-- /wp:group -->\n"
		. ndtv_tpl_title( $level )
		. ( $date ? "\n<!-- wp:post-date {\"format\":\"F j, Y\"} /-->" : '' );
}

/**
 * A video rail: a Query Loop over ndtv_video with a named rail (see the
 * plugin's inc/rails.php).
 *
 * @param string $rail    latest, shorts, live, top or related.
 * @param int    $count   Items.
 * @param string $class   Wrapper class, e.g. ndtv-carousel or ndtv-grid.
 * @param array  $layout  post-template layout.
 * @param array  $extra   Extra query args (exclude, taxQuery).
 * @return string
 */
function ndtv_video_rail( $rail, $count, $class, $layout = array(), $extra = array() ) {
	$ratio = 'shorts' === $rail ? '9/16' : ( 'live' === $rail ? '1' : '16/9' );
	$tpl   = 'live' === $rail ? ndtv_tpl_channel() : ndtv_tpl_video( $ratio, 3, 'latest' === $rail );

	return ndtv_query(
		array_merge(
			array(
				'perPage'  => $count,
				'postType' => 'ndtv_video',
				'ndtvRail' => $rail,
			),
			$extra
		),
		$tpl,
		trim( 'ndtv-rail-' . $rail . ' ' . $class ),
		$layout
	);
}

/**
 * Live channel tile: a round badge with the channel name.
 *
 * @return string
 */
function ndtv_tpl_channel() {
	return "<!-- wp:group {\"className\":\"ndtv-channel\",\"layout\":{\"type\":\"default\"}} -->\n"
		. "<div class=\"wp-block-group ndtv-channel\">\n"
		. "<!-- wp:post-featured-image {\"isLink\":true,\"aspectRatio\":\"1\",\"sizeSlug\":\"medium\"} /-->\n"
		. "<!-- wp:ndtv/badge {\"type\":\"live\"} /-->\n"
		. "<!-- wp:post-title {\"level\":3,\"isLink\":true} /-->\n"
		. "</div>\n<!-- /wp:group -->";
}

/**
 * Section heading with an optional row of tabs.
 *
 * @param string $title Section title.
 * @param string $url   Section URL.
 * @param array  $tabs  label => URL. The first tab is marked active.
 * @return string
 */
function ndtv_section_head( $title, $url, $tabs = array() ) {
	$html  = "<!-- wp:group {\"className\":\"ndtv-section__head\",\"layout\":{\"type\":\"flex\",\"flexWrap\":\"wrap\",\"justifyContent\":\"space-between\"}} -->\n";
	$html .= "<div class=\"wp-block-group ndtv-section__head\">\n";
	$html .= "<!-- wp:heading {\"className\":\"ndtv-section__title\"} -->\n";
	$html .= '<h2 class="wp-block-heading ndtv-section__title"><a href="' . esc_url( $url ) . '">' . esc_html( $title ) . "</a></h2>\n<!-- /wp:heading -->\n";
	if ( $tabs ) {
		$links = array();
		$first = true;
		foreach ( $tabs as $label => $href ) {
			$links[] = '<a' . ( $first ? ' class="is-active"' : '' ) . ' href="' . esc_url( $href ) . '">' . esc_html( $label ) . '</a>';
			$first   = false;
		}
		$html .= "<!-- wp:paragraph {\"className\":\"ndtv-tabs\"} -->\n<p class=\"ndtv-tabs\">" . implode( '', $links ) . "</p>\n<!-- /wp:paragraph -->\n";
	}
	$html .= "</div>\n<!-- /wp:group -->\n";
	return $html;
}

/**
 * A homepage section block: one lead story with an image, plus a list.
 *
 * @param string $slug    Category slug.
 * @param string $title   Display title (defaults to the category name).
 * @param array  $tabs    Optional tabs, label => URL.
 * @param bool   $compact Stacked variant for the four-up row.
 * @return string
 */
function ndtv_section( $slug, $title = '', $tabs = array(), $compact = false ) {
	$term_id = ndtv_term_id( $slug );
	if ( ! $term_id ) {
		return '';
	}
	$title = $title ? $title : get_term( $term_id )->name;
	$tax   = array( 'category' => array( $term_id ) );

	$html  = "<!-- wp:group {\"className\":\"ndtv-section" . ( $compact ? ' is-compact' : '' ) . "\",\"layout\":{\"type\":\"default\"}} -->\n";
	$html .= '<div class="wp-block-group ndtv-section' . ( $compact ? ' is-compact' : '' ) . "\">\n";
	$html .= ndtv_section_head( $title, get_term_link( $term_id ), $tabs );

	if ( $compact ) {
		$html .= ndtv_query( array( 'perPage' => 1, 'taxQuery' => $tax ), ndtv_tpl_card( 3 ), 'ndtv-section__lead' );
		$html .= ndtv_query( array( 'perPage' => 3, 'offset' => 1, 'taxQuery' => $tax ), ndtv_tpl_title( 3 ), 'ndtv-list' );
	} else {
		$html .= "<!-- wp:columns {\"className\":\"ndtv-section__body\"} -->\n<div class=\"wp-block-columns ndtv-section__body\">\n";
		$html .= "<!-- wp:column -->\n<div class=\"wp-block-column\">\n";
		$html .= ndtv_query( array( 'perPage' => 1, 'taxQuery' => $tax ), ndtv_tpl_card( 3 ), 'ndtv-section__lead' );
		$html .= "</div>\n<!-- /wp:column -->\n";
		$html .= "<!-- wp:column -->\n<div class=\"wp-block-column\">\n";
		$html .= ndtv_query( array( 'perPage' => 3, 'offset' => 1, 'taxQuery' => $tax ), ndtv_tpl_row( 3 ), 'ndtv-rows' );
		$html .= "</div>\n<!-- /wp:column -->\n";
		$html .= "</div>\n<!-- /wp:columns -->\n";
	}

	$html .= "</div>\n<!-- /wp:group -->\n";
	return $html;
}

/**
 * Wrap pieces of markup in core/columns, one column each.
 *
 * @param string[] $cells Column contents.
 * @param string   $class Extra class on the columns wrapper.
 * @return string
 */
function ndtv_columns( $cells, $class = '' ) {
	$attrs = $class ? ndtv_block_attrs( array( 'className' => $class ) ) : '';
	$html  = '<!-- wp:columns' . $attrs . " -->\n<div class=\"wp-block-columns" . ( $class ? ' ' . esc_attr( $class ) : '' ) . "\">\n";
	foreach ( $cells as $cell ) {
		$html .= "<!-- wp:column -->\n<div class=\"wp-block-column\">\n" . $cell . "</div>\n<!-- /wp:column -->\n";
	}
	return $html . "</div>\n<!-- /wp:columns -->\n";
}

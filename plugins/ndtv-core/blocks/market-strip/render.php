<?php
/**
 * NDTV Market Strip render.
 *
 * @package NDTV_Core
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filter the masthead market and weather values.
 *
 * Static demo values on Playground; in production this would read a cached
 * feed (rates API, weather/AQI API) refreshed by cron.
 *
 * @param array $data Market and weather data.
 */
$data = apply_filters(
	'ndtv_core_market_data',
	array(
		'gold'   => '₹14,196/g',
		'silver' => '₹172/g',
		'temp'   => '29° C',
		'aqi'    => 165,
	)
);

$ist  = new DateTimeZone( 'Asia/Kolkata' );
$now  = wp_date( 'F j, Y h:i a', null, $ist );
$aqi  = (int) $data['aqi'];
$band = $aqi > 200 ? 'poor' : ( $aqi > 100 ? 'moderate' : 'good' );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'ndtv-market-strip' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="ndtv-market-strip__rates" aria-label="<?php esc_attr_e( 'Bullion rates', 'ndtv-core' ); ?>">
		<span class="ndtv-market-strip__rate">
			<span class="ndtv-market-strip__label"><?php esc_html_e( 'Gold', 'ndtv-core' ); ?></span>
			<span class="ndtv-market-strip__value"><?php echo esc_html( $data['gold'] ); ?></span>
		</span>
		<span class="ndtv-market-strip__rate">
			<span class="ndtv-market-strip__label"><?php esc_html_e( 'Silver', 'ndtv-core' ); ?></span>
			<span class="ndtv-market-strip__value"><?php echo esc_html( $data['silver'] ); ?></span>
		</span>
	</div>
	<div class="ndtv-market-strip__when">
		<time datetime="<?php echo esc_attr( wp_date( 'c', null, $ist ) ); ?>"><?php echo esc_html( $now ); ?> IST</time>
		<span class="ndtv-market-strip__weather">
			<?php echo esc_html( $attributes['city'] ); ?>
			<svg width="14" height="10" viewBox="0 0 24 16" aria-hidden="true"><path fill="currentColor" d="M19 6.1A6 6 0 0 0 7.3 4.6 4.8 4.8 0 0 0 5 16h14a5 5 0 0 0 0-9.9Z"/></svg>
			<?php echo esc_html( $data['temp'] ); ?>
			<span class="ndtv-market-strip__aqi is-<?php echo esc_attr( $band ); ?>">| AQI <b><?php echo esc_html( $aqi ); ?></b></span>
		</span>
	</div>
	<a class="ndtv-market-strip__icon" href="#network" aria-label="<?php esc_attr_e( 'NDTV network sites', 'ndtv-core' ); ?>">
		<svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true"><g fill="currentColor"><circle cx="3" cy="3" r="2"/><circle cx="9" cy="3" r="2"/><circle cx="15" cy="3" r="2"/><circle cx="3" cy="9" r="2"/><circle cx="9" cy="9" r="2"/><circle cx="15" cy="9" r="2"/><circle cx="3" cy="15" r="2"/><circle cx="9" cy="15" r="2"/><circle cx="15" cy="15" r="2"/></g></svg>
	</a>
	<a class="ndtv-market-strip__icon" href="<?php echo esc_url( wp_login_url() ); ?>" aria-label="<?php esc_attr_e( 'Log in', 'ndtv-core' ); ?>">
		<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.4 0-9 2.2-9 5.5V22h18v-2.5c0-3.3-4.6-5.5-9-5.5Z"/></svg>
	</a>
</div>

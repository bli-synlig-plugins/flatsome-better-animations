<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FBA_Plugin {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );
		add_action( 'wp_head', array( $this, 'mark_js' ), 0 );
		add_action( 'wp_head', array( $this, 'noscript_fallback' ), 1 );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter(
			'plugin_action_links_' . plugin_basename( FBA_DIR . 'flatsome-better-animations.php' ),
			array( $this, 'action_links' )
		);

		FBA_Admin::init();
		FBA_Builder::init();
		FBA_Output::init();
	}

	public static function get_options() {
		$saved    = get_option( 'fba_settings', array() );
		$saved    = is_array( $saved ) ? $saved : array();
		$defaults = FBA_Library::defaults();
		$opts     = wp_parse_args( $saved, $defaults );

		if ( ! array_key_exists( 'flatsome_distance', $saved ) && isset( $saved['move_distance'] ) ) {
			$opts['flatsome_distance'] = (int) $saved['move_distance'];
		}

		return $opts;
	}

	public function enqueue_assets() {
		$opts = self::get_options();

		wp_enqueue_style(
			'flatsome-better-animations',
			FBA_URL . 'assets/css/animations.css',
			array(),
			FBA_VERSION
		);

		wp_add_inline_style(
			'flatsome-better-animations',
			$this->build_dynamic_css( $opts )
		);

		wp_enqueue_script(
			'flatsome-better-animations',
			FBA_URL . 'assets/js/animations.js',
			array(),
			FBA_VERSION,
			true
		);

		if ( ! empty( $opts['defer_js'] ) ) {
			wp_script_add_data( 'flatsome-better-animations', 'strategy', 'defer' );
		}

		wp_localize_script(
			'flatsome-better-animations',
			'fbaSettings',
			array(
				'overrideFlatsome' => (bool) $opts['override_flatsome'],
				'waypointOffset'   => (int) $opts['waypoint_offset'],
				'staggerStep'      => (int) $opts['stagger_step'],
				'revealSystem'     => (bool) $opts['reveal_system'],
				'revealThreshold'  => (int) $opts['reveal_threshold'] / 100,
				'playOnce'         => (bool) $opts['play_once'],
				'enableMobile'     => (bool) $opts['enable_mobile'],
				'reduceMotion'     => (bool) $opts['reduce_motion'],
				'defaultAnim'      => $opts['default_anim'],
				'textStyle'        => $opts['text_style'],
				'textUnit'         => $opts['text_unit'],
				'textStagger'      => (int) $opts['text_stagger'],
				'textDuration'     => (int) $opts['text_duration'],
				'mobileMax'        => 849,
			)
		);
	}

	public function body_class( $classes ) {
		$opts = self::get_options();
		if ( ! empty( $opts['reduce_motion'] ) ) {
			$classes[] = 'fba-respect-reduced';
		}
		return $classes;
	}

	public function mark_js() {
		$opts = self::get_options();
		if ( empty( $opts['reveal_system'] ) ) {
			return;
		}
		echo "<script>document.documentElement.classList.add('fba-js');window.setTimeout(function(){var n=document.querySelectorAll('.reveal:not(.is-visible),.reveal-text:not(.is-visible)');for(var i=0;i<n.length;i++)n[i].classList.add('is-visible');},2500);</script>\n";
	}

	public function noscript_fallback() {
		$opts = self::get_options();
		if ( empty( $opts['reveal_system'] ) && empty( $opts['override_flatsome'] ) ) {
			return;
		}
		?>
		<noscript>
		<style id="fba-noscript-fallback">
			.reveal,
			.reveal-text .fba-u > i {
				opacity: 1 !important;
				transform: none !important;
				filter: none !important;
				clip-path: none !important;
				animation: none !important;
			}
			[data-animate]:not([data-animated="true"]) {
				opacity: 1 !important;
				transform: none !important;
				filter: none !important;
			}
		</style>
		</noscript>
		<?php
	}

	private function build_dynamic_css( $opts ) {
		$t     = (int) $opts['transform_duration'];
		$o     = (int) $opts['opacity_duration'];
		$fd    = (int) $opts['flatsome_distance'];
		$rd    = (int) $opts['move_distance'];
		$rdur  = (int) $opts['reveal_duration'];
		$rodur = (int) $opts['reveal_opacity_duration'];
		$tdur  = (int) $opts['text_duration'];
		$ease  = FBA_Library::easing_value( $opts['ease'] );
		$bt    = (int) ( $t * 1.1 );
		$db    = $fd * 6;
		$anim  = FBA_Library::animation( $opts['default_anim'] );

		$default_from   = $anim['transform'];
		$default_filter = $anim['filter'] ? $anim['filter'] : 'none';
		$default_clip   = $anim['clip'] ? $anim['clip'] : 'none';

		$css = "
:root {
	--fba-move: {$rd}px;
	--fba-ease: {$ease};
	--fba-duration: {$rdur}ms;
	--fba-opacity-duration: {$rodur}ms;
	--fba-text-duration: {$tdur}ms;
	--fba-default-from: {$default_from};
	--fba-default-filter: {$default_filter};
	--fba-default-clip: {$default_clip};
}
";

		$css .= "
@media (prefers-reduced-motion: no-preference) {
	[data-animate] {
		transition:
			filter    {$t}ms {$ease},
			transform {$t}ms {$ease},
			opacity   {$o}ms ease-out !important;
	}
	[data-animate=bounceInDown],
	[data-animate=bounceInLeft],
	[data-animate=bounceInRight],
	[data-animate=bounceInUp] {
		transition:
			transform {$bt}ms cubic-bezier(0.34, 1.56, 0.64, 1),
			opacity   {$o}ms ease-out !important;
	}

	[data-animate=fadeInLeft]:not([data-animated=\"true\"])    { transform: translate3d(-{$fd}px,  0,     0) !important; }
	[data-animate=fadeInRight]:not([data-animated=\"true\"])   { transform: translate3d( {$fd}px,  0,     0) !important; }
	[data-animate=fadeInUp]:not([data-animated=\"true\"]),
	[data-animate=bounceInUp]:not([data-animated=\"true\"])    { transform: translate3d( 0,  {$fd}px, 0) !important; }
	[data-animate=fadeInDown]:not([data-animated=\"true\"]),
	[data-animate=bounceInDown]:not([data-animated=\"true\"])  { transform: translate3d( 0, -{$fd}px, 0) !important; }
	[data-animate=bounceInLeft]:not([data-animated=\"true\"])  { transform: translate3d(-{$db}px, 0, 0) !important; }
	[data-animate=bounceInRight]:not([data-animated=\"true\"]) { transform: translate3d( {$db}px, 0, 0) !important; }
}
";

		return $css;
	}

	public static function sanitize_settings( $input ) {
		$defaults = FBA_Library::defaults();
		$input    = is_array( $input ) ? $input : array();
		$clean    = array();

		$clean['override_flatsome'] = array_key_exists( 'override_flatsome', $input )
			? ! empty( $input['override_flatsome'] )
			: true;
		$clean['reveal_system'] = array_key_exists( 'reveal_system', $input )
			? ! empty( $input['reveal_system'] )
			: true;
		$clean['play_once'] = array_key_exists( 'play_once', $input )
			? ! empty( $input['play_once'] )
			: true;
		$clean['enable_mobile'] = array_key_exists( 'enable_mobile', $input )
			? ! empty( $input['enable_mobile'] )
			: true;
		$clean['reduce_motion'] = array_key_exists( 'reduce_motion', $input )
			? ! empty( $input['reduce_motion'] )
			: true;
		$clean['defer_js'] = array_key_exists( 'defer_js', $input )
			? ! empty( $input['defer_js'] )
			: true;

		$clean['transform_duration']      = max( 100, min( 2000, (int) ( $input['transform_duration'] ?? $defaults['transform_duration'] ) ) );
		$clean['opacity_duration']        = max( 100, min( 2000, (int) ( $input['opacity_duration'] ?? $defaults['opacity_duration'] ) ) );
		$clean['waypoint_offset']         = max( 50, min( 101, (int) ( $input['waypoint_offset'] ?? $defaults['waypoint_offset'] ) ) );
		$clean['stagger_step']            = max( 0, min( 500, (int) ( $input['stagger_step'] ?? $defaults['stagger_step'] ) ) );
		$clean['reveal_threshold']        = max( 0, min( 100, (int) ( $input['reveal_threshold'] ?? $defaults['reveal_threshold'] ) ) );
		$clean['move_distance']           = max( 0, min( 200, (int) ( $input['move_distance'] ?? $defaults['move_distance'] ) ) );
		$clean['flatsome_distance']       = max( 0, min( 200, (int) ( $input['flatsome_distance'] ?? $defaults['flatsome_distance'] ) ) );
		$clean['reveal_duration']         = max( 200, min( 1200, (int) ( $input['reveal_duration'] ?? $defaults['reveal_duration'] ) ) );
		$clean['reveal_opacity_duration'] = max( 100, min( 900, (int) ( $input['reveal_opacity_duration'] ?? $defaults['reveal_opacity_duration'] ) ) );
		$clean['text_stagger']            = max( 10, min( 200, (int) ( $input['text_stagger'] ?? $defaults['text_stagger'] ) ) );
		$clean['text_duration']           = max( 300, min( 1400, (int) ( $input['text_duration'] ?? $defaults['text_duration'] ) ) );

		$anim = isset( $input['default_anim'] ) ? sanitize_key( $input['default_anim'] ) : $defaults['default_anim'];
		$clean['default_anim'] = FBA_Library::is_animation( $anim ) ? $anim : $defaults['default_anim'];

		$easings = FBA_Library::easings();
		$ease    = isset( $input['ease'] ) ? sanitize_key( $input['ease'] ) : $defaults['ease'];
		$clean['ease'] = isset( $easings[ $ease ] ) ? $ease : $defaults['ease'];

		$styles = FBA_Library::text_styles();
		$style  = isset( $input['text_style'] ) ? sanitize_key( $input['text_style'] ) : $defaults['text_style'];
		$clean['text_style'] = isset( $styles[ $style ] ) ? $style : $defaults['text_style'];

		$units = FBA_Library::text_units();
		$unit  = isset( $input['text_unit'] ) ? sanitize_key( $input['text_unit'] ) : $defaults['text_unit'];
		$clean['text_unit'] = isset( $units[ $unit ] ) ? $unit : $defaults['text_unit'];

		return $clean;
	}

	public function action_links( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=flatsome-better-animations' ) ) . '">' . esc_html__( 'Innstillinger', 'flatsome-better-animations' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
}

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FBA_Builder {

	public static function init() {
		add_action( 'ux_builder_setup', array( __CLASS__, 'register_option_type' ), 5 );
		add_action( 'ux_builder_enqueue_scripts', array( __CLASS__, 'enqueue' ), 20 );
		add_filter( 'ux_builder_shortcode_data', array( __CLASS__, 'extend_element' ), 20, 2 );
	}

	public static function register_option_type() {
		if ( ! function_exists( 'add_ux_builder_option_type' ) ) {
			return;
		}

		add_ux_builder_option_type(
			'fba-anim',
			array(
				'defaults' => array(
					'full_width' => true,
					'default'    => '',
				),
			)
		);
	}

	public static function enqueue( $context ) {
		if ( $context !== 'editor' ) {
			return;
		}

		wp_enqueue_script(
			'fba-ux-option',
			FBA_URL . 'assets/js/ux-option-fba.js',
			array( 'ux-builder-core' ),
			FBA_VERSION,
			true
		);

		wp_localize_script(
			'fba-ux-option',
			'fbaMotion',
			FBA_Library::frontend_library()
		);

		wp_enqueue_style(
			'fba-ux-option',
			FBA_URL . 'assets/css/ux-option-fba.css',
			array( 'ux-builder-core' ),
			FBA_VERSION
		);
	}

	public static function extend_element( $data, $tag = '' ) {
		if ( ! is_array( $data ) || empty( $data['options'] ) || ! is_array( $data['options'] ) ) {
			return $data;
		}

		if ( ! in_array( $tag, FBA_Library::builder_tags(), true ) ) {
			return $data;
		}

		$group = array(
			'fba_motion' => array(
				'type'    => 'group',
				'heading' => __( 'Motion', 'flatsome-better-animations' ),
				'options' => self::group_options( $tag ),
			),
		);

		$data['options'] = self::insert_before( $data['options'], 'advanced_options', $group );

		return $data;
	}

	private static function group_options( $tag ) {
		$is_child_parent = in_array( $tag, FBA_Library::child_anim_tags(), true );

		$options = array(
			'fba_anim'  => array(
				'type'        => 'fba-anim',
				'heading'     => __( 'Animasjon', 'flatsome-better-animations' ),
				'description' => $is_child_parent
					? __( 'Valgfritt. Kolonner uten egen animasjon arver denne. Raden/seksjonen animeres ikke selv.', 'flatsome-better-animations' )
					: '',
				'full_width'  => true,
				'default'     => '',
			),
			'fba_delay' => array(
				'type'        => 'slider',
				'heading'     => __( 'Delay', 'flatsome-better-animations' ),
				'default'     => '0',
				'min'         => '0',
				'max'         => '1000',
				'step'        => '50',
				'unit'        => 'ms',
				'conditions'  => 'fba_anim',
			),
		);

		if ( in_array( $tag, FBA_Library::stagger_tags(), true ) ) {
			$options['fba_stagger'] = array(
				'type'        => 'radio-buttons',
				'heading'     => __( 'Stagger barn', 'flatsome-better-animations' ),
				'description' => $is_child_parent
					? __( 'Animerte barn (kolonne, tekst, …) kommer inn etter hverandre. Kan brukes uten animasjon her.', 'flatsome-better-animations' )
					: __( 'Forsink under-elementer som har animasjon.', 'flatsome-better-animations' ),
				'full_width'  => true,
				'default'     => '',
				'options'     => array(
					''     => array( 'title' => __( 'Av', 'flatsome-better-animations' ) ),
					'true' => array( 'title' => __( 'På', 'flatsome-better-animations' ) ),
				),
			);
		}

		if ( in_array( $tag, FBA_Library::text_tags(), true ) ) {
			$text_opts = array(
				'' => array( 'title' => __( 'Av', 'flatsome-better-animations' ) ),
			);
			foreach ( FBA_Library::text_styles() as $id => $style ) {
				$text_opts[ $id ] = array( 'title' => $style['label'] );
			}

			$unit_opts = array();
			foreach ( FBA_Library::text_units() as $id => $label ) {
				$unit_opts[ $id ] = array( 'title' => $label );
			}

			$options['fba_text'] = array(
				'type'       => 'radio-buttons',
				'heading'    => __( 'Tekst', 'flatsome-better-animations' ),
				'full_width' => true,
				'default'    => '',
				'options'    => $text_opts,
			);
			$options['fba_text_unit'] = array(
				'type'       => 'radio-buttons',
				'heading'    => __( 'Del opp', 'flatsome-better-animations' ),
				'full_width' => true,
				'default'    => 'ord',
				'conditions' => 'fba_text',
				'options'    => $unit_opts,
			);
		}

		return $options;
	}

	private static function insert_before( $options, $before, $insert ) {
		if ( ! array_key_exists( $before, $options ) ) {
			return $options + $insert;
		}

		$next = array();
		foreach ( $options as $key => $option ) {
			if ( $key === $before ) {
				foreach ( $insert as $insert_key => $insert_option ) {
					$next[ $insert_key ] = $insert_option;
				}
			}
			$next[ $key ] = $option;
		}

		return $next;
	}
}

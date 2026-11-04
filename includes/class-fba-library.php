<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FBA_Library {

	public static function defaults() {
		return array(
			'override_flatsome'         => true,
			'transform_duration'        => 550,
			'opacity_duration'          => 400,
			'waypoint_offset'           => 88,
			'stagger_step'              => 80,
			'reveal_system'             => true,
			'reveal_threshold'          => 12,
			'move_distance'             => 24,
			'default_anim'              => 'fade-up',
			'reveal_duration'           => 650,
			'reveal_opacity_duration'   => 420,
			'ease'                      => 'myk',
			'play_once'                 => true,
			'enable_mobile'             => true,
			'flatsome_distance'         => 24,
			'text_style'                => 'rise',
			'text_unit'                 => 'ord',
			'text_stagger'              => 45,
			'text_duration'             => 800,
			'reduce_motion'             => true,
			'defer_js'                  => true,
		);
	}

	public static function animations() {
		return array(
			'fade-up'    => array(
				'name'        => __( 'Opp', 'flatsome-better-animations' ),
				'description' => __( 'Fade og glid opp', 'flatsome-better-animations' ),
				'class'       => 'reveal--fade-up',
				'aliases'     => array(),
				'transform'   => 'translate3d(0, var(--fba-move), 0)',
				'filter'      => 'none',
				'clip'        => 'none',
			),
			'fade-down'  => array(
				'name'        => __( 'Ned', 'flatsome-better-animations' ),
				'description' => __( 'Fade og glid ned', 'flatsome-better-animations' ),
				'class'       => 'reveal--fade-down',
				'aliases'     => array( 'reveal--down' ),
				'transform'   => 'translate3d(0, calc(var(--fba-move) * -1), 0)',
				'filter'      => 'none',
				'clip'        => 'none',
			),
			'fade-left'  => array(
				'name'        => __( 'Fra venstre', 'flatsome-better-animations' ),
				'description' => __( 'Glir inn fra venstre', 'flatsome-better-animations' ),
				'class'       => 'reveal--fade-left',
				'aliases'     => array( 'reveal--left' ),
				'transform'   => 'translate3d(calc(var(--fba-move) * -1), 0, 0)',
				'filter'      => 'none',
				'clip'        => 'none',
			),
			'fade-right' => array(
				'name'        => __( 'Fra høyre', 'flatsome-better-animations' ),
				'description' => __( 'Glir inn fra høyre', 'flatsome-better-animations' ),
				'class'       => 'reveal--fade-right',
				'aliases'     => array( 'reveal--right' ),
				'transform'   => 'translate3d(var(--fba-move), 0, 0)',
				'filter'      => 'none',
				'clip'        => 'none',
			),
			'fade'       => array(
				'name'        => __( 'Fade', 'flatsome-better-animations' ),
				'description' => __( 'Kun opasitet', 'flatsome-better-animations' ),
				'class'       => 'reveal--fade',
				'aliases'     => array(),
				'transform'   => 'none',
				'filter'      => 'none',
				'clip'        => 'none',
			),
			'zoom-in'    => array(
				'name'        => __( 'Zoom inn', 'flatsome-better-animations' ),
				'description' => __( 'Vokser fra 88 %', 'flatsome-better-animations' ),
				'class'       => 'reveal--zoom-in',
				'aliases'     => array( 'reveal--zoom' ),
				'transform'   => 'scale(0.88)',
				'filter'      => 'none',
				'clip'        => 'none',
			),
			'zoom-out'   => array(
				'name'        => __( 'Zoom ut', 'flatsome-better-animations' ),
				'description' => __( 'Krymper fra 112 %', 'flatsome-better-animations' ),
				'class'       => 'reveal--zoom-out',
				'aliases'     => array(),
				'transform'   => 'scale(1.12)',
				'filter'      => 'none',
				'clip'        => 'none',
			),
			'blur'       => array(
				'name'        => __( 'Uskarp', 'flatsome-better-animations' ),
				'description' => __( 'Fokuserer og løfter', 'flatsome-better-animations' ),
				'class'       => 'reveal--blur',
				'aliases'     => array(),
				'transform'   => 'translate3d(0, calc(var(--fba-move) * 0.5), 0)',
				'filter'      => 'blur(10px)',
				'clip'        => 'none',
			),
			'wipe'       => array(
				'name'        => __( 'Maske opp', 'flatsome-better-animations' ),
				'description' => __( 'Avsløres nedenfra', 'flatsome-better-animations' ),
				'class'       => 'reveal--wipe',
				'aliases'     => array(),
				'transform'   => 'none',
				'filter'      => 'none',
				'clip'        => 'inset(100% 0 0 0)',
			),
			'wipe-l'     => array(
				'name'        => __( 'Maske side', 'flatsome-better-animations' ),
				'description' => __( 'Avsløres fra venstre', 'flatsome-better-animations' ),
				'class'       => 'reveal--wipe-l',
				'aliases'     => array(),
				'transform'   => 'none',
				'filter'      => 'none',
				'clip'        => 'inset(0 100% 0 0)',
			),
			'tilt'       => array(
				'name'        => __( '3D-tilt', 'flatsome-better-animations' ),
				'description' => __( 'Vipper fram i dybden', 'flatsome-better-animations' ),
				'class'       => 'reveal--tilt',
				'aliases'     => array(),
				'transform'   => 'perspective(800px) rotateX(16deg) translate3d(0, var(--fba-move), 0)',
				'filter'      => 'none',
				'clip'        => 'none',
			),
			'flip'       => array(
				'name'        => __( 'Snu', 'flatsome-better-animations' ),
				'description' => __( 'Roterer rundt Y-aksen', 'flatsome-better-animations' ),
				'class'       => 'reveal--flip',
				'aliases'     => array(),
				'transform'   => 'perspective(800px) rotateY(-55deg)',
				'filter'      => 'none',
				'clip'        => 'none',
			),
		);
	}

	public static function easings() {
		return array(
			'myk'    => array(
				'label' => __( 'Myk', 'flatsome-better-animations' ),
				'value' => 'cubic-bezier(0.22, 1, 0.36, 1)',
			),
			'jevn'   => array(
				'label' => __( 'Jevn', 'flatsome-better-animations' ),
				'value' => 'cubic-bezier(0.65, 0, 0.35, 1)',
			),
			'spenst' => array(
				'label' => __( 'Spenst', 'flatsome-better-animations' ),
				'value' => 'cubic-bezier(0.34, 1.56, 0.64, 1)',
			),
			'lett'   => array(
				'label' => __( 'Lett', 'flatsome-better-animations' ),
				'value' => 'ease-out',
			),
		);
	}

	public static function text_styles() {
		return array(
			'rise' => array(
				'label'     => __( 'Løft', 'flatsome-better-animations' ),
				'transform' => 'translateY(130%)',
				'opacity'   => '1',
				'filter'    => 'none',
			),
			'fade' => array(
				'label'     => __( 'Fade', 'flatsome-better-animations' ),
				'transform' => 'translateY(10px)',
				'opacity'   => '0',
				'filter'    => 'none',
			),
			'blur' => array(
				'label'     => __( 'Uskarp', 'flatsome-better-animations' ),
				'transform' => 'translateY(6px)',
				'opacity'   => '0',
				'filter'    => 'blur(8px)',
			),
			'rot'  => array(
				'label'     => __( 'Roter', 'flatsome-better-animations' ),
				'transform' => 'translateY(60%) rotate(5deg)',
				'opacity'   => '0',
				'filter'    => 'none',
			),
		);
	}

	public static function text_units() {
		return array(
			'tegn'  => __( 'Tegn', 'flatsome-better-animations' ),
			'ord'   => __( 'Ord', 'flatsome-better-animations' ),
			'linje' => __( 'Linje', 'flatsome-better-animations' ),
		);
	}

	public static function builder_tags() {
		return array(
			'col',
			'col_grid',
			'row',
			'section',
			'title',
			'text',
			'button',
			'ux_image',
			'ux_image_box',
			'featured_box',
			'text_box',
			'ux_banner',
			'team_member',
			'testimonial',
		);
	}

	public static function stagger_tags() {
		return array_values(
			array_unique(
				array_merge(
					self::child_anim_tags(),
					array( 'col', 'col_inner', 'col_inner_1', 'col_inner_2', 'col_grid' )
				)
			)
		);
	}

	public static function child_anim_tags() {
		return array(
			'row',
			'row_inner',
			'row_inner_1',
			'row_inner_2',
			'section',
			'section_inner',
			'background',
		);
	}

	public static function text_tags() {
		return array( 'title', 'text' );
	}

	public static function output_tags() {
		$tags = self::builder_tags();
		$tags[] = 'ux_text';
		$tags[] = 'col_inner';
		$tags[] = 'col_inner_1';
		$tags[] = 'col_inner_2';
		$tags[] = 'row_inner';
		$tags[] = 'row_inner_1';
		$tags[] = 'row_inner_2';
		$tags[] = 'section_inner';
		$tags[] = 'background';
		return array_values( array_unique( $tags ) );
	}

	public static function animation( $id ) {
		$all = self::animations();
		return isset( $all[ $id ] ) ? $all[ $id ] : $all['fade-up'];
	}

	public static function is_animation( $id ) {
		$all = self::animations();
		return is_string( $id ) && isset( $all[ $id ] );
	}

	public static function class_for( $id ) {
		$anim = self::animation( $id );
		return $anim['class'];
	}

	public static function easing_value( $id ) {
		$all = self::easings();
		if ( isset( $all[ $id ] ) ) {
			return $all[ $id ]['value'];
		}
		return $all['myk']['value'];
	}

	public static function is_truthy( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}
		$value = strtolower( trim( (string) $value ) );
		return $value !== '' && ! in_array( $value, array( '0', 'false', 'off', 'no', 'none' ), true );
	}

	public static function frontend_library() {
		$animations = array();
		foreach ( self::animations() as $id => $anim ) {
			$animations[ $id ] = array(
				'n'     => $anim['name'],
				'd'     => $anim['description'],
				't'     => $anim['transform'],
				'f'     => $anim['filter'],
				'c'     => $anim['clip'],
				'class' => $anim['class'],
			);
		}

		$easings = array();
		foreach ( self::easings() as $id => $ease ) {
			$easings[ $id ] = array(
				'label' => $ease['label'],
				'value' => $ease['value'],
			);
		}

		$text_styles = array();
		foreach ( self::text_styles() as $id => $style ) {
			$text_styles[ $id ] = array(
				'label'     => $style['label'],
				'transform' => $style['transform'],
				'opacity'   => $style['opacity'],
				'filter'    => $style['filter'],
			);
		}

		return array(
			'animations'  => $animations,
			'easings'     => $easings,
			'textStyles'  => $text_styles,
			'textUnits'   => self::text_units(),
		);
	}
}

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FBA_Output {

	public static function init() {
		add_filter( 'do_shortcode_tag', array( __CLASS__, 'filter_shortcode' ), 10, 3 );
	}

	public static function filter_shortcode( $output, $tag, $attr ) {
		if ( ! in_array( $tag, FBA_Library::output_tags(), true ) ) {
			return $output;
		}

		if ( ! is_string( $output ) || $output === '' || ! is_array( $attr ) ) {
			return $output;
		}

		$classes = array();
		$styles  = array();
		$data    = array();

		$anim            = isset( $attr['fba_anim'] ) ? sanitize_key( $attr['fba_anim'] ) : '';
		$has_anim        = FBA_Library::is_animation( $anim );
		$is_child_parent = in_array( $tag, FBA_Library::child_anim_tags(), true );
		$stagger_set     = array_key_exists( 'fba_stagger', $attr );
		$stagger_on      = $stagger_set
			? FBA_Library::is_truthy( $attr['fba_stagger'] )
			: ( $has_anim && $is_child_parent );

		if ( $has_anim && $is_child_parent ) {
			$data['fba-child-anim'] = $anim;
			if ( ! $stagger_on ) {
				$data['fba-stagger'] = 'off';
			}
		} elseif ( $has_anim ) {
			$classes[] = 'reveal';
			$classes[] = FBA_Library::class_for( $anim );
		}

		if ( $stagger_on ) {
			$classes[] = 'reveal-stagger';
		}

		$delay = isset( $attr['fba_delay'] ) ? (int) $attr['fba_delay'] : 0;
		if ( $delay > 0 ) {
			$styles['--reveal-delay'] = $delay . 'ms';
			$data['reveal-delay']     = (string) $delay;
		}

		$text        = isset( $attr['fba_text'] ) ? sanitize_key( $attr['fba_text'] ) : '';
		$text_styles = FBA_Library::text_styles();
		if ( $text !== '' && isset( $text_styles[ $text ] ) ) {
			$classes[] = 'reveal-text';
			$classes[] = 'reveal-text--' . $text;

			$units = FBA_Library::text_units();
			$unit  = isset( $attr['fba_text_unit'] ) ? sanitize_key( $attr['fba_text_unit'] ) : 'ord';
			if ( ! isset( $units[ $unit ] ) ) {
				$unit = 'ord';
			}
			$classes[]           = 'reveal-text--' . $unit;
			$data['reveal-text'] = $text;
			$data['reveal-unit'] = $unit;

			if ( $tag === 'title' ) {
				$data['reveal-target'] = '.section-title-main';
			}
		}

		$classes = array_values( array_unique( array_filter( $classes ) ) );
		if ( empty( $classes ) && empty( $styles ) && empty( $data ) ) {
			return $output;
		}

		return self::inject_root( $output, $classes, $styles, $data );
	}

	private static function inject_root( $html, $classes, $styles, $data ) {
		if ( ! preg_match( '/<([a-zA-Z][a-zA-Z0-9:-]*)(\s[^>]*)?>/', $html, $match, PREG_OFFSET_CAPTURE ) ) {
			return $html;
		}

		$full   = $match[0][0];
		$offset = $match[0][1];
		$tag    = $match[1][0];
		$attrs  = isset( $match[2][0] ) ? $match[2][0] : '';

		if ( in_array( strtolower( $tag ), array( 'style', 'script', 'link', 'meta', 'br', 'hr' ), true ) ) {
			return $html;
		}

		$new_attrs = rtrim( $attrs );

		if ( ! empty( $classes ) ) {
			if ( preg_match( '/\bclass=([\'"])(.*?)\1/i', $new_attrs, $class_match ) ) {
				$existing  = preg_split( '/\s+/', trim( $class_match[2] ) );
				$merged    = array_values( array_unique( array_filter( array_merge( $existing, $classes ) ) ) );
				$new_attrs = preg_replace(
					'/\bclass=([\'"])(.*?)\1/i',
					'class=' . $class_match[1] . esc_attr( implode( ' ', $merged ) ) . $class_match[1],
					$new_attrs,
					1
				);
			} else {
				$new_attrs .= ' class="' . esc_attr( implode( ' ', $classes ) ) . '"';
			}
		}

		if ( ! empty( $styles ) ) {
			$chunks = array();
			foreach ( $styles as $prop => $value ) {
				$chunks[] = $prop . ':' . $value;
			}
			$extra = implode( ';', $chunks );

			if ( preg_match( '/\bstyle=([\'"])(.*?)\1/i', $new_attrs, $style_match ) ) {
				$combined  = rtrim( $style_match[2], '; ' );
				$combined  = $combined === '' ? $extra : $combined . ';' . $extra;
				$new_attrs = preg_replace(
					'/\bstyle=([\'"])(.*?)\1/i',
					'style=' . $style_match[1] . esc_attr( $combined ) . $style_match[1],
					$new_attrs,
					1
				);
			} else {
				$new_attrs .= ' style="' . esc_attr( $extra ) . '"';
			}
		}

		foreach ( $data as $name => $value ) {
			$attr_name = 'data-' . $name;
			if ( preg_match( '/\b' . preg_quote( $attr_name, '/' ) . '=/i', $new_attrs ) ) {
				continue;
			}
			$new_attrs .= ' ' . $attr_name . '="' . esc_attr( $value ) . '"';
		}

		$replacement = '<' . $tag . $new_attrs . '>';
		return substr( $html, 0, $offset ) . $replacement . substr( $html, $offset + strlen( $full ) );
	}
}

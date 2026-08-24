<?php
/**
 * Settings storage and validation.
 *
 * @package LoginMood
 */

namespace Fegutogi\LoginMood;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Settings {
	const OPTION_NAME = 'fegutogi_loginmood_settings';
	const LEGACY_OPTION_NAME = 'lbrd_settings';

	/**
	 * Plugin defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		$defaults = array(
			'logo_id'                   => 0,
			'hide_logo'                 => 0,
			'logo_width'                => 180,
			'logo_panel_gap'            => 24,
			'logo_url'                  => home_url( '/' ),
			'logo_shape'                => 'none',
			'logo_border_style'         => 'none',
			'logo_border_color'         => '#ffffff',
			'logo_background_color'     => '#ffffff',
			'logo_shadow_style'         => 'none',
			'logo_shadow_color'         => '#0f172a',
			'brand_palette'             => array(),
			'background_type'           => 'color',
			'background_color'          => '#f1f5f9',
			'gradient_start'            => '#0f172a',
			'gradient_end'              => '#334155',
			'gradient_angle'            => 135,
			'background_image_id'       => 0,
			'panel_color'               => '#ffffff',
			'primary_color'             => '#2563eb',
			'text_color'                => '#1e293b',
			'background_text_color'     => '#1e293b',
			'link_color'                => '#1d4ed8',
			'button_text_color'         => '#ffffff',
			'field_background_color'    => '#ffffff',
			'field_text_color'          => '#1e293b',
			'border_radius'             => 12,
			'control_radius'            => 8,
			'button_shape'              => 'rounded',
			'footer_text'               => '',
			'enable_animation'          => 1,
			'hide_language_switcher'    => 0,
			'delete_data_on_uninstall'  => 0,
		);

		/**
		 * Filter the initial plugin defaults.
		 *
		 * Themes may use this filter to provide a preset before the option is created.
		 * Saved administrator settings always take precedence.
		 *
		 * @param array<string, mixed> $defaults Default settings.
		 */
		return (array) apply_filters( 'fegutogi_loginmood_default_settings', $defaults );
	}

	/**
	 * Add the option without autoloading it.
	 */
	public static function activate() {
		if ( false === get_option( self::OPTION_NAME, false ) ) {
			$legacy  = get_option( self::LEGACY_OPTION_NAME, false );
			$initial = is_array( $legacy ) ? $legacy : self::defaults();
			add_option( self::OPTION_NAME, self::sanitize( $initial ), '', false );
		}
	}

	/**
	 * Return merged settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function get() {
		$saved = get_option( self::OPTION_NAME, false );

		if ( false === $saved ) {
			$saved = get_option( self::LEGACY_OPTION_NAME, array() );
		}

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		if ( ! array_key_exists( 'control_radius', $saved ) && isset( $saved['button_shape'] ) ) {
			$legacy_radii = array(
				'square'  => 0,
				'rounded' => 8,
				'pill'    => 50,
			);
			if ( isset( $legacy_radii[ $saved['button_shape'] ] ) ) {
				$saved['control_radius'] = $legacy_radii[ $saved['button_shape'] ];
			}
		}

		if ( ! array_key_exists( 'background_text_color', $saved ) && isset( $saved['text_color'] ) ) {
			$saved['background_text_color'] = $saved['text_color'];
		}

		return wp_parse_args( $saved, self::defaults() );
	}

	/**
	 * Validate and sanitize a complete settings array.
	 *
	 * @param mixed $input Raw settings.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ) {
		$defaults = self::defaults();
		$input    = is_array( $input ) ? $input : array();
		$output   = array();
		$control_radius_fallback = $defaults['control_radius'];
		if ( ! isset( $input['control_radius'] ) && isset( $input['button_shape'] ) ) {
			$legacy_radii = array(
				'square'  => 0,
				'rounded' => 8,
				'pill'    => 50,
			);
			if ( isset( $legacy_radii[ $input['button_shape'] ] ) ) {
				$control_radius_fallback = $legacy_radii[ $input['button_shape'] ];
			}
		}

		$output['logo_id']             = isset( $input['logo_id'] ) ? absint( $input['logo_id'] ) : 0;
		$output['hide_logo']           = empty( $input['hide_logo'] ) ? 0 : 1;
		$output['logo_width']          = self::bounded_int( $input, 'logo_width', 60, 320, $defaults['logo_width'] );
		$output['logo_panel_gap']      = self::bounded_int( $input, 'logo_panel_gap', 0, 80, $defaults['logo_panel_gap'] );
		$output['logo_url']            = isset( $input['logo_url'] ) ? esc_url_raw( $input['logo_url'] ) : $defaults['logo_url'];
		$output['logo_shape']          = self::choice( $input, 'logo_shape', array( 'none', 'rounded', 'circle' ), $defaults['logo_shape'] );
		$output['logo_border_style']   = self::choice( $input, 'logo_border_style', array( 'none', 'thin', 'strong' ), $defaults['logo_border_style'] );
		$output['logo_border_color']   = self::color( $input, 'logo_border_color', $defaults['logo_border_color'] );
		$output['logo_background_color'] = self::color( $input, 'logo_background_color', $defaults['logo_background_color'] );
		$output['logo_shadow_style']   = self::choice( $input, 'logo_shadow_style', array( 'none', 'soft', 'strong' ), $defaults['logo_shadow_style'] );
		$output['logo_shadow_color']   = self::color( $input, 'logo_shadow_color', $defaults['logo_shadow_color'] );
		$output['brand_palette']       = self::color_list( isset( $input['brand_palette'] ) ? $input['brand_palette'] : array() );
		$output['background_type']     = self::choice( $input, 'background_type', array( 'color', 'gradient', 'image' ), $defaults['background_type'] );
		$output['background_color']    = self::color( $input, 'background_color', $defaults['background_color'] );
		$output['gradient_start']      = self::color( $input, 'gradient_start', $defaults['gradient_start'] );
		$output['gradient_end']        = self::color( $input, 'gradient_end', $defaults['gradient_end'] );
		$output['gradient_angle']      = self::bounded_int( $input, 'gradient_angle', 0, 360, $defaults['gradient_angle'] );
		$output['background_image_id'] = isset( $input['background_image_id'] ) ? absint( $input['background_image_id'] ) : 0;
		$output['panel_color']         = self::color( $input, 'panel_color', $defaults['panel_color'] );
		$output['primary_color']       = self::color( $input, 'primary_color', $defaults['primary_color'] );
		$output['text_color']          = self::color( $input, 'text_color', $defaults['text_color'] );
		$background_text_fallback      = isset( $input['text_color'] ) ? self::color( $input, 'text_color', $defaults['text_color'] ) : $defaults['background_text_color'];
		$output['background_text_color'] = self::color( $input, 'background_text_color', $background_text_fallback );
		$output['link_color']          = self::color( $input, 'link_color', $defaults['link_color'] );
		$output['button_text_color']   = self::color( $input, 'button_text_color', $defaults['button_text_color'] );
		$output['field_background_color'] = self::color( $input, 'field_background_color', $defaults['field_background_color'] );
		$output['field_text_color']       = self::color( $input, 'field_text_color', $defaults['field_text_color'] );
		$output['border_radius']       = self::bounded_int( $input, 'border_radius', 0, 50, $defaults['border_radius'] );
		$output['control_radius']      = self::bounded_int( $input, 'control_radius', 0, 50, $control_radius_fallback );
		$output['button_shape']        = self::choice( $input, 'button_shape', array( 'square', 'rounded', 'pill' ), $defaults['button_shape'] );
		$output['footer_text']         = isset( $input['footer_text'] ) ? sanitize_text_field( $input['footer_text'] ) : '';
		$output['enable_animation']    = array_key_exists( 'enable_animation', $input ) ? ( empty( $input['enable_animation'] ) ? 0 : 1 ) : $defaults['enable_animation'];
		$output['hide_language_switcher']   = empty( $input['hide_language_switcher'] ) ? 0 : 1;
		$output['delete_data_on_uninstall'] = empty( $input['delete_data_on_uninstall'] ) ? 0 : 1;

		return $output;
	}

	/**
	 * Sanitize a color field.
	 *
	 * @param array<string, mixed> $input Input settings.
	 * @param string               $key Field key.
	 * @param string               $fallback Default color.
	 * @return string
	 */
	private static function color( $input, $key, $fallback ) {
		$color = isset( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : '';

		return $color ? $color : $fallback;
	}

	/**
	 * Sanitize a stored brand palette.
	 *
	 * @param mixed $input Raw array or JSON string.
	 * @return array<int, array{name: string, color: string}>
	 */
	private static function color_list( $input ) {
		if ( is_string( $input ) ) {
			$decoded = json_decode( wp_unslash( $input ), true );
			$input   = is_array( $decoded ) ? $decoded : array();
		}

		if ( ! is_array( $input ) ) {
			return array();
		}

		$colors = array();
		foreach ( $input as $candidate ) {
			$raw_color = is_array( $candidate ) && isset( $candidate['color'] ) ? $candidate['color'] : $candidate;
			$raw_name  = is_array( $candidate ) && isset( $candidate['name'] ) ? $candidate['name'] : '';
			$color     = sanitize_hex_color( is_string( $raw_color ) ? $raw_color : '' );
			$name      = is_string( $raw_name ) ? sanitize_text_field( $raw_name ) : '';
			$name      = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 80 ) : substr( $name, 0, 80 );

			if ( $color ) {
				$color = strtolower( $color );
				$index = array_search( $color, array_column( $colors, 'color' ), true );
				if ( false === $index ) {
					$colors[] = array(
						'name'  => $name,
						'color' => $color,
					);
				} elseif ( '' === $colors[ $index ]['name'] && '' !== $name ) {
					$colors[ $index ]['name'] = $name;
				}
			}

			if ( 32 === count( $colors ) ) {
				break;
			}
		}

		return $colors;
	}

	/**
	 * Sanitize an integer and keep it within a range.
	 *
	 * @param array<string, mixed> $input Input settings.
	 * @param string               $key Field key.
	 * @param int                  $min Minimum.
	 * @param int                  $max Maximum.
	 * @param int                  $fallback Default.
	 * @return int
	 */
	private static function bounded_int( $input, $key, $min, $max, $fallback ) {
		$value = isset( $input[ $key ] ) ? absint( $input[ $key ] ) : $fallback;

		return max( $min, min( $max, $value ) );
	}

	/**
	 * Sanitize an enumerated value.
	 *
	 * @param array<string, mixed> $input Input settings.
	 * @param string               $key Field key.
	 * @param string[]             $allowed Allowed values.
	 * @param string               $fallback Default.
	 * @return string
	 */
	private static function choice( $input, $key, $allowed, $fallback ) {
		$value = isset( $input[ $key ] ) ? sanitize_key( $input[ $key ] ) : $fallback;

		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}
}

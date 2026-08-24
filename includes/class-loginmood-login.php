<?php
/**
 * Login-screen presentation.
 *
 * @package LoginMood
 */

namespace Fegutogi\LoginMood;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Login {
	/**
	 * Register login hooks.
	 */
	public function __construct() {
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'login_footer', array( $this, 'render_footer' ) );
		add_filter( 'login_headerurl', array( $this, 'logo_url' ) );
		add_filter( 'login_headertext', array( $this, 'logo_text' ) );
		add_filter( 'login_body_class', array( $this, 'body_class' ) );
		add_filter( 'login_display_language_dropdown', array( $this, 'language_dropdown' ) );
	}

	/**
	 * Load the login stylesheet and its setting-specific variables.
	 */
	public function enqueue_assets() {
		wp_enqueue_style(
			'loginmood',
			FEGUTOGI_LOGINMOOD_URL . 'assets/css/login.css',
			array(),
			FEGUTOGI_LOGINMOOD_VERSION
		);

		wp_add_inline_style( 'loginmood', $this->dynamic_css( Settings::get() ) );
	}

	/**
	 * Build safe setting-specific CSS.
	 *
	 * @param array<string, mixed> $settings Plugin settings.
	 * @return string
	 */
	private function dynamic_css( $settings ) {
		$background = $settings['background_color'];
		$logo_width = (int) $settings['logo_width'];
		$logo_height = 90;
		$logo_aspect = '2 / 1';
		$control_padding = min( 26, 12 + (int) round( (int) $settings['control_radius'] * 0.28 ) );
		$message_padding = min( 32, 20 + (int) round( (int) $settings['border_radius'] * 0.24 ) );
		$logo_url = wp_get_attachment_image_url( (int) $settings['logo_id'], 'full' );
		$logo_data = wp_get_attachment_image_src( (int) $settings['logo_id'], 'full' );
		if ( ! $logo_url ) {
			$logo_url = wp_get_attachment_url( (int) $settings['logo_id'] );
		}

		if ( is_array( $logo_data ) && ! empty( $logo_data[1] ) && ! empty( $logo_data[2] ) ) {
			$logo_height = (int) round( $logo_width * ( (int) $logo_data[2] / (int) $logo_data[1] ) );
			$logo_aspect = (int) $logo_data[1] . ' / ' . (int) $logo_data[2];
		}

		$logo_radius = '0px';
		$logo_size   = 'contain';
		if ( 'rounded' === $settings['logo_shape'] ) {
			$logo_radius = '18px';
		} elseif ( 'circle' === $settings['logo_shape'] ) {
			$logo_height = $logo_width;
			$logo_aspect = '1 / 1';
			$logo_radius = '50%';
			$logo_size   = 'cover';
		}

		$logo_border_width = array(
			'none'   => 0,
			'thin'   => 1,
			'strong' => 3,
		);
		$logo_shadows = array(
			'none'   => 'none',
			'soft'   => '0 10px 28px ' . $this->hex_to_rgba( $settings['logo_shadow_color'], 0.24 ),
			'strong' => '0 16px 42px ' . $this->hex_to_rgba( $settings['logo_shadow_color'], 0.42 ),
		);
		$effective_border_width = $logo_border_width[ $settings['logo_border_style'] ];
		$effective_shadow       = $logo_shadows[ $settings['logo_shadow_style'] ];
		$logo_background        = $settings['logo_background_color'];
		$logo_filter            = 'none';

		if ( 'none' === $settings['logo_shape'] ) {
			$filter_parts = array();
			if ( 'thin' === $settings['logo_border_style'] ) {
				$filter_parts[] = 'drop-shadow(1px 0 0 ' . $settings['logo_border_color'] . ')';
				$filter_parts[] = 'drop-shadow(-1px 0 0 ' . $settings['logo_border_color'] . ')';
				$filter_parts[] = 'drop-shadow(0 1px 0 ' . $settings['logo_border_color'] . ')';
				$filter_parts[] = 'drop-shadow(0 -1px 0 ' . $settings['logo_border_color'] . ')';
			} elseif ( 'strong' === $settings['logo_border_style'] ) {
				$filter_parts[] = 'drop-shadow(2px 0 0 ' . $settings['logo_border_color'] . ')';
				$filter_parts[] = 'drop-shadow(-2px 0 0 ' . $settings['logo_border_color'] . ')';
				$filter_parts[] = 'drop-shadow(0 2px 0 ' . $settings['logo_border_color'] . ')';
				$filter_parts[] = 'drop-shadow(0 -2px 0 ' . $settings['logo_border_color'] . ')';
			}

			if ( 'soft' === $settings['logo_shadow_style'] ) {
				$filter_parts[] = 'drop-shadow(0 10px 14px ' . $this->hex_to_rgba( $settings['logo_shadow_color'], 0.32 ) . ')';
			} elseif ( 'strong' === $settings['logo_shadow_style'] ) {
				$filter_parts[] = 'drop-shadow(0 16px 20px ' . $this->hex_to_rgba( $settings['logo_shadow_color'], 0.52 ) . ')';
			}

			$effective_border_width = 0;
			$effective_shadow       = 'none';
			$logo_background        = 'transparent';
			$logo_filter            = $filter_parts ? implode( ' ', $filter_parts ) : 'none';
		}
		if ( 'gradient' === $settings['background_type'] ) {
			$background = sprintf(
				'linear-gradient(%1$ddeg, %2$s, %3$s)',
				(int) $settings['gradient_angle'],
				$settings['gradient_start'],
				$settings['gradient_end']
			);
		} elseif ( 'image' === $settings['background_type'] ) {
			$image_url = wp_get_attachment_image_url( (int) $settings['background_image_id'], 'full' );
			if ( $image_url ) {
				$background = 'url("' . $this->escape_css_url( $image_url ) . '") center / cover no-repeat fixed';
			}
		}

		$css = sprintf(
			':root{--loginmood-background:%1$s;--loginmood-panel:%2$s;--loginmood-primary:%3$s;--loginmood-text:%4$s;--loginmood-link:%5$s;--loginmood-button-text:%6$s;--loginmood-radius:%7$dpx;--loginmood-logo-width:%8$dpx;--loginmood-logo-height:%9$dpx;--loginmood-logo-radius:%10$s;--loginmood-logo-size:%11$s;--loginmood-logo-border-width:%12$dpx;--loginmood-logo-border-color:%13$s;--loginmood-logo-shadow:%14$s;--loginmood-control-radius:%15$dpx;--loginmood-field-background:%16$s;--loginmood-field-text:%17$s;--loginmood-logo-aspect:%18$s;--loginmood-control-padding:%19$dpx;--loginmood-logo-background:%20$s;--loginmood-logo-filter:%21$s;--loginmood-background-text:%22$s;--loginmood-message-padding:%23$dpx;--loginmood-logo-panel-gap:%24$dpx;}',
			$background,
			$settings['panel_color'],
			$settings['primary_color'],
			$settings['text_color'],
			$settings['link_color'],
			$settings['button_text_color'],
			(int) $settings['border_radius'],
			$logo_width,
			$logo_height,
			$logo_radius,
			$logo_size,
			$effective_border_width,
			$settings['logo_border_color'],
			$effective_shadow,
			(int) $settings['control_radius'],
			$settings['field_background_color'],
			$settings['field_text_color'],
			$logo_aspect,
			$control_padding,
			$logo_background,
			$logo_filter,
			$settings['background_text_color'],
			$message_padding,
			(int) $settings['logo_panel_gap']
		);

		if ( $logo_url ) {
			$css .= '.login h1 a{background-image:url("' . $this->escape_css_url( $logo_url ) . '");}';
		}
		if ( ! empty( $settings['hide_logo'] ) ) {
			$css .= '.loginmood-login h1{display:none;}';
		}

		return $css;
	}

	/**
	 * Convert a sanitized hexadecimal color to rgba().
	 *
	 * @param string $hex Hexadecimal color.
	 * @param float  $alpha Alpha value from zero to one.
	 * @return string
	 */
	private function hex_to_rgba( $hex, $alpha ) {
		$hex = ltrim( $hex, '#' );

		return sprintf(
			'rgba(%1$d,%2$d,%3$d,%4$s)',
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
			rtrim( rtrim( number_format( $alpha, 2, '.', '' ), '0' ), '.' )
		);
	}

	/**
	 * Escape a URL for a quoted CSS url() value.
	 *
	 * @param string $url Media URL.
	 * @return string
	 */
	private function escape_css_url( $url ) {
		return str_replace(
			array( '\\', '"', "\r", "\n" ),
			array( '\\\\', '\\"', '', '' ),
			esc_url_raw( $url )
		);
	}

	/**
	 * Use the configured logo link.
	 *
	 * @return string
	 */
	public function logo_url() {
		$settings = Settings::get();

		return $settings['logo_url'] ? $settings['logo_url'] : home_url( '/' );
	}

	/**
	 * Return an accessible logo label.
	 *
	 * @return string
	 */
	public function logo_text() {
		return get_bloginfo( 'name' );
	}

	/**
	 * Add a stable body class for integrations.
	 *
	 * @param string[] $classes Login body classes.
	 * @return string[]
	 */
	public function body_class( $classes ) {
		$settings  = Settings::get();
		$classes[] = 'loginmood-login';
		if ( ! empty( $settings['hide_logo'] ) ) {
			$classes[] = 'loginmood-no-logo';
		}
		if ( empty( $settings['enable_animation'] ) ) {
			$classes[] = 'loginmood-no-animation';
		}

		return $classes;
	}

	/**
	 * Optionally hide the core language dropdown.
	 *
	 * @param bool $display Whether to display it.
	 * @return bool
	 */
	public function language_dropdown( $display ) {
		$settings = Settings::get();

		return ! empty( $settings['hide_language_switcher'] ) ? false : $display;
	}

	/**
	 * Render optional login footer copy.
	 */
	public function render_footer() {
		$settings = Settings::get();

		if ( empty( $settings['footer_text'] ) ) {
			return;
		}

		printf(
			'<p class="loginmood-footer">%s</p>',
			esc_html( $settings['footer_text'] )
		);
	}
}

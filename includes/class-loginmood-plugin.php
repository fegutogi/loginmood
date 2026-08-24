<?php
/**
 * Plugin bootstrap.
 *
 * @package LoginMood
 */

namespace Fegutogi\LoginMood;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {
	/**
	 * Plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Return the plugin instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register the plugin components.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		new Svg();
		new Login();

		if ( is_admin() ) {
			new Admin();
		}
	}

	/**
	 * Load the translations bundled with the plugin.
	 */
	public function load_textdomain() {
		// phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- The release bundles Spanish translations for direct ZIP installs outside WordPress.org.
		load_plugin_textdomain( 'loginmood', false, dirname( plugin_basename( FEGUTOGI_LOGINMOOD_FILE ) ) . '/languages' );
	}

}

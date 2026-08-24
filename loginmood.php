<?php
/**
 * Plugin Name:       LoginMood
 * Plugin URI:        https://fegutogi.com/plugins/loginmood
 * Description:       Make the WordPress login feel like your site with focused visual identity, color, background, and content controls.
 * Version:           1.0.0-rc.3
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Fegutogi
 * Author URI:        https://fegutogi.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       loginmood
 * Domain Path:       /languages
 */

namespace Fegutogi\LoginMood;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FEGUTOGI_LOGINMOOD_VERSION', '1.0.0-rc.3' );
define( 'FEGUTOGI_LOGINMOOD_FILE', __FILE__ );
define( 'FEGUTOGI_LOGINMOOD_PATH', plugin_dir_path( __FILE__ ) );
define( 'FEGUTOGI_LOGINMOOD_URL', plugin_dir_url( __FILE__ ) );

require_once FEGUTOGI_LOGINMOOD_PATH . 'includes/class-loginmood-settings.php';
require_once FEGUTOGI_LOGINMOOD_PATH . 'includes/class-loginmood-svg.php';
require_once FEGUTOGI_LOGINMOOD_PATH . 'includes/class-loginmood-login.php';
require_once FEGUTOGI_LOGINMOOD_PATH . 'admin/class-loginmood-admin.php';
require_once FEGUTOGI_LOGINMOOD_PATH . 'includes/class-loginmood-plugin.php';

register_activation_hook( __FILE__, array( Settings::class, 'activate' ) );

Plugin::instance();

<?php
/**
 * Plugin uninstall routine.
 *
 * @package LoginMood
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$fegutogi_loginmood_settings = get_option( 'fegutogi_loginmood_settings', array() );

if ( is_array( $fegutogi_loginmood_settings ) && ! empty( $fegutogi_loginmood_settings['delete_data_on_uninstall'] ) ) {
	delete_option( 'fegutogi_loginmood_settings' );
	delete_option( 'lbrd_settings' );
}

if ( is_multisite() ) {
	delete_site_transient( 'fegutogi_loginmood_network_settings_cache' );
}

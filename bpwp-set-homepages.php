<?php
/**
 * Plugin Name:     BPWP Set Homepages
 * Plugin URI:      https://biliplugins.com/
 * Description:     Set a different homepage for logged-in users.
 * Author:          Bili Plugins
 * Author URI:      https://bhargavb.com/
 * Text Domain:     bpwp-set-homepages
 * Domain Path:     /languages
 * Version:         1.1.0
 *
 * @package         Wp_Set_Homepages
 */

if ( ! defined( 'BPWPSH_VERSION' ) ) {
	/**
	 * The version of the plugin.
	 */
	define( 'BPWPSH_VERSION', '1.1.0' );
}
if ( ! defined( 'BPWPSH_PATH' ) ) {
	/**
	 *  The server file system path to the plugin directory.
	 */
	define( 'BPWPSH_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'BPWPSH_URL' ) ) {
	/**
	 * The url to the plugin directory.
	 */
	define( 'BPWPSH_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'BPWPSH_BASE_NAME' ) ) {
	/**
	 * The url to the plugin directory.
	 */
	define( 'BPWPSH_BASE_NAME', plugin_basename( __FILE__ ) );
}

/**
 * Apply transaltion file as per WP language.
 */
function bpbpwpsh_text_domain_loader() {

	// Get mo file as per current locale.
	$mofile = BPWPSH_PATH . 'languages/' . get_locale() . '.mo';

	// If file does not exists, then applu default mo.
	if ( ! file_exists( $mofile ) ) {
		$mofile = BPWPSH_PATH . 'languages/default.mo';
	}

	load_textdomain( 'wp-set-homepages', $mofile );
}

add_action( 'plugins_loaded', 'bpbpwpsh_text_domain_loader' );

// Include functions file.
require BPWPSH_PATH . 'app/main/class-wp-set-homepages.php';
require BPWPSH_PATH . 'app/admin/class-wp-set-homepages-admin.php';

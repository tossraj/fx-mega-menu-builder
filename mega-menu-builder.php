<?php
/**
 * Plugin Name: Mega Menu Builder
 * Plugin URI:  https://github.com/tossraj/mega-menu-builder
 * Description: A powerful mega menu builder with React-based visual editor, column layouts, Bootstrap Icons, AJAX search, and image support.
 * Version:     1.0.1
 * Author:      Shiv Singh
 * Author URI:  https://github.com/tossraj
 * License:     GPL-2.0+
 * Text Domain: mega-menu-builder
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'MMB_VERSION',    '1.0.1' );
define( 'MMB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MMB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MMB_BUILD_URL',  MMB_PLUGIN_URL . 'build/' );

require_once MMB_PLUGIN_DIR . 'includes/class-admin.php';
require_once MMB_PLUGIN_DIR . 'includes/class-api.php';
require_once MMB_PLUGIN_DIR . 'includes/class-frontend.php';
require_once MMB_PLUGIN_DIR . 'includes/class-walker.php';

new MMB_Admin();
new MMB_API();
new MMB_Frontend();

register_activation_hook( __FILE__, 'mmb_activate' );

function mmb_activate() {
    // Create default option structure
    if ( ! get_option( 'mmb_menus' ) ) {
        update_option( 'mmb_menus', [] );
    }
}

<?php
/**
 * Plugin Name: Binrik Navigation Menu Designer
 * Plugin URI:  https://github.com/binrik/fx-mega-menu-builder
 * Description: A powerful mega menu builder with React-based visual editor, column layouts, Bootstrap Icons, AJAX search, and image support.
 * Version:     1.0.1
 * Author:      Shiv Singh
 * Author URI:  https://github.com/binrik
 * License:     GPL-2.0+
 * Text Domain: binrik-navigation-menu-designer
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BINRIK_NMD_VERSION',    '1.0.1' );
define( 'BINRIK_NMD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BINRIK_NMD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BINRIK_NMD_BUILD_URL',  BINRIK_NMD_PLUGIN_URL . 'build/' );

require_once BINRIK_NMD_PLUGIN_DIR . 'includes/class-admin.php';
require_once BINRIK_NMD_PLUGIN_DIR . 'includes/class-api.php';
require_once BINRIK_NMD_PLUGIN_DIR . 'includes/class-frontend.php';
require_once BINRIK_NMD_PLUGIN_DIR . 'includes/class-walker.php';

new Binrik_NMD_Admin();
new Binrik_NMD_API();
new Binrik_NMD_Frontend();

register_activation_hook( __FILE__, 'binrik_nmd_activate' );

function binrik_nmd_activate() {
    // Create default option structure
    if ( ! get_option( 'binrik_nmd_menus' ) ) {
        update_option( 'binrik_nmd_menus', [] );
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MMB_Frontend {

    public function __construct() {
        add_action( 'wp_enqueue_scripts',    [ $this, 'enqueue_scripts' ] );
        add_filter( 'wp_nav_menu_args',      [ $this, 'maybe_inject_walker' ] );
    }

    public function enqueue_scripts() {
        if ( ! $this->has_active_menus() ) return;

        wp_enqueue_style(
            'bootstrap-icons',
            MMB_PLUGIN_URL . 'assets/css/bootstrap-icons.min.css',
            [],
            '1.11.3'
        );

        wp_enqueue_style(
            'mmb-frontend',
            MMB_PLUGIN_URL . 'assets/css/frontend.css',
            [ 'bootstrap-icons' ],
            MMB_VERSION
        );

        wp_enqueue_script(
            'mmb-frontend',
            MMB_PLUGIN_URL . 'assets/js/frontend.js',
            [],
            MMB_VERSION,
            true
        );

        wp_localize_script( 'mmb-frontend', 'mmbFrontend', [
            'menus' => get_option( 'mmb_menus', [] ),
        ] );
    }

    private function has_active_menus() {
        $menus = get_option( 'mmb_menus', [] );
        return ! empty( $menus );
    }

    public function maybe_inject_walker( $args ) {
        $menus = get_option( 'mmb_menus', [] );
        if ( ! empty( $menus ) ) {
            $args['walker'] = new MMB_Walker();
        }
        return $args;
    }
}

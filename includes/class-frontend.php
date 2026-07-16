<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Binrik_NMD_Frontend {

    public function __construct() {
        add_action( 'wp_enqueue_scripts',    [ $this, 'enqueue_scripts' ] );
        add_filter( 'wp_nav_menu_args',      [ $this, 'maybe_inject_walker' ] );
    }

    public function enqueue_scripts() {
        if ( ! $this->has_active_menus() ) return;

        wp_enqueue_style(
            'binrik-nmd-bootstrap-icons',
            BINRIK_NMD_PLUGIN_URL . 'assets/css/bootstrap-icons.min.css',
            [],
            '1.11.3'
        );

        wp_enqueue_style(
            'binrik-nmd-frontend',
            BINRIK_NMD_PLUGIN_URL . 'assets/css/frontend.css',
            [ 'binrik-nmd-bootstrap-icons' ],
            BINRIK_NMD_VERSION
        );

        wp_enqueue_script(
            'binrik-nmd-frontend',
            BINRIK_NMD_PLUGIN_URL . 'assets/js/frontend.js',
            [],
            BINRIK_NMD_VERSION,
            true
        );

        wp_localize_script( 'binrik-nmd-frontend', 'binrikNmdFrontend', [
            'menus' => get_option( 'binrik_nmd_menus', [] ),
        ] );
    }

    private function has_active_menus() {
        $menus = get_option( 'binrik_nmd_menus', [] );
        return ! empty( $menus );
    }

    public function maybe_inject_walker( $args ) {
        $menus = get_option( 'binrik_nmd_menus', [] );
        if ( ! empty( $menus ) ) {
            $args['walker'] = new Binrik_NMD_Walker();
        }
        return $args;
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Binrik_NMD_Admin {

    public function __construct() {
        add_action( 'admin_menu',            [ $this, 'add_admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'Binrik Navigation Menu Designer', 'binrik-navigation-menu-designer' ),
            __( 'Navigation Menu', 'binrik-navigation-menu-designer' ),
            'manage_options',
            'binrik-navigation-menu-designer',
            [ $this, 'render_admin_page' ],
            'dashicons-menu-alt3',
            30
        );
    }

    public function render_admin_page() {
        echo '<div id="binrik-nmd-root" class="binrik-nmd-admin-wrap"></div>';
    }

    public function enqueue_scripts( $hook ) {
        if ( 'toplevel_page_binrik-navigation-menu-designer' !== $hook ) return;

        // Bootstrap Icons CSS (Local)
        wp_enqueue_style(
            'binrik-nmd-bootstrap-icons',
            BINRIK_NMD_PLUGIN_URL . 'assets/css/bootstrap-icons.min.css',
            [],
            '1.11.3'
        );

        // React App (self-contained webpack bundle)
        wp_enqueue_script(
            'binrik-nmd-admin-script',
            BINRIK_NMD_BUILD_URL . 'index.js',
            [],
            BINRIK_NMD_VERSION,
            true
        );

        wp_enqueue_style(
            'binrik-nmd-admin-style',
            BINRIK_NMD_BUILD_URL . 'index.css',
            [ 'binrik-nmd-bootstrap-icons' ],
            BINRIK_NMD_VERSION
        );

        // WordPress Media Library
        wp_enqueue_media();

        // Localize data passed to React
        wp_localize_script( 'binrik-nmd-admin-script', 'binrikNmdData', [
            'apiUrl'       => esc_url( rest_url( 'binrik-nmd/v1' ) ),
            'nonce'        => wp_create_nonce( 'wp_rest' ),
            'adminUrl'     => esc_url( admin_url() ),
            'siteUrl'      => esc_url( get_site_url() ),
            'navMenus'     => $this->get_nav_menus(),
            'navLocations' => $this->get_nav_locations(),
            'pluginUrl'    => BINRIK_NMD_PLUGIN_URL,
        ] );
    }

    private function get_nav_menus() {
        $menus  = get_terms( [ 'taxonomy' => 'nav_menu', 'hide_empty' => false ] );
        $result = [];
        if ( ! is_wp_error( $menus ) ) {
            foreach ( $menus as $menu ) {
                $items = wp_get_nav_menu_items( $menu->term_id );
                $menu_items = [];
                if ( $items && ! is_wp_error( $items ) ) {
                    foreach ( $items as $item ) {
                        $menu_items[] = [
                            'id'       => intval( $item->ID ),
                            'title'    => $item->title,
                            'url'      => $item->url,
                            'parentId' => intval( $item->menu_item_parent ),
                            'order'    => intval( $item->menu_order ),
                        ];
                    }
                }
                $result[] = [
                    'id'    => $menu->term_id,
                    'name'  => $menu->name,
                    'slug'  => $menu->slug,
                    'items' => $menu_items,
                ];
            }
        }
        return $result;
    }

    /**
     * Get all registered nav menu locations from the active theme.
     */
    private function get_nav_locations() {
        $registered = get_registered_nav_menus();
        $assigned   = get_nav_menu_locations();
        $result     = [];

        foreach ( $registered as $location_id => $description ) {
            $result[] = [
                'id'          => $location_id,
                'name'        => $description,
                'currentMenu' => isset( $assigned[ $location_id ] ) ? intval( $assigned[ $location_id ] ) : 0,
            ];
        }

        return $result;
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MMB_API {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        $ns = 'mega-menu/v1';

        // Get all mega menu configurations
        register_rest_route( $ns, '/menus', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_menus' ],
            'permission_callback' => [ $this, 'check_permission' ],
        ] );

        // Save a mega menu configuration
        register_rest_route( $ns, '/menus', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'save_menu' ],
            'permission_callback' => [ $this, 'check_permission' ],
        ] );

        // Delete a mega menu configuration
        register_rest_route( $ns, '/menus/(?P<id>[\w-]+)', [
            'methods'             => 'DELETE',
            'callback'            => [ $this, 'delete_menu' ],
            'permission_callback' => [ $this, 'check_permission' ],
        ] );

        // Search WordPress content (posts, pages, custom post types)
        register_rest_route( $ns, '/search', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'search_content' ],
            'permission_callback' => [ $this, 'check_permission' ],
        ] );

        // Get WordPress search types (post types & taxonomies)
        register_rest_route( $ns, '/search-types', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_search_types' ],
            'permission_callback' => [ $this, 'check_permission' ],
        ] );

        // Get WordPress nav menu items
        register_rest_route( $ns, '/nav-items/(?P<menu_id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_nav_items' ],
            'permission_callback' => [ $this, 'check_permission' ],
        ] );
    }

    public function check_permission() {
        return current_user_can( 'manage_options' );
    }

    public function get_menus() {
        $menus = get_option( 'mmb_menus', [] );
        return rest_ensure_response( $menus );
    }

    public function save_menu( WP_REST_Request $request ) {
        $data = $request->get_json_params();

        if ( empty( $data ) || empty( $data['id'] ) ) {
            return new WP_Error( 'invalid_data', 'Invalid menu data', [ 'status' => 400 ] );
        }

        // Sanitize
        $menu_id   = sanitize_key( $data['id'] );
        $menu_name = sanitize_text_field( $data['name'] ?? '' );
        $columns   = $this->sanitize_columns( $data['columns'] ?? [] );

        $menus          = get_option( 'mmb_menus', [] );
        $menus[$menu_id] = [
            'id'         => $menu_id,
            'name'       => $menu_name,
            'menuId'     => intval( $data['menuId'] ?? 0 ),
            'columns'    => $columns,
            'settings'   => $this->sanitize_settings( $data['settings'] ?? [] ),
            'updated_at' => current_time( 'mysql' ),
        ];

        update_option( 'mmb_menus', $menus );
        return rest_ensure_response( [ 'success' => true, 'menu' => $menus[$menu_id] ] );
    }

    private function sanitize_columns( $columns ) {
        $clean = [];
        foreach ( (array) $columns as $col ) {
            $items = [];
            foreach ( (array) ( $col['items'] ?? [] ) as $item ) {
                $items[] = [
                    'id'          => sanitize_key( $item['id'] ?? uniqid() ),
                    'type'        => sanitize_key( $item['type'] ?? 'link' ),
                    'label'       => sanitize_text_field( $item['label'] ?? '' ),
                    'url'         => esc_url_raw( $item['url'] ?? '' ),
                    'icon'        => sanitize_html_class( $item['icon'] ?? '' ),
                    'imageUrl'    => esc_url_raw( $item['imageUrl'] ?? '' ),
                    'imageId'     => intval( $item['imageId'] ?? 0 ),
                    'description' => sanitize_textarea_field( $item['description'] ?? '' ),
                    'badge'       => sanitize_text_field( $item['badge'] ?? '' ),
                    'badgeColor'  => sanitize_hex_color( $item['badgeColor'] ?? '' ) ?: '#dc3545',
                    'openBlank'   => (bool) ( $item['openBlank'] ?? false ),
                    'imageWidth'  => sanitize_text_field( $item['imageWidth'] ?? '' ),
                    'imageHeight' => sanitize_text_field( $item['imageHeight'] ?? '' ),
                    'imageFull'   => (bool) ( $item['imageFull'] ?? false ),
                    'customHtml'  => wp_kses_post( $item['customHtml'] ?? '' ),
                    'pageId'            => intval( $item['pageId'] ?? 0 ),
                    'pageType'          => sanitize_text_field( $item['pageType'] ?? '' ),
                    'dynamicPage'       => (bool) ( $item['dynamicPage'] ?? false ),
                    'showPageTitle'     => (bool) ( $item['showPageTitle'] ?? false ),
                    'showPageExcerpt'   => (bool) ( $item['showPageExcerpt'] ?? false ),
                    'showPageThumbnail' => (bool) ( $item['showPageThumbnail'] ?? false ),
                    'showPageContent'   => (bool) ( $item['showPageContent'] ?? false ),
                    'shortcode'         => sanitize_text_field( $item['shortcode'] ?? '' ),
                    'children'    => $this->sanitize_columns( $item['children'] ?? [] ),
                ];
            }
            $clean[] = [
                'id'      => sanitize_key( $col['id'] ?? uniqid() ),
                'width'   => sanitize_text_field( $col['width'] ?? '25%' ),
                'heading' => sanitize_text_field( $col['heading'] ?? '' ),
                'row'     => intval( $col['row'] ?? 0 ),
                'items'   => $items,
            ];
        }
        return $clean;
    }

    private function sanitize_settings( $settings ) {
        $mobile_bg      = sanitize_hex_color( $settings['mobileBackground'] ?? '' ) ?: '#ffffff';
        $mobile_link_bg = $settings['mobileLinkBackground'] ?? 'transparent';
        if ( $mobile_link_bg !== 'transparent' ) {
            $mobile_link_bg = sanitize_hex_color( $mobile_link_bg ) ?: 'transparent';
        }
        return [
            'width'                => sanitize_text_field( $settings['width'] ?? '100%' ),
            'background'           => sanitize_hex_color( $settings['background'] ?? '' ) ?: '#ffffff',
            'mobileBackground'     => $mobile_bg,
            'mobileLinkBackground' => $mobile_link_bg,
            'position'             => sanitize_key( $settings['position'] ?? 'left' ),
            'animation'            => sanitize_key( $settings['animation'] ?? 'fade' ),
            'showOnHover'          => (bool) ( $settings['showOnHover'] ?? true ),
        ];
    }

    public function delete_menu( WP_REST_Request $request ) {
        $menu_id = sanitize_key( $request->get_param( 'id' ) );
        $menus   = get_option( 'mmb_menus', [] );
        unset( $menus[$menu_id] );
        update_option( 'mmb_menus', $menus );
        return rest_ensure_response( [ 'success' => true ] );
    }

    public function get_search_types() {
        $post_types = get_post_types( [ 'public' => true ], 'objects' );
        $taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );

        $types = [];
        $types[] = [ 'value' => 'any', 'label' => 'All' ];

        foreach ( $post_types as $pt ) {
            if ( $pt->name === 'attachment' ) continue;
            $types[] = [ 'value' => 'post_' . $pt->name, 'label' => $pt->label ];
        }

        foreach ( $taxonomies as $tax ) {
            $types[] = [ 'value' => 'tax_' . $tax->name, 'label' => $tax->label ];
        }

        return rest_ensure_response( $types );
    }

    public function search_content( WP_REST_Request $request ) {
        $query   = sanitize_text_field( $request->get_param( 'q' ) ?? '' );
        $type    = sanitize_text_field( $request->get_param( 'type' ) ?? 'any' );
        $limit   = min( intval( $request->get_param( 'limit' ) ?? 20 ), 50 );

        $results = [];

        // Taxonomy Term Query
        if ( strpos( $type, 'tax_' ) === 0 ) {
            $taxonomy = substr( $type, 4 );
            $term_args = [
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
                'number'     => $limit,
            ];
            if ( ! empty( $query ) ) {
                $term_args['search'] = $query;
            }

            $terms = get_terms( $term_args );
            if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                foreach ( $terms as $term ) {
                    $results[] = [
                        'id'        => $term->term_id,
                        'title'     => $term->name,
                        'url'       => get_term_link( $term ),
                        'type'      => $taxonomy,
                        'thumbnail' => '',
                    ];
                }
            }
        } else {
            // Post type query
            $post_type = 'any';
            if ( strpos( $type, 'post_' ) === 0 ) {
                $post_type = substr( $type, 5 );
            }

            $args = [
                'post_status'      => 'publish',
                'posts_per_page'   => $limit,
                'cache_results'    => false,
                'suppress_filters' => false,
            ];

            if ( ! empty( $query ) ) {
                $args['s'] = $query;
            }

            if ( $post_type !== 'any' ) {
                $args['post_type'] = $post_type;
            } else {
                $public_types = get_post_types( [ 'public' => true ] );
                if ( isset( $public_types['attachment'] ) ) {
                    unset( $public_types['attachment'] );
                }
                $args['post_type'] = array_values( $public_types );
            }

            add_filter( 'posts_search', [ $this, 'filter_search_by_title_or_slug' ], 10, 2 );
            $posts = get_posts( $args );
            remove_filter( 'posts_search', [ $this, 'filter_search_by_title_or_slug' ], 10 );

            foreach ( $posts as $post ) {
                $results[] = [
                    'id'        => $post->ID,
                    'title'     => get_the_title( $post ),
                    'url'       => get_permalink( $post ),
                    'type'      => $post->post_type,
                    'thumbnail' => '',
                ];
            }
        }

        return rest_ensure_response( $results );
    }

    public function filter_search_by_title_or_slug( $search, $wp_query ) {
        if ( ! empty( $search ) ) {
            global $wpdb;
            $terms = $wp_query->query_vars['search_terms'] ?? [];
            if ( empty( $terms ) && ! empty( $wp_query->query_vars['s'] ) ) {
                $terms = [ $wp_query->query_vars['s'] ];
            }
            
            // Trim and filter empty terms
            $terms = array_filter( array_map( 'trim', $terms ) );
            
            if ( empty( $terms ) ) {
                return " AND (1=0) ";
            }
            
            $search = '';
            $searchand = '';
            foreach ( $terms as $term ) {
                $like = '%' . $wpdb->esc_like( $term ) . '%';
                $search .= $searchand . $wpdb->prepare( "({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_name LIKE %s)", $like, $like );
                $searchand = ' AND ';
            }
            if ( ! empty( $search ) ) {
                $search = " AND ({$search}) ";
            }
        }
        return $search;
    }

    public function get_nav_items( WP_REST_Request $request ) {
        $menu_id = intval( $request->get_param( 'menu_id' ) );
        $items   = wp_get_nav_menu_items( $menu_id );
        $results = [];

        if ( $items && ! is_wp_error( $items ) ) {
            foreach ( $items as $item ) {
                $results[] = [
                    'id'       => $item->ID,
                    'title'    => $item->title,
                    'url'      => $item->url,
                    'parentId' => $item->menu_item_parent,
                    'order'    => $item->menu_order,
                ];
            }
        }

        return rest_ensure_response( $results );
    }
}

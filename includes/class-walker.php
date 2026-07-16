<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Binrik_NMD_Walker extends Walker_Nav_Menu {

    private $mega_menus = [];

    public function __construct() {
        $this->mega_menus = get_option( 'binrik_nmd_menus', [] );
    }

    public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
        if ( ! $element ) {
            return;
        }

        $id_field = $this->db_fields['id'];
        $id       = $element->$id_field;

        // If this item has an active mega menu, we discard its children to overwrite the existing sub-menu
        $mega_cfg = $this->find_mega_menu( $id );
        if ( $mega_cfg && $depth === 0 ) {
            unset( $children_elements[$id] );
        }

        parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }

    public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
        if ( $depth !== 0 ) {
            parent::start_el( $output, $data_object, $depth, $args, $current_object_id );
            return;
        }

        $item_id  = $data_object->ID;
        $mega_cfg = $this->find_mega_menu( $item_id );

        if ( ! $mega_cfg ) {
            parent::start_el( $output, $data_object, $depth, $args, $current_object_id );
            return;
        }

        // Top-level item with mega menu
        $classes   = empty( $data_object->classes ) ? [] : (array) $data_object->classes;
        $classes[] = 'binrik-nmd-has-mega-menu';
        $settings  = $mega_cfg['settings'] ?? [];
        $pos       = esc_attr( $settings['position'] ?? 'left' );
        $classes[] = 'binrik-nmd-position-' . $pos;
        $class_str = implode( ' ', array_filter( array_map( 'esc_attr', $classes ) ) );

        $title = apply_filters( 'the_title', $data_object->title, $data_object->ID );
        $url   = esc_url( $data_object->url );

        $mobile_link_bg = esc_attr( $settings['mobileLinkBackground'] ?? 'transparent' );

        $output .= '<li class="' . $class_str . '">';
        $output .= '<div class="binrik-nmd-link-wrap" style="--binrik-nmd-link-bg-phone:' . $mobile_link_bg . ';">';
        $output .= '<a href="' . $url . '" class="binrik-nmd-top-link">' . esc_html( $title ) . '</a>';
        $output .= '<button class="binrik-nmd-toggle-btn" aria-expanded="false"><i class="bi bi-chevron-down binrik-nmd-arrow"></i></button>';
        $output .= '</div>';
        $output .= $this->render_mega_panel( $mega_cfg );
    }

    private function find_mega_menu( $item_id ) {
        foreach ( $this->mega_menus as $menu ) {
            if ( isset( $menu['menuId'] ) && intval( $menu['menuId'] ) === intval( $item_id ) ) {
                return $menu;
            }
        }
        return null;
    }

    private function render_mega_panel( $cfg ) {
        $settings = $cfg['settings'] ?? [];
        $columns  = $cfg['columns'] ?? [];
        $bg        = esc_attr( $settings['background'] ?? '#ffffff' );
        $mobile_bg = esc_attr( $settings['mobileBackground'] ?? '#ffffff' );
        $anim      = esc_attr( $settings['animation'] ?? 'fade' );
        $width     = esc_attr( $settings['width'] ?? '100%' );
        if ( $width === 'container' ) {
            $width = 'var(--global-content-width)';
        }

        // Group columns by row index
        $grouped_rows = [];
        foreach ( $columns as $col ) {
            $row_idx = intval( $col['row'] ?? 0 );
            $grouped_rows[$row_idx][] = $col;
        }
        ksort( $grouped_rows );

        $html  = '<div class="binrik-nmd-mega-panel binrik-nmd-anim-' . $anim . '" style="--binrik-nmd-bg:' . $bg . '; --binrik-nmd-bg-phone:' . $mobile_bg . '; --binrik-nmd-max-width:' . $width . ';">';

        foreach ( $grouped_rows as $row_cols ) {
            $col_widths = [];
            $num_cols = count( $row_cols );
            foreach ( $row_cols as $col ) {
                $width_str = $col['width'] ?? '25%';
                $pct = floatval( $width_str ) / 100;
                $gap_reduce = $pct * ( $num_cols - 1 ) * 20;
                if ( $gap_reduce > 0 ) {
                    $col_widths[] = 'calc(' . esc_attr( $width_str ) . ' - ' . $gap_reduce . 'px)';
                } else {
                    $col_widths[] = esc_attr( $width_str );
                }
            }
            $grid_template = implode( ' ', $col_widths );

            $html .= '<div class="binrik-nmd-panel-inner" style="grid-template-columns: ' . $grid_template . ';">';

            foreach ( $row_cols as $col ) {
                $html .= '<div class="binrik-nmd-column">';
                if ( ! empty( $col['heading'] ) ) {
                    $html .= '<div class="binrik-nmd-col-heading">' . esc_html( $col['heading'] ) . '</div>';
                }
                $html .= '<ul class="binrik-nmd-col-items">';
                foreach ( (array) $col['items'] as $item ) {
                    $html .= $this->render_item( $item );
                }
                $html .= '</ul></div>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    private function render_item( $item ) {
        $url       = esc_url( $item['url'] ?? '#' );
        $label     = esc_html( $item['label'] ?? '' );
        $icon      = esc_attr( $item['icon'] ?? '' );
        $img_url   = esc_url( $item['imageUrl'] ?? '' );
        $desc      = esc_html( $item['description'] ?? '' );
        $badge     = esc_html( $item['badge'] ?? '' );
        $badge_clr = esc_attr( $item['badgeColor'] ?? '#dc3545' );
        $target    = ! empty( $item['openBlank'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';

        $html = '<li class="binrik-nmd-item">';

        $custom_html = $item['customHtml'] ?? '';
        if ( ! empty( $custom_html ) ) {
            $html .= '<div class="binrik-nmd-item-custom-html">' . do_shortcode( wp_kses_post( $custom_html ) ) . '</div>';
            $html .= '</li>';
            return $html;
        }

        $shortcode = $item['shortcode'] ?? '';
        if ( ! empty( $shortcode ) ) {
            $html .= '<div class="binrik-nmd-item-shortcode">' . do_shortcode( $shortcode ) . '</div>';
            $html .= '</li>';
            return $html;
        }

        $dynamic = ! empty( $item['dynamicPage'] );
        $page_id = intval( $item['pageId'] ?? 0 );

        if ( $dynamic && $page_id > 0 ) {
            $post_obj = get_post( $page_id );
            if ( $post_obj && $post_obj->post_status === 'publish' ) {
                $show_title   = ! empty( $item['showPageTitle'] );
                $show_excerpt = ! empty( $item['showPageExcerpt'] );
                $show_thumb   = ! empty( $item['showPageThumbnail'] );
                $show_content = ! empty( $item['showPageContent'] );

                $html .= '<div class="binrik-nmd-item-dynamic-content">';
                
                if ( $show_thumb && has_post_thumbnail( $post_obj->ID ) ) {
                    $html .= '<div class="binrik-nmd-dynamic-thumb" style="margin-bottom:10px;">' . get_the_post_thumbnail( $post_obj->ID, 'medium' ) . '</div>';
                }
                
                if ( $show_title ) {
                    $html .= '<h5 class="binrik-nmd-dynamic-title" style="margin:0 0 5px 0; font-size:14px; font-weight:600;"><a href="' . get_permalink( $post_obj ) . '">' . get_the_title( $post_obj ) . '</a></h5>';
                }

                if ( $show_content ) {
                    global $post;
                    $backup_post = $post;
                    
                    static $rendering_content = [];
                    if ( ! isset( $rendering_content[ $page_id ] ) ) {
                        $rendering_content[ $page_id ] = true;
                        
                        $post = $post_obj;
                        setup_postdata( $post );
                        
                        $content = apply_filters( 'the_content', get_post_field( 'post_content', $page_id ) );
                        $html .= '<div class="binrik-nmd-dynamic-page-content">' . $content . '</div>';
                        
                        wp_reset_postdata();
                        $post = $backup_post;
                        unset( $rendering_content[ $page_id ] );
                    } else {
                        $html .= '<div class="binrik-nmd-dynamic-page-content-loop-prevented">Circular content rendering prevented.</div>';
                    }
                }
                
                if ( $show_excerpt && ! $show_content ) {
                    $excerpt = $post_obj->post_excerpt ?: wp_trim_words( $post_obj->post_content, 20 );
                    $html .= '<div class="binrik-nmd-dynamic-excerpt" style="font-size:12px; color:#666; line-height:1.4;">' . esc_html( $excerpt ) . '</div>';
                }
                
                $html .= '</div>';
            }
            $html .= '</li>';
            return $html;
        }

        $html .= '<a href="' . $url . '" class="binrik-nmd-item-link"' . $target . '>';

        if ( $img_url ) {
            $img_style = '';
            $img_width  = esc_attr( $item['imageWidth'] ?? '' );
            $img_height = esc_attr( $item['imageHeight'] ?? '' );
            $img_full   = ! empty( $item['imageFull'] );

            if ( $img_full ) {
                $img_style = 'width:100%; height:auto; object-fit:cover; display:block;';
            } else {
                if ( $img_width )  $img_style .= 'width:' . $img_width . ';';
                if ( $img_height ) $img_style .= 'height:' . $img_height . ';';
            }

            $style_attr = $img_style ? ' style="' . $img_style . '"' : '';
            $html .= '<img src="' . $img_url . '" alt="' . $label . '" class="binrik-nmd-item-img" loading="lazy"' . $style_attr . '>';
        }

        if ( $label || $icon || $desc ) {
            $html .= '<span class="binrik-nmd-item-content">';

            if ( $icon ) {
                $html .= '<i class="bi ' . $icon . ' binrik-nmd-item-icon"></i>';
            }

            if ( $label || $desc ) {
                $html .= '<span class="binrik-nmd-item-text">';
                if ( $label ) {
                    $html .= '<span class="binrik-nmd-item-label">' . $label;
                    if ( $badge ) {
                        $html .= ' <span class="binrik-nmd-badge" style="background:' . $badge_clr . ';">' . $badge . '</span>';
                    }
                    $html .= '</span>';
                }

                if ( $desc ) {
                    $html .= '<span class="binrik-nmd-item-desc">' . $desc . '</span>';
                }
                $html .= '</span>';
            }

            $html .= '</span>';
        }

        $html .= '</a></li>';

        return $html;
    }
}

<?php
// Prevent direct access
if (!class_exists('Shortcode_Handler')) {
    class Shortcode_Handler {
        
        public function __construct() {
            // Register shortcodes
            add_shortcode('associated_pages_list', [$this, 'render_associated_pages_list']);
            add_shortcode('user_menu', [$this, 'render_user_menu']);
            // Register strings for multilingual translation (Polylang)
            add_action('admin_init', [$this, 'register_strings_for_translation']);
        }

        /**
         * Register static strings for Polylang translations
         */
        public function register_strings_for_translation() {
            if (function_exists('pll_register_string')) {
                pll_register_string('menu_cursus_overzicht', 'Cursus overzicht', 'Menu');
                pll_register_string('menu_kennisbank', 'Kennisbank', 'Menu');
                pll_register_string('menu_mijn_account', 'Mijn Account', 'Menu');
                pll_register_string('menu_uitloggen', 'Uitloggen', 'Menu');
            }
        }

        /**
         * Shortcode: [associated_pages_list]
         * Outputs a vertical Elementor-style menu listing associated modules for a CPT
         */
        public function render_associated_pages_list($atts) {
            global $post;
            $post_type = get_post_type($post);

            // Check if current post type is one created by the plugin
            $custom_post_types = get_option('academy_custom_post_types', []);
            $custom_post_type_slugs = array_map(function($cpt) {
                return strtolower(sanitize_title($cpt['name']));
            }, $custom_post_types);
            $is_custom_cpt = in_array($post_type, $custom_post_type_slugs);

            // Start building the output HTML with Elementor classes
            $output = '<div class="elementor-element ...">
                <div class="elementor-widget-container">
                    <link rel="stylesheet" href="https://www.academy-bewegenisleven.nl/wp-content/plugins/elementor-pro/assets/css/widget-nav-menu.min.css">
                    <nav class="elementor-nav-menu__container ...">
                        <ul>';

            // Main links (hardcoded page IDs)
            $specific_page_id = 7;
            $kennisbank_page_id = 957;

            $output .= $this->create_translated_list_item($specific_page_id, function_exists('pll__') ? pll__('Cursus overzicht') : 'Cursus overzicht');

            // If on a course, add all modules
            if ($is_custom_cpt) {
                $modules = get_posts([
                    'post_type' => $post_type,
                    'posts_per_page' => -1,
                    'order' => 'ASC'
                ]);

                foreach ($modules as $module) {
                    $output .= $this->create_list_item($module->ID);
                }
            }

            // Close primary nav
            $output .= '</ul></nav>';

            // Mobile nav toggle
            $output .= '<div class="elementor-menu-toggle" ...> ... </div>';

            // Duplicate nav for mobile
            $output .= '<nav id="nav-menu-shortcode" class="..."><ul>';
            $output .= $this->create_translated_list_item($specific_page_id, function_exists('pll__') ? pll__('Cursus overzicht') : 'Cursus overzicht');
            $output .= $this->create_translated_list_item($kennisbank_page_id, function_exists('pll__') ? pll__('Kennisbank') : 'Kennisbank');

            if ($is_custom_cpt) {
                foreach ($modules as $module) {
                    $output .= $this->create_list_item($module->ID);
                }
            }

            // Final nav close
            $output .= '</ul></nav></div></div>';

            return $output;
        }

        /**
         * Shortcode: [user_menu]
         * Displays a user menu with:
         * - language switcher
         * - profile link
         * - logout link
         */
        public function render_user_menu() {
            $output = '<ul class="user-menu">';

            // Multilingual switch
            $output .= $this->get_polylang_langswitcher();

            // Link to profile page (hardcoded)
            $output .= '<li class="menu-item"><a class="elementor-item" href="' . esc_url(admin_url('profile.php')) . '">
                <svg ...></svg>' .
                (function_exists('pll__') ? pll__('Mijn Account') : 'Mijn Account') . '</a></li>';

            // Logout
            $output .= '<li class="menu-item"><a class="elementor-item" href="' . esc_url(wp_logout_url()) . '">
                <svg ...></svg>' .
                (function_exists('pll__') ? pll__('Uitloggen') : 'Uitloggen') . '</a></li>';

            $output .= '</ul>';

            return $output;
        }

        /**
         * Helper: Create a list item link to a translated page
         */
        private function create_translated_list_item($post_id, $title) {
            $translated_post_id = function_exists('pll_get_post') ? pll_get_post($post_id) : $post_id;
            $link = get_permalink($translated_post_id);

            return '<li class="menu-item"><a class="elementor-item" href="' . esc_url($link) . '">' . esc_html($title) . '</a></li>';
        }

        /**
         * Helper: Create a list item for a given post
         */
        private function create_list_item($post_id) {
            $link = get_permalink($post_id);
            $title = get_the_title($post_id);

            return '<li class="menu-item"><a class="elementor-item" href="' . esc_url($link) . '">' . esc_html($title) . '</a></li>';
        }

        /**
         * Helper: Display language switcher using Polylang
         */
        private function get_polylang_langswitcher() {
            $output = '';
            if (function_exists('pll_the_languages')) {
                $args = [
                    'show_flags' => 1,
                    'show_names' => 1,
                    'echo' => 0,
                    'hide_current' => 1,
                    'raw' => 1,
                ];

                $languages = pll_the_languages($args);

                if (!empty($languages) && is_array($languages)) {
                    foreach ($languages as $language) {
                        $output .= '<li class="menu-item"><a href="' . esc_url($language['url']) . '">';
                        if (!empty($language['flag'])) {
                            $output .= $language['flag'] . ' ';
                        }
                        $output .= esc_html($language['name']) . '</a></li>';
                    }
                }
            }

            return $output;
        }
    }

    // Initialize the shortcode handler
    new Shortcode_Handler();
}

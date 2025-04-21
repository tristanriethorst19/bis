<?php

// Avoid redefining the class
if (!class_exists('Academy_Core')) {
    class Academy_Core {

        public function __construct() {
            // Create main admin menu item
            add_action('admin_menu', [$this, 'add_admin_menu']);

            // Add submenu items for each dynamic custom post type
            add_action('admin_menu', [$this, 'add_submenus'], 11);

            // Enqueue scripts and styles for the admin panel
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

            // Enqueue scripts for the public frontend
            add_action('wp_enqueue_scripts', [$this, 'enqueue_public_scripts']);

            // Enable and customize menu order in the admin panel
            add_filter('custom_menu_order', '__return_true');
            add_filter('menu_order', [$this, 'set_custom_menu_order']);

            // Register custom navigation menu for use in the theme
            add_action('after_setup_theme', [$this, 'register_custom_nav_menus']);
        }

        /**
         * Enqueues admin-specific JavaScript and CSS files
         */
        public function enqueue_admin_scripts() {
            wp_enqueue_media(); // Required for media uploader
            wp_enqueue_script(
                'academy-admin-scripts',
                ACADEMY_PLUGIN_URL . 'admin/js/academy-admin.js',
                ['jquery'],
                null,
                true
            );
            wp_enqueue_style(
                'academy-admin-styles',
                ACADEMY_PLUGIN_URL . 'admin/css/academy-admin.css'
            );
        }

        /**
         * Enqueues public-facing JavaScript
         */
        public function enqueue_public_scripts() {
            wp_enqueue_script(
                'menu-position-adjustment',
                ACADEMY_PLUGIN_URL . 'public/js/academy-public.js',
                [],
                null,
                true
            );
        }

        /**
         * Adds a main menu item for the Academy plugin in the WP admin
         */
        public function add_admin_menu() {
            add_menu_page(
                'Academy',                 // Page title
                'Academy',                 // Menu title
                'manage_options',          // Capability
                'academy',                 // Menu slug
                [$this, 'admin_page_content'], // Callback function
                'dashicons-welcome-learn-more', // Menu icon
                2                          // Menu position (top of menu list)
            );
        }

        /**
         * Loads the main admin page content
         */
        public function admin_page_content() {
            include ACADEMY_PLUGIN_DIR . 'admin/pages/academy-page.php';
        }

        /**
         * Dynamically adds a submenu page for each registered custom post type
         */
        public function add_submenus() {
            $custom_post_types = get_option('academy_custom_post_types', []);
            if (!empty($custom_post_types)) {
                foreach ($custom_post_types as $cpt) {
                    if (isset($cpt['name'])) {
                        add_submenu_page(
                            'academy', // Parent menu slug
                            'Edit ' . esc_html($cpt['name']), // Page title
                            'Edit ' . esc_html($cpt['name']), // Menu label
                            'manage_options', // Capability
                            'edit_cpt_' . strtolower(sanitize_title($cpt['name'])), // Unique page slug
                            function() use ($cpt) {
                                include ACADEMY_PLUGIN_DIR . 'admin/pages/edit-cpt.php'; // Load editor
                            }
                        );
                    }
                }
            }
        }

        /**
         * Allows manual reordering of admin menu items
         */
        public function set_custom_menu_order($menu_order) {
            if (!is_array($menu_order)) {
                return $menu_order;
            }

            $new_order = [];

            foreach ($menu_order as $index => $item) {
                if ($item == 'edit.php') {
                    // Move 'Posts' menu higher up
                    $new_order[5] = 'edit.php';
                } else {
                    $new_order[$index] = $item;
                }
            }

            ksort($new_order); // Ensure order is preserved
            return $new_order;
        }

        /**
         * Registers a custom menu location for frontend use (e.g., in theme header)
         */
        public function register_custom_nav_menus() {
            register_nav_menus([
                'academy_custom_menu' => __('Academy Custom Menu') // Slug => Label
            ]);
        }
    }
}

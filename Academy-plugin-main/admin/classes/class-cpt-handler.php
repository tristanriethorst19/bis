<?php

if (!class_exists('Academy_CPT_Handler')) {
    class Academy_CPT_Handler {
        
        public function __construct() {
            // Register hooks to handle create/edit/delete actions
            add_action('admin_post_create_cpt', [$this, 'create_custom_post_type']);
            add_action('admin_post_edit_cpt', [$this, 'edit_custom_post_type']);
            add_action('admin_post_delete_cpt', [$this, 'delete_custom_post_type']);
            add_action('init', [$this, 'register_custom_post_types']); // Register CPTs on init
            add_action('init', [$this, 'enable_elementor_support'], 20); // Sync with Elementor after init
        }

        /**
         * Handles creation of a new custom post type
         */
        public function create_custom_post_type() {
            // Security check
            if (!isset($_POST['academy_nonce']) || !wp_verify_nonce($_POST['academy_nonce'], 'academy_create_cpt')) {
                wp_die('Permission denied');
            }

            // Permission check
            if (!current_user_can('manage_options')) {
                wp_die('Permission denied');
            }

            // Get user input
            $cpt_name = sanitize_text_field($_POST['cpt_name']);
            $attachment_id = isset($_POST['cpt_image_id']) ? intval($_POST['cpt_image_id']) : 0;
            if ($attachment_id == 0) {
                wp_die('A featured image is required.');
            }

            // Generate unique ID
            $counter = get_option('academy_cpt_counter', 1);
            $unique_id = 'c-' . $counter;
            update_option('academy_cpt_counter', $counter + 1);

            // Store new CPT
            $custom_post_types = get_option('academy_custom_post_types', []);
            $custom_post_types[] = [
                'id' => $unique_id,
                'name' => $cpt_name,
                'slug' => sanitize_title($cpt_name),
                'image' => $attachment_id,
            ];
            update_option('academy_custom_post_types', $custom_post_types);

            // Register and sync
            $this->register_single_custom_post_type($cpt_name, sanitize_title($cpt_name));
            flush_rewrite_rules();
            $this->enable_elementor_support();

            wp_redirect(admin_url('admin.php?page=academy&tab=courses&cpt_created=' . $cpt_name));
            exit;
        }

        /**
         * Register a single CPT on the fly
         */
        private function register_single_custom_post_type($name, $slug) {
            $labels = array(
                'name' => $name,
                'singular_name' => $name,
                'menu_name' => $name,
                'add_new' => 'Nieuwe toevoegen',
                'edit_item' => 'Bewerk ' . $name,
                'view_item' => 'Bekijk ' . $name,
                'all_items' => 'Alle ' . $name,
                'search_items' => 'Zoek ' . $name,
                'not_found' => 'Geen ' . $name . ' gevonden.',
            );

            $args = array(
                'labels' => $labels,
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'has_archive' => true,
                'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'],
                'rewrite' => ['slug' => $slug],
                'menu_icon' => 'dashicons-book-alt',
                'menu_position' => 4,
            );

            register_post_type($slug, $args);

            if (function_exists('pll_register_string')) {
                pll_register_string($slug . '_name', $name, 'Custom Post Types');
            }
        }

        /**
         * Handles editing an existing CPT (including renaming slug and posts)
         */
        public function edit_custom_post_type() {
            global $wpdb;

            // Security check
            if (!isset($_POST['academy_nonce']) || !wp_verify_nonce($_POST['academy_nonce'], 'academy_edit_cpt')) {
                wp_die('Permission denied');
            }

            if (!current_user_can('manage_options')) {
                wp_die('Permission denied');
            }

            $old_cpt_name = sanitize_text_field($_POST['old_cpt_name']);
            $new_cpt_name = sanitize_text_field($_POST['new_cpt_name']);
            $new_cpt_slug = sanitize_title($new_cpt_name);
            $attachment_id = isset($_POST['cpt_image_id']) ? intval($_POST['cpt_image_id']) : 0;

            $custom_post_types = get_option('academy_custom_post_types', []);
            foreach ($custom_post_types as &$cpt) {
                if ($cpt['name'] === $old_cpt_name) {
                    $old_cpt_slug = $cpt['slug'];

                    // Register old CPT temporarily for DB transition
                    register_post_type($old_cpt_slug, [
                        'public' => true,
                        'show_ui' => true,
                        'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'],
                        'rewrite' => ['slug' => $old_cpt_slug],
                    ]);

                    // Update DB and options
                    $cpt['name'] = $new_cpt_name;
                    $cpt['slug'] = $new_cpt_slug;
                    $cpt['image'] = $attachment_id;

                    $wpdb->update(
                        $wpdb->posts,
                        ['post_type' => $new_cpt_slug],
                        ['post_type' => $old_cpt_slug]
                    );

                    register_post_type($new_cpt_slug, [
                        'public' => true,
                        'show_ui' => true,
                        'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'],
                        'rewrite' => ['slug' => $new_cpt_slug],
                    ]);

                    break;
                }
            }

            update_option('academy_custom_post_types', $custom_post_types);
            flush_rewrite_rules();
            $this->enable_elementor_support();

            // Soft plugin reload
            deactivate_plugins('academy-plugin/academy-plugin.php');
            activate_plugin('academy-plugin/academy-plugin.php');

            wp_redirect(admin_url('admin.php?page=academy&tab=courses&cpt_edited=' . $new_cpt_name));
            exit;
        }

        /**
         * Deletes a custom post type from the options
         */
        public function delete_custom_post_type() {
            if (!isset($_POST['academy_delete_nonce']) || !wp_verify_nonce($_POST['academy_delete_nonce'], 'academy_delete_cpt')) {
                wp_die('Permission denied');
            }

            if (!current_user_can('manage_options')) {
                wp_die('Permission denied');
            }

            $cpt_name = sanitize_text_field($_POST['cpt_name']);
            $confirm_name = sanitize_text_field($_POST['confirm_name']);

            if ($cpt_name !== $confirm_name) {
                wp_die('The confirmation name does not match the post type name.');
            }

            $custom_post_types = get_option('academy_custom_post_types', []);
            $custom_post_types = array_filter($custom_post_types, fn($cpt) => $cpt['name'] !== $cpt_name);
            update_option('academy_custom_post_types', $custom_post_types);

            flush_rewrite_rules();

            wp_redirect(admin_url('admin.php?page=academy&tab=courses&cpt_deleted=' . $cpt_name));
            exit;
        }

        /**
         * Registers all saved custom post types
         */
        public function register_custom_post_types() {
            $custom_post_types = get_option('academy_custom_post_types', []);
            foreach ($custom_post_types as $cpt) {
                if (!empty($cpt['slug']) && !empty($cpt['name'])) {
                    $this->register_single_custom_post_type($cpt['name'], $cpt['slug']);
                }
            }
        }

        /**
         * Adds custom post types to Elementor support
         */
        public function enable_elementor_support() {
            $custom_post_types = get_option('academy_custom_post_types', []);
            $supported = get_option('elementor_cpt_support', []);

            foreach ($custom_post_types as $cpt) {
                if (!in_array($cpt['slug'], $supported)) {
                    $supported[] = $cpt['slug'];
                }
            }

            update_option('elementor_cpt_support', $supported);
        }

        /**
         * Helper for handling direct file uploads (unused but reusable)
         */
        private function upload_image($file) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $attachment_id = media_handle_upload('cpt_image', 0);

            if (is_wp_error($attachment_id)) {
                wp_die('Image upload failed: ' . $attachment_id->get_error_message());
            }

            return $attachment_id;
        }
    }
}

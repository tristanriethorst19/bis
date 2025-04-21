<?php

// Prevent direct access
if (!class_exists('Academy_Profile_Customizations')) {
    class Academy_Profile_Customizations {

        public function __construct() {
            // Remove dashboard menu item for subscribers
            add_action('admin_menu', [$this, 'customize_admin_menu'], 999);

            // Remove certain sections from the user profile page
            add_action('admin_init', [$this, 'customize_user_profile_page']);

            // Add CSS to hide profile options for subscribers
            add_action('admin_head', [$this, 'custom_profile_styles']);

            // Modify the admin bar (remove logo and adjust links)
            add_action('wp_before_admin_bar_render', [$this, 'customize_admin_bar']);

            // Hide admin bar on frontend for subscribers
            add_filter('show_admin_bar', [$this, 'disable_admin_bar_for_subscribers']);
        }

        /**
         * Remove unwanted admin menu items for subscribers
         */
        public function customize_admin_menu() {
            if (current_user_can('subscriber')) {
                remove_menu_page('index.php'); // Dashboard
            }
        }

        /**
         * Optional method (not used here) to redirect users to homepage
         */
        public function redirect_to_homepage() {
            wp_redirect(home_url());
            exit;
        }

        /**
         * Customize the profile edit page:
         * - Remove color scheme selector
         * - Remove application passwords section
         */
        public function customize_user_profile_page() {
            remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');
            remove_action('show_user_profile', 'wp_application_passwords_show_user_profile');
            remove_action('edit_user_profile', 'wp_application_passwords_show_user_profile');
        }

        /**
         * Hide profile elements using CSS for subscribers
         */
        public function custom_profile_styles() {
            if (current_user_can('subscriber')) {
                echo '<style>
                    .user-admin-color-wrap,
                    #application-passwords-section,
                    tr.show-admin-bar,
                    tr.user-language-wrap {
                        display: none;
                    }
                </style>';
            }
        }

        /**
         * Customize admin bar:
         * - Remove WordPress logo
         * - Add a "Back to Academy" link
         * - Remove default "Visit Site" submenu
         */
        public function customize_admin_bar() {
            global $wp_admin_bar;

            // Remove the WP logo
            $wp_admin_bar->remove_node('wp-logo');

            // Replace "Site Name" with a custom label and URL
            $wp_admin_bar->add_node([
                'id'    => 'site-name',
                'title' => 'Terug naar de Academy',
                'href'  => home_url(),
            ]);

            // Remove sub-item "View Site"
            $wp_admin_bar->remove_node('view-site');
        }

        /**
         * Hide the frontend admin bar for subscribers
         */
        public function disable_admin_bar_for_subscribers($show_admin_bar) {
            if (current_user_can('subscriber') && !is_admin()) {
                return false;
            }
            return $show_admin_bar;
        }
    }
}

// Initialize the class immediately
new Academy_Profile_Customizations();

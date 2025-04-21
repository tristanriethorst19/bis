<?php
// Block direct file access
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Academy_Access_Restriction')) {
    class Academy_Access_Restriction {

        public function __construct() {
            // Restrict access to courses and handle redirection logic
            add_action('template_redirect', [$this, 'restrict_access']);

            // Redirect archive views to the first post of the archive
            add_action('template_redirect', [$this, 'redirect_to_first_post_in_archive'], 20);

            // Redirect users after login based on previous login page
            add_action('wp_login', [$this, 'redirect_after_login'], 10, 2);

            // Redirect users to login page after logging out
            add_action('wp_logout', [$this, 'redirect_after_logout']);
        }

        /**
         * Restricts access to content based on login and user-course permissions
         */
        public function restrict_access() {
            // Redirect logged-in users *away* from the login page (ID 203)
            if (is_user_logged_in() && is_page(203)) {
                wp_redirect(get_permalink(7)); // Redirect to dashboard or home
                exit;
            }

            // Redirect non-logged-in users to the login page unless they're already there (203 or 1122)
            if (!is_user_logged_in() && !is_page([203, 1122])) {
                wp_redirect(get_permalink(203));
                exit;
            }

            // Store login origin (for multilingual redirect logic after login)
            if (!is_user_logged_in() && is_page(1122)) {
                setcookie('login_page', '1122', time() + 3600, COOKIEPATH, COOKIE_DOMAIN);
            } elseif (!is_user_logged_in() && is_page(203)) {
                setcookie('login_page', '203', time() + 3600, COOKIEPATH, COOKIE_DOMAIN);
            }

            // Allow administrators to bypass all access restrictions
            if (current_user_can('administrator')) {
                return;
            }

            // Restrict access to individual course content (single post view)
            $custom_post_types = get_option('academy_custom_post_types', []);
            $post_type_slugs = array_map(function($cpt) {
                return $cpt['slug'];
            }, $custom_post_types);

            global $post;
            $course_id = null;

            // Restrict access to individual course content pages
            if (is_singular($post_type_slugs) && $post) {
                $course_id = $this->get_course_id_by_post_id($post->ID);

                if (!$this->user_has_access_to_course($course_id)) {
                    wp_redirect(get_permalink(208)); // Restricted access page
                    exit;
                }
            }

            // Restrict access to archive listings (e.g., /cursus-x/)
            if (is_post_type_archive($post_type_slugs)) {
                $post_type = get_query_var('post_type');
                foreach ($custom_post_types as $cpt) {
                    if ($cpt['slug'] === $post_type) {
                        $course_id = $cpt['id'];
                        break;
                    }
                }

                if (!$this->user_has_access_to_course($course_id)) {
                    wp_redirect(get_permalink(208)); // Restricted access page
                    exit;
                }
            }
        }

        /**
         * Automatically redirect archive pages to their first post
         * (e.g., redirect /cursus-x/ to the first lesson)
         */
        public function redirect_to_first_post_in_archive() {
            if (is_post_type_archive()) {
                global $wp_query;

                $args = [
                    'post_type'      => get_post_type(),
                    'posts_per_page' => 1,
                    'orderby'        => 'date',
                    'order'          => 'ASC'
                ];

                $query = new WP_Query($args);

                if ($query->have_posts()) {
                    $query->the_post();
                    wp_redirect(get_permalink(get_the_ID()));
                    exit;
                }

                wp_reset_postdata();
            }
        }

        /**
         * Redirect user after successful login, based on original login page
         */
        public function redirect_after_login($user_login, $user) {
            if (isset($_COOKIE['login_page'])) {
                if ($_COOKIE['login_page'] == '1122') {
                    wp_redirect(get_permalink(1016)); // English version
                } else {
                    wp_redirect(get_permalink(7));    // Default (Dutch)
                }
                setcookie('login_page', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN); // Clear cookie
            } else {
                wp_redirect(get_permalink(7)); // Default fallback
            }
            exit;
        }

        /**
         * Redirect to login page after logout
         */
        public function redirect_after_logout() {
            wp_redirect(get_permalink(203));
            exit;
        }

        /**
         * Get the course ID based on post ID
         */
        public function get_course_id_by_post_id($post_id) {
            $custom_post_types = get_option('academy_custom_post_types', []);
            foreach ($custom_post_types as $cpt) {
                if (get_post_type($post_id) == $cpt['slug']) {
                    return $cpt['id'];
                }
            }
            return null;
        }

        /**
         * Determine if current user has access to given course ID
         */
        public function user_has_access_to_course($course_id) {
            if (!$course_id) return false;

            $user_id = get_current_user_id();
            $user_courses = get_user_meta($user_id, 'user_courses', true); // Stored as comma-separated string

            if ($user_courses) {
                $courses_array = explode(',', $user_courses);
                if (in_array($course_id, $courses_array)) {
                    return true;
                }
            }

            return false;
        }
    }

    // Instantiate the class immediately
    new Academy_Access_Restriction();
}
?>

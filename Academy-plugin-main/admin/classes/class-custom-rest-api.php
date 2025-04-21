<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly for security.
}

/**
 * Class Custom_Rest_API
 * 
 * This class registers a custom REST API endpoint to assign courses to users.
 * It supports both creating a new user and updating an existing one, as well
 * as managing course access via user meta.
 */
class Custom_Rest_API {

    public function __construct() {
        // Register REST routes when the API is initialized
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Registers the /(restricted) endpoint under /wp-json/(restricted)
     */
    public function register_routes() {
        register_rest_route('*****', '*****', array(
            'methods'  => 'POST',
            'callback' => array($this, 'assign_courses_to_user'),
            'permission_callback' => '__return_true', // Allow open access (for dev/testing); should be restricted in production
        ));
    }

    /**
     * Handles the course assignment to a user.
     * 
     * If the user does not exist, it creates a new user.
     * It then assigns the specified course to the user.
     *
     * @param WP_REST_Request $request The REST request object
     * @return WP_REST_Response
     */
    public function assign_courses_to_user(WP_REST_Request $request) {
        $data = $request->get_json_params();

        // Sanitize incoming data
        $email       = sanitize_email($data['email']);
        $first_name  = sanitize_text_field($data['first_name']);
        $last_name   = sanitize_text_field($data['last_name']);
        $new_course_id = sanitize_text_field($data['user_courses']); // Course ID (single, string)

        // Check if the user already exists by email
        if (email_exists($email)) {
            $user = get_user_by('email', $email);
        } else {
            // Create a new user with a generated password
            $password = wp_generate_password();
            $user_id = wp_create_user($email, $password, $email);

            if (is_wp_error($user_id)) {
                return new WP_REST_Response(array('status' => 'error', 'message' => 'Error creating user'), 500);
            }

            $user = get_user_by('id', $user_id);

            // Send notification to user to set their password
            wp_new_user_notification($user_id, null, 'user');
        }

        // Update user's first and last name
        wp_update_user(array(
            'ID'         => $user->ID,
            'first_name' => $first_name,
            'last_name'  => $last_name
        ));

        // Retrieve existing assigned courses from user meta
        $existing_courses = get_user_meta($user->ID, 'user_courses', true);
        if ($existing_courses) {
            $courses = explode(',', $existing_courses);
            if (!in_array($new_course_id, $courses)) {
                $courses[] = $new_course_id;
            }
        } else {
            $courses = [$new_course_id];
        }

        // Save the updated course list to user meta
        $courses_string = implode(',', $courses);
        update_user_meta($user->ID, 'user_courses', $courses_string);

        // Return success response
        return new WP_REST_Response(array('status' => 'success'), 200);
    }
}

<?php

// Prevent redefinition of the class
if (!class_exists('Academy_Course_Access')) {
    class Academy_Course_Access {

        public function __construct() {
            // Show course access checkboxes on the user profile page (top position using priority 0)
            add_action('edit_user_profile', [$this, 'show_user_courses'], 0);
            add_action('show_user_profile', [$this, 'show_user_courses'], 0);
            
            // Save selected courses when a user profile is updated
            add_action('personal_options_update', [$this, 'save_user_courses']);
            add_action('edit_user_profile_update', [$this, 'save_user_courses']);
        }

        /**
         * Assigns a course to a specific user
         */
        public function assign_course_to_user($user_id, $course_id) {
            $user_courses = get_user_meta($user_id, 'user_courses', true);
            if (!$user_courses) {
                $user_courses = '';
            }

            $courses_array = explode(',', $user_courses);

            // Add only if not already assigned
            if (!in_array($course_id, $courses_array)) {
                $courses_array[] = $course_id;
                $user_courses = implode(',', $courses_array);
                update_user_meta($user_id, 'user_courses', $user_courses);
            }
        }

        /**
         * Removes a course from the user if it exists
         */
        public function remove_course_from_user($user_id, $course_id) {
            $user_courses = get_user_meta($user_id, 'user_courses', true);

            if ($user_courses) {
                $courses_array = explode(',', $user_courses);
                if (in_array($course_id, $courses_array)) {
                    // Filter the removed ID out
                    $courses_array = array_diff($courses_array, [$course_id]);
                    $user_courses = implode(',', $courses_array);
                    update_user_meta($user_id, 'user_courses', $user_courses);
                }
            }
        }

        /**
         * Displays a checkbox list of courses in the user profile page (only for administrators)
         */
        public function show_user_courses($user) {
            // Avoid duplicate rendering due to multiple hooks
            static $ran = false;
            if ($ran) return;
            $ran = true;

            // Restrict visibility to admins
            if (!current_user_can('administrator')) {
                return;
            }

            $courses = get_option('academy_custom_post_types', []);
            $user_courses = get_user_meta($user->ID, 'user_courses', true);
            $user_courses = $user_courses ? explode(',', $user_courses) : [];

            ?>
            <h3>Assigned Courses</h3>
            <table class="form-table">
                <tr>
                    <th><label for="user_courses">Courses</label></th>
                    <td>
                        <?php foreach ($courses as $course) : ?>
                            <label>
                                <input type="checkbox"
                                       name="user_courses[]"
                                       value="<?php echo esc_attr($course['id']); ?>"
                                       <?php checked(in_array($course['id'], $user_courses)); ?>>
                                <?php echo esc_html($course['name']); ?>
                            </label><br>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
            <?php
        }

        /**
         * Saves selected courses to the user_meta field `user_courses`
         */
        public function save_user_courses($user_id) {
            // Permission check
            if (!current_user_can('edit_user', $user_id)) {
                return false;
            }

            $user_courses = isset($_POST['user_courses']) ? array_map('sanitize_text_field', $_POST['user_courses']) : [];
            $user_courses_string = implode(',', $user_courses);

            update_user_meta($user_id, 'user_courses', $user_courses_string);
        }
    }
}

// Instantiate the class on load
new Academy_Course_Access();

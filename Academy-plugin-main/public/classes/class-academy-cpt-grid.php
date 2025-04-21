<?php

// Only define the class if it hasn’t already been declared (prevents conflicts)
if (!class_exists('Academy_CPT_Grid')) {

    /**
     * Class Academy_CPT_Grid
     *
     * Responsible for rendering a frontend grid of all custom post types
     * registered by the Academy Plugin (via the admin UI).
     *
     * The grid uses a shortcode: [academy_custom_post_grid]
     */
    class Academy_CPT_Grid {

        /**
         * Constructor: Hook into WordPress by registering the shortcode.
         */
        public function __construct() {
            add_shortcode('academy_custom_post_grid', [$this, 'render_custom_post_grid']);
        }

        /**
         * Render the HTML output of the shortcode.
         * 
         * This function retrieves all custom post types (CPTs)
         * stored in the WordPress options table by the plugin,
         * and displays them in a simple visual grid.
         *
         * @return string HTML markup
         */
        public function render_custom_post_grid() {
            // Retrieve custom post type definitions from plugin settings
            $custom_post_types = get_option('academy_custom_post_types', []);

            // Handle case where no CPTs have been defined
            if (empty($custom_post_types)) {
                return '<p>No custom post types found.</p>';
            }

            // Begin capturing output
            ob_start();
            ?>
            <div class="academy-custom-post-grid">
                <?php foreach ($custom_post_types as $cpt): ?>
                    <?php
                    // Get archive URL and preview image (ACF image ID expected)
                    $archive_url = get_post_type_archive_link($cpt['slug']);
                    $image = wp_get_attachment_image_src($cpt['image'], 'medium'); // e.g. 300x300 thumbnail
                    ?>
                    <div class="academy-custom-post-item">
                        <?php if ($image): ?>
                            <a href="<?php echo esc_url($archive_url); ?>">
                                <img src="<?php echo esc_url($image[0]); ?>" width="300" height="300" alt="<?php echo esc_attr($cpt['name']); ?>">
                            </a>
                        <?php endif; ?>
                        <h6>
                            <a href="<?php echo esc_url($archive_url); ?>">
                                <?php echo esc_html($cpt['name']); ?>
                            </a>
                        </h6>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php

            // Return the captured HTML
            return ob_get_clean();
        }
    }

}

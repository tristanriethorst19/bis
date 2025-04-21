<?php

// Prevent class redeclaration in case of duplicate loading
if (!class_exists('Academy_Module_Grid')) {

    /**
     * Class Academy_Module_Grid
     *
     * This class registers a shortcode that renders a visual grid of all posts
     * belonging to the current post type. This allows dynamic display of "modules"
     * or grouped content on archive-like pages or single views.
     *
     * Usage: [academy_module_grid]
     */
    class Academy_Module_Grid {

        /**
         * Constructor: binds the shortcode to WordPress.
         */
        public function __construct() {
            add_shortcode('academy_module_grid', [$this, 'render_module_grid']);
        }

        /**
         * Renders a grid of posts matching the post type of the current page.
         *
         * This can be reused for any post type, as it adapts dynamically to
         * the post type being viewed when the shortcode is rendered.
         *
         * @return string HTML output of the grid
         */
        public function render_module_grid() {
            global $post;

            // Determine the post type of the current context (e.g., 'course', 'lesson')
            $post_type = get_post_type($post);

            // Set up a custom WP_Query to fetch all published posts of that type
            $args = [
                'post_type'      => $post_type,
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'order'          => 'ASC'
            ];
            $query = new WP_Query($args);

            // Fallback message if no results found
            if (!$query->have_posts()) {
                return '<p>No posts found.</p>';
            }

            // Start output buffering
            ob_start();
            ?>
            <div class="academy-module-grid">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="academy-module-item">
                        <?php if (has_post_thumbnail()): ?>
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium'); ?>
                            </a>
                        <?php endif; ?>
                        <h6><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h6>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php

            // Reset the global $post to its original state
            wp_reset_postdata();

            // Return the rendered content
            return ob_get_clean();
        }
    }

    // Instantiate the class so the shortcode becomes available
    new Academy_Module_Grid();
}

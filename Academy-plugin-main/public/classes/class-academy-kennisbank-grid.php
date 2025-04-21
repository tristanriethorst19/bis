<?php

// Only define the class if it hasn't been defined yet (to avoid conflicts)
if (!class_exists('Academy_Kennisbank_Grid')) {

    /**
     * Class Academy_Kennisbank_Grid
     *
     * Responsible for rendering a frontend grid of all published posts
     * from the custom post type `kennisbank` (used as a knowledge base).
     *
     * This grid is output via the [academy_kennisbank_grid] shortcode.
     */
    class Academy_Kennisbank_Grid {

        /**
         * Constructor: registers the shortcode on init.
         */
        public function __construct() {
            add_shortcode('academy_kennisbank_grid', [$this, 'render_kennisbank_grid']);
        }

        /**
         * Renders a grid layout for all published `kennisbank` posts.
         *
         * @return string HTML output of the knowledge base grid.
         */
        public function render_kennisbank_grid() {
            // Set up a WP_Query to fetch all published posts of the 'kennisbank' post type
            $args = [
                'post_type'      => 'kennisbank',
                'posts_per_page' => -1,       // Fetch all available items
                'post_status'    => 'publish',
                'order'          => 'ASC'     // Ascending order by default (can be extended later)
            ];
            $query = new WP_Query($args);

            // If there are no results, display a fallback message
            if (!$query->have_posts()) {
                return '<p>Geen berichten gevonden.</p>';
            }

            // Capture output into buffer
            ob_start();
            ?>
            <div class="academy-module-grid">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="academy-module-item">
                        <?php if (has_post_thumbnail()): ?>
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium'); // Display post image ?>
                            </a>
                        <?php endif; ?>
                        <h6><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h6>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php

            // Reset global $post object to avoid interfering with other templates
            wp_reset_postdata();

            // Return the generated output
            return ob_get_clean();
        }
    }

    // Instantiate the class to ensure the shortcode is registered
    new Academy_Kennisbank_Grid();
}

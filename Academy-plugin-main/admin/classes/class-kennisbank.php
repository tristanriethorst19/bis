<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class Academy_Kennisbank
 *
 * Registers the custom post type "kennisbank" (knowledge base) and sets up a custom permalink structure.
 */
if (!class_exists('Academy_Kennisbank')) {
    class Academy_Kennisbank {

        public function __construct() {
            // Hook into the 'init' action to register the custom post type and rewrite rules
            add_action('init', [$this, 'register_kennisbank_post_type']);
            add_action('init', [$this, 'add_custom_rewrite_rules']);
        }

        /**
         * Register the custom post type 'kennisbank'
         */
        public function register_kennisbank_post_type() {
            // Labels shown in the admin UI
            $labels = [
                'name'                  => _x('Kennisbank', 'Post Type General Name', 'textdomain'),
                'singular_name'         => _x('Kennisbank', 'Post Type Singular Name', 'textdomain'),
                'menu_name'             => __('Kennisbank', 'textdomain'),
                'name_admin_bar'        => __('Kennisbank', 'textdomain'),
                'archives'              => __('Kennisbank Archieven', 'textdomain'),
                'attributes'            => __('Kennisbank Attributen', 'textdomain'),
                'parent_item_colon'     => __('Bovenliggende Kennisbank:', 'textdomain'),
                'all_items'             => __('Alle Kennisbank Items', 'textdomain'),
                'add_new_item'          => __('Nieuw Kennisbank Item toevoegen', 'textdomain'),
                'add_new'               => __('Nieuwe toevoegen', 'textdomain'),
                'new_item'              => __('Nieuw Kennisbank Item', 'textdomain'),
                'edit_item'             => __('Kennisbank Item bewerken', 'textdomain'),
                'update_item'           => __('Kennisbank Item bijwerken', 'textdomain'),
                'view_item'             => __('Kennisbank Item bekijken', 'textdomain'),
                'view_items'            => __('Kennisbank Items bekijken', 'textdomain'),
                'search_items'          => __('Zoek Kennisbank Item', 'textdomain'),
                'not_found'             => __('Niet gevonden', 'textdomain'),
                'not_found_in_trash'    => __('Niet gevonden in prullenbak', 'textdomain'),
                'featured_image'        => __('Uitgelichte afbeelding', 'textdomain'),
                'set_featured_image'    => __('Stel uitgelichte afbeelding in', 'textdomain'),
                'remove_featured_image' => __('Verwijder uitgelichte afbeelding', 'textdomain'),
                'use_featured_image'    => __('Gebruik als uitgelichte afbeelding', 'textdomain'),
                'insert_into_item'      => __('Invoegen in Kennisbank item', 'textdomain'),
                'uploaded_to_this_item' => __('Geüpload naar dit Kennisbank item', 'textdomain'),
                'items_list'            => __('Kennisbank items lijst', 'textdomain'),
                'items_list_navigation' => __('Navigatie van Kennisbank items lijst', 'textdomain'),
                'filter_items_list'     => __('Filter Kennisbank items lijst', 'textdomain'),
            ];

            // Arguments to register the post type
            $args = [
                'label'                 => __('Kennisbank', 'textdomain'),
                'description'           => __('Een custom post type voor de Kennisbank', 'textdomain'),
                'labels'                => $labels,
                'supports'              => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
                'hierarchical'          => false,
                'public'                => true,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'menu_position'         => 5,
                'menu_icon'             => 'dashicons-welcome-learn-more',
                'show_in_admin_bar'     => true,
                'show_in_nav_menus'     => true,
                'can_export'            => true,
                'has_archive'           => 'kennisbank-archief', // Custom archive slug
                'exclude_from_search'   => false,
                'publicly_queryable'    => true,
                'rewrite'               => ['slug' => 'kennisbank'], // Pretty permalinks for posts
                'capability_type'       => 'post',
            ];

            register_post_type('kennisbank', $args);
        }

        /**
         * Adds a custom rewrite rule to ensure permalinks work with the /kennisbank/ structure.
         */
        public function add_custom_rewrite_rules() {
            add_rewrite_rule(
                '^kennisbank/([^/]+)?', // Match /kennisbank/post-name
                'index.php?post_type=kennisbank&name=$matches[1]', // Internally route to the correct post
                'top' // Add rule at the top of the rewrite array
            );
        }
    }

    // Initialize the class
    new Academy_Kennisbank();
}

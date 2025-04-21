<?php
/**
 * Plugin Name: Academy Plugin
 * Description: A plugin to create custom post types from the admin panel and dynamically adjust menus.
 * Version: 1.0
 * Author: Tristan Riethorst
 */

// Security check — exit if the file is accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define constants for reuse throughout the plugin
define('ACADEMY_PLUGIN_DIR', plugin_dir_path(__FILE__)); // Full server path
define('ACADEMY_PLUGIN_URL', plugin_dir_url(__FILE__));  // Public-facing URL

/**
 * Include all modular classes responsible for admin logic
 * These are organized in `admin/classes/` and cover core functionality
 */
require_once ACADEMY_PLUGIN_DIR . 'admin/classes/class-core.php';               // Core setup and hooks
require_once ACADEMY_PLUGIN_DIR . 'admin/classes/class-cpt-handler.php';       // Handles custom post type creation
require_once ACADEMY_PLUGIN_DIR . 'admin/classes/class-shortcode-handler.php'; // Registers frontend shortcodes
require_once ACADEMY_PLUGIN_DIR . 'admin/classes/class-custom-rest-api.php';   // Adds custom REST endpoints
require_once ACADEMY_PLUGIN_DIR . 'admin/classes/class-acces-restriction.php'; // Access restriction rules (per role/user)
require_once ACADEMY_PLUGIN_DIR . 'admin/classes/class-course-acces.php';      // Course-level access control
require_once ACADEMY_PLUGIN_DIR . 'admin/classes/class-user-profiles.php';     // User profile customizations
require_once ACADEMY_PLUGIN_DIR . 'admin/classes/class-custom-emails.php';     // Email behavior overrides
require_once ACADEMY_PLUGIN_DIR . 'admin/classes/class-kennisbank.php';        // Knowledge base (Kennisbank) admin logic

/**
 * Include public-facing classes that render dynamic content on the frontend
 */
require_once ACADEMY_PLUGIN_DIR . 'public/classes/class-academy-cpt-grid.php';        // Grid output for CPTs
require_once ACADEMY_PLUGIN_DIR . 'public/classes/class-academy-module-grid.php';     // Grid for "modules" (course units)
require_once ACADEMY_PLUGIN_DIR . 'public/classes/class-academy-kennisbank-grid.php'; // Grid for knowledge base items

/**
 * Initialize all plugin classes after all plugins are loaded
 * Uses conditional `class_exists()` to prevent fatal errors if class not found
 */
function academy_plugin_init() {
    if (class_exists('Academy_Core')) {
        new Academy_Core();
    }
    if (class_exists('Academy_CPT_Handler')) {
        new Academy_CPT_Handler();
    }
    if (class_exists('Shortcode_Handler')) {
        new Shortcode_Handler();
    }
    if (class_exists('Academy_CPT_Grid')) {
        new Academy_CPT_Grid();
    }
    if (class_exists('Custom_Rest_API')) {
        new Custom_Rest_API();
    }
    if (class_exists('Academy_Access_Restriction')) {
        new Academy_Access_Restriction();
    }
    if (class_exists('Academy_Course_Access')) {
        new Academy_Course_Access();
    }
    if (class_exists('Academy_Profile_Customizations')) {
        new Academy_Profile_Customizations();
    }
    if (class_exists('Custom_Email_Modifications')) {
        new Custom_Email_Modifications();
    }
    if (class_exists('Academy_Module_Grid')) {
        new Academy_Module_Grid();
    }
    if (class_exists('Academy_Kennisbank')) {
        new Academy_Kennisbank();
    }
    if (class_exists('Academy_Kennisbank_Grid')) {
        new Academy_Kennisbank_grid(); // Note: Possible typo in class name casing?
    }
}
add_action('plugins_loaded', 'academy_plugin_init');

/**
 * Integrate custom post types with Polylang for multilingual support
 * This allows dynamically created CPTs to work with Polylang settings
 */
add_filter('pll_get_post_types', 'academy_polylang_custom_post_types', 10, 2);

function academy_polylang_custom_post_types($post_types, $is_settings) {
    // Fetch custom post types stored by the plugin in options table
    $custom_post_types = get_option('academy_custom_post_types', []);

    foreach ($custom_post_types as $cpt) {
        $post_types[$cpt['slug']] = $cpt['slug'];
    }

    return $post_types;
}

/**
 * Plugin activation hook
 * Registers custom post types and flushes rewrite rules to make them immediately accessible
 */
register_activation_hook(__FILE__, 'academy_plugin_activate');

function academy_plugin_activate() {
    // Re-register post types before flushing
    $handler = new Academy_CPT_Handler();
    $handler->register_custom_post_types();

    flush_rewrite_rules(); // Prevents 404 errors on custom post type URLs
}

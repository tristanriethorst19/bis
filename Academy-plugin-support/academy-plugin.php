<?php
/**
 * Plugin Name: E-Learning integratie
 * Description: Niet verwijderen! Deze plugin verzorgt de automatische integratie met het e-learning platform. 
 * Version: 1.0
 * Author: Tristan Riethorst
 */

// Prevent direct access to the file for security
if (!defined('ABSPATH')) {
    exit;
}

// Define constants for the plugin directory and URL to use elsewhere in the plugin
define('ACADEMY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ACADEMY_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the core class that contains the main logic for the integration
require_once ACADEMY_PLUGIN_DIR . 'admin/classes/class-core.php';

// Initialize the plugin after all plugins are loaded
function academy_plugin_init() {
    // Only instantiate the core class if it exists
    if (class_exists('Core_Class')) {
        new Core_Class();
    }
}
add_action('plugins_loaded', 'academy_plugin_init');

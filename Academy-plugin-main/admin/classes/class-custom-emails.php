<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly to prevent unauthorized access.
}

/**
 * Class Custom_Email_Modifications
 * 
 * This class modifies the default WordPress email sender name and address
 * for all outgoing emails using the wp_mail function.
 */
class Custom_Email_Modifications {
    
    public function __construct() {
        // Filter the "From" email address
        add_filter('wp_mail_from', array($this, 'custom_wp_mail_from'));

        // Filter the "From" name
        add_filter('wp_mail_from_name', array($this, 'custom_wp_mail_from_name'));
    }

    /**
     * Override the default "From" email address in WordPress.
     *
     * @param string $original_email_address The original sender email.
     * @return string The new sender email address.
     */
    public function custom_wp_mail_from($original_email_address) {
        return 'info@bewegenisleven.nl'; // Replace with the desired sender email address
    }

    /**
     * Override the default "From" name in WordPress.
     *
     * @param string $original_email_from The original sender name.
     * @return string The new sender name.
     */
    public function custom_wp_mail_from_name($original_email_from) {
        return 'Bewegen is Leven Academy'; // Replace with the desired sender name
    }
}

// Initialize the class to apply the filters
new Custom_Email_Modifications();

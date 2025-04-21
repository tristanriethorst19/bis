<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Core_Class
 * 
 * This class handles the integration between the WooCommerce webshop
 * and the e-learning platform. It:
 * 1. Automatically completes orders that contain e-learning products.
 * 2. Sends user and course information to the academy website after purchase.
 */
class Core_Class {

    public function __construct() {
        // When a WooCommerce order is placed (thank you page), try auto-completing it if needed
        add_action('woocommerce_thankyou', array($this, 'auto_complete_elearning_orders'));

        // When a WooCommerce order is marked as completed, assign the purchased courses
        add_action('woocommerce_order_status_completed', array($this, 'assign_courses_to_user_on_order_completion'), 10, 1);
    }

    /**
     * Automatically completes orders that contain e-learning products
     * by checking if any item in the order is in the 'academy' category.
     */
    public function auto_complete_elearning_orders($order_id) {
        error_log('auto_complete_elearning_orders called');

        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('Order not found: ' . $order_id);
            return;
        }

        $e_learning_category_slug = 'academy';
        $has_elearning_product = false;

        // Loop through the order items to check for e-learning products
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if (has_term($e_learning_category_slug, 'product_cat', $product_id)) {
                $has_elearning_product = true;
                break;
            }
        }

        // If found, automatically mark order as completed
        if ($has_elearning_product) {
            error_log('Order ' . $order_id . ' contains e-learning products. Setting status to completed.');
            $order->update_status('completed');
        } else {
            error_log('Order ' . $order_id . ' does not contain e-learning products.');
        }
    }

    /**
     * Assigns e-learning courses to the user after an order is completed.
     * It checks the product category and retrieves course IDs via SKU.
     * Then sends this data to the main e-learning platform via REST API.
     */
    public function assign_courses_to_user_on_order_completion($order_id) {
        error_log('assign_courses_to_user_on_order_completion called');

        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('Order not found: ' . $order_id);
            return;
        }

        // Retrieve billing information
        $user_email  = $order->get_billing_email();
        $first_name  = $order->get_billing_first_name();
        $last_name   = $order->get_billing_last_name();

        if (!$user_email) {
            error_log('No billing email found for order: ' . $order_id);
            return;
        }

        error_log('Processing order for email: ' . $user_email);

        $courses = [];
        $e_learning_category_slug = 'academy';

        // Loop through each product in the order
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if (!$product_id) {
                error_log('No product ID found for item: ' . $item->get_id());
                continue;
            }

            // Check if product is in the e-learning category
            if (has_term($e_learning_category_slug, 'product_cat', $product_id)) {
                error_log('Product ' . $product_id . ' is in e-learning category');

                $product   = wc_get_product($product_id);
                $course_id = $product->get_sku(); // Assumes SKU = course ID

                if ($course_id) {
                    error_log('Found course ID ' . $course_id . ' for product ' . $product_id);
                    $courses[] = $course_id;
                } else {
                    error_log('No course ID (SKU) found for product: ' . $product_id);
                }
            } else {
                error_log('Product ' . $product_id . ' is not in e-learning category');
            }
        }

        // If there are courses, send user data + course IDs to academy platform
        if (!empty($courses)) {
            $courses_string = implode(',', $courses);
            error_log('Assigning courses: ' . $courses_string . ' to user: ' . $user_email);

            $response = wp_remote_post('restricted', [
                'body' => json_encode([
                    'email'        => $user_email,
                    'first_name'   => $first_name,
                    'last_name'    => $last_name,
                    'user_courses' => $courses_string
                ]),
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            if (is_wp_error($response)) {
                error_log('Error assigning courses: ' . $response->get_error_message());
            } else {
                $response_body = wp_remote_retrieve_body($response);
                error_log('Courses assigned successfully for ' . $user_email . '. Response: ' . $response_body);
            }
        } else {
            error_log('No courses to assign for order: ' . $order_id);
        }
    }
}

<?php

/**
 * Plugin Name: Custom Repeater Fields
 * Plugin URI: https://jhonalvincafe.com
 * Description: A custom Repeater for my own project
 * Version: 1.0.0
 * Author: Jhon Alvin Cafe IT Team
 * License: GPL v2 or later
 * Text Domain: custom-repeater-fields
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants with unique prefixes
define('CRF_PLUGIN_FILE', __FILE__);
define('CRF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CRF_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CRF_VERSION', '1.0.0');
define('CRF_QR_VERSION', '1.0.0');

// Include the main plugin class
require_once CRF_PLUGIN_PATH . 'includes/class-custom-repeater-fields.php';
// Initialize the plugin
function CRF_init()
{
    return Custom_Repeater_Fields::get_instance();
}
add_action('plugins_loaded', 'CRF_init');

// Activation and deactivation hooks
register_activation_hook(__FILE__, array('Custom_Repeater_Fields', 'activate'));
register_deactivation_hook(__FILE__, array('Custom_Repeater_Fields', 'deactivate'));
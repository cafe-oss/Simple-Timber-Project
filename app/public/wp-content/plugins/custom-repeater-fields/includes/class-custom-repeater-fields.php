<?php
/**
 * Main plugin class - Bootstrap only
 * File: includes/class-custom-repeater-fields.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Custom_Repeater_Fields
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies()
    {

        // Load our custom autoloader FIRST
        require_once CRF_PLUGIN_PATH . 'includes/class-autoloader.php';
        CRF_Autoloader::register(CRF_PLUGIN_PATH . 'includes/');
    }

    private function init_hooks()
    {
        new CRF_Admin();
        new CRF_Frontend();
    }

    public static function activate() {}

    public static function deactivate() {}

    public static function uninstall() {}

    public static function get_version()
    {
        return CRF_VERSION;
    }
}

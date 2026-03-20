<?php
// =============================================================================
// 1. AUTOLOADER CLASS - includes/class-autoloader.php
// =============================================================================

class CRF_Autoloader {
    private static $class_map = array(
        'CRF_Admin'    => 'class-crf-admin.php',
        'CRF_Frontend' => 'class-crf-frontend.php',
    );
    
    private static $base_path = '';
    
    public static function register($base_path = '') {
        self::$base_path = $base_path ?: CRF_PLUGIN_PATH . 'includes/';
        spl_autoload_register(array(__CLASS__, 'load'));
    }
    
    public static function load($class_name) {
        if (isset(self::$class_map[$class_name])) {
            $file_path = self::$base_path . self::$class_map[$class_name];
            if (file_exists($file_path)) {
                require_once $file_path;
                return true;
            }
        }
        return false;
    }

    /**
     * Get all registered classes
     * 
     * @return array List of registered class names
     */
    public static function get_registered_classes() {
        return array_keys(self::$class_map);
    }
    
    /**
     * Check if a class is registered
     * 
     * @param string $class_name Class name to check
     * @return bool Whether class is registered
     */
    public static function is_registered($class_name) {
        return isset(self::$class_map[$class_name]);
    }
}
<?php
/**
 * Timber starter-theme
 * https://github.com/timber/starter-theme
 */

// Load Composer dependencies.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) || file_exists(__DIR__ . '/src/StarterSite.php') ) {
    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/src/StarterSite.php';
} else {
    wp_die( 'Autoload file not found. Run composer install.' );
}

use Timber\Timber;

Timber::init();

// Sets the directories (inside your theme) to find .twig files.
Timber::$dirname = [ 'templates', 'views' ];

new StarterSite();

//enqueue style//
function jadev_enqueue_style()
{
    $version = wp_get_theme()->get('Version');
    wp_enqueue_style('jadev-style', get_template_directory_uri() . '/src/output.css', array(), $version, 'all');
}
add_action('wp_enqueue_scripts', 'jadev_enqueue_style');

function jadev_enqueue_scripts()
{
    wp_enqueue_script('jadev-main', get_stylesheet_directory_uri() .  '/assets/js/main.js', array('jadev-jquery'), false, true);
    wp_enqueue_script('jadev-jquery', 'https://code.jquery.com/jquery-3.7.1.slim.min.js', array(), false, true);
}
add_action('wp_enqueue_scripts', 'jadev_enqueue_scripts');

function jadev_menu()
{
    $locations = array(
        'primary' => "Primary Menu",
        'footer' => "Footer Menu"
    );
    register_nav_menus($locations);
}
add_action('init', 'jadev_menu');

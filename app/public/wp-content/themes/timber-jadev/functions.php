<?php

/**
 * Timber starter-theme
 * https://github.com/timber/starter-theme
 */

// Load Composer dependencies.
if (file_exists(__DIR__ . '/vendor/autoload.php') || file_exists(__DIR__ . '/src/StarterSite.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/src/StarterSite.php';
} else {
    wp_die('Autoload file not found. Run composer install.');
}

use Timber\Timber;
use Timber\Menu;

Timber::init();

// Sets the directories (inside your theme) to find .twig files.
Timber::$dirname = ['templates', 'views'];


new StarterSite();

//enqueue style//
function jadev_enqueue_style()
{
    $version = wp_get_theme()->get('Version');
    wp_enqueue_style('jadev-style', get_template_directory_uri() . '/src/output.css', array(), $version, 'all');
    wp_enqueue_style(
        'swiper-css',
        get_stylesheet_directory_uri() . '/assets/css/swiper-bundle.min.css',
        array(),
        '11.0.0'
    );
}
add_action('wp_enqueue_scripts', 'jadev_enqueue_style');

function jadev_enqueue_scripts()
{
    wp_enqueue_script('jadev-main', get_stylesheet_directory_uri() .  '/assets/js/main.js', array('jadev-jquery'), false, true);
    wp_enqueue_script('jadev-jquery', 'https://code.jquery.com/jquery-3.7.1.slim.min.js', array(), false, true);
    wp_enqueue_script(
        'swiper-element',
        get_stylesheet_directory_uri() . '/assets/js/swiper-element-bundle.min.js',
        array(),
        '11.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'jadev_enqueue_scripts');

function jadev_menu()
{
    $locations = array(
        'primary' => "Primary Menu",
        'about-footer' => "About Tonal Menu",
        'support-footer' => "Support Menu",
        'legal-footer' => "Legal Menu"
    );
    register_nav_menus($locations);
}
add_action('init', 'jadev_menu');

add_filter('timber/context', function ($context) {
    $locations = get_nav_menu_locations();

    $context['footer_menus'] = [];
    $context['header_menus'] = [];

    if (isset($locations['primary'])) {
        $term = wp_get_nav_menu_object($locations['primary']);
        $context['header_menus']['Primary'] = Menu::build($term);
    }

    if (isset($locations['about-footer'])) {
        $term = wp_get_nav_menu_object($locations['about-footer']);
        $context['footer_menus']['About Tonal'] = Menu::build($term);
    }

    if (isset($locations['support-footer'])) {
        $term = wp_get_nav_menu_object($locations['support-footer']);
        $context['footer_menus']['Support'] = Menu::build($term);
    }

    if (isset($locations['legal-footer'])) {
        $term = wp_get_nav_menu_object($locations['legal-footer']);
        $context['footer_menus']['Legal'] = Menu::build($term);
    }

    return $context;
});

// var_dump(get_nav_menu_locations());
// var_dump(has_nav_menu('about-footer'));
// echo "Timber version: " . \Timber\Timber::$version;

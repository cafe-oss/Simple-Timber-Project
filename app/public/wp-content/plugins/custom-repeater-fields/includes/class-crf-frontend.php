<?php
/**
 * Frontend class — enqueue accordion JS on single product pages
 * File: includes/class-crf-frontend.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class CRF_Frontend
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts()
    {
        if (!is_singular('product')) return;

        wp_enqueue_script(
            'crf-frontend',
            CRF_PLUGIN_URL . 'assets/js/frontend.js',
            [],
            CRF_VERSION,
            true
        );
    }
}

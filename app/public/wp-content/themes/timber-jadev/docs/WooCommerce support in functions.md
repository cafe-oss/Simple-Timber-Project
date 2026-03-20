```php

// -------------------------------------------------------
// WooCommerce Support
// -------------------------------------------------------
add_action('after_setup_theme', function () {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
});

// Remove default WooCommerce styles — we use our own
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

// Add WooCommerce cart data to Timber context globally
add_filter('timber/context', function ($context) {
    if (class_exists('WooCommerce')) {
        $context['cart_count'] = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
        $context['cart_url']   = wc_get_cart_url();
        $context['shop_url']   = get_permalink(wc_get_page_id('shop'));
    }
    return $context;
});

npx @tailwindcss/cli -i src/input.css -o src/output.css 
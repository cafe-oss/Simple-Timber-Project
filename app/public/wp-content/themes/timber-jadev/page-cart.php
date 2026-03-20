<?php

/**
 * Cart page template.
 *
 * WordPress routes the WooCommerce cart page through page.php (and this file
 * takes priority over it). We build the cart context here and render the
 * custom cart Twig template.
 */

use Timber\Timber;

$context = Timber::context();

if (class_exists('WooCommerce') && WC()->cart) {
    $cart = WC()->cart;

    // Build cart items for Twig
    $cart_items = [];
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        if (!$product || !$product->exists() || $cart_item['quantity'] === 0) {
            continue;
        }
        $img_id       = $product->get_image_id();
        $cart_items[] = [
            'key'        => $cart_item_key,
            'name'       => apply_filters('woocommerce_cart_item_name', $product->get_name(), $cart_item, $cart_item_key),
            'quantity'   => $cart_item['quantity'],
            'link'       => get_permalink($product->get_id()),
            'img_url'    => $img_id ? wp_get_attachment_image_url($img_id, 'woocommerce_thumbnail') : wc_placeholder_img_src(),
            'img_alt'    => get_post_meta($img_id, '_wp_attachment_image_alt', true) ?: $product->get_name(),
            'line_price' => $cart->get_product_subtotal($product, $cart_item['quantity']),
            'remove_url' => wc_get_cart_remove_url($cart_item_key),
        ];
    }

    // Nonce field HTML (includes _wp_http_referer hidden field)
    ob_start();
    wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce');
    $cart_nonce_field = ob_get_clean();

    // WooCommerce notices (e.g. "Cart updated", coupon errors)
    ob_start();
    wc_print_notices();
    $wc_notices = ob_get_clean();

    $context['cart_items']       = $cart_items;
    $context['is_empty']         = $cart->is_empty();
    $context['cart_form_url']    = wc_get_cart_url();
    $context['cart_nonce_field'] = $cart_nonce_field;
    $context['cart_subtotal']    = $cart->get_cart_subtotal();
    $context['cart_total']       = $cart->get_cart_total();
    $context['needs_shipping']   = $cart->needs_shipping();
    $context['checkout_url']     = wc_get_checkout_url();
    $context['shop_page_url']    = get_permalink(wc_get_page_id('shop')) ?: home_url();
    $context['wc_notices']       = $wc_notices;

    // Shipping: total already calculated (non-zero when a zone/method is matched)
    $shipping_total = (float) $cart->get_shipping_total();
    $context['cart_shipping'] = $shipping_total > 0
        ? wc_price($shipping_total + (float) $cart->get_shipping_tax())
        : '';

    // Free-shipping progress bar — reads threshold from WooCommerce admin settings
    $threshold = jadev_get_free_shipping_threshold();
    if ($threshold) {
        $subtotal  = (float) $cart->get_subtotal();
        $remaining = max(0, $threshold - $subtotal);
        $context['free_shipping_threshold'] = $threshold;
        $context['free_shipping_remaining'] = $remaining > 0 ? wc_price($remaining) : '';
        $context['free_shipping_met']       = $remaining <= 0;
        $context['free_shipping_pct']       = min(100, (int) round(($subtotal / $threshold) * 100));
    }

    // Applied coupons + discount
    $applied_coupons = [];
    foreach ($cart->get_applied_coupons() as $code) {
        $discount        = $cart->get_coupon_discount_amount($code, $cart->display_cart_ex_tax);
        $applied_coupons[] = [
            'code'     => $code,
            'label'    => strtoupper($code),
            'discount' => wc_price($discount),
        ];
    }
    $context['applied_coupons'] = $applied_coupons;
    $context['cart_discount']   = $cart->get_discount_total() > 0
        ? wc_price($cart->get_discount_total())
        : '';
}

Timber::render('woocommerce/cart.twig', $context);

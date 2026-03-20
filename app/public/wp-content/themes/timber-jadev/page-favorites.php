<?php

use Timber\Timber;

$context       = Timber::context();
$context['post'] = Timber::get_post();

if (is_user_logged_in() && class_exists('WooCommerce')) {
    $user_id   = get_current_user_id();
    $favorites = get_user_meta($user_id, 'jadev_favorites', true) ?: [];
    $favorites = array_values(array_map('intval', (array) $favorites));

    $products = [];

    if (!empty($favorites)) {
        $query = new WP_Query([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'post__in'       => $favorites,
            'orderby'        => 'post__in',
            'posts_per_page' => -1,
        ]);

        foreach ($query->posts as $p) {
            $rp       = wc_get_product($p->ID);
            if (!$rp) continue;
            $image_id = $rp->get_image_id();

            $products[] = [
                'product_id'               => $rp->get_id(),
                'card_title'               => $rp->get_name(),
                'card_description'         => wp_trim_words(wp_strip_all_tags($rp->get_description()), 15, '…'),
                'card_image_src'           => wp_get_attachment_image_url($image_id, 'woocommerce_single'),
                'card_image_alt_attribute' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $rp->get_name(),
                'product_url'              => get_permalink($rp->get_id()),
                'add_to_cart_url'          => $rp->add_to_cart_url(),
                'price_html'               => $rp->get_price_html(),
                'is_new'                   => get_field('is_new', $p->ID),
            ];
        }

        wp_reset_postdata();
    }

    $context['favorites_products'] = $products;
    $context['is_logged_in']       = true;
} else {
    $context['favorites_products'] = null;
    $context['is_logged_in']       = false;
}

Timber::render('page-favorites.twig', $context);

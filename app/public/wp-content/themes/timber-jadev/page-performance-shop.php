<?php

use Timber\Timber;

$context = Timber::context();

// Sanitize the orderby param from the URL (?orderby=featured|best_selling|az|za)
$allowed_orderby = ['featured', 'best_selling', 'az', 'za'];
$orderby         = in_array($_GET['orderby'] ?? '', $allowed_orderby, true)
                   ? $_GET['orderby']
                   : 'featured';

$query_args = [
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'tax_query'      => [[
        'taxonomy' => 'product_cat',
        'field'    => 'slug',
        'terms'    => 'performance-shop',
    ]],
];

switch ($orderby) {
    case 'best_selling':
        $query_args['meta_key'] = 'total_sales';
        $query_args['orderby']  = 'meta_value_num';
        $query_args['order']    = 'DESC';
        break;
    case 'az':
        $query_args['orderby'] = 'title';
        $query_args['order']   = 'ASC';
        break;
    case 'za':
        $query_args['orderby'] = 'title';
        $query_args['order']   = 'DESC';
        break;
    case 'featured':
    default:
        $query_args['orderby'] = 'menu_order';
        $query_args['order']   = 'ASC';
        break;
}

$performance_shop_query = new WP_Query($query_args);

$performance_shop = [];
foreach ($performance_shop_query->posts as $related_post) {
    $rp       = wc_get_product($related_post->ID);
    $image_id = $rp->get_image_id();

    $performance_shop[] = [
        'card_title'               => $rp->get_name(),
        'card_description'         => wp_trim_words(wp_strip_all_tags($rp->get_description()), 15, '…'),
        'card_image_src'           => wp_get_attachment_image_url($image_id, 'woocommerce_single'),
        'card_image_alt_attribute' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $rp->get_name(),
        'product_id'               => $rp->get_id(),
        'product_url'              => get_permalink($rp->get_id()),
        'add_to_cart_url'          => $rp->add_to_cart_url(),
        'price_html'               => $rp->get_price_html(),
        'is_new'                   => get_field('is_new', $related_post->ID),
    ];
}
wp_reset_postdata();

$context['performance_shop']      = $performance_shop;
$context['current_orderby']  = $orderby;
$context['current_page_url'] = get_permalink();

Timber::render('page-performance-shop.twig', $context);

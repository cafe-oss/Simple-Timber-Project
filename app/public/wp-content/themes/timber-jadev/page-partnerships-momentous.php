<?php

use Timber\Timber;

$context = Timber::context();

$creatine_query_args = [
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'orderby'        => 'title',
    'order'          => 'ASC',
    'posts_per_page' => -1,
    'tax_query'      => [[
        'taxonomy' => 'product_cat',
        'field'    => 'slug',
        'terms'    => 'creatine',
    ]],
];

$protein_query_args = [
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'orderby'        => 'title',
    'order'          => 'ASC',
    'posts_per_page' => -1,
    'tax_query'      => [[
        'taxonomy' => 'product_cat',
        'field'    => 'slug',
        'terms'    => 'protein',
    ]],
];

$supplements_query_args = [
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'orderby'        => 'title',
    'order'          => 'ASC',
    'posts_per_page' => -1,
    'tax_query'      => [[
        'taxonomy' => 'product_cat',
        'field'    => 'slug',
        'terms'    => 'supplements',
    ]],
];

$creatine = new WP_Query($creatine_query_args);
$protein = new WP_Query($protein_query_args);
$supplements = new WP_Query($supplements_query_args);

$creatine_array = [];
foreach ($creatine->posts as $related_post) {
    $rp       = wc_get_product($related_post->ID);
    $image_id = $rp->get_image_id();

    $creatine_array[] = [
        'product_id'               => $rp->get_id(),
        'card_title'               => $rp->get_name(),
        'card_description'         => wp_trim_words(wp_strip_all_tags($rp->get_description()), 15, '…'),
        'card_image_src'           => wp_get_attachment_image_url($image_id, 'large'),
        'card_image_alt_attribute' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $rp->get_name(),
        'card_image_size'          => "h-full w-full",
        'price_html'               => $rp->get_price_html(),
        'is_new'                   => get_field('is_new', $related_post->ID),
        'card_button'              => [
            'card_button_url'       => $rp->get_product_url(),
            'card_button_text'      => 'SHOP NOW',
            'card_button_arialabel' => 'Shop ' . $rp->get_name() . ' on Momentous',
        ],
    ];
}


$protein_array = [];
foreach ($protein->posts as $related_post) {
    $rp       = wc_get_product($related_post->ID);
    $image_id = $rp->get_image_id();

    $protein_array[] = [
        'product_id'               => $rp->get_id(),
        'card_title'               => $rp->get_name(),
        'card_description'         => wp_trim_words(wp_strip_all_tags($rp->get_description()), 15, '…'),
        'card_image_src'           => wp_get_attachment_image_url($image_id, 'large'),
        'card_image_alt_attribute' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $rp->get_name(),
        'card_image_size'          => "h-full w-full",
        'price_html'               => $rp->get_price_html(),
        'is_new'                   => get_field('is_new', $related_post->ID),
        'card_button'              => [
            'card_button_url'       => $rp->get_product_url(),
            'card_button_text'      => 'SHOP NOW',
            'card_button_arialabel' => 'Shop ' . $rp->get_name() . ' on Momentous',
        ],
    ];
}


$supplements_array = [];
foreach ($supplements->posts as $related_post) {
    $rp       = wc_get_product($related_post->ID);
    $image_id = $rp->get_image_id();

    $supplements_array[] = [
        'product_id'               => $rp->get_id(),
        'card_title'               => $rp->get_name(),
        'card_description'         => wp_trim_words(wp_strip_all_tags($rp->get_description()), 15, '…'),
        'card_image_src'           => wp_get_attachment_image_url($image_id, 'large'),
        'card_image_alt_attribute' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $rp->get_name(),
        'card_image_size'          => "h-full w-full",
        'price_html'               => $rp->get_price_html(),
        'is_new'                   => get_field('is_new', $related_post->ID),
        'card_button'              => [
            'card_button_url'       => $rp->get_product_url(),
            'card_button_text'      => 'SHOP NOW',
            'card_button_arialabel' => 'Shop ' . $rp->get_name() . ' on Momentous',
        ],
    ];
}
wp_reset_postdata();

$context['creatine_array'] = $creatine_array;
$context['protein_array'] = $protein_array;
$context['supplements_array'] = $supplements_array;
$context['current_page_url'] = get_permalink();

Timber::render('page-partnerships-momentous.twig', $context);

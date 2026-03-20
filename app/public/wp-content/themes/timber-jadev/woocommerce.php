<?php

/**
 * WooCommerce bridge for Timber.
 *
 * WordPress/WooCommerce will route all WooCommerce pages (shop, product,
 * cart, checkout, account) through this file instead of index.php,
 * because it exists in the theme root.
 *
 * We detect which page type is being viewed and pass the correct Twig
 * template(s) to Timber. The woocommerce/woocommerce.twig acts as the
 * fallback for any page type that does not have its own dedicated template.
 */

use Timber\Timber;

$context = Timber::context();

if (is_singular('product')) {
    $context['post'] = Timber::get_post();

    // Pass ACF product note fields so single-product.twig can render
    // them directly without relying on woocommerce_single_product_summary hooks.
    $post_id = get_the_ID();
    $context['show_affirm_text']     = get_field('show_affirm_text', $post_id);
    $context['show_membership_note'] = get_field('show_membership_note', $post_id);
    $context['membership_note_text'] = get_field('membership_note_text', $post_id);
    $context['show_delivery_lookup'] = get_field('show_delivery_lookup', $post_id);
    $context['show_compare_section'] = get_field('show_compare_section', $post_id);
    $context['compare_content'] = get_field('compare_content', $post_id);
    $context['show_purchase_eligible'] = get_field('show_purchase_eligible', $post_id);
    $context['show_no_return_message'] = get_field('show_no_return_message', $post_id);
    $context['is_new'] = get_field('is_new', $post_id);
    $dropdowns_raw = get_post_meta($post_id, 'crf_product_dropdowns', true);
    $context['dropdowns'] = is_array($dropdowns_raw) ? $dropdowns_raw : [];
    
    // in woocommerce.php, after $context['show_delivery_lookup'] is set
    if ( get_field('show_delivery_lookup', $post_id) ) {
        add_action('yith_wapo_after_addons', function() {
            // render your delivery lookup HTML here
            ?>
            <div class="delivery-lookup pt-8 text-[rgb(var(--scheme-text))]">
                <h2 class="text-sm mb-4">White-Glove Delivery &amp; In-Home Installation</h2>
                <p class="text-sm mb-3">
                    Enter your zip code to calculate delivery and installation cost.
                    For your safety, your Tonal trainer will be professionally installed by a member of our team.
                </p>
                <p class="text-sm mb-4">
                    Once you place your order, you'll be able to schedule delivery in 1–4 weeks.
                </p>
                <div class="flex items-center gap-2">
                    <label for="delivery-zipcode" class="sr-only">Zip Code</label>
                    <input
                        id="delivery-zipcode"
                        type="text"
                        name="zipcode"
                        pattern="^\d{5}(-\d{4})?$"
                        placeholder="Zip Code"
                        maxlength="10"
                        class="grow border border-gray-300 px-3 py-2 text-sm"
                    >
                    <button id="delivery-zipcode-submit" class="button px-4 py-2 text-sm duration-300">
                        Submit
                    </button>
                </div>
                <div id="delivery-result" class="mt-3 text-sm" aria-live="polite"></div>
            </div>
            <?php
        });
    }

    if ( get_field('show_membership_note', $post_id) && get_field('membership_note_text', $post_id) ) {
        $membership_note_text = get_field('membership_note_text', $post_id);
        add_action('yith_wapo_after_addons', function() use ( $membership_note_text ) {
            ?>
            <div class="membership-note text-sm pt-10">
                <h2 class="text-sm mb-5 text-[rgb(var(--scheme-text))]">Membership (purchased separately)</h2>
                <?php echo wp_kses_post( $membership_note_text ); ?>
            </div>
            <?php
        });
    }

    if ( get_field('compare_content', $post_id) && get_field('compare_content', $post_id) ) {
        $compare_content = get_field('compare_content', $post_id);
        add_action('yith_wapo_before_addons', function() use ( $compare_content ) {
            ?>
                <?php echo wp_kses_post( $compare_content ); ?>
            <?php
        });
    }
  
    // Product-specific sections are handled by per-product Twig partials.
    // See views/woocommerce/product-sections/{product-slug}.twig

    // Build product image gallery (main image + gallery images)
    $product        = wc_get_product($post_id);
    $main_image_id  = $product->get_image_id();
    $gallery_ids    = $product->get_gallery_image_ids();
    $all_image_ids  = array_filter(array_merge([$main_image_id], $gallery_ids));

    $gallery_images = [];
    foreach ($all_image_ids as $image_id) {
        $gallery_images[] = [
            'url'   => wp_get_attachment_image_url($image_id, 'woocommerce_single'),
            'full'  => wp_get_attachment_image_url($image_id, 'full'),
            'thumb' => wp_get_attachment_image_url($image_id, 'woocommerce_gallery_thumbnail'),
            'alt'   => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: get_the_title($image_id),
        ];
    }
    $context['gallery_images'] = $gallery_images;

    // Price — get_price_html() returns formatted HTML including sale/regular price
    $context['price_html']     = $product->get_price_html();
    $context['regular_price']  = wc_price($product->get_regular_price());
    $context['sale_price']     = $product->is_on_sale() ? wc_price($product->get_sale_price()) : null;
    $context['is_on_sale']     = $product->is_on_sale();
    

    // Capture the YITH WAPO blocks + add-to-cart form as raw HTML so
    // single-product.twig can place it wherever needed via {{ add_to_cart_html|raw }}.
    global $product;
    $product = wc_get_product($post_id);
    ob_start();
    woocommerce_template_single_add_to_cart();
    $context['add_to_cart_html'] = ob_get_clean();

    // Product categories — used for breadcrumb and related products carousel
    $product_collections = wp_get_post_terms($post_id, 'product_cat', ['fields' => 'slugs']);

    // Breadcrumb ancestors: inject the Tonal Accessories page for accessory products
    $context['breadcrumb_ancestors'] = [];
    if (!is_wp_error($product_collections) && in_array('accessories', $product_collections)) {
        $accessories_page = get_page_by_path('tonal-accessories');
        if ($accessories_page) {
            $context['breadcrumb_ancestors'] = [[
                'title' => get_the_title($accessories_page),
                'link'  => get_permalink($accessories_page->ID),
            ]];
        }
    }

    $context['breadcrumb_performance_shop'] = [];
    if (!is_wp_error($product_collections) && in_array('performance-shop', $product_collections)) {
        $performance_shop_page = get_page_by_path('performance-shop');
        if ($performance_shop_page) {
            $context['breadcrumb_performance_shop'] = [[
                'title' => get_the_title($performance_shop_page),
                'link'  => get_permalink($performance_shop_page->ID),
            ]];
        }
    }

    // Related products carousel — only for products in the accessories collection
    $context['related_banner_cards'] = [];
    if (!is_wp_error($product_collections) && in_array('accessories', $product_collections)) {
        $related_query = new WP_Query([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 10,
            'post__not_in'   => [$post_id],
            'tax_query'      => [[
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => 'accessories',
            ]],
        ]);
        $banner_cards = [];
        foreach ($related_query->posts as $related_post) {
            $rp             = wc_get_product($related_post->ID);
            $image_id       = $rp->get_image_id();
            $banner_cards[] = [
                'card_title'               => $rp->get_name(),
                'card_description'         => wp_trim_words(wp_strip_all_tags($rp->get_description()), 15, '…'),
                'card_image_src'           => wp_get_attachment_image_url($image_id, 'woocommerce_single'),
                'card_image_alt_attribute' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $rp->get_name(),
                'card_button'              => [
                    'card_button_url'       => get_permalink($rp->get_id()),
                    'card_button_text'      => 'Shop Now',
                    'card_button_arialabel' => 'Shop ' . $rp->get_name(),
                ],
            ];
        }
        wp_reset_postdata();
        $context['related_banner_cards'] = $banner_cards;
    } elseif (!is_wp_error($product_collections) && in_array('performance-shop', $product_collections)) {
        $related_query = new WP_Query([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 10,
            'post__not_in'   => [$post_id],
            'tax_query'      => [[
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => 'performance-shop',
            ]],
        ]);
        $banner_cards = [];
        foreach ($related_query->posts as $related_post) {
            $rp             = wc_get_product($related_post->ID);
            $image_id       = $rp->get_image_id();
            $banner_cards[] = [
                'card_title'               => $rp->get_name(),
                'card_description'         => wp_trim_words(wp_strip_all_tags($rp->get_description()), 15, '…'),
                'card_image_src'           => wp_get_attachment_image_url($image_id, 'woocommerce_single'),
                'card_image_alt_attribute' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $rp->get_name(),
                'card_button'              => [
                    'card_button_url'       => get_permalink($rp->get_id()),
                    'card_button_text'      => 'Shop Now',
                    'card_button_arialabel' => 'Shop ' . $rp->get_name(),
                ],
            ];
        }
        wp_reset_postdata();
        $context['related_banner_cards'] = $banner_cards;
    }

    $templates = [
        'woocommerce/single-product.twig',
        'woocommerce/woocommerce.twig',
    ];
} elseif (is_checkout()) {
    $templates = [
        'woocommerce/checkout.twig',
        'woocommerce/woocommerce.twig',
    ];
} elseif (is_account_page()) {
    $templates = [
        'woocommerce/my-account.twig',
        'woocommerce/woocommerce.twig',
    ];
} elseif (is_shop() || is_product_category() || is_product_tag()) {
    $context['term'] = get_queried_object();
    $templates = [
        'woocommerce/archive-product.twig',
        'woocommerce/woocommerce.twig',
    ];
} else {
    $templates = ['woocommerce/woocommerce.twig'];
}

Timber::render($templates, $context);

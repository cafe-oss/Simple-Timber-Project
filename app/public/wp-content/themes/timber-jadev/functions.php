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
    // Depend on WordPress's built-in jQuery (full version) so WooCommerce
    // checkout AJAX works and there is only one jQuery instance on the page.
    wp_enqueue_script('jadev-main', get_stylesheet_directory_uri() . '/assets/js/main.js', array('jquery'), false, true);

    // Pass AJAX URL and nonces to JS
    wp_localize_script('jadev-main', 'jadevAjax', [
        'url'          => admin_url('admin-ajax.php'),
        'nonce'        => wp_create_nonce('jadev_shipping_nonce'),
        'cart_nonce'   => wp_create_nonce('jadev_cart_nonce'),
        'coupon_nonce' => wp_create_nonce('jadev_coupon_nonce'),
    ]);

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
    $context['is_front_page'] = is_front_page();

    if (isset($locations['primary'])) {
        $term = wp_get_nav_menu_object($locations['primary']);
        if ($term) {
            $context['header_menus']['Primary'] = Menu::build($term);
        }
    }

    if (isset($locations['about-footer'])) {
        $term = wp_get_nav_menu_object($locations['about-footer']);
        if ($term) {
            $context['footer_menus']['About Tonal'] = Menu::build($term);
        }
    }

    if (isset($locations['support-footer'])) {
        $term = wp_get_nav_menu_object($locations['support-footer']);
        if ($term) {
            $context['footer_menus']['Support'] = Menu::build($term);
        }
    }

    if (isset($locations['legal-footer'])) {
        $term = wp_get_nav_menu_object($locations['legal-footer']);
        if ($term) {
            $context['footer_menus']['Legal'] = Menu::build($term);
        }
    }

    return $context;
});

// var_dump(get_nav_menu_locations());
// var_dump(has_nav_menu('about-footer'));
// echo "Timber version: " . \Timber\Timber::$version;

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

// -------------------------------------------------------
// Sidebar Cart — HTML renderer (also used as WC fragment)
// -------------------------------------------------------

/**
 * Render the #sidebar-cart-body div.
 * Called by Timber context and by the WC fragment filter.
 */
function jadev_sidebar_cart_html()
{
    if (!class_exists('WooCommerce') || !WC()->cart) {
        return '';
    }

    $cart         = WC()->cart;
    $items        = $cart->get_cart();
    $subtotal     = $cart->get_cart_subtotal();
    $checkout_url = wc_get_checkout_url();
    $cart_url     = wc_get_cart_url();

    ob_start();
    ?>
    <div id="sidebar-cart-body" class="flex flex-col flex-1 overflow-hidden text-[rgb(28,28,26)]">

        <?php if (empty($items)) : ?>
            <div class="flex-1 flex items-center justify-center px-5 py-16">
                <p class="text-sm text-center !text-[rgb(28,28,26)]">Your cart is empty.</p>
            </div>
        <?php else : ?>

            <ul class="flex-1 overflow-y-auto px-5! py-4 divide-y divide-gray-100">
                <?php foreach ($items as $cart_item_key => $cart_item) :
                    $product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                    if (!$product || !$product->exists() || $cart_item['quantity'] === 0) {
                        continue;
                    }
                    $name       = apply_filters('woocommerce_cart_item_name', $product->get_name(), $cart_item, $cart_item_key);
                    $qty        = $cart_item['quantity'];
                    $img_id     = $product->get_image_id();
                    $img_url    = $img_id ? wp_get_attachment_image_url($img_id, 'thumbnail') : wc_placeholder_img_src();
                    $line_price = $cart->get_product_subtotal($product, $qty);
                    $remove_url = wc_get_cart_remove_url($cart_item_key);
                    $link       = get_permalink($product->get_id());
                ?>
                <li class="flex gap-3 py-4 items-start ">
                    <a href="<?php echo esc_url($link); ?>" class="flex-shrink-0 block">
                        <img src="<?php echo esc_url($img_url); ?>"
                             alt="<?php echo esc_attr($name); ?>"
                             width="64" height="64"
                             class="w-16 h-16 object-cover">
                    </a>
                    <div class="flex-1 min-w-0">
                        <a href="<?php echo esc_url($link); ?>"
                           class="text-sm font-medium block leading-snug mb-1 hover:underline text-[rgb(28,28,26)]!">
                            <?php echo esc_html($name); ?>
                        </a>
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>Qty: <?php echo esc_html($qty); ?></span>
                            <span><?php echo $line_price; ?></span>
                        </div>
                    </div>
                    <a href="<?php echo esc_url($remove_url); ?>"
                       class="sidebar-cart-remove flex-shrink-0 text-gray-400 hover:text-black transition-colors mt-0.5 text-[rgb(28,28,26)]!"
                       data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
                       aria-label="<?php echo esc_attr(sprintf('Remove %s from cart', $name)); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6"/>
                            <path d="M14 11v6"/>
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                        </svg>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>

            <?php
            // Free-shipping progress bar
            $fs_threshold = jadev_get_free_shipping_threshold();
            if ($fs_threshold) :
                $subtotal_raw = (float) $cart->get_subtotal();
                $fs_remaining = max(0, $fs_threshold - $subtotal_raw);
                $fs_pct       = min(100, (int) round(($subtotal_raw / $fs_threshold) * 100));
            ?>
            <div class="px-5 pt-4 pb-2 text-xs">
                <?php if ($fs_remaining <= 0) : ?>
                    <p class="text-green-600 font-medium">&#10003; You qualify for free shipping!</p>
                <?php else : ?>
                    <p class="text-gray-600">
                        Add <strong><?php echo wc_price($fs_remaining); ?></strong> more for free shipping.
                    </p>
                    <div class="mt-1.5 h-1 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-[rgb(28,28,26)] transition-all duration-300"
                             style="width: <?php echo $fs_pct; ?>%"></div>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="px-5 py-5 border-t border-gray-200 bg-white flex-shrink-0 ">
                <div class="flex items-center justify-between mb-4 text-sm">
                    <span class="uppercase tracking-wider font-medium">Subtotal</span>
                    <span><?php echo $subtotal; ?></span>
                </div>
                <a href="<?php echo esc_url($checkout_url); ?>"
                   class="block w-full text-center py-3 px-5 bg-[rgb(28,28,26)] text-[rgb(244,244,244)] text-sm uppercase tracking-widest hover:opacity-90 transition-opacity">
                    Checkout
                </a>
                <a href="<?php echo esc_url($cart_url); ?>"
                   class="block w-full text-center pt-3 text-sm underline underline-offset-4 hover:no-underline text-[rgb(28,28,26)]!">
                    View Cart
                </a>
            </div>

        <?php endif; ?>

    </div>
    <?php
    return ob_get_clean();
}

// AJAX: remove an item from the sidebar cart and return fresh fragments
add_action('wp_ajax_jadev_remove_cart_item',        'jadev_remove_cart_item');
add_action('wp_ajax_nopriv_jadev_remove_cart_item', 'jadev_remove_cart_item');

function jadev_remove_cart_item()
{
    check_ajax_referer('jadev_cart_nonce', 'nonce');

    $key = sanitize_text_field($_POST['cart_item_key'] ?? '');
    if ($key) {
        WC()->cart->remove_cart_item($key);
    }

    $count    = WC()->cart->get_cart_contents_count();
    $hidden   = $count === 0 ? ' hidden' : '';
    $fragments = [
        '#sidebar-cart-body'  => jadev_sidebar_cart_html(),
        '.cart-count-bubble'  => '<span class="cart-count-bubble' . $hidden . '">' . $count . '</span>',
    ];

    wp_send_json_success(['fragments' => $fragments]);
}

// Register WooCommerce fragments so the sidebar updates on add-to-cart
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    if (!class_exists('WooCommerce') || !WC()->cart) {
        return $fragments;
    }

    // Replace sidebar cart body
    $fragments['#sidebar-cart-body'] = jadev_sidebar_cart_html();

    // Replace cart count badge (header button + sidebar header)
    $count  = WC()->cart->get_cart_contents_count();
    $hidden = $count === 0 ? ' hidden' : '';
    $fragments['.cart-count-bubble'] = '<span class="cart-count-bubble' . $hidden . '">' . $count . '</span>';

    return $fragments;
});

// Add WooCommerce cart data to Timber context globally
add_filter('timber/context', function ($context) {
    if (class_exists('WooCommerce')) {
        $context['cart_count']       = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
        $context['cart_url']         = wc_get_cart_url();
        $context['shop_url']         = get_permalink(wc_get_page_id('shop'));
        $context['checkout_url']     = wc_get_checkout_url();
        $context['sidebar_cart_html'] = jadev_sidebar_cart_html();
    }
    return $context;
});

// -------------------------------------------------------
// WooCommerce — Exclude favorites page from "Coming Soon" mode
// so guests can load and use the page even when the store is
// in coming-soon / maintenance mode.
// -------------------------------------------------------
add_filter('woocommerce_is_coming_soon', function ($is_coming_soon) {
    if (is_page('favorites')) {
        return false;
    }
    return $is_coming_soon;
});

// -------------------------------------------------------
// Favorites
// -------------------------------------------------------

// Pass favorites data to JS after the main script is enqueued
add_action('wp_enqueue_scripts', function () {
    $favorites = [];
    if (is_user_logged_in()) {
        $favorites = get_user_meta(get_current_user_id(), 'jadev_favorites', true) ?: [];
    }

    wp_localize_script('jadev-main', 'jadevFavorites', [
        'nonce'      => wp_create_nonce('jadev_favorites_nonce'),
        'isLoggedIn' => is_user_logged_in(),
        'favorites'  => array_values(array_map('intval', (array) $favorites)),
    ]);
}, 20);

add_action('wp_ajax_jadev_toggle_favorite',        'jadev_toggle_favorite');
add_action('wp_ajax_jadev_get_favorites_products',        'jadev_get_favorites_products');
add_action('wp_ajax_nopriv_jadev_get_favorites_products', 'jadev_get_favorites_products');

function jadev_toggle_favorite()
{
    check_ajax_referer('jadev_favorites_nonce', 'nonce');

    $product_id = intval($_POST['product_id'] ?? 0);
    if (!$product_id) {
        wp_send_json_error(['message' => 'Invalid product ID.']);
        return;
    }

    $user_id   = get_current_user_id();
    $favorites = get_user_meta($user_id, 'jadev_favorites', true) ?: [];
    $favorites = array_values(array_map('intval', (array) $favorites));

    $key = array_search($product_id, $favorites, true);
    if ($key !== false) {
        unset($favorites[$key]);
        $favorited = false;
    } else {
        $favorites[] = $product_id;
        $favorited   = true;
    }

    $favorites = array_values($favorites);
    update_user_meta($user_id, 'jadev_favorites', $favorites);

    wp_send_json_success([
        'favorited' => $favorited,
        'favorites' => $favorites,
    ]);
}

function jadev_get_favorites_products()
{
    check_ajax_referer('jadev_favorites_nonce', 'nonce');

    $raw_ids = $_POST['product_ids'] ?? [];
    $ids     = array_values(array_filter(array_map('intval', (array) $raw_ids)));

    if (empty($ids)) {
        wp_send_json_success(['products' => []]);
        return;
    }

    $query = new WP_Query([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'post__in'       => $ids,
        'orderby'        => 'post__in',
        'posts_per_page' => -1,
    ]);

    $products = [];
    foreach ($query->posts as $p) {
        $rp = wc_get_product($p->ID);
        if (!$rp) continue;
        $image_id   = $rp->get_image_id();
        $products[] = [
            'product_id'      => $rp->get_id(),
            'card_title'      => $rp->get_name(),
            'card_description'=> wp_trim_words(wp_strip_all_tags($rp->get_description()), 15, '…'),
            'image_src'       => wp_get_attachment_image_url($image_id, 'woocommerce_single') ?: '',
            'image_alt'       => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $rp->get_name(),
            'product_url'     => get_permalink($rp->get_id()),
            'add_to_cart_url' => $rp->add_to_cart_url(),
            'price_html'      => $rp->get_price_html(),
            'is_new'          => (bool) get_field('is_new', $p->ID),
        ];
    }

    wp_reset_postdata();
    wp_send_json_success(['products' => $products]);
}

// Custom page titles
add_filter('document_title_parts', function ($title) {
    if (is_404()) {
        $title['title'] = '404 Not Found';
    }
    return $title;
});

// -------------------------------------------------------
// WooCommerce — Delivery zip code lookup (AJAX)
// -------------------------------------------------------
add_action('wp_ajax_jadev_check_shipping', 'jadev_check_shipping_rate');
add_action('wp_ajax_nopriv_jadev_check_shipping', 'jadev_check_shipping_rate');

function jadev_check_shipping_rate()
{
    check_ajax_referer('jadev_shipping_nonce', 'nonce');

    $zip = sanitize_text_field($_POST['zipcode'] ?? '');

    if (!preg_match('/^\d{5}(-\d{4})?$/', $zip)) {
        wp_send_json_error(['message' => 'Please enter a valid 5-digit zip code.']);
        return;
    }

    // Use WooCommerce's own zone matching to find the right shipping zone
    $package = [
        'destination' => [
            'country'  => 'US',
            'state'    => '',
            'postcode' => $zip,
            'city'     => '',
            'address'  => '',
        ],
    ];

    $zone    = WC_Shipping_Zones::get_zone_matching_package($package);
    $methods = $zone->get_shipping_methods(true);

    if (empty($methods)) {
        wp_send_json_error(['message' => 'Delivery is not available in your area. Please contact us.']);
        return;
    }

    $rates = [];
    foreach ($methods as $method) {
        $cost    = $method->get_option('cost');
        $rates[] = [
            'label' => $method->get_title(),
            'cost'  => $cost ? wc_price((float) $cost) : 'Included',
        ];
    }

    wp_send_json_success(['rates' => $rates]);
}

// -------------------------------------------------------
// WooCommerce — Product page: after price (priority 11)
// -------------------------------------------------------

// PER-PRODUCT — Membership note, controlled by ACF toggle
add_action('woocommerce_single_product_summary', function () {
    if (!is_product()) return;
    if (!get_field('show_membership_note')) return;

    $note = get_field('membership_note_text');
    $url  = get_field('membership_learn_more_url') ?: '/membership';
    ?>
    <p class="membership-note">
        <?php echo esc_html($note); ?>
        <a href="<?php echo esc_url($url); ?>" class="underline">Learn more</a>
    </p>
    <?php
}, 12);

// -------------------------------------------------------
// WooCommerce — Product tabs: Compare Models tab
// Only appears when ACF "Show Compare Section" is toggled on
// -------------------------------------------------------
add_filter('woocommerce_product_tabs', function ($tabs) {
    global $product;
    if (!$product) return $tabs;
    if (!get_field('show_compare_section', $product->get_id())) return $tabs;

    $tabs['compare'] = [
        'title'    => 'Compare Models',
        'priority' => 50,
        'callback' => 'jadev_compare_tab_content',
    ];

    return $tabs;
});

function jadev_compare_tab_content()
{
    global $product;
    $content = get_field('compare_content', $product->get_id());
    if ($content) {
        echo '<div class="compare-tab-content">' . wp_kses_post($content) . '</div>';
    }
}

// -------------------------------------------------------
// Free-shipping progress bar on the checkout page
// The checkout routes through page.php → page.twig (not
// checkout.twig), so we inject via the_content filter.
// -------------------------------------------------------
add_filter('the_content', function ($content) {
    if (!is_checkout() || is_order_received_page()) {
        return $content;
    }

    $threshold = jadev_get_free_shipping_threshold();
    if (!$threshold || !class_exists('WooCommerce') || !WC()->cart) {
        return $content;
    }

    $subtotal  = (float) WC()->cart->get_subtotal();
    $remaining = max(0, $threshold - $subtotal);
    $pct       = min(100, (int) round(($subtotal / $threshold) * 100));

    ob_start();
    ?>
    <div id="jadev-free-shipping-bar" style="font-family: inherit; font-size: 0.875rem; display: none; padding: 1rem;">
        <?php if ($remaining <= 0) : ?>
            <p style="color: #16a34a; font-weight: 500;">&#10003; You qualify for free shipping!</p>
        <?php else : ?>
            <p style="color: #4b5563;">
                Add <strong><?php echo wc_price($remaining); ?></strong> more for free shipping.
            </p>
            <div style="margin-top: 0.5rem; height: 4px; background: #e5e7eb; border-radius: 9999px; overflow: hidden;">
                <div style="height: 100%; width: <?php echo $pct; ?>%; background: rgb(28,28,26); transition: width 0.3s;"></div>
            </div>
        <?php endif; ?>
    </div>
    <script>
    (function () {
        function moveBar() {
            var bar    = document.getElementById('jadev-free-shipping-bar');
            var target = document.querySelector('.wp-block-woocommerce-checkout-order-summary-block');
            if (bar && target) {
                bar.style.display = '';
                target.appendChild(bar);
                return true;
            }
            return false;
        }
        if (!moveBar()) {
            var obs = new MutationObserver(function () {
                if (moveBar()) { obs.disconnect(); }
            });
            obs.observe(document.body, { childList: true, subtree: true });
        }
    })();
    </script>
    <?php
    return $content . ob_get_clean();
});

// -------------------------------------------------------
// Shipping — read free-shipping threshold from WooCommerce admin
// -------------------------------------------------------

/**
 * Returns the minimum order amount configured on the first active
 * Free Shipping method across all shipping zones, or null if none.
 *
 * Configure in: WooCommerce → Settings → Shipping → [Zone] → Free Shipping
 * Set "Free shipping requires..." to "A minimum order amount" and enter the value.
 */
function jadev_get_free_shipping_threshold()
{
    // Collect all zone IDs, including "Rest of the World" (id 0)
    $zone_ids   = array_column(WC_Shipping_Zones::get_zones(), 'zone_id');
    $zone_ids[] = 0;

    foreach ($zone_ids as $zone_id) {
        $zone = new WC_Shipping_Zone($zone_id);
        foreach ($zone->get_shipping_methods(true) as $method) {
            if ($method->id !== 'free_shipping') {
                continue;
            }
            $requires = $method->get_option('requires');
            if (!in_array($requires, ['min_amount', 'either', 'both'], true)) {
                continue;
            }
            $min = (float) $method->get_option('min_amount');
            if ($min > 0) {
                return $min;
            }
        }
    }

    return null;
}

// -------------------------------------------------------
// Coupon / Discount — AJAX handlers
// -------------------------------------------------------

/**
 * Build a compact summary array for the cart order summary
 * so the AJAX response can update the DOM without a page reload.
 */
function jadev_cart_summary_data()
{
    $cart = WC()->cart;
    $cart->calculate_totals();

    $applied = [];
    foreach ($cart->get_applied_coupons() as $code) {
        $discount  = $cart->get_coupon_discount_amount($code, $cart->display_cart_ex_tax);
        $applied[] = [
            'code'     => $code,
            'label'    => strtoupper($code),
            'discount' => wc_price($discount),
        ];
    }

    return [
        'subtotal'        => $cart->get_cart_subtotal(),
        'discount_total'  => $cart->get_discount_total() > 0
                                ? wc_price($cart->get_discount_total())
                                : '',
        'total'           => $cart->get_cart_total(),
        'applied_coupons' => $applied,
    ];
}

add_action('wp_ajax_jadev_apply_coupon',        'jadev_apply_coupon');
add_action('wp_ajax_nopriv_jadev_apply_coupon', 'jadev_apply_coupon');

function jadev_apply_coupon()
{
    check_ajax_referer('jadev_coupon_nonce', 'nonce');

    $code = sanitize_text_field($_POST['coupon_code'] ?? '');
    if (!$code) {
        wp_send_json_error(['message' => 'Please enter a coupon code.']);
        return;
    }

    if (WC()->cart->has_discount($code)) {
        wp_send_json_error(['message' => 'Coupon already applied.']);
        return;
    }

    wc_clear_notices();
    $result = WC()->cart->apply_coupon($code);

    if ($result) {
        wp_send_json_success([
            'message' => 'Coupon applied!',
            'summary' => jadev_cart_summary_data(),
        ]);
    } else {
        $notices = wc_get_notices('error');
        $message = !empty($notices)
            ? wp_strip_all_tags($notices[0]['notice'])
            : 'Invalid or expired coupon code.';
        wc_clear_notices();
        wp_send_json_error(['message' => $message]);
    }
}

add_action('wp_ajax_jadev_remove_coupon',        'jadev_remove_coupon');
add_action('wp_ajax_nopriv_jadev_remove_coupon', 'jadev_remove_coupon');

function jadev_remove_coupon()
{
    check_ajax_referer('jadev_coupon_nonce', 'nonce');

    $code = sanitize_text_field($_POST['coupon_code'] ?? '');
    if ($code) {
        WC()->cart->remove_coupon($code);
    }

    wp_send_json_success([
        'message' => 'Coupon removed.',
        'summary' => jadev_cart_summary_data(),
    ]);
}


# Related Products Carousel

---

## Overview

Displays a carousel of related products below the single product hero section.
The carousel only appears when the current product belongs to the `accessories`
product category. Products in any other category are unaffected.

The carousel reuses the existing `components/row-banner-carousel.twig` component
and is driven entirely by WooCommerce product data — no manual content entry needed.

---

## How It Works

  Browser requests /products/some-accessory
       ↓
  woocommerce.php checks if the product belongs to the 'accessories' product_cat
       ↓
  If yes → fetches up to 10 other published products from the same category
           (excluding the current product)
       ↓
  Each product is formatted into a banner_cards[] array
       ↓
  $context['related_banner_cards'] is passed to Twig
       ↓
  single-product.twig renders row-banner-carousel.twig if related_banner_cards is not empty
       ↓
  If product is NOT in accessories → related_banner_cards = [] → carousel hidden

---

## Files Involved

  ┌────────────────────────────────────────────────┬──────────────────────────────────────────────┐
  │ File                                           │ Role                                         │
  ├────────────────────────────────────────────────┼──────────────────────────────────────────────┤
  │ woocommerce.php                                │ Fetches related products, builds banner_cards │
  ├────────────────────────────────────────────────┼──────────────────────────────────────────────┤
  │ views/woocommerce/single-product.twig          │ Includes the carousel conditionally           │
  ├────────────────────────────────────────────────┼──────────────────────────────────────────────┤
  │ views/components/row-banner-carousel.twig      │ Carousel shell — swiper + progress bar        │
  ├────────────────────────────────────────────────┼──────────────────────────────────────────────┤
  │ views/components/banner-card.twig              │ Individual product card inside the carousel   │
  └────────────────────────────────────────────────┴──────────────────────────────────────────────┘

---

## Step 1 — woocommerce.php

Added inside the `is_singular('product')` branch, just before the `$templates` array:

```php
// Related products carousel — only for products in the accessories category
$product_collections = wp_get_post_terms($post_id, 'product_cat', ['fields' => 'slugs']);
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
            'card_description'         => wp_trim_words(wp_strip_all_tags($rp->get_description()), 20, '…'),
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
```

What each field maps to:

  ┌──────────────────────────────────┬────────────────────────────────────────────────────────────┐
  │ banner_cards[] field             │ Source                                                     │
  ├──────────────────────────────────┼────────────────────────────────────────────────────────────┤
  │ card_title                       │ $rp->get_name()                                            │
  ├──────────────────────────────────┼────────────────────────────────────────────────────────────┤
  │ card_description                 │ $rp->get_short_description() — HTML stripped               │
  ├──────────────────────────────────┼────────────────────────────────────────────────────────────┤
  │ card_image_src                   │ Featured image at woocommerce_single size                  │
  ├──────────────────────────────────┼────────────────────────────────────────────────────────────┤
  │ card_image_alt_attribute         │ Image alt meta, falls back to product name                 │
  ├──────────────────────────────────┼────────────────────────────────────────────────────────────┤
  │ card_button.card_button_url      │ get_permalink($rp->get_id())                               │
  ├──────────────────────────────────┼────────────────────────────────────────────────────────────┤
  │ card_button.card_button_text     │ Hardcoded 'Shop Now'                                       │
  ├──────────────────────────────────┼────────────────────────────────────────────────────────────┤
  │ card_button.card_button_arialabel│ 'Shop ' + product name                                    │
  └──────────────────────────────────┴────────────────────────────────────────────────────────────┘

---

## Step 2 — single-product.twig

Added after `</article>` and before the product-sections include:

```twig
{# ── Related products carousel — accessories category only ───────────── #}
{% if related_banner_cards %}
    {% include 'components/row-banner-carousel.twig' with {
        rct_title: 'Related Products',
        banner_cards: related_banner_cards
    } %}
{% endif %}
```

- `related_banner_cards` is `[]` for non-accessories products so the
  `{% if %}` block never renders on other product pages.
- No `rct_darkbg` passed — defaults to light background.

---

## Assigning a Product to the Accessories Category

1. WP Admin → Products → Edit the product
2. On the right side panel find **Product categories**
3. Check **Accessories**
4. Click **Update**

The carousel will now appear on that product page with all other accessories
(excluding itself).

---

## Extending to Other Categories

To show the carousel on a different category, change the slug in both places:

```php
// Check
if (!is_wp_error($product_collections) && in_array('performance-shop', $product_collections)) {

// Query
'tax_query' => [[
    'taxonomy' => 'product_cat',
    'field'    => 'slug',
    'terms'    => 'performance-shop',
]],
```

To support multiple categories at once:

```php
$carousel_categories = ['accessories', 'performance-shop'];
$matching = array_intersect($carousel_categories, $product_collections);

if (!is_wp_error($product_collections) && !empty($matching)) {
    $related_query = new WP_Query([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        'post__not_in'   => [$post_id],
        'tax_query'      => [[
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => array_values($matching),
        ]],
    ]);
    // ... same loop
}
```

---

## Changing the Section Title

Edit the `rct_title` value in `single-product.twig`:

```twig
{% include 'components/row-banner-carousel.twig' with {
    rct_title: 'You May Also Like',   {# ← change this #}
    banner_cards: related_banner_cards
} %}
```

---

## Changing the Number of Results

Edit the `posts_per_page` value in `woocommerce.php`:

```php
'posts_per_page' => 10,   // ← increase or decrease
```

---

## Dark Background Toggle

Pass `rct_darkbg` in `single-product.twig` to switch to a dark background:

```twig
{% include 'components/row-banner-carousel.twig' with {
    rct_title:    'Related Products',
    banner_cards: related_banner_cards,
    rct_darkbg:   'bg-[rgb(28,28,26)]'
} %}
```

  ''                      → light background (rgb 244 244 244), dark text
  'bg-[rgb(28,28,26)]'    → dark background, light text

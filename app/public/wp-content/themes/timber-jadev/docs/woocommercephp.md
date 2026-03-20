 Here's what was created and how it all fits together:  
 
 --- How the bridge works Browser visits /shop, /product/xyz, /cart, / checkout 
         ↓
  WordPress finds woocommerce.php in your theme root
          ↓
  woocommerce.php detects the page type (is_shop, is_singular, is_cart…)
          ↓
  Picks the right Twig template (with woocommerce.twig as fallback)
          ↓
  Twig extends base.twig → your header/footer/nav stay intact
          ↓
  {{ function('woocommerce_content') }} outputs WooCommerce's content

  Template priority (most → least specific)

  ┌─────────────────┬──────────────────────────────────┬──────────────────────────────┐
  │      Page       │        Timber tries first        │        Falls back to         │
  ├─────────────────┼──────────────────────────────────┼──────────────────────────────┤
  │ Single product  │ woocommerce/single-product.twig  │ woocommerce/woocommerce.twig │
  ├─────────────────┼──────────────────────────────────┼──────────────────────────────┤
  │ Shop / category │ woocommerce/archive-product.twig │ woocommerce/woocommerce.twig │
  ├─────────────────┼──────────────────────────────────┼──────────────────────────────┤
  │ Cart            │ woocommerce/cart.twig            │ woocommerce/woocommerce.twig │
  ├─────────────────┼──────────────────────────────────┼──────────────────────────────┤
  │ Checkout        │ woocommerce/checkout.twig        │ woocommerce/woocommerce.twig │
  ├─────────────────┼──────────────────────────────────┼──────────────────────────────┤
  │ My Account      │ woocommerce/my-account.twig      │ woocommerce/woocommerce.twig │
  └─────────────────┴──────────────────────────────────┴──────────────────────────────┘

  Right now everything falls back to woocommerce.twig — which is fine. Each dedicated template will be built out as you add products and design the Tonal-inspired pages.


wp-content\themes\timber-jadev\views\woocommerce\woocommerce.twig
```php
{% extends 'base.twig' %}

{% block content %}
    <div class="wrapper py-8 px-5">
        {{ function('woocommerce_content') }}
    </div>
{% endblock %}
```

wp-content\themes\timber-jadev\woocommerce.php
```php
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
    $templates = [
        'woocommerce/single-product.twig',
        'woocommerce/woocommerce.twig',
    ];
} elseif (is_cart()) {
    $templates = [
        'woocommerce/cart.twig',
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

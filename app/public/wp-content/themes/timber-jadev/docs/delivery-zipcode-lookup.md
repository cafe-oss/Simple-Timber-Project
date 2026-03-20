# White-Glove Delivery & In-Home Installation — Zip Code Lookup

## Overview

This feature adds a zip code input form to single WooCommerce product pages.
When a customer enters their zip code, it queries WordPress via AJAX, matches the zip against WooCommerce shipping zones, and returns the delivery/installation cost — all without leaving the product page.

---

## Files Changed

| File | What was added |
|------|---------------|
| `functions.php` | AJAX endpoint, form hook, nonce passed to JS |
| `assets/js/main.js` | Zip code form handler |

---

## How It Works

```
User types zip → clicks Submit (or presses Enter)
        ↓
JS sends AJAX POST to WordPress admin-ajax.php
        ↓
PHP validates zip code format (5-digit US)
        ↓
WC_Shipping_Zones::get_zone_matching_package() finds the matching zone
        ↓
Returns shipping methods + costs for that zone
        ↓
JS displays the result below the form
```

---

## functions.php — Three additions

### 1. Nonce passed to JS via `wp_localize_script`

Added inside `jadev_enqueue_scripts()`:

```php
wp_localize_script('jadev-main', 'jadevAjax', [
    'url'   => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('jadev_shipping_nonce'),
]);
```

This makes `jadevAjax.url` and `jadevAjax.nonce` available globally in JS.
The nonce prevents unauthorized AJAX requests (security).

---

### 2. AJAX endpoint — `jadev_check_shipping_rate()`

```php
add_action('wp_ajax_jadev_check_shipping', 'jadev_check_shipping_rate');
add_action('wp_ajax_nopriv_jadev_check_shipping', 'jadev_check_shipping_rate');
```

- `wp_ajax_` → handles logged-in users
- `wp_ajax_nopriv_` → handles logged-out users (guests)
- Both point to the same function

**What the function does:**
1. Validates the nonce (`check_ajax_referer`)
2. Sanitizes and validates the zip code format
3. Builds a fake WooCommerce package with the zip as destination
4. Calls `WC_Shipping_Zones::get_zone_matching_package()` — WooCommerce's own matching logic
5. Loops through the zone's enabled shipping methods
6. Returns labels and costs as JSON

**Success response:**
```json
{
  "success": true,
  "data": {
    "rates": [
      { "label": "White-Glove Installation", "cost": "$299.00" }
    ]
  }
}
```

**Error response:**
```json
{
  "success": false,
  "data": { "message": "Delivery is not available in your area." }
}
```

---

### 3. Form hook — `woocommerce_single_product_summary` priority 35

```php
add_action('woocommerce_single_product_summary', function () { ... }, 35);
```

The form only renders if the ACF field **`show_delivery_lookup`** is toggled ON for that product.
This is controlled per product in WP Admin → Products → [Product] → Product Notes.

**WooCommerce summary hook priority order for reference:**

| Priority | What renders |
|----------|-------------|
| 10 | Price |
| 11 | Affirm text (global) |
| 12 | Membership note (per-product ACF) |
| 20 | Short description |
| 30 | Add to cart button |
| **35** | **Delivery zip code form** |
| 40 | Product meta |

The form outputs plain HTML with these key IDs:
- `#delivery-zipcode` — the text input
- `#delivery-zipcode-submit` — the submit button
- `#delivery-result` — where the result is injected (has `aria-live="polite"` for accessibility)

---

## main.js — Zip code form handler

Runs on `DOMContentLoaded`. Exits early if the form elements are not on the page (so it does not run on non-product pages).

**Triggers:**
- Click on `#delivery-zipcode-submit`
- `Enter` key while focused on `#delivery-zipcode`

**Flow:**
1. Validates zip format with regex `/^\d{5}(-\d{4})?$/`
2. Disables the button and shows "Checking..." during the request
3. POSTs to `jadevAjax.url` with `action`, `nonce`, and `zipcode`
4. On success: injects rate label + cost into `#delivery-result`
5. On error: injects the error message in red
6. Re-enables the button regardless of outcome (`finally`)

---

## Required Admin Setup

The AJAX endpoint queries WooCommerce shipping zones.
**Without a shipping zone configured, the form will always return "not available."**

### Set up a shipping zone:

> WP Admin → WooCommerce → Settings → Shipping → Add shipping zone

| Field | Value |
|-------|-------|
| Zone name | `Continental US` |
| Zone regions | United States (or specific states) |
| Shipping method | Flat rate |
| Flat rate cost | Your installation fee (e.g. `299`) |

You can create multiple zones with different costs for different regions (e.g. remote areas may cost more).

---

## Extending This Feature

**Add more zones:**
Create additional shipping zones in WP Admin for different regions/price tiers. WooCommerce's zone matching handles the lookup automatically.

**Show availability only (no cost):**
Change the `jadev_check_shipping_rate()` return to just confirm availability without exposing the cost on the product page — cost is then only revealed at checkout.

**Restrict to specific products:**
Wrap the form hook in a product ID or category check:

```php
add_action('woocommerce_single_product_summary', function () {
    global $product;
    if (!has_term('equipment', 'product_cat', $product->get_id())) return;
    // ... form HTML
}, 35);
```

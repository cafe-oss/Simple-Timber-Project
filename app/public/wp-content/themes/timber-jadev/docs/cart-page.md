# Cart Page

A fully custom cart page built with Timber/Twig. Displays cart items in a two-column layout — product list on the left, order summary on the right — with a "Proceed to Checkout" button. All cart data is passed from PHP into Twig context; no `woocommerce_content()` black-box rendering.

---

## Files Involved

| File | Role |
|---|---|
| `page-cart.php` | PHP controller — builds cart context and renders the Twig template |
| `views/woocommerce/cart.twig` | Twig template — full page layout (items + order summary) |

---

## Why `page-cart.php` and Not `woocommerce.php`

WooCommerce provides a `woocommerce.php` hook point for themes, but whether the cart page actually routes through it depends on the WooCommerce version and WordPress page configuration. In this theme the WooCommerce cart page (WordPress page slug: `cart`) is routed through the standard WordPress template hierarchy instead:

```
page-cart.php   ← wins (theme root, matches slug "cart")
page.php
index.php
```

`page.php` in this theme does:
```php
Timber::render( array( 'page-' . $timber_post->post_name . '.twig', 'page.twig' ), $context );
```

`page-cart.php` exists to intercept before `page.php` and inject the full cart context before rendering `woocommerce/cart.twig`.

---

## Architecture

### 1. PHP Controller — `page-cart.php`

Runs on every visit to the cart URL. Responsibilities:

1. Calls `Timber::context()` to get the standard global context (menus, site, etc.).
2. Iterates `WC()->cart->get_cart()` to build the `$cart_items` array.
3. Generates the WooCommerce nonce field HTML (required for the "Update Cart" form).
4. Captures WooCommerce notices (e.g. "Cart updated", coupon errors) via `wc_print_notices()`.
5. Passes all cart data into `$context`.
6. Calls `Timber::render('woocommerce/cart.twig', $context)`.

Guard: the entire cart block is wrapped in `if (class_exists('WooCommerce') && WC()->cart)` so the page degrades gracefully if WooCommerce is deactivated.

---

### 2. Twig Template — `views/woocommerce/cart.twig`

Extends `base.twig`. Renders one of two states:

#### Empty state

A centred message with a "Continue Shopping" link pointing to `shop_page_url`.

#### Filled state — two-column layout

```
┌────────────────────────────────────┬──────────────────────┐
│  Product  │  Quantity  │ Subtotal  │   Order Summary      │
│  [img] Name                        │   Subtotal: $xxx     │
│           Remove                   │   Shipping note      │
│                                    │   Total:    $xxx     │
│                  [Update Cart]     │  [Proceed to Checkout]│
│                                    │   Continue Shopping  │
└────────────────────────────────────┴──────────────────────┘
```

**Left column — cart items form**

- Loops over `cart_items` and renders each product as a row.
- Wrapped in a `<form method="post">` pointing to `cart_form_url` (the WooCommerce cart URL).
- Each item has a `<input type="number" name="cart[{key}][qty]">` field — WooCommerce reads these on form submission to update quantities.
- The "Remove" link uses the WooCommerce remove URL (`remove_url`). It triggers a standard page reload removal (not AJAX — for AJAX removal, see the sidebar cart doc).
- The `cart_nonce_field` variable is output as raw HTML at the bottom of the form. It contains two hidden inputs:
  - `woocommerce-cart-nonce` — the security nonce WooCommerce checks on cart update.
  - `_wp_http_referer` — the referring URL WooCommerce uses to redirect back after updating.
- "Update Cart" is a `<button type="submit" name="update_cart">`. WooCommerce's default cart handler detects this `name` attribute and triggers a quantity update.

**Right column — order summary**

Purely display. Uses context variables for subtotal and total (formatted HTML strings from WooCommerce, output with `|raw`). Shows a "Shipping & taxes calculated at checkout" note when `needs_shipping` is true. The "Proceed to Checkout" button is a plain `<a>` tag pointing to `checkout_url`.

---

## Twig Context Variables

All variables are set in `page-cart.php` and are only available on the cart page.

| Variable | Type | Description |
|---|---|---|
| `cart_items` | `array` | Array of cart item objects (see below) |
| `is_empty` | `bool` | `true` when the cart has no items |
| `cart_form_url` | `string` | Form action URL — the WooCommerce cart page URL |
| `cart_nonce_field` | `string` (raw HTML) | Two hidden `<input>` tags: WC nonce + `_wp_http_referer` |
| `cart_subtotal` | `string` (raw HTML) | Formatted subtotal from WooCommerce (e.g. `<span class="woocommerce-Price-amount">$99.00</span>`) |
| `cart_total` | `string` (raw HTML) | Formatted total from WooCommerce (includes any discounts/taxes already calculated) |
| `needs_shipping` | `bool` | `true` when at least one item in the cart requires shipping |
| `checkout_url` | `string` | URL of the WooCommerce checkout page |
| `shop_page_url` | `string` | URL of the WooCommerce shop page |
| `wc_notices` | `string` (raw HTML) | WooCommerce notice HTML (e.g. "Cart updated", coupon errors); empty string if none |

### `cart_items` array — each item

| Key | Type | Description |
|---|---|---|
| `key` | `string` | WooCommerce cart item key (internal session hash, not the product ID) |
| `name` | `string` | Product display name |
| `quantity` | `int` | Current quantity in cart |
| `link` | `string` | URL of the product page |
| `img_url` | `string` | Thumbnail URL (`woocommerce_thumbnail` size) |
| `img_alt` | `string` | Image alt text (falls back to product name) |
| `line_price` | `string` (raw HTML) | Formatted price for this line (qty × unit price) |
| `remove_url` | `string` | WooCommerce remove-item URL (standard page-reload removal) |

---

## How to Customise

### Add a product attribute or variation below the name

In `views/woocommerce/cart.twig`, below the name `<a>` tag, add:

```twig
{# If you pass variation data from page-cart.php #}
{% if item.variation %}
    <p class="text-xs text-gray-400 mt-0.5">{{ item.variation }}</p>
{% endif %}
```

Then in `page-cart.php`, add to the item array:
```php
'variation' => wc_get_formatted_cart_item_data($cart_item),
```

### Change the product image size

In `page-cart.php`, change the second argument to `wp_get_attachment_image_url()`:
```php
'img_url' => $img_id ? wp_get_attachment_image_url($img_id, 'woocommerce_single') : wc_placeholder_img_src(),
```
Standard WooCommerce sizes: `thumbnail`, `woocommerce_thumbnail`, `woocommerce_single`, `full`.

### Show a coupon field

Add a coupon input inside the cart `<form>` in `cart.twig`:
```twig
<div class="flex gap-2 mt-4">
    <input type="text" name="coupon_code" placeholder="Coupon code"
           class="border border-gray-300 px-3 py-2 text-sm flex-1">
    <button type="submit" name="apply_coupon"
            class="px-4 py-2 text-sm border border-gray-700">
        Apply
    </button>
</div>
```
WooCommerce detects `name="apply_coupon"` on submit and processes it automatically.

### Change the order summary background

In `cart.twig`, find the `<div class="bg-gray-50 p-6">` wrapper inside the right column and swap the Tailwind class:
```twig
<div class="bg-white border border-gray-200 p-6">
```

### Move the checkout button to below the cart table (single column layout)

Remove the `lg:flex-row` class from the outer wrapper div and move the order summary block to sit below the form. Both use `w-full` by default when not in flex-row context.

### Add AJAX item removal (instead of page reload)

The sidebar cart already has a working AJAX remove handler (`jadev_remove_cart_item` in `functions.php`). To use it on the cart page:
1. Add `data-cart-item-key="{{ item.key }}"` to the remove link and give it the class `sidebar-cart-remove`.
2. The existing JavaScript in `main.js` will intercept clicks on `.sidebar-cart-remove`, call the AJAX handler, and replace `#sidebar-cart-body` in the DOM.
3. You would need to add a matching `#cart-page-body` fragment and handler if you want the full cart page items list to update without a reload.

# Shipping Rules & Coupons

Custom shipping logic, coupon apply/remove via AJAX, and the free-shipping progress bar — all built without extra plugins.

---

## Overview

| Feature | Where configured | Where code lives |
|---|---|---|
| Free-shipping threshold | WooCommerce admin → Shipping zone → Free Shipping method | `functions.php` `jadev_get_free_shipping_threshold()` |
| Progress bar on cart | Automatic (reads same threshold) | `page-cart.php`, `views/woocommerce/cart.twig` |
| Progress bar on checkout | Automatic (reads same threshold) | `functions.php` `the_content` filter |
| Coupon apply (cart) | — | `functions.php` `jadev_apply_coupon()`, `assets/js/main.js` |
| Coupon remove (cart) | — | `functions.php` `jadev_remove_coupon()`, `assets/js/main.js` |

---

## 1. Free-Shipping Threshold

### How to configure

1. Go to **WooCommerce → Settings → Shipping**.
2. Click the shipping zone that covers your customers (or "Rest of the World").
3. Click **Free Shipping** → **Edit**.
4. Set **"Free shipping requires…"** to one of:
   - `A minimum order amount` — triggers when cart subtotal ≥ threshold.
   - `A minimum order amount OR a coupon` — triggers on either condition.
5. Enter the **Minimum order amount** (e.g. `1000`).
6. Save.

> **No code change needed.** `jadev_get_free_shipping_threshold()` reads this value dynamically at runtime.

### `jadev_get_free_shipping_threshold()` — `functions.php`

Scans every shipping zone (including "Rest of the World", zone ID 0) and returns the `min_amount` from the first active Free Shipping method whose "requires" setting includes a minimum amount. Returns `null` if none is found.

```php
function jadev_get_free_shipping_threshold(): ?float
```

**Used by:**
- `page-cart.php` — to build progress bar context for the cart page.
- `functions.php` `the_content` filter — to render the progress bar on the checkout page.

---

## 2. Free-Shipping Progress Bar

### On the cart page

Built in PHP inside `page-cart.php` and rendered in `views/woocommerce/cart.twig`.

**Context variables added by `page-cart.php`:**

| Variable | Type | Description |
|---|---|---|
| `free_shipping_threshold` | `float` | The configured minimum amount (only set when a threshold exists) |
| `free_shipping_remaining` | `string` (raw HTML) | Formatted price of how much more is needed; empty string when met |
| `free_shipping_met` | `bool` | `true` when subtotal ≥ threshold |
| `free_shipping_pct` | `int` | 0–100, used to set the progress bar width |

These variables are only defined in context when `jadev_get_free_shipping_threshold()` returns a value. The Twig template guards with `{% if free_shipping_threshold is defined %}`.

**Template location:** `views/woocommerce/cart.twig` — inside the Order Summary right column, above the subtotal row.

### On the checkout page

The checkout page routes through `page.php → page.twig` (not `woocommerce.php → checkout.twig`), so Twig context injection cannot be used. Instead, the bar is injected via a `the_content` WordPress filter in `functions.php`.

**How it works:**

1. The filter fires on the checkout page (guarded by `is_checkout() && !is_order_received_page()`).
2. Renders the bar HTML with `id="jadev-free-shipping-bar"` and `display:none`.
3. Appends it after `$content` (i.e. after the WooCommerce Block Checkout markup).
4. An inline `<script>` uses a `MutationObserver` to detect when the Block Checkout's React sidebar renders (`.wp-block-woocommerce-checkout-order-summary-block`), then moves the bar element inside it and sets `display: ''`.

**Why MutationObserver?** The Block Checkout renders via React after the initial HTML is parsed. A simple `DOMContentLoaded` listener would run before React has added the sidebar to the DOM.

```
HTML loads → PHP appended bar (hidden) + script tag
↓
React renders Block Checkout sidebar
↓
MutationObserver fires
↓
Bar moved inside .wp-block-woocommerce-checkout-order-summary-block
Bar made visible
```

**To change position inside the sidebar:** Replace `target.appendChild(bar)` in the inline script with `target.insertBefore(bar, target.firstChild)` to place it at the top instead of the bottom.

---

## 3. Coupons (AJAX Apply & Remove)

Coupons are created and managed entirely in **WooCommerce → Marketing → Coupons**. No custom coupon logic is needed there. The custom code only handles the cart-page UI interaction.

### AJAX handlers — `functions.php`

#### `jadev_apply_coupon()`

Registered on `wp_ajax_jadev_apply_coupon` and `wp_ajax_nopriv_jadev_apply_coupon`.

1. Verifies nonce (`coupon_nonce` from `jadevAjax`).
2. Sanitizes the submitted `coupon_code`.
3. Calls `WC()->cart->apply_coupon($code)`.
4. Returns a JSON response:
   - On success: `{ success: true, data: { message: '...', summary: {...} } }`
   - On failure: `{ success: false, data: { message: '...' } }`

The `summary` object is built by `jadev_cart_summary_data()`.

#### `jadev_remove_coupon()`

Registered on `wp_ajax_jadev_remove_coupon` and `wp_ajax_nopriv_jadev_remove_coupon`.

Same flow as apply, but calls `WC()->cart->remove_coupon($code)`.

#### `jadev_cart_summary_data()` — helper

Returns an array of cart totals used to update the cart UI after a coupon change without a full page reload:

```php
[
    'subtotal'        => string,   // formatted HTML
    'total'           => string,   // formatted HTML
    'discount'        => string,   // formatted HTML, empty if no discount
    'applied_coupons' => [
        [ 'code' => string, 'label' => string, 'discount' => string ],
        ...
    ],
]
```

#### Nonce

The nonce is output in `wp_localize_script` as `jadevAjax.coupon_nonce` (action string: `'jadev_coupon_action'`). It is verified in both AJAX handlers with `wp_verify_nonce`.

---

### JavaScript — `assets/js/main.js`

All coupon JS is inside a single `DOMContentLoaded` listener that bails early (`return`) when `#cart-coupon-apply` is not on the page, so it only runs on the cart page.

#### `applyCoupon()`

1. Reads `#cart-coupon-input` value.
2. POSTs to `jadevAjax.ajaxurl` with `action: 'jadev_apply_coupon'`.
3. On success: clears the input, calls `updateSummary(data.data.summary)`.
4. On failure: calls `showMessage(message, true)` (red text).

Triggered by clicking `#cart-coupon-apply` or pressing **Enter** in the input.

#### `removeCoupon(code)`

POSTs to `jadevAjax.ajaxurl` with `action: 'jadev_remove_coupon'` and the coupon code. On success calls `updateSummary`.

Triggered via event delegation on `document` for `.coupon-remove-btn[data-code]` — this handles dynamically rendered remove buttons.

#### `updateSummary(summary)`

Updates the cart order summary DOM elements without reloading:

| DOM element | Updated value |
|---|---|
| `#cart-summary-subtotal` | `summary.subtotal` |
| `#cart-summary-total` | `summary.total` |
| `#cart-discount-amount` | `summary.discount` |
| `#cart-discount-row` | shown/hidden depending on `summary.discount` |
| `#cart-applied-coupons` | re-rendered via `renderAppliedCoupons()` |

#### `renderAppliedCoupons(coupons)`

Rebuilds the `#cart-applied-coupons` container HTML from the `applied_coupons` array. Each coupon renders as a row with its discount amount and a `.coupon-remove-btn` button.

---

### Cart Twig elements used by coupon JS

These IDs must remain in `views/woocommerce/cart.twig` for the JS to work:

| ID | Purpose |
|---|---|
| `#cart-coupon-input` | Coupon code text input |
| `#cart-coupon-apply` | Apply button |
| `#cart-coupon-message` | Status/error message display |
| `#cart-applied-coupons` | Container for applied coupon rows |
| `#cart-discount-row` | Discount total row (toggled hidden/visible) |
| `#cart-discount-amount` | Discount amount text |
| `#cart-summary-subtotal` | Subtotal amount text |
| `#cart-summary-total` | Total amount text |

---

## 4. Shipping Context on Cart Page

Two additional variables are passed by `page-cart.php`:

| Variable | Type | Description |
|---|---|---|
| `cart_shipping` | `string` (raw HTML) | Formatted shipping cost (tax-inclusive). Empty string when no shipping rate is matched |
| `needs_shipping` | `bool` | `true` when the cart contains at least one shippable item |

In `cart.twig`, when `cart_shipping` is non-empty it displays the shipping cost. When it's empty but `needs_shipping` is true, it shows "Shipping & taxes calculated at checkout".

---

## 5. Active Coupons

Managed in **WooCommerce → Marketing → Coupons**.

| Code | Type | Amount | Limit | Expiry |
|---|---|---|---|---|
| `FREESHIP` | Fixed cart discount | ₱0 | Unlimited | None |
| `WELCOME20` | Percentage discount | 20% | Unlimited | Dec 31, 2026 |
| `PERF150` | Fixed cart discount | ₱150 | Unlimited | None |
| `ACC10` | Percentage discount | 10% | 50 uses | None |

**Notes:**
- `FREESHIP` has a ₱0 discount amount — its purpose is solely to trigger the Free Shipping method. The Free Shipping method must have "requires" set to **"A minimum order amount OR a coupon"** for this to work.
- `WELCOME20` is a 20% site-wide welcome discount; expires end of 2026.
- `PERF150` gives ₱150 off, intended for performance-shop products.
- `ACC10` gives 10% off accessories; capped at 50 total uses.

---

## 6. Active Shipping Methods

Configured in **WooCommerce → Settings → Shipping → [Zone] → Shipping methods**.

| Method | Enabled | Description |
|---|---|---|
| Free shipping | Yes | Triggered by coupon or minimum order amount |
| Flat rate | Yes | Fixed cost; shown as fallback when free shipping is not yet met |

**How they interact:**
- When a customer meets the minimum order amount (₱1,000) or applies `FREESHIP`, the **Free Shipping** method becomes available and the total shipping cost is ₱0.
- **Flat rate** remains listed as an alternative option at checkout. This is expected Block Checkout behaviour — it shows all eligible methods and lets the customer choose. Flat rate cannot be suppressed reliably on the Block Checkout via PHP filters alone.

---

## 7. How to Add a New Coupon

1. Go to **WooCommerce → Marketing → Coupons → Add coupon**.
2. Set the coupon code, discount type (percentage / fixed cart / fixed product), and amount.
3. Under **Usage restriction**, optionally set a minimum spend or usage limit.
4. To make a coupon trigger free shipping: check **"Grant free shipping"** under Usage restriction, and ensure the Free Shipping method's "requires" setting includes `A coupon`.

No code changes are needed for standard coupon types.

---

## 8. Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| Progress bar not showing on cart | `jadev_get_free_shipping_threshold()` returns `null` | Check WC admin: Free Shipping method must be active and have `min_amount` > 0 |
| Progress bar not showing on checkout | React sidebar not matching selector | Inspect the sidebar element class in your browser and update `.wp-block-woocommerce-checkout-order-summary-block` in the inline script |
| Coupon apply returns "Invalid nonce" | `jadevAjax.coupon_nonce` not enqueued | Check `wp_localize_script` in `functions.php` includes `coupon_nonce` key |
| Coupon applies but totals don't update | JS `updateSummary` DOM IDs mismatched | Verify IDs in `cart.twig` match the IDs targeted in `main.js` |
| Free shipping not triggered by coupon | WC method "requires" set to min_amount only | Change to "A minimum order amount OR a coupon" in WC admin |

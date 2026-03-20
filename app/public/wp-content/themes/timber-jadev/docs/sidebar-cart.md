# Sidebar Cart

A slide-in cart drawer that replaces the default WooCommerce cart page flow. Clicking the cart icon in the header opens a panel from the right side of the screen. The panel updates live via WooCommerce's built-in fragment system — no page reload required.

---

## Files Involved

| File | Role |
|---|---|
| `views/woocommerce/sidebar-cart.twig` | Drawer shell (overlay + `<aside>` + header) |
| `views/base.twig` | Cart button (toggle) + sidebar include in footer |
| `functions.php` | PHP renderer, WC fragment filter, AJAX handler, Timber context |
| `assets/js/main.js` | Open/close logic, AJAX remove, auto-open on add-to-cart |

---

## Architecture

### 1. Drawer Shell — `sidebar-cart.twig`

Rendered once on every page as part of `base.twig`'s footer block. Contains two elements:

**Overlay** (`#sidebar-cart-overlay`)
Semi-transparent black backdrop. Clicking it closes the drawer. Controlled purely with Tailwind opacity and `pointer-events` classes — no `display:none` toggling, so CSS transitions work cleanly.

**Drawer** (`#sidebar-cart`)
A `<aside role="dialog">` fixed to the right edge, 400 px wide at most. Starts off-screen via `translate-x-full`. The header (title + close button) is static HTML inside the Twig file. The body below it is injected by PHP:

```twig
{{ sidebar_cart_html|raw }}
```

`sidebar_cart_html` is a Timber context variable set in `functions.php` on every page request. It renders the `#sidebar-cart-body` div.

---

### 2. PHP Renderer — `jadev_sidebar_cart_html()`

Defined in `functions.php`. Returns a self-contained HTML string for `#sidebar-cart-body`.

**Two states:**

- **Empty cart** — centered "Your cart is empty" message.
- **Items present** — scrollable item list + sticky footer with subtotal, Checkout button, and View Cart link.

**Each cart item renders:**
- Product thumbnail (64×64, `thumbnail` image size)
- Product name (linked to product page)
- Quantity and line subtotal
- Trash-can remove button (`.sidebar-cart-remove` class + `data-cart-item-key` attribute, used by JS for AJAX removal)

The function is called in three places:

1. `timber/context` filter — populates `sidebar_cart_html` for the initial page render.
2. `woocommerce_add_to_cart_fragments` filter — provides the updated HTML to WooCommerce after add-to-cart.
3. `jadev_remove_cart_item()` AJAX handler — returns fresh HTML after an item is removed.

---

### 3. WooCommerce Fragments

WooCommerce has a built-in fragment system. After any add-to-cart action it fires an AJAX request to `?wc-ajax=get_refreshed_fragments` and replaces DOM elements whose selectors match the keys returned by the `woocommerce_add_to_cart_fragments` filter.

Two fragments are registered:

```php
// Replaces the entire cart body (items + subtotal + buttons)
$fragments['#sidebar-cart-body'] = jadev_sidebar_cart_html();

// Replaces every .cart-count-bubble element on the page
// (header button badge + sidebar header count)
$fragments['.cart-count-bubble'] = '<span class="cart-count-bubble ...">5</span>';
```

This means the sidebar content and the item count badge stay in sync automatically — including on page load, when WooCommerce refreshes fragments to account for session-based carts.

Note: the fragment system handles **add-to-cart** only. Item **removal** from the sidebar uses a separate custom AJAX endpoint (see section 5 below).

---

### 4. Cart Button — `base.twig`

```twig
<button id="sidebar-cart-toggle"
        aria-haspopup="dialog"
        aria-expanded="false">
    <!-- cart SVG -->
    <span class="cart-count-bubble ...
                 {{ cart_count == 0 ? 'hidden' : '' }}">
        {{ cart_count }}
    </span>
</button>
```

- `id="sidebar-cart-toggle"` — targeted by JS click handler.
- `aria-expanded` — toggled between `"true"` / `"false"` by JS.
- `.cart-count-bubble` — teal badge in the top-right corner of the icon. Hidden when count is 0. Updated by the WC fragment on cart changes.

---

### 5. AJAX System — `jadevAjax`

This is the most important section to understand because the same JavaScript object (`jadevAjax`) is shared by **two unrelated features**: the delivery zip code lookup and the sidebar cart item removal.

#### What is `jadevAjax`?

It is a plain JavaScript object injected into every page by WordPress's `wp_localize_script()`. It exists solely to pass PHP values (the AJAX URL and security tokens) into JavaScript, which has no way to call PHP functions directly.

```php
// functions.php — jadev_enqueue_scripts()
wp_localize_script('jadev-main', 'jadevAjax', [
    'url'        => admin_url('admin-ajax.php'),  // always: /wp-admin/admin-ajax.php
    'nonce'      => wp_create_nonce('jadev_shipping_nonce'), // for zip code lookup
    'cart_nonce' => wp_create_nonce('jadev_cart_nonce'),     // for cart item removal
]);
```

WordPress renders this as an inline `<script>` tag before `main.js` loads:

```html
<script>
var jadevAjax = {
    "url":        "https://yoursite.com/wp-admin/admin-ajax.php",
    "nonce":      "a1b2c3d4e5",
    "cart_nonce": "f6g7h8i9j0"
};
</script>
```

Both `nonce` and `cart_nonce` are short-lived tokens generated fresh on every page load by `wp_create_nonce()`. They expire after 12 hours and are tied to the current user's session, so they can't be reused by another person.

#### What is `admin-ajax.php`?

`admin-ajax.php` is WordPress's universal AJAX endpoint. Every AJAX request from the frontend goes to this single file. The `action` field in the POST body tells WordPress which PHP function to call.

```
POST /wp-admin/admin-ajax.php
  action = jadev_remove_cart_item   ← WordPress routes to this PHP function
  nonce  = f6g7h8i9j0
  cart_item_key = abc123xyz
```

WordPress looks for hooks named `wp_ajax_{action}` (for logged-in users) and `wp_ajax_nopriv_{action}` (for guests) and calls the registered callback.

#### The two AJAX actions that use `jadevAjax`

| `action` field | `nonce` key used | PHP callback | Purpose |
|---|---|---|---|
| `jadev_check_shipping` | `jadevAjax.nonce` | `jadev_check_shipping_rate()` | Delivery zip code lookup on product page |
| `jadev_remove_cart_item` | `jadevAjax.cart_nonce` | `jadev_remove_cart_item()` | Remove item from sidebar cart |

These two features are **completely independent**. They share the `jadevAjax` object only because it is convenient to register all frontend AJAX config in one `wp_localize_script()` call. `jadev_check_shipping_rate()` has nothing to do with the cart.

#### Cart item removal — full request-response cycle

**Step 1 — User clicks the trash icon**

JS intercepts the click, prevents navigation, and fades the row:

```js
jQuery(document.body).on('click', '#sidebar-cart .sidebar-cart-remove', function (e) {
    e.preventDefault();
    var $link = jQuery(this);
    var $item = $link.closest('li');
    $item.css({ opacity: 0.5, pointerEvents: 'none' }); // visual feedback
    ...
```

**Step 2 — JS posts to `admin-ajax.php`**

```js
    jQuery.post(jadevAjax.url, {
        action:        'jadev_remove_cart_item', // routes to jadev_remove_cart_item()
        nonce:         jadevAjax.cart_nonce,     // security token
        cart_item_key: $link.data('cart-item-key'), // which item to remove
    }, function (response) { ... });
```

`$link.data('cart-item-key')` reads the `data-cart-item-key` attribute on the remove link, which was written by PHP when the cart HTML was rendered:

```php
data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
```

The `$cart_item_key` is the key WooCommerce uses internally to identify a specific row in the cart array (e.g. `"b6d767d2f8ed5d21a44b0e5886680cb9"`). It is not a product ID.

**Step 3 — PHP verifies, removes, responds**

```php
function jadev_remove_cart_item()
{
    check_ajax_referer('jadev_cart_nonce', 'nonce'); // abort if nonce is wrong/expired

    $key = sanitize_text_field($_POST['cart_item_key'] ?? '');
    if ($key) {
        WC()->cart->remove_cart_item($key); // remove directly from the WC session
    }

    // Build fresh fragments to send back
    $count  = WC()->cart->get_cart_contents_count();
    $hidden = $count === 0 ? ' hidden' : '';
    $fragments = [
        '#sidebar-cart-body' => jadev_sidebar_cart_html(),
        '.cart-count-bubble' => '<span class="cart-count-bubble' . $hidden . '">' . $count . '</span>',
    ];

    wp_send_json_success(['fragments' => $fragments]);
    // ^ sends: { "success": true, "data": { "fragments": { ... } } }
}
```

`check_ajax_referer()` compares the posted nonce against what WordPress expects. If it doesn't match (expired, tampered, or missing), WordPress kills the request immediately. This prevents cross-site request forgery.

`WC()->cart->remove_cart_item($key)` removes the row directly from the WooCommerce cart object in the PHP session. No redirect. No page reload.

**Step 4 — JS receives the response and patches the DOM**

```js
    }, function (response) {
        if (response.success && response.data.fragments) {
            jQuery.each(response.data.fragments, function (selector, html) {
                jQuery(selector).replaceWith(html); // swap old DOM node for new HTML
            });
        }
    });
```

`jQuery.each` iterates the fragments object:
- `jQuery('#sidebar-cart-body').replaceWith(newHtml)` — replaces the entire item list + subtotal footer with a fresh render
- `jQuery('.cart-count-bubble').replaceWith(newHtml)` — updates the count badge in both the header button and the sidebar title

The whole cycle (click → POST → PHP removes → JSON response → DOM update) typically completes in under 200 ms.

#### Why not use `jQuery.get(removeUrl)`?

The WooCommerce standard remove URL (e.g. `?remove_item=abc123&_wpnonce=xyz`) works by calling `wp_safe_redirect()` when the item is removed and exiting PHP. In a normal browser navigation the redirect is followed and the page reloads with the item gone. In an AJAX context the redirect is followed silently but PHP exits before WordPress fully closes the session write, making the removal unreliable. There is also no way to get structured data back from that response. The custom AJAX endpoint avoids both problems.

---

### 6. JavaScript — `main.js`

#### `openSidebarCart()` / `closeSidebarCart()`

Global functions (not scoped to a listener) so they can be called from multiple event sources.

**Open sequence:**
1. Set `aria-hidden="false"` on the drawer.
2. Remove `pointer-events-none` from the overlay.
3. Double `requestAnimationFrame` — ensures the browser paints the element before the CSS transition begins, preventing a flash.
4. Remove `translate-x-full` from drawer → slides in.
5. Remove `opacity-0` / add `opacity-100` on overlay → fades in.
6. Add `overflow-hidden` to `<body>` → prevents background scroll.

**Close sequence:**
1. Add `translate-x-full` to drawer → slides out.
2. Fade overlay out.
3. Remove `overflow-hidden` from body.
4. After 300 ms (matching the CSS transition duration): re-add `pointer-events-none` to overlay and `aria-hidden="true"` to drawer.

#### Event listeners (wired in `DOMContentLoaded`)

| Element | Event | Action |
|---|---|---|
| `#sidebar-cart-toggle` | `click` | `openSidebarCart()` |
| `#sidebar-cart-close` | `click` | `closeSidebarCart()` |
| `#sidebar-cart-overlay` | `click` | `closeSidebarCart()` |
| `document` | `keydown Escape` | `closeSidebarCart()` |

#### Auto-open on add-to-cart

```js
jQuery(document.body).on('added_to_cart', openSidebarCart);
```

WooCommerce fires `added_to_cart` on `document.body` after a successful AJAX add-to-cart. The sidebar opens, and WooCommerce's fragment refresh (which happens automatically in the same AJAX cycle) updates the cart contents.

---

## Timber Context Variables

These are available in every Twig template:

| Variable | Type | Description |
|---|---|---|
| `cart_count` | `int` | Number of items currently in the cart |
| `cart_url` | `string` | URL of the WooCommerce cart page |
| `checkout_url` | `string` | URL of the WooCommerce checkout page |
| `shop_url` | `string` | URL of the WooCommerce shop page |
| `sidebar_cart_html` | `string` (raw HTML) | Rendered `#sidebar-cart-body` div |

All five are set inside the `timber/context` filter in `functions.php` and are only populated when WooCommerce is active.

---

## DOM IDs and Classes Reference

| Selector | Element | Purpose |
|---|---|---|
| `#sidebar-cart-toggle` | `<button>` in header | Opens the drawer |
| `#sidebar-cart-overlay` | `<div>` | Backdrop; click to close |
| `#sidebar-cart` | `<aside>` | The drawer panel itself |
| `#sidebar-cart-close` | `<button>` in drawer | Closes the drawer |
| `#sidebar-cart-body` | `<div>` inside drawer | Fragment target — replaced on cart changes |
| `.cart-count-bubble` | `<span>` (×2) | Item count badge; appears in header button and drawer header |
| `.sidebar-cart-remove` | `<a>` per item | Remove-item link; intercepted by JS for AJAX removal |

---

## CSS Transitions

All animation is handled with Tailwind utility classes and CSS transitions. No keyframes or animation libraries are used.

| Element | Closed state | Open state | Transition |
|---|---|---|---|
| `#sidebar-cart` | `translate-x-full` | `translate-x-0` (class removed) | `transition-transform duration-300 ease-in-out` |
| `#sidebar-cart-overlay` | `opacity-0 pointer-events-none` | `opacity-100` | `transition-opacity duration-300` |

---

## How to Customise

### Change the drawer width
In `views/woocommerce/sidebar-cart.twig`, change `max-w-[400px]` on the `<aside>`:
```twig
class="... max-w-[480px] ..."
```

### Change the slide-in direction (e.g. left side)
In the same file, replace:
- `right-0` → `left-0`
- `translate-x-full` → `-translate-x-full`

In `assets/js/main.js`, the JS only removes/adds `translate-x-full`, so update that class name in both `openSidebarCart` and `closeSidebarCart`.

### Change the product image size
In `jadev_sidebar_cart_html()` in `functions.php`, change the second argument to `wp_get_attachment_image_url()`:
```php
$img_url = wp_get_attachment_image_url($img_id, 'woocommerce_thumbnail');
```
Standard WooCommerce sizes: `thumbnail`, `woocommerce_thumbnail`, `woocommerce_single`, `full`.

### Change the count badge colour
In `views/base.twig`, update the Tailwind classes on `.cart-count-bubble`:
```twig
class="cart-count-bubble ... bg-[rgb(17,221,196)] text-[rgb(28,28,26)] ..."
```

### Add a quantity stepper
Replace the static `Qty: X` display in `jadev_sidebar_cart_html()` with a form that posts to `?wc-ajax=update_order_review`, or use WooCommerce's standard cart update URL with a quantity input.

### Show/hide the sidebar on the cart page
Add a check inside `openSidebarCart()` in `main.js`:
```js
if (document.body.classList.contains('woocommerce-cart')) return;
```

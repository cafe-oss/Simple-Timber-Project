# Favorites Feature

Allows any visitor to save WooCommerce products to a personal favorites list and view them on a dedicated page. Works differently depending on whether the visitor is logged in to WordPress or not.

- **Guests** — favorites are stored in the browser's `localStorage`. They persist across page loads but are device-specific and disappear if the browser data is cleared.
- **Logged-in users** — favorites are stored in WordPress user meta (`jadev_favorites`). They persist in the database, survive browser clears, and are tied to the WordPress account.

A heart button appears on every product card and on single product pages. Clicking it toggles the favorite state. A dedicated `/favorites` page shows all saved products.

---

## Files Involved

| File | Role |
|---|---|
| `functions.php` | AJAX handlers, JS data injection, WooCommerce coming-soon bypass |
| `page-favorites.php` | PHP page template for the favorites page |
| `views/page-favorites.twig` | Twig template that renders the favorites page |
| `views/components/favorite-button.twig` | Reusable heart toggle button component |
| `views/components/product-grid.twig` | Product grid — heart button overlaid on each card image |
| `views/woocommerce/single-product.twig` | Single product — heart button added beside the title |
| `page-performance-shop.php` | Product data builder — `product_id` key added to array |
| `page-tonal-accessories.php` | Product data builder — `product_id` key added to array |
| `assets/js/main.js` | All favorites JavaScript — toggle logic, AJAX, page loader |

---

## How the Two Storage Paths Work

Understanding this is essential to understanding all other parts of the feature.

### Guest path (localStorage)

When a visitor is not logged in, every favorite action reads and writes a single key in the browser's `localStorage`:

```
localStorage['jadev_favorites'] = "[101, 204, 307]"
```

The value is a JSON-encoded array of integer product IDs. There is no server involvement. When the visitor clicks a heart:

1. JS reads the array from `localStorage`.
2. If the product ID is in the array — remove it. If not — append it.
3. JS writes the updated array back to `localStorage`.
4. JS updates all heart buttons on the page to match the new state.

When the visitor lands on the favorites page, JS reads the IDs from `localStorage` and fires one AJAX request to get product data, then renders the cards.

### Logged-in path (user meta + AJAX)

When a visitor is logged in, favorites are stored in WordPress's user meta table:

```
wp_usermeta  →  user_id: 5, meta_key: jadev_favorites, meta_value: a:2:{i:0;i:101;i:1;i:204;}
```

The value is a PHP serialized array of integers (WordPress serializes arrays automatically). When a logged-in user clicks a heart:

1. JS fires a POST request to WordPress's `admin-ajax.php`.
2. PHP reads the user's current `jadev_favorites` meta, adds or removes the ID, and writes the updated array back to user meta.
3. PHP returns the full updated favorites list as JSON.
4. JS updates `jadevFavorites.favorites` in memory and updates all heart buttons.

The favorites page is rendered server-side using PHP — the template queries the product IDs from user meta and builds the product grid before sending HTML to the browser.

---

## PHP — `functions.php`

Three things are added to `functions.php`: the JS data injection, the toggle AJAX handler, and the product-fetch AJAX handler.

### 1. JS data injection — `jadevFavorites`

Runs at `wp_enqueue_scripts` priority 20 (after `jadev-main` is enqueued at the default priority 10):

```php
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
```

`wp_localize_script()` adds an inline `<script>` block immediately before `main.js` loads:

```html
<script>
var jadevFavorites = {
    "nonce":      "a1b2c3d4",
    "isLoggedIn": true,
    "favorites":  [101, 204]
};
</script>
```

| Property | Type | Description |
|---|---|---|
| `nonce` | `string` | A short-lived security token. Used to verify every AJAX request came from a legitimate page load. Generated fresh on each page load by `wp_create_nonce()`. Expires after 12 hours. |
| `isLoggedIn` | `bool` | `true` when the current visitor has a WordPress session. JS uses this to decide whether to write to `localStorage` or call AJAX. |
| `favorites` | `int[]` | For logged-in users: the product IDs already saved, so JS can set the correct heart state on page load without an extra round trip. Empty array `[]` for guests (their state is read from `localStorage`). |

---

### 2. AJAX action — `jadev_toggle_favorite`

**Registered as:** `wp_ajax_jadev_toggle_favorite` only. There is no `nopriv` version because guests never call this endpoint — they use `localStorage` instead.

**When called:** Every time a logged-in user clicks a heart button.

**Request (POST):**

| Field | Value |
|---|---|
| `action` | `jadev_toggle_favorite` |
| `nonce` | `jadevFavorites.nonce` |
| `product_id` | Integer ID of the product being toggled |

**What PHP does:**

```php
function jadev_toggle_favorite()
{
    // 1. Kill the request immediately if the nonce is wrong or expired.
    check_ajax_referer('jadev_favorites_nonce', 'nonce');

    // 2. Sanitise the incoming product ID.
    $product_id = intval($_POST['product_id'] ?? 0);
    if (!$product_id) {
        wp_send_json_error(['message' => 'Invalid product ID.']);
        return;
    }

    // 3. Load the user's current saved list from the database.
    $user_id   = get_current_user_id();
    $favorites = get_user_meta($user_id, 'jadev_favorites', true) ?: [];
    $favorites = array_values(array_map('intval', (array) $favorites));

    // 4. Toggle: remove if present, add if not.
    $key = array_search($product_id, $favorites, true);
    if ($key !== false) {
        unset($favorites[$key]);    // un-favorite
        $favorited = false;
    } else {
        $favorites[] = $product_id; // favorite
        $favorited   = true;
    }

    // 5. Re-index and persist back to the database.
    $favorites = array_values($favorites);
    update_user_meta($user_id, 'jadev_favorites', $favorites);

    // 6. Send the full updated list back so JS can sync all buttons on the page.
    wp_send_json_success([
        'favorited' => $favorited,
        'favorites' => $favorites,
    ]);
}
```

**Response (success):**

```json
{
    "success": true,
    "data": {
        "favorited": true,
        "favorites": [101, 180, 204]
    }
}
```

The `favorites` array contains **every** product the user has saved, not just the one that was toggled. JS replaces the entire in-memory list with this and re-checks every heart button on the page — this keeps multiple open tabs consistent.

---

### 3. AJAX action — `jadev_get_favorites_products`

**Registered as:** Both `wp_ajax_` and `wp_ajax_nopriv_` — this is a public endpoint because guests call it from the favorites page to fetch product data for the IDs they have in `localStorage`.

**When called:** Once, when a guest lands on the `/favorites` page and JS has found saved product IDs in `localStorage`.

**Request (POST):**

| Field | Value |
|---|---|
| `action` | `jadev_get_favorites_products` |
| `nonce` | `jadevFavorites.nonce` |
| `product_ids[]` | One entry per saved ID (sent as a PHP array via `URLSearchParams.append`) |

**What PHP does:**

```php
function jadev_get_favorites_products()
{
    // 1. Verify nonce.
    check_ajax_referer('jadev_favorites_nonce', 'nonce');

    // 2. Sanitise every ID — intval() forces integers, array_filter() removes zeros.
    $ids = array_values(array_filter(array_map('intval', (array) ($_POST['product_ids'] ?? []))));

    if (empty($ids)) {
        wp_send_json_success(['products' => []]);
        return;
    }

    // 3. Query only published products whose IDs match the list.
    //    'orderby' => 'post__in' preserves the save order from localStorage.
    $query = new WP_Query([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'post__in'       => $ids,
        'orderby'        => 'post__in',
        'posts_per_page' => -1,
    ]);

    // 4. Build the product data array (same shape as the product-grid component uses).
    $products = [];
    foreach ($query->posts as $p) {
        $rp       = wc_get_product($p->ID);
        if (!$rp) continue;
        $image_id   = $rp->get_image_id();
        $products[] = [
            'product_id'       => $rp->get_id(),
            'card_title'       => $rp->get_name(),
            'card_description' => wp_trim_words(wp_strip_all_tags($rp->get_description()), 15, '…'),
            'image_src'        => wp_get_attachment_image_url($image_id, 'woocommerce_single') ?: '',
            'image_alt'        => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $rp->get_name(),
            'product_url'      => get_permalink($rp->get_id()),
            'add_to_cart_url'  => $rp->add_to_cart_url(),
            'price_html'       => $rp->get_price_html(),
            'is_new'           => (bool) get_field('is_new', $p->ID),
        ];
    }

    wp_reset_postdata();
    wp_send_json_success(['products' => $products]);
}
```

**Why the IDs are re-validated by `WP_Query`:**
The IDs come from the browser (untrusted). Even after `intval()` sanitisation, a guest could theoretically send IDs of draft products, private posts, or non-product post types. The `post_type => 'product'` and `post_status => 'publish'` constraints in the query ensure only publicly visible products are returned, regardless of what IDs the client sends.

**Response (success):**

```json
{
    "success": true,
    "data": {
        "products": [
            {
                "product_id": 180,
                "card_title": "Tonal Ankle Straps",
                "card_description": "Sculpt, tone, and strengthen your lower body…",
                "image_src": "https://…/ankle-straps.webp",
                "image_alt": "Tonal Ankle Straps",
                "product_url": "https://…/product/tonal-ankle-straps/",
                "add_to_cart_url": "https://…/?add-to-cart=180",
                "price_html": "<span class=\"woocommerce-Price-amount\">…</span>",
                "is_new": true
            }
        ]
    }
}
```

---

### 4. WooCommerce Coming Soon bypass

WooCommerce's "Coming Soon" mode (enabled at WooCommerce → Settings → Store management) blocks non-logged-in visitors from accessing shop pages and replaces their content with a coming-soon block. Since the favorites page is used by guests, it must be excluded:

```php
add_filter('woocommerce_is_coming_soon', function ($is_coming_soon) {
    if (is_page('favorites')) {
        return false; // never treat this page as coming soon, regardless of store setting
    }
    return $is_coming_soon;
});
```

`is_page('favorites')` checks by slug. If the WordPress page slug is ever changed, update this value to match.

---

## The Heart Button — `views/components/favorite-button.twig`

A self-contained partial included anywhere a product ID is available.

```twig
{% include 'components/favorite-button.twig' with {
    product_id: 180,
    add_class: 'w-9 h-9 flex items-center justify-center ...'
} %}
```

**Parameters:**

| Param | Required | Description |
|---|---|---|
| `product_id` | Yes | The WooCommerce product ID. Written to `data-product-id` so JS can read it on click. |
| `add_class` | No | Additional Tailwind classes. Used to control size and position depending on where the button appears (card overlay vs. single product). |

**Rendered HTML:**

```html
<button
    class="favorite-btn [add_class] cursor-pointer"
    data-product-id="180"
    type="button"
    aria-label="Add to favorites"
    aria-pressed="false">

    <!-- Shown when NOT favorited -->
    <svg class="fav-icon-outline w-5 h-5" ...>...</svg>

    <!-- Shown when favorited (hidden by default) -->
    <svg class="fav-icon-filled w-5 h-5 hidden" ...>...</svg>
</button>
```

`cursor-pointer` is hardcoded on the component itself (not left to `add_class`) so the pointer cursor is guaranteed on every button regardless of where it is included. The button always starts in the un-favorited state in HTML. JavaScript applies the correct state on `DOMContentLoaded` by reading favorites from either `localStorage` or `jadevFavorites.favorites`.

**State classes managed by JS:**

| State | Classes on `<button>` | `.fav-icon-outline` | `.fav-icon-filled` |
|---|---|---|---|
| Not favorited | `text-gray-500` | visible | `hidden` |
| Favorited | `is-favorited text-red-500` | `hidden` | visible |

The `is-favorited` class is used as a JS hook (not styled directly). `text-red-500` colours the filled SVG red.

---

## Where the Heart Button Appears

### Product grid — `views/components/product-grid.twig`

The button is overlaid in the top-right corner of the product image. It is wrapped in an absolute-positioned `<div>` inside the image container:

```twig
<div class="relative w-full" style="padding-bottom: 102.77%;">

    {# ... "New" badge (top-left) ... #}

    {% if product.product_id %}
        <div class="absolute right-3 top-3 z-2">
            {% include 'components/favorite-button.twig' with {
                product_id: product.product_id,
                add_class: 'w-9 h-9 ... bg-white/80 hover:bg-white shadow-sm'
            } %}
        </div>
    {% endif %}

    <img class="absolute inset-0 ..." ...>
</div>
```

The `{% if product.product_id %}` guard prevents a broken button from rendering on any card that was built without the `product_id` key (e.g. banner cards, which use a different data shape).

**Why `product_id` had to be added to the data builders:**
The product grid component works with a plain PHP array built in page templates like `page-performance-shop.php`. This array never included a `product_id` key. The WooCommerce product ID was used internally during construction (`$rp->get_id()`) but was never stored in the array. Adding it explicitly makes it available to Twig:

```php
// page-performance-shop.php and page-tonal-accessories.php
$products[] = [
    'product_id'  => $rp->get_id(),   // ← added
    'card_title'  => $rp->get_name(),
    // ...
];
```

### Single product — `views/woocommerce/single-product.twig`

The button sits to the right of the product title in the summary column:

```twig
<div class="flex items-start justify-between gap-4">
    <h2 class="text-[2.5rem] mt-[0.5em] mb-2 ...">{{ post.title }}</h2>
    {% include 'components/favorite-button.twig' with {
        product_id: post.ID,
        add_class: 'mt-[0.75em] flex-shrink-0 w-10 h-10 ... border rounded-full'
    } %}
</div>
```

`post.ID` is available because `woocommerce.php` sets `$context['post'] = Timber::get_post()` for all `is_singular('product')` pages. The Timber post object's `.ID` property equals the WooCommerce product ID.

---

## JavaScript — `assets/js/main.js`

Three separate `DOMContentLoaded` blocks handle favorites.

---

### Block 1 — Heart toggle (runs on every page)

This is the core block. It runs on every page load, initialises button states, and handles all click events.

#### Setup variables

```js
var STORAGE_KEY = 'jadev_favorites';
var isLoggedIn  = typeof jadevFavorites !== 'undefined' && jadevFavorites.isLoggedIn;
```

`typeof jadevFavorites !== 'undefined'` guards against a scenario where `wp_localize_script` didn't run (e.g. the script was dequeued). Without this guard, accessing an undefined variable would throw a `ReferenceError` and crash the entire block.

#### `getFavorites()`

Returns the current list of favorited product IDs as an array of numbers.

```js
function getFavorites() {
    if (isLoggedIn) {
        // Use the list PHP injected into the page. This is always up-to-date
        // because wp_localize_script runs on every server render.
        return (jadevFavorites.favorites || []).map(Number);
    }
    try {
        // Parse the JSON string stored in localStorage.
        // The try/catch handles corrupt or non-JSON values gracefully.
        return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]').map(Number);
    } catch (e) {
        return [];
    }
}
```

`.map(Number)` ensures all IDs are native JavaScript numbers, not strings. This matters because `localStorage` stores everything as strings, so `"180"` becomes `180`. Strict comparisons like `favorites.includes(180)` would fail against `"180"`.

#### `saveFavoritesLocally(favorites)`

```js
function saveFavoritesLocally(favorites) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(favorites));
    } catch (e) {}
}
```

The `try/catch` handles `QuotaExceededError` — thrown when `localStorage` is full (rare but possible on some mobile browsers).

Only called on the guest path. Logged-in users never write to `localStorage` — their data lives in the database.

#### `setButtonState(btn, isFavorited)`

Updates a single button's visual appearance and ARIA attributes:

```js
function setButtonState(btn, isFavorited) {
    var outline = btn.querySelector('.fav-icon-outline');
    var filled  = btn.querySelector('.fav-icon-filled');

    btn.setAttribute('aria-pressed', isFavorited ? 'true' : 'false');
    btn.setAttribute('aria-label',   isFavorited ? 'Remove from favorites' : 'Add to favorites');
    btn.classList.toggle('is-favorited', isFavorited);

    if (isFavorited) {
        outline && outline.classList.add('hidden');
        filled  && filled.classList.remove('hidden');
        btn.classList.add('text-red-500');
        btn.classList.remove('text-gray-500');
    } else {
        outline && outline.classList.remove('hidden');
        filled  && filled.classList.add('hidden');
        btn.classList.remove('text-red-500');
        btn.classList.add('text-gray-500');
    }
}
```

`aria-pressed` makes the button semantically correct for screen readers — it communicates a toggle state ("this is currently pressed / not pressed") rather than just an action. `aria-label` updates the spoken label to match the current action the button will take next.

#### `applyFavoriteStates(favorites)`

Iterates **every** `.favorite-btn` on the page and calls `setButtonState` based on whether that button's `data-product-id` is in the `favorites` array:

```js
function applyFavoriteStates(favorites) {
    document.querySelectorAll('.favorite-btn').forEach(function (btn) {
        var id = parseInt(btn.dataset.productId, 10);
        setButtonState(btn, favorites.includes(id));
    });
}
```

This is called both on page load (initial state) and after every toggle (to keep all buttons in sync — if the same product appears twice on a page, both hearts update).

#### `toggleFavorite(btn)`

The main toggle function. Reads the product ID from the button, then branches based on login state:

**Logged-in path:**

```js
var body = new URLSearchParams({
    action:     'jadev_toggle_favorite',
    nonce:      jadevFavorites.nonce,
    product_id: productId,
});

fetch(jadevAjax.url, { method: 'POST', body: body })
    .then(function (res) { return res.json(); })
    .then(function (data) {
        if (data.success) {
            jadevFavorites.favorites = data.data.favorites; // update in-memory list
            applyFavoriteStates(data.data.favorites);       // sync all buttons
            dispatchFavoritesUpdated(data.data.favorites);  // notify favorites page
        }
    });
```

Updating `jadevFavorites.favorites` in memory is important: `applyFavoriteStates` here is called with `data.data.favorites` directly — it has no connection to `getFavorites()`. The in-memory update keeps `getFavorites()` accurate for any future reads (e.g. if the user toggles a second button before the server responds).

**Guest path:**

```js
var favorites = getFavorites();          // read localStorage
var idx = favorites.indexOf(productId);

if (idx !== -1) { //because array starts from 0
    favorites.splice(idx, 1);           // remove
} else {
    favorites.push(productId);          // add
}

saveFavoritesLocally(favorites);         // write localStorage
applyFavoriteStates(favorites);          // sync all buttons
dispatchFavoritesUpdated(favorites);     // notify favorites page
```

#### `dispatchFavoritesUpdated(favorites)`

Fires a `CustomEvent` on `#favorites-products-grid` if that element exists:

```js
function dispatchFavoritesUpdated(favorites) {
    var grid = document.getElementById('favorites-products-grid');
    if (grid) {
        grid.dispatchEvent(new CustomEvent('favorites-updated', { detail: favorites }));
    }
}
```
This just says "hey grid, something changed, here's the new list." That's it. 

This is how live card removal on the favorites page works without a page reload. The event carries the updated ID list in `detail`. Both the logged-in view (Block 2) and the guest view (Block 3) have listeners grid.addEventListener('favorites-updated', ...);   on the grid that remove any card whose product ID is no longer in the list.

#### Event delegation

A single click listener on `document` catches clicks on any `.favorite-btn`, including buttons added to the DOM after page load (e.g. by the guest loader):

```js
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.favorite-btn');
    if (!btn) return;
    e.preventDefault(); // prevent any default <button> form submission
    toggleFavorite(btn);
});
```

`e.target.closest('.favorite-btn')` walks up the DOM from the exact element clicked. This handles clicks on the SVG icon itself (which is a child of the button) — `closest` finds the `.favorite-btn` ancestor correctly.


### There are three favorites-related DOMContentLoaded blocks in main.js:

  ┌─────────┬────────────────────────────┬─────────────────────────────────────────┐
  │  Block  │          Runs on           │                   Job                   │
  ├─────────┼────────────────────────────┼─────────────────────────────────────────┤
  │ Block 1 │ Every page                 │ Core toggle logic, button state init    │
  ├─────────┼────────────────────────────┼─────────────────────────────────────────┤
  │ Block 2 │ Favorites page (logged-in) │ Empty state + live card removal         │
  ├─────────┼────────────────────────────┼─────────────────────────────────────────┤
  │ Block 3 │ Favorites page (guest)     │ AJAX product loader + live card removal │
  └─────────┴────────────────────────────┴─────────────────────────────────────────┘

  "Block 1/2/3" isn't JavaScript terminology — it's just a naming convention used in the docs to make it easier to refer to each section.

#### Initialization

```js
applyFavoriteStates(getFavorites());
```

Runs last, after all handlers are set up. Sets the correct heart state on every button already in the DOM at page load. This is synchronous — no flicker. at the bottom of Block 1

---

### Block 2 — Favorites page: logged-in empty state + live card removal

Runs only on the favorites page when the logged-in server-rendered view is present (identified by the absence of `#favorites-loading`, which only exists in the guest view).

```js
document.addEventListener('DOMContentLoaded', function () {
    var grid    = document.getElementById('favorites-products-grid');
    var empty   = document.getElementById('favorites-empty');
    var loading = document.getElementById('favorites-loading');

    // Bail if this is the guest view (loading spinner exists) or not the favorites page.
    if (!grid || !empty || loading) return;

    // Count server-rendered product cards.
    var hasCards = grid.querySelectorAll(':scope > .flex.flex-col').length > 0;
    if (!hasCards) {
        empty.classList.remove('hidden'); // show "no favorites" message
    }

    // Remove a card live when the user un-favorites it while on this page.
    // dispatchFavoritesUpdated() in Block 1 fires this event after every toggle.
    grid.addEventListener('favorites-updated', function (e) {
        var currentIds = (e.detail || []).map(Number);

        grid.querySelectorAll('.favorite-btn[data-product-id]').forEach(function (btn) {
            var id = parseInt(btn.dataset.productId, 10);
            if (!currentIds.includes(id)) {
                btn.closest('.flex.flex-col').remove();
            }
        });

        if (!grid.querySelectorAll(':scope > .flex.flex-col').length) {
            grid.classList.add('hidden');
            empty.classList.remove('hidden');
        }
    });
});
```

**Initial empty state — why JS controls this instead of Twig:**
Twig inline ternary expressions (`{{ condition ? 'hidden' : '' }}`) have unreliable behaviour when the condition involves array truthiness checks. Using a `{% if %}` block test in the class attribute also proved unreliable across Timber/Twig versions. Moving the empty state visibility entirely to JS avoids this issue: the Twig template always renders `#favorites-empty` with `class="... hidden"`, and JS removes `hidden` immediately on page load if needed. Since this runs synchronously before the first paint, there is no visible flicker.

**Live card removal — how it works:**
When the user clicks a heart button on the favorites page, Block 1's `toggleFavorite()` runs, updates the database, and then calls `dispatchFavoritesUpdated()`. That function fires a `favorites-updated` CustomEvent on `#favorites-products-grid`. The listener added here receives the event, iterates every `.favorite-btn` in the grid, and removes the parent `.flex.flex-col` card element for any product ID that is no longer in the updated favorites list. If the grid becomes empty after the removal, it is hidden and `#favorites-empty` is revealed — all without a page reload.

`:scope > .flex.flex-col` selects only direct children with that class combination, avoiding false matches from nested elements.

---

### Block 3 — Favorites page: guest product loader

Runs only on the favorites page when the guest view is present — identified by the existence of `#favorites-loading`, which is only rendered by Twig for non-logged-in visitors.

```js
document.addEventListener('DOMContentLoaded', function () {
    var grid    = document.getElementById('favorites-products-grid');
    var loading = document.getElementById('favorites-loading');
    var empty   = document.getElementById('favorites-empty');

    // Guard: only run if both the grid AND the guest loading spinner are present.
    // On the logged-in view, #favorites-loading does not exist, so this returns early
    // and never interferes with the server-rendered content or the empty state.
    if (!grid || !loading) return;

    // Read saved IDs from localStorage.
    var ids = [];
    try {
        ids = JSON.parse(localStorage.getItem('jadev_favorites') || '[]').map(Number).filter(Boolean);
    } catch (e) {}

    // If nothing saved, skip the AJAX call and show the empty state immediately.
    if (!ids.length) {
        loading && loading.classList.add('hidden');
        empty   && empty.classList.remove('hidden');
        return;
    }

    // Build the AJAX request. Each ID is appended as a separate product_ids[] entry
    // so PHP receives it as $_POST['product_ids'] = [101, 180, ...].
    var body = new URLSearchParams({ action: 'jadev_get_favorites_products', nonce: jadevFavorites.nonce });
    ids.forEach(function (id) { body.append('product_ids[]', id); });

    fetch(jadevAjax.url, { method: 'POST', body: body })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            loading && loading.classList.add('hidden');

            if (!data.success || !data.data.products.length) {
                empty && empty.classList.remove('hidden');
                return;
            }

            // Build a card for each product and append to the grid.
            // The heart button is injected with is-favorited and cursor-pointer already
            // set — all products on the favorites page are by definition favorited.
            data.data.products.forEach(function (p) {
                var card = document.createElement('div');
                card.className = 'flex flex-col gap-4';
                card.innerHTML = /* ... */ ;
                grid.appendChild(card);
            });

            grid.classList.remove('hidden');

            // Live removal: listen for the favorites-updated event fired by Block 1
            // when the user un-favorites a product while on this page.
            grid.addEventListener('favorites-updated', function (e) {
                var currentIds = e.detail || [];
                grid.querySelectorAll('.favorite-btn[data-product-id]').forEach(function (btn) {
                    var id = parseInt(btn.dataset.productId, 10);
                    if (!currentIds.includes(id)) {
                        btn.closest('.flex.flex-col').remove(); // remove the card from DOM
                    }
                });
                if (!grid.querySelectorAll('.flex.flex-col').length) {
                    grid.classList.add('hidden');
                    empty && empty.classList.remove('hidden');
                }
            });
        })
        .catch(function () {
            loading && loading.classList.add('hidden');
            empty   && empty.classList.remove('hidden');
        });
});
```

**`escHtml(str)` — XSS prevention**

Product data returned from the server (titles, URLs) is inserted into `innerHTML`. Before any string goes into `innerHTML`, it is passed through a local escape helper:

```js
function escHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
```

`price_html` is intentionally NOT escaped — it contains valid WooCommerce HTML (`<span>`, `<bdi>`, currency symbols) that must render as HTML.

---

## Page Template — `page-favorites.php` + `views/page-favorites.twig`

### How WordPress selects `page-favorites.php`

WordPress uses a naming convention: if a file named `page-{slug}.php` exists in the theme root, it is automatically used as the template for any WordPress page whose slug matches `{slug}`. No `Template Name:` PHP comment is needed. When a page with slug `favorites` is visited, WordPress loads `page-favorites.php` before rendering anything.

### `page-favorites.php` — PHP logic

```php
if (is_user_logged_in() && class_exists('WooCommerce')) {

    // Get the user's saved IDs from the database.
    $user_id   = get_current_user_id();
    $favorites = get_user_meta($user_id, 'jadev_favorites', true) ?: [];
    $favorites = array_values(array_map('intval', (array) $favorites));

    $products = [];
    if (!empty($favorites)) {
        // Query only those specific product IDs, preserving save order.
        $query = new WP_Query([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'post__in'       => $favorites,
            'orderby'        => 'post__in',
            'posts_per_page' => -1,
        ]);
        foreach ($query->posts as $p) {
            $rp = wc_get_product($p->ID);
            if (!$rp) continue; // skip if product was deleted from WooCommerce
            // ... build product data array ...
        }
        wp_reset_postdata();
    }

    $context['favorites_products'] = $products;
    $context['is_logged_in']       = true;

} else {
    // Guest: products will be loaded by JavaScript from localStorage via AJAX.
    $context['favorites_products'] = null;
    $context['is_logged_in']       = false;
}

Timber::render('page-favorites.twig', $context);
```

`wp_reset_postdata()` is called after the custom `WP_Query` to restore the global `$post` variable to the current page's post. Without it, functions like `is_page()` and `get_the_title()` would return data from the last queried product instead of the favorites page.

`wc_get_product($p->ID)` returns a `WC_Product` object with full product data. The check `if (!$rp) continue` skips any post whose product data could not be loaded (e.g. a product deleted or corrupted after the ID was saved to favorites).

### `views/page-favorites.twig` — Template structure

The template branches on `is_logged_in`:

**Logged-in branch — server-rendered grid:**

```twig
{% if is_logged_in %}

    {# Always present, always hidden. JS shows it when the grid has no cards. #}
    <div id="favorites-empty" class="py-20 text-center hidden">
        <p ...>You haven't saved any favorites yet.</p>
        <a href="/shop" class="button">Browse Products</a>
    </div>

    {# Always rendered. Contains zero or more server-rendered product cards. #}
    <div id="favorites-products-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 ...">
        {% for product in favorites_products %}
            <div class="flex flex-col gap-4">
                ...product card with favorite button...
            </div>
        {% endfor %}
    </div>

{% else %}
    ...guest branch...
{% endif %}
```

Both `#favorites-empty` and `#favorites-products-grid` are always in the DOM. This lets JS manipulate both elements without checking whether they exist first, and it allows live card removal (Block 1's `favorites-updated` event) to work after the initial render.

**Guest branch — JS placeholder:**

```twig
{% else %}
    <div id="favorites-guest-container">

        {# Shown immediately on page load. Hidden by JS when the AJAX response arrives. #}
        <div id="favorites-loading" class="py-20 text-center text-sm">
            Loading your favorites&hellip;
        </div>

        {# Hidden. JS shows this when there are no saved IDs or AJAX returns empty. #}
        <div id="favorites-empty" class="py-20 text-center hidden">...</div>

        {# Hidden, empty. JS appends cards here and removes 'hidden'. #}
        <div id="favorites-products-grid" class="grid ... hidden"></div>

    </div>
{% endif %}
```

`#favorites-loading` is the key discriminator. Block 2 (logged-in empty state) bails out if `#favorites-loading` exists. Block 3 (guest loader) bails out if `#favorites-loading` does NOT exist. This ensures the two JS blocks never interfere with each other.

---

## Full Request-Response Cycle (Logged-In Toggle)

```
User clicks heart on a product card
            │
            ▼
JS: e.target.closest('.favorite-btn')  →  found
JS: toggleFavorite(btn)
JS: productId = parseInt(btn.dataset.productId)  →  180
JS: isLoggedIn = true  →  AJAX path
            │
            ▼
POST /wp-admin/admin-ajax.php
  action     = jadev_toggle_favorite
  nonce      = a1b2c3d4
  product_id = 180
            │
            ▼
PHP: check_ajax_referer()            →  nonce valid
PHP: get_user_meta(5, 'jadev_favorites')  →  [101, 204]
PHP: search for 180  →  not found
PHP: push 180  →  [101, 204, 180]
PHP: update_user_meta(5, 'jadev_favorites', [101, 204, 180])
PHP: wp_send_json_success({ favorited: true, favorites: [101, 204, 180] })
            │
            ▼
JS: jadevFavorites.favorites = [101, 204, 180]
JS: applyFavoriteStates([101, 204, 180])
    → finds all .favorite-btn on page
    → btn#180: setButtonState(btn, true)
       → adds is-favorited, text-red-500
       → hides outline SVG, shows filled SVG
       → aria-pressed="true", aria-label="Remove from favorites"
JS: dispatchFavoritesUpdated([101, 204, 180])
    → if on favorites page, fires CustomEvent on #favorites-products-grid
```

---

## Full Request-Response Cycle (Guest Favorites Page Load)

```
Guest visits /favorites
            │
            ▼
PHP: is_user_logged_in() → false
PHP: $context['is_logged_in'] = false
PHP: Timber::render('page-favorites.twig', $context)
            │
            ▼
Browser receives HTML with:
  #favorites-loading  (visible)
  #favorites-empty    (hidden)
  #favorites-products-grid  (hidden, empty)
            │
            ▼
DOMContentLoaded fires
Block 3 (guest loader) runs:
  ids = JSON.parse(localStorage['jadev_favorites'])  →  [180]
            │
            ▼
POST /wp-admin/admin-ajax.php
  action       = jadev_get_favorites_products
  nonce        = a1b2c3d4
  product_ids[] = 180
            │
            ▼
PHP: check_ajax_referer()  →  valid
PHP: WP_Query({ post__in: [180], post_type: 'product' })
PHP: builds product data array for ID 180
PHP: wp_send_json_success({ products: [ {...} ] })
            │
            ▼
JS: loading.classList.add('hidden')     →  loading disappears
JS: builds card HTML for product 180   →  appended to grid
JS: grid.classList.remove('hidden')    →  grid appears
JS: applyFavoriteStates([180])         →  heart on new card set to filled/red
JS: grid.addEventListener('favorites-updated', ...)  →  live removal ready
```

---

## Setup — Creating the WordPress Page

1. Go to **WordPress Admin → Pages → Add New**.
2. Set the title to anything (e.g. `Favorites`).
3. Set the **slug** (URL) to exactly `favorites`.
4. Leave the content area empty — the template provides all content.
5. Publish.

WordPress will automatically use `page-favorites.php` because the slug matches the filename. If you change the slug, rename `page-favorites.php` to match.

---

## DOM IDs and Classes Reference

| Selector | Element | Present in | Purpose |
|---|---|---|---|
| `.favorite-btn` | `<button>` | Every product card / single product | Heart toggle button; targeted by event delegation |
| `data-product-id` | attribute on `.favorite-btn` | Same as above | Identifies which product to toggle |
| `.fav-icon-outline` | `<svg>` inside button | Same | Shown when not favorited |
| `.fav-icon-filled` | `<svg>` inside button | Same | Shown when favorited |
| `.is-favorited` | class on `.favorite-btn` | Same | JS hook to identify active state |
| `#favorites-products-grid` | `<div>` | Favorites page | Grid of product cards; target of `favorites-updated` event |
| `#favorites-empty` | `<div>` | Favorites page | "No favorites yet" message; toggled by JS |
| `#favorites-loading` | `<div>` | Favorites page (guest only) | Loading spinner; discriminator between logged-in and guest views |
| `#favorites-guest-container` | `<div>` | Favorites page (guest only) | Wrapper for the three guest-view elements |

---

## Security

| Concern | Mitigation |
|---|---|
| Unauthorized toggle | `check_ajax_referer()` verifies the nonce on every AJAX call before any data is read or written |
| Logged-in toggle by guests | `jadev_toggle_favorite` is registered only under `wp_ajax_` (not `wp_ajax_nopriv_`), so WordPress rejects it for unauthenticated requests automatically |
| Fake product IDs from guests | `jadev_get_favorites_products` sanitises all IDs with `intval()` and constrains the database query to `post_type => 'product'` and `post_status => 'publish'`, so private, draft, or non-product IDs are silently ignored |
| XSS in guest card HTML | All product data inserted via `innerHTML` in the guest loader passes through the local `escHtml()` function; `price_html` is the only exception (intentionally raw, as it contains valid WooCommerce markup) |
| Nonce expiry | WordPress nonces expire after 12 hours. If a user leaves a product page open overnight and clicks a heart, the AJAX call will fail silently (the `.catch` block absorbs the error). The page must be refreshed to get a fresh nonce. This is standard WordPress AJAX behaviour. |

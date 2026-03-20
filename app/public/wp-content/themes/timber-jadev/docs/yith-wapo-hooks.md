# YITH WooCommerce Product Add-Ons — Frontend Hooks

Reference for the two action hooks that fire during YITH WAPO's front-end render on the single product page.

---

## Render order inside the add-to-cart form

```
<form class="cart">
    │
    ├── [YITH add-on blocks]          ← Smart Accessories, Extended Warranty, etc.
    │        ↑ yith_wapo_after_addons fires here
    ├── [#wapo-total-price-table]     ← running order total
    │        ↑ yith_wapo_after_main_container fires here
    ├── [quantity input]
    └── [Add to cart button]
</form>
```

---

## `yith_wapo_after_addons`

**Source:** `wp-content/plugins/yith-woocommerce-product-add-ons/includes/class-yith-wapo-front.php:564`

Fires after all add-on blocks have been printed, but **before** the `#wapo-total-price-table` renders.

Use this hook when you need content to appear between the add-on options and the price summary table — still inside the WooCommerce `<form>`.

### Usage

```php
add_action('yith_wapo_after_addons', function() {
    // output HTML here
});
```

### Current use in this theme

`woocommerce.php` uses this hook to inject the White-Glove Delivery zip code lookup form between the add-on options and the price table, when the ACF field `show_delivery_lookup` is toggled on for the product:

```php
if ( get_field('show_delivery_lookup', $post_id) ) {
    add_action('yith_wapo_after_addons', function() {
        ?>
        <div class="delivery-lookup pt-8">
            ...
        </div>
        <?php
    });
}
```

---

## `yith_wapo_after_main_container`

**Source:** `wp-content/plugins/yith-woocommerce-product-add-ons/templates/front/addons-container.php:36`

Fires after the entire `#yith-wapo-container` div closes — including the price table — but **before** the WooCommerce quantity input and Add to cart button.

Use this hook when you need content after the full YITH block (add-ons + price table), still inside the `<form>`.

### Usage

```php
add_action('yith_wapo_after_main_container', function() {
    // output HTML here
});
```

---

## Choosing between the two

| Hook | Position | Use when |
|------|----------|----------|
| `yith_wapo_after_addons` | After add-on options, before price table | Content should appear before the order total summary |
| `yith_wapo_after_main_container` | After price table, before quantity + button | Content should appear after the full YITH block |

---

## Notes

- Both hooks fire **inside** the WooCommerce `<form class="cart">`, so any `<input>` elements you add will be submitted with the cart form. Use `name` attributes carefully to avoid conflicts.
- If a product has no YITH add-ons configured, `yith_wapo_after_addons` may not fire. Guard with a product check if needed.
- Hook registration in `woocommerce.php` runs at template load time, after all plugins are initialised, so there is no priority conflict with YITH's own registrations.

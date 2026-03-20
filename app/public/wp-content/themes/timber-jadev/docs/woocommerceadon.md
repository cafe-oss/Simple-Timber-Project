Option A — WooCommerce Product Add-Ons plugin (Recommended)

  Install the free "YITH WooCommerce Product Add-Ons" plugin or the official "WooCommerce Product Add-Ons" (paid). This lets you add:
  - A checkbox for Smart Accessories Bundle (+$495)
  - A radio group for Warranty (None / 4-Year +$425 / 5-Year +$449)

  This is the most accurate replica of Tonal's UX with the least complexity.


  Part 1 — Create the Tonal 2 Product WP Admin → Products → Add New 

  Basic info:
  - Title: Tonal 2
  - Short description (right panel):
  The ultimate home strength training system. Includes white-glove delivery & in-home installation and a 2-year limited warranty.

  Product Data panel (below the editor) → set dropdown to Simple product

  - Regular price: 4295
  - SKU: tonal-2 (optional but good practice)

  Right panel:
  - Product image → upload your Tonal 2 hero image
  - Product gallery → upload additional angles

  Hit Publish when done.

  ---
  Part 2 — Set Up YITH Add-Ons for Tonal 2

  WP Admin → YITH → Product Add-Ons → Add New

  ---
  Add-On Group 1 — Smart Accessories Bundle

  ┌──────────────┬────────────────────────────────────┐
  │    Field     │               Value                │
  ├──────────────┼────────────────────────────────────┤
  │ Group name   │ Smart Accessories Bundle           │
  ├──────────────┼────────────────────────────────────┤
  │ Display type │ Checkbox                           │
  ├──────────────┼────────────────────────────────────┤
  │ Apply to     │ Specific products → select Tonal 2 │
  └──────────────┴────────────────────────────────────┘

  Add one option inside the group:

  ┌──────────────┬────────────────────────────────────────────────────────────────────────────────────────┐
  │    Field     │                                         Value                                          │
  ├──────────────┼────────────────────────────────────────────────────────────────────────────────────────┤
  │ Option label │ Add Smart Accessories Bundle (Smart Handles, Smart Bar, Rope, Bench, Mat, Foam Roller) │
  ├──────────────┼────────────────────────────────────────────────────────────────────────────────────────┤
  │ Price type   │ Fixed                                                                                  │
  ├──────────────┼────────────────────────────────────────────────────────────────────────────────────────┤
  │ Price        │ 495                                                                                    │
  └──────────────┴────────────────────────────────────────────────────────────────────────────────────────┘

  ---
  Add-On Group 2 — Warranty

  ┌──────────────┬────────────────────────────────────┐
  │    Field     │               Value                │
  ├──────────────┼────────────────────────────────────┤
  │ Group name   │ Extended Warranty                  │
  ├──────────────┼────────────────────────────────────┤
  │ Display type │ Radio buttons                      │
  ├──────────────┼────────────────────────────────────┤
  │ Apply to     │ Specific products → select Tonal 2 │
  └──────────────┴────────────────────────────────────┘

  Add three options inside the group:

  ┌─────────────────────────────────────────┬────────────┬───────┐
  │                  Label                  │ Price type │ Price │
  ├─────────────────────────────────────────┼────────────┼───────┤
  │ Free 4-Year Limited Warranty (Included) │ Fixed      │ 0     │
  ├─────────────────────────────────────────┼────────────┼───────┤
  │ 4-Year Protection Plan                  │ Fixed      │ 425   │
  ├─────────────────────────────────────────┼────────────┼───────┤
  │ 5-Year Protection Plan                  │ Fixed      │ 449   │
  └─────────────────────────────────────────┴────────────┴───────┘

  Set "Free 4-Year Limited Warranty" as the default selected option.

  ---
  Part 3 — Verify

  Go to your shop front-end and visit the Tonal 2 product page. You should see:
  - Price: $4,295
  - A checkbox for the Smart Accessories Bundle (+$495)
  - Radio buttons for warranty options with price updating


## How to organize extra message 

Global — shown on all products, managed in one place in functions.php: 

- Affirm text (every product in your store will use financing) 

Per-product via ACF — you control per product in the WP Admin product editor:  
- Membership note (toggle on/off + custom text per product) Compare section content (WYSIWYG field per product) 
-  Any other unique product note  

---                                                                                                                                                                                                                      
  Field Group Settings
  ┌───────────────┬───────────────────────────────┐ 
  │    Setting    │             Value             │
  ├───────────────┼───────────────────────────────┤
  │ Group title   │ Product Notes                 │
  ├───────────────┼───────────────────────────────┤
  │ Location rule │ Post Type is equal to Product │
  └───────────────┴───────────────────────────────┘

  ---
  Fields to Add

  Field 1

  ┌─────────┬──────────────────────┐
  │ Setting │        Value         │
  ├─────────┼──────────────────────┤
  │ Label   │ Show Membership Note │
  ├─────────┼──────────────────────┤
  │ Name    │ show_membership_note │
  ├─────────┼──────────────────────┤
  │ Type    │ True / False         │
  ├─────────┼──────────────────────┤
  │ UI      │ Toggle on            │
  ├─────────┼──────────────────────┤
  │ Default │ Off                  │
  └─────────┴──────────────────────┘

  ---
  Field 2

  ┌───────────────────┬────────────────────────────────────────────────────────────────────────────────────────────┐
  │      Setting      │                                           Value                                            │
  ├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────┤
  │ Label             │ Membership Note Text                                                                       │
  ├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────┤
  │ Name              │ membership_note_text                                                                       │
  ├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────┤
  │ Type              │ Text                                                                                       │
  ├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────┤
  │ Default value     │ Tonal Membership separate. Requires a 12-month commitment ($59.95/mo. + applicable taxes). │
  ├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────┤
  │ Conditional logic │ Show if show_membership_note is equal to 1                                                 │
  └───────────────────┴────────────────────────────────────────────────────────────────────────────────────────────┘

  ---
  Field 3

  ┌───────────────────┬────────────────────────────────────────────┐
  │      Setting      │                   Value                    │
  ├───────────────────┼────────────────────────────────────────────┤
  │ Label             │ Membership Learn More URL                  │
  ├───────────────────┼────────────────────────────────────────────┤
  │ Name              │ membership_learn_more_url                  │
  ├───────────────────┼────────────────────────────────────────────┤
  │ Type              │ URL                                        │
  ├───────────────────┼────────────────────────────────────────────┤
  │ Default value     │ /membership                                │
  ├───────────────────┼────────────────────────────────────────────┤
  │ Conditional logic │ Show if show_membership_note is equal to 1 │
  └───────────────────┴────────────────────────────────────────────┘

the hooks in functions.php that read these ACF fields and output them on the product page — plus the global Affirm text.

wp-content\themes\timber-jadev\functions.php

```php
// -------------------------------------------------------
// WooCommerce — Product page: after price (priority 11)
// -------------------------------------------------------

// GLOBAL — Affirm financing text shown on every product
add_action('woocommerce_single_product_summary', function () {
    if (!is_product()) return;
    ?>
    <p class="affirm-note">
        0% APR and as low as $120/mo with Affirm.
        <a href="#" class="underline">See if you qualify</a>
    </p>
    <?php
}, 11);

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

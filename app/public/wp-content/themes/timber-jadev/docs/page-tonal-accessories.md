# Tonal Accessories Page

## Overview

Displays all WooCommerce products assigned to the **accessories** product category in a responsive grid. Includes a sort dropdown, product images with an optional "New" badge, pricing, description, and two action buttons per card.

---

## Files

| File | Purpose |
|------|---------|
| `page-tonal-accessories.php` | WordPress page template — queries products, builds context, renders Twig |
| `views/page-tonal-accessories.twig` | Twig template — sort bar + product grid markup |

WordPress loads `page-tonal-accessories.php` automatically when a page with the slug `tonal-accessories` is visited, following the [WordPress template hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/).

---

## PHP Template (`page-tonal-accessories.php`)

### Sort / Orderby

Reads `?orderby=` from the URL query string and validates it against an allowlist before use.

| `?orderby=` value | WP_Query behaviour |
|-------------------|--------------------|
| `featured` *(default)* | `menu_order ASC` — respects the order set in WP Admin → Products |
| `best_selling` | `total_sales` meta key `DESC` |
| `az` | `title ASC` |
| `za` | `title DESC` |

Any value not in the allowlist falls back to `featured`.

### WP_Query

- `post_type`: `product`
- `post_status`: `publish`
- `posts_per_page`: `-1` (all products)
- `tax_query`: products in the `accessories` product category (slug)

### Timber Context Variables

| Variable | Type | Description |
|----------|------|-------------|
| `accessories` | `array` | Array of product data arrays (see below) |
| `current_orderby` | `string` | Active sort value, used to mark the selected `<option>` |
| `current_page_url` | `string` | Permalink of the current page, used as the sort form `action` |

### Product Data Array (`accessories[]`)

Each item in the `accessories` array contains:

| Key | Source | Description |
|-----|--------|-------------|
| `card_title` | `$rp->get_name()` | Product name |
| `card_description` | `$rp->get_description()` | First 15 words of the plain-text description |
| `card_image_src` | `wp_get_attachment_image_url()` | Product image at `woocommerce_single` size |
| `card_image_alt_attribute` | `_wp_attachment_image_alt` meta | Image alt text, falls back to product name |
| `product_url` | `get_permalink()` | Link to the single product page |
| `add_to_cart_url` | `$rp->add_to_cart_url()` | WooCommerce add-to-cart URL (handles simple and variable products) |
| `price_html` | `$rp->get_price_html()` | Formatted price HTML including sale/regular prices |
| `is_new` | ACF field `is_new` | Boolean — shows the "New" badge when `true` |

---

## Twig Template (`views/page-tonal-accessories.twig`)

Extends `base.twig` and outputs inside `{% block content %}`.

### Sort Bar

A `<form method="get">` with a `<select>` dropdown. Submits automatically on change via `onchange="this.form.submit()"`. The active option is highlighted using `current_orderby`.

### Product Grid

Responsive CSS grid:

| Breakpoint | Columns |
|------------|---------|
| Mobile (default) | 1 |
| `sm` (≥ 640px) | 2 |
| `lg` (≥ 1024px) | 3 |

### Product Card Structure

Each card renders:

1. **Image** — wrapped in a link to the product page. Uses a padding-bottom aspect-ratio trick (`102.77%`) with `absolute` positioning so images are uniformly sized.
2. **"New" badge** — shown top-left over the image when `product.is_new` is `true`. Controlled by the ACF field `is_new` on each product.
3. **Title** — `<h4>` inside `.richtext-prose`.
4. **Price** — rendered with `|raw` to preserve WooCommerce's sale/regular price HTML.
5. **Description** — truncated plain-text excerpt (15 words).
6. **See Details** button — secondary style, links to `product_url`.
7. **Add to Cart** button — primary style, links to `add_to_cart_url`.

If no products are found the grid shows a fallback message: *"No accessories found."*

---

## ACF Fields Required

| Field name | Field type | Location | Purpose |
|------------|------------|----------|---------|
| `is_new` | True / False | Product (post type) | Displays the "New" badge on the card |

---

## Adding a New Product to This Page

1. Go to **WP Admin → Products → Add New**.
2. Set the **Product Category** to `Accessories`.
3. Fill in title, description, price, and featured image.
4. *(Optional)* Enable the **Is New** ACF field to show the badge.
5. To control the **Featured** sort order, set a **Menu Order** value under the product's Page Attributes panel.
6. Publish — the product appears on the accessories page immediately.

# Custom Email Template

**Files:** `woocommerce/emails/`

Template overrides live in the theme — never touched by WooCommerce updates. When WooCommerce bumps a template version, WP Admin → WooCommerce → Status → System Status will show a notice; review and merge changes manually.

---

## Files

| File | Purpose |
|---|---|
| `email-styles.php` | All inline CSS (colors, fonts, layout) |
| `email-header.php` | Full `<html>` open + branded dark header |
| `email-footer.php` | Closes HTML, renders footer links |
| `customer-processing-order.php` | Body of the "order received" email |

---

## Hooks — what each one does

### `woocommerce_email_header`
```php
do_action( 'woocommerce_email_header', $email_heading, $email );
```
Renders the full HTML open + header bar. Calls our `email-header.php`. Always the **first** thing in an email body template.

---

### `woocommerce_email_order_details`
```php
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
```
Outputs the order items table (product name, qty, price) and order totals. Also triggers structured data generation for email clients that support it.

---

### `woocommerce_email_order_meta`
```php
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
```
Outputs any extra order meta (e.g. custom fields added via `woocommerce_email_order_meta_fields`). Usually empty unless you register custom fields.

---

### `woocommerce_email_customer_details`
```php
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
```
Outputs the shipping/billing address block and customer email address.

---

### `woocommerce_email_footer`
```php
do_action( 'woocommerce_email_footer', $email );
```
Renders the footer block and closes the HTML. Calls our `email-footer.php`. Always the **last** thing in an email body template.

---

## Styling

Colors are defined in `email-styles.php` as PHP variables at the top:

```php
$brand_dark    = '#1c1c1a';
$brand_light   = '#f4f4f4';
$brand_accent  = '#11ddc4';  // teal divider / badge
$brand_muted   = '#6b7280';
```

Change them there — no other files need updating.

---

## Settings (WP Admin)

**WooCommerce → Settings → Emails → Processing Order**

| Setting | Effect |
|---|---|
| Header image URL | Logo shown in the dark header bar |
| Footer text | Text rendered in `email-footer.php` above the links |
| Additional content | Appended below order details via `$additional_content` |

---

## Preview

WP Admin → WooCommerce → Settings → Emails → Processing Order → **Preview**.

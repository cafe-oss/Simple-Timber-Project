# Checkout Page

Variable: billing_html                            
  What it is: do_action('woocommerce_checkout_billing')
  Why it's needed: Billing address fields (name, email, phone, address)                                                                                                                                                      ────────────────────────────────────────                                                                                                                                                                                   Variable: shipping_html                                                                                                                                                                                                  
  What it is: do_action('woocommerce_checkout_shipping')
  Why it's needed: Shipping address fields — WC only renders this when the cart needs shipping
  ────────────────────────────────────────
  Variable: order_review_html
  What it is: do_action('woocommerce_checkout_order_review')
  Why it's needed: Items table + totals + payment methods + Place Order button — captured together because WC's AJAX replaces #order_review as a single unit
  ────────────────────────────────────────
  Variable: checkout_nonce
  What it is: wp_nonce_field(...)
  Why it's needed: Two hidden inputs WC verifies on submit — if missing, the order fails
  ────────────────────────────────────────
  Variable: checkout_action
  What it is: wc_get_checkout_url()
  Why it's needed: The form's action="" attribute
  ────────────────────────────────────────
  Variable: cart_items
  What it is: Built from WC()->cart->get_cart()
  Why it's needed: Available if you want to show a custom thumbnail list above the WC order review
  ────────────────────────────────────────
  Variable: cart_subtotal / cart_total
  What it is: WC cart getters
  Why it's needed: Available for any custom display
  ────────────────────────────────────────
  Variable: is_empty / cart_url
  What it is: —
  Why it's needed: Gate the empty-cart state
  ────────────────────────────────────────
  Variable: wc_notices
  What it is: wc_print_notices()
  Why it's needed: Validation errors, login prompts shown at the top

  The one critical rule: #order_review must stay as the wrapping div around order_review_html. WooCommerce's JS targets that ID when it refreshes the right column after a shipping address change.
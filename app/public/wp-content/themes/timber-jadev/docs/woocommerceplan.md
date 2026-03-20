 WooCommerce Integration Plan (Tonal-inspired) Step 2 — What goes into functions.php (current step) Three things need to be added
  
  ┌─────┬────────────────────────────────────────────────────┬───────────────────────────────────────────────────────┐                                         
  │  #  │                        What                        │                          Why                          │
  ├─────┼────────────────────────────────────────────────────┼───────────────────────────────────────────────────────┤
  │ A   │ add_theme_support('woocommerce') + gallery support │ Tells WooCommerce your theme is compatible            │
  ├─────┼────────────────────────────────────────────────────┼───────────────────────────────────────────────────────┤
  │ B   │ Remove default WooCommerce CSS                     │ So we can fully custom-style it to match Tonal's look │
  ├─────┼────────────────────────────────────────────────────┼───────────────────────────────────────────────────────┤
  │ C   │ Add cart data to Timber context                    │ Makes cart count/items available in all .twig files   │
  └─────┴────────────────────────────────────────────────────┴───────────────────────────────────────────────────────┘

  ---
  Upcoming Steps (for reference)

  Step 3 — Create woocommerce.php (bridge file in theme root)
           Routes WooCommerce pages through Timber

  Step 4 — Create views/woocommerce/ Twig templates
           - shop/        → Accessories grid (like tonal.com/collections/accessories)
           - single/      → Product detail page (like tonal.com/products/tonal-2)
           - cart/        → Cart page
           - checkout/    → Checkout + payment

  Step 5 — Add products in WP Admin

  Step 6 — Connect payment gateway (Stripe recommended)

  Step 7 — Style everything to match Tonal's design

  ---
  Shall I go ahead and add the WooCommerce support code to your functions.php now? It's a safe, non-destructive change — just additions, nothing will break your existing pages.
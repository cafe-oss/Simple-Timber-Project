--- Stripe Setup (WP Admin) 1. Install the Stripe plugin 
WP Admin → Plugins → Add New → search "WooCommerce Stripe Payment Gateway" → Install & Activate

  2. Get your Stripe API keys
  Go to https://dashboard.stripe.com → Create a free account if you don't have one
  Developers → API Keys → copy your Publishable key and Secret key
  (Use the Test keys first — toggle at the top of the Stripe dashboard)

  3. Connect in WooCommerce
  WP Admin → WooCommerce → Settings → Payments tab
  Click Stripe → Enable it → paste both keys → Save

  4. Set up required WooCommerce pages
  WP Admin → WooCommerce → Settings → Advanced tab
  Make sure Cart, Checkout, and My Account pages are assigned


Yes, Stripe has a full test mode — no real money moves, but everything behaves exactly like a real transaction.                                                                                                                                                                                                                                                                                                                                     
  --- How to use Stripe Test ModeIn your Stripe dashboard — make sure the toggle at the top says "Test mode" (not Live). Your test API keys start with: 
  
  - Publishable key: pk_test_...
  - Secret key: sk_test_...

  Paste those into WooCommerce → Settings → Payments → Stripe.

  ---
  Test Card Numbers

  Use these on your checkout form — any future expiry date, any 3-digit CVC, any ZIP:

  ┌─────────────────────┬───────────────────────────────────┐
  │     Card Number     │              Result               │
  ├─────────────────────┼───────────────────────────────────┤
  │ 4242 4242 4242 4242 │ Payment succeeds                  │
  ├─────────────────────┼───────────────────────────────────┤
  │ 4000 0000 0000 0002 │ Payment declined                  │
  ├─────────────────────┼───────────────────────────────────┤
  │ 4000 0025 0000 3155 │ Requires 3D Secure authentication │
  ├─────────────────────┼───────────────────────────────────┤
  │ 4000 0000 0000 9995 │ Insufficient funds                │
  └─────────────────────┴───────────────────────────────────┘

  ---
  Switch to Live when ready

  When you're ready to take real payments:
  1. Toggle "Live mode" in your Stripe dashboard
  2. Copy the Live keys (pk_live_... / sk_live_...)
  3. Paste them into WooCommerce → Settings → Payments → Stripe → replace the test keys

  ---
  So the workflow is:
  Test mode (now) → Add products → Build templates → Go live

  Go ahead and install the Stripe plugin and connect using test keys. Let me know when that's done and we'll move to Step 5 — adding your first products.
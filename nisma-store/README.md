# Nisma — UAE lifestyle store concept

A fictional portfolio store built with WordPress and WooCommerce. Not a real business or client. Products, specifications, stock, shipping policies and AED prices are illustrative. No real payment processing is configured. Photography is free Pexels stock imagery, with source and licence links in `images.json` and the Photo credits page. No generated pictures are used.

## Run locally (Windows / PowerShell)

From the repository root:

```powershell
./nisma-store/setup.ps1
./nisma-store/start.ps1
```

Open http://127.0.0.1:8090. Admin: http://127.0.0.1:8090/wp-admin/.
The generated credentials are in `work/nisma-runtime/admin-credentials.txt`, excluded from Git. Do not commit the runtime or credentials. Setup extracts cached official packages where available, otherwise downloads them from official sources. PHP, WordPress, WooCommerce and the WordPress SQLite integration live in the ignored runtime. The existing blog, bakery concept and Haya2 store are separate and untouched.

Stop the server with Ctrl+C. Do not start a second server on port 8090. Setup can be rerun to refresh theme, sample content and catalogue metadata; it preserves existing product stock, orders and users.

## Structure and technologies

- `theme/`: reusable WordPress header/footer, front page, generic content and WooCommerce wrapper.
- `theme/functions.php`: catalogue filters, product information tabs, checkout address labels, local wishlist interface, metadata and contact form.
- `theme/store.js`: accessible saved-product controls; only product IDs are kept in local storage.
- `theme/style.css`: responsive layout and WooCommerce styling; system sans-serif and Georgia avoid external font requests.
- `theme/images/`: locally served, optimized WebP stock photographs. WooCommerce creates responsive attachment sizes.
- `plugins/nisma-demo.php`: local-only approved/declined payment simulator; outbound email suppression; local SQLite stock-reservation compatibility.
- `seed.php`: idempotent page/catalogue/coupon/shipping configuration.
- `seed-media.php`: photo credits and sample scarf galleries.
- `setup.ps1`, `start.ps1`, `router.php`: Windows local runtime.
- `download-images.cjs`, `images.json`: reproducible stock-image download/optimization and source provenance. Download script needs Node 22+ and the parent repository's `sharp` dependency.
- `verify.php`: meaningful store configuration and commerce checks.

Commerce data and calculations use WooCommerce rather than duplicated front-end pricing logic. The contact form validates a demo message but sends and stores nothing. Account creation, order history and order confirmation use WooCommerce's native flows. Password-reset email is intentionally unavailable in the local demo because all email is suppressed.

## Customer journey and information architecture

Home → collection/search → filter/sort shop → product and variation → bag → guest checkout → order confirmation. Saved pieces offer a return path to product details. The four collections are Scarves, Journals & stationery, Prayer essentials, and Gifts & home. Each product has description, materials, care and delivery/returns tabs. Scarves demonstrate colour variants; prayer mats demonstrate size variants. Sold-out pieces and variants make unavailable inventory visible.

The scarf gallery uses a second illustrative stock image; it is not another exact view of the same manufactured item. Replace both with verified product photographs for a real catalogue.

## Mobile and accessibility decisions

Two-column mobile product grids become four columns on desktop. Navigation wraps, checkout fields stack, controls have usable target sizes, and native selects work on touch devices. Labels remain visible, focus rings are explicit, page landmarks and a skip link support keyboard navigation. No animation is needed to complete a task. Wishlist changes are announced through a live region. Browser storage failure leaves shopping functional.

The UAE address form uses Emirate, Area, Building/villa and street, optional Apartment/floor and delivery instructions. UAE postcodes are omitted. Accounts are optional. The final summary, shipping choice and test-payment result sit in WooCommerce's checkout flow.

## Store configuration and cart flow

- Currency AED; UAE-only sales and shipping; sample prices exclude tax (tax calculation disabled for this concept).
- Guest checkout enabled; optional registration.
- Real variable-product inventory and stock reduction on approved test orders.
- FIRST10: 10% off merchandise at AED 100 minimum; no coupon stacking.
- Standard AED 20 / 2–4 business days; free standard from AED 250 **after discounts**; express AED 35 / 1–2 business days.
- Paid standard is hidden when free standard applies; express remains available.
- Classic WooCommerce cart and checkout support address customization, coupons, quantities, server-side totals and validation.
- Local simulated payment: select Approved or Declined. Approved orders reduce stock, empty the cart and show the native thank-you page; declined orders show an error and allow retry. No card data is requested and no provider is contacted.

Run checks:

```powershell
./work/nisma-runtime/php/php.exe ./nisma-store/verify.php
```

Local SQLite does not support MySQL row-lock stock reservations, so only that reservation feature is disabled locally. WooCommerce validates and reduces stock. This single-user setup is not suitable for concurrent production traffic. Scheduled WordPress jobs are disabled locally. Back up the whole runtime directory while stopped to preserve the database, uploads, accounts and orders.

## Deploy

Deploy the theme and demo plugin to a separate WordPress staging installation on a PHP host supporting WooCommerce, HTTPS and MySQL/MariaDB. Use the current [WooCommerce server requirements](https://woocommerce.com/document/server-requirements/) and [variable-product documentation](https://woocommerce.com/document/variable-product/). Upload `theme/` as `wp-content/themes/nisma/`; activate WooCommerce and Nisma. Migrate the local site with a WordPress migration tool that supports SQLite source databases, or recreate the documented catalogue in staging. Never copy a SQLite database into a MySQL installation or deploy the Windows router/start scripts.

Set the staging URL through the host/migration tool and perform a serialized-data-aware URL replacement. Enable normal WP cron or host cron. Keep staging noindexed; the local installation is already noindexed. Replace placeholder imagery, product specs, policies, brand identity and business contacts before any commercial release. Configure tax and delivery rules with verified business requirements.

The bundled payment simulator is available **only when `WP_ENVIRONMENT_TYPE` is `local`**. Hosted staging requires an explicitly configured provider sandbox (for example WooCommerce Stripe with test credentials), and end-to-end sandbox verification before release. The download setup includes the official Stripe plugin but does not activate it or supply credentials. Real payment activation is outside this project scope. After a real launch review, enable indexing and verify canonical, Open Graph and product schema URLs. WooCommerce supplies Product structured data; the theme supplies page titles, description, social metadata and favicon.

## Scope

No newsletter subscription, real contact transmission, analytics, fulfilment integration or live payment provider is configured. Policies are concept content, not legal advice. See the on-site Project case study for the portfolio narrative.

## Verification record

Local evaluation completed with PHP 8.3, WordPress and WooCommerce: PHP syntax checks and `verify.php` passed. Browser checks covered desktop/mobile layout, combined collection/colour/price/stock filtering, saved-piece persistence, keyboard product selection/add-to-cart, coupon application, UAE checkout, free standard shipping with express still available, declined test payment and approved retry, order confirmation, cart clearing and inventory reduction. The approved retry with coupon produced AED 415 merchandise − AED 41.50 discount + free standard shipping = AED 373.50. No funds moved.

Declined local test orders are cancelled and the next retry creates a fresh order, avoiding an unsupported SQLite joined-delete during order reuse. The initial compatibility issue was corrected and the revised retry produced no new database errors. Hosted MySQL/MariaDB staging should receive its own provider sandbox verification.

For optional code formatting, use Prettier and `@prettier/plugin-php` with the included PHP 8.3 configuration. Formatting is a development convenience, not a site runtime dependency.

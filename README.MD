# SimpleCoin v2

SimpleCoin v2 is a PHP-powered storefront that showcases seed products and processes checkout flows through Coinbase Commerce. Product data is managed in JSON, while the front end uses Bootstrap, jQuery, and simple JavaScript cart utilities to present items, calculate totals, and hand off orders to Coinbase.

## Features
- Product catalog sourced from `products.json` with per-item detail pages.
- Session storage–backed shopping cart rendered by `coinbasecart.js` and `shoppingCart.js`.
- Coinbase Commerce integrations for initiating payments and handling webhooks.
- Sitemap assets (`sitemap.php` and `sitemap.xml`) for search indexing support.
- Lightweight PHP pages (`index.php`, `product.php`, `cart/`, `subscribe.php`, and related endpoints) that require no external framework.
- Optional ASX wallet generator (`create_asx_wallet.php`) that produces encrypted wallets for post-checkout workflows.
- Admin dashboard (`admin.php`) gated by Google login for overseeing users and minting NFT rewards with on-disk logs.
- Wallet linking endpoints (`link_wallet.php`) allow users to attach MetaMask or Coinbase addresses to their profile.

## Getting Started
1. Install PHP 7.4+ with PDO (SQLite or MySQL).
2. Clone the repository and move into the project directory.
3. (Optional) Set database environment variables for MySQL/PostgreSQL; otherwise SQLite will be used automatically:
   - `DB_DSN` (or `DATABASE_URL`)
   - `DB_USER`
   - `DB_PASS`
4. Start a local server (for example, `php -S localhost:8000`).
5. Visit `http://localhost:8000` in your browser to browse products and the cart.

## Configuration
- Product data lives in `products.json` and accompanying images in `product-images/`.
- Coinbase API keys or webhook secrets should be stored securely via environment variables or a local `.env`-style file and consumed by the PHP endpoints that create charges or verify webhooks.
- Subscriber signups are stored in `subscribers.json`; clear or rotate this file if you need a fresh list.
- ASX wallets are encrypted and persisted in `wallets/` by `create_asx_wallet.php`; keep this directory non-public (for example, with web server deny rules) and supply a strong password when invoking the endpoint.
- Google login posts to `auth/google_login.php`; the first successful login becomes admin. Protect this endpoint behind real token verification (Firebase/OAuth2) before production use.
- NFT reward metadata lives in `nft_rewards/` (generated via `generate_nft_rewards.php`), and mint events are logged to `mint_logs/`.
- Wallet linking uses `link_wallet.php`; ensure sessions are protected (HTTPS, secure cookies) and consider CSRF defenses for production.

### Coinbase Commerce Environment Variables
| Variable | Description |
| --- | --- |
| `COINBASE_API_KEY` | Coinbase Commerce API key used to create charges. |
| `COINBASE_WEBHOOK_SECRET` | Shared secret for validating webhook signatures in `webhook_handler.php`. |
| `COINBASE_WEBHOOK_URL` | Public HTTPS endpoint that receives Coinbase Commerce webhooks. |

Example `.env` snippet:
```
# Coinbase Commerce
COINBASE_API_KEY=sk_live_...
COINBASE_WEBHOOK_SECRET=whsec_...
COINBASE_WEBHOOK_URL=https://yourdomain.com/webhook_handler.php
```

Security tips:
- Never commit real secrets; keep `.env` files out of version control and block public access.
- Verify all incoming webhooks with `COINBASE_WEBHOOK_SECRET` and log failures.
- Ensure the webhook URL is HTTPS and not world-readable beyond the handler.

## Repository Structure
- `index.php`: Landing page with the product grid and cart modal.
- `product.php`: Individual product details pulled from `products.json`.
- `coinbasecart.js` / `shoppingCart.js`: Cart rendering, calculations, and client-side storage helpers.
- `coinbasepay.php`, `webhook_handler.php`: Coinbase Commerce payment flows and webhook handling.
- `create_asx_wallet.php`: Generates a 256-bit ASX wallet, encrypts it with AES-256-GCM, and stores the encrypted payload in `wallets/`.
- `db.php`: PDO bootstrap that defaults to SQLite when no DSN is provided.
- `auth/google_login.php`: Accepts Google login posts, seeds the first user as admin, and stores the session.
- `admin.php`: Admin dashboard to list users and mint NFT reward files.
- `nft_mint.php`: Server-side NFT reward logger that reads metadata per tier and writes mint logs.
- `generate_nft_rewards.php`: Utility to generate sample SVG/JSON metadata for bronze/silver/gold tiers.
- `link_wallet.php`: Links MetaMask or Coinbase wallet addresses to the authenticated user’s profile.
- `products.json`: Product catalog consumed by the PHP pages and cart scripts.
- `cart/`: Additional cart-related PHP endpoints.

## Next Steps
A living implementation plan and to-do list are tracked in `plan.md`.

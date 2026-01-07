# Project Plan & To-Do List

## Current Goals
- Keep the storefront lightweight while improving resilience around payments and cart storage.
- Clarify operational runbooks so the site can be deployed quickly in new environments.

## To-Do List
- [x] Add configuration documentation for required Coinbase Commerce keys and webhook secrets, including sample environment variable names.
- [x] Harden webhook validation in `webhook_handler.php` with signature verification and logging for failed attempts.
- [ ] Persist carts server-side (or via signed cookies) so they survive across devices and browser sessions.
- [ ] Add automated tests or smoke checks for product loading (`products.json`) and cart math in `coinbasecart.js`/`shoppingCart.js`.
- [ ] Create a minimal CI job to run linting (PHP and JavaScript) and validate `products.json` structure on every commit.
- [ ] Add a secure UI prompt (or account-based secret) to supply the password used by `create_asx_wallet.php`, and block public access to the `wallets/` directory at the web server layer.
- [ ] Replace placeholder Google login handling with real token verification (Firebase or OAuth2), CSRF protection, and per-session expiry.
- [ ] Add CSRF protection and server-side rate limiting to `link_wallet.php`; surface wallet link success state in the UI after reload.
- [ ] Wire NFT minting to an on-chain contract or custodial service instead of file-only logs.
- [ ] Add UI and storage for checkout emails/order IDs (e.g., `checkoutEmail` field) so metadata sent to Coinbase is user-provided and persisted.
- [ ] Rotate and compress webhook/order logs periodically to avoid unbounded growth.

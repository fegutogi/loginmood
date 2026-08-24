# LoginMood automated QA

Requirements: Node.js 20 or newer. The kit uses WordPress Playground and Playwright, so it does not require Docker or a permanent database.

## First use

```bash
npm ci
npx playwright install chromium firefox webkit
npm run qa:visual:update
```

## Daily development

```bash
npm run qa:quick
```

This starts a disposable WordPress installation, activates LoginMood, checks the principal login states, imports palette fixtures, and closes the environment.

## Visual regression

```bash
npm run qa:visual
```

Approved screenshots cover desktop and mobile login, error, and lost-password states. Use `qa:visual:update` only after reviewing an intentional visual change.

Local visual comparisons use a strict 1% pixel-difference ceiling. Linux CI runners use a 4% ceiling to absorb known font-rasterization and antialiasing differences from the macOS reference images; functional, dimensional, responsive, and contrast assertions remain separate and strict.

## Compatibility matrix

```bash
npm run qa:matrix
```

The smoke suite covers the declared minimum, current stable, newest PHP, and WordPress nightly. Playground versions may evolve; update the matrix when the plugin support policy changes.

When the project is hosted on GitHub with Actions enabled, the same matrix runs automatically every Monday. A failed scheduled run is an early warning to review WordPress or PHP changes; it does not publish or modify the plugin automatically.

## Third-party compatibility

```bash
npm run qa:compat
```

This creates four independent WordPress sites and activates the current directory versions of WP 2FA, Simple Cloudflare Turnstile, WooCommerce, and Paid Memberships Pro. It verifies that LoginMood still owns the native login presentation, error and password-recovery states remain usable, and administrator authentication does not end in a fatal error.

Wordfence cannot activate inside the WebAssembly-based Playground runtime, so it requires the normal-PHP LocalWP check documented below. CAPTCHA challenges, email codes, paid services, and account-backed features still require a separate manual test with real service credentials.

### Wordfence on LocalWP

The LocalWP compatibility clone requires explicit paths so no machine-specific details are stored in the repository:

```bash
export LOGINMOOD_LOCAL_SITE_PATH="/path/to/site/app/public"
export LOGINMOOD_LOCAL_MYSQL_SOCKET="/path/to/mysql/mysqld.sock"
export LOGINMOOD_LOCAL_PHP="/path/to/php"
export LOGINMOOD_LOCAL_MYSQL="/path/to/mysql"
export LOGINMOOD_LOCAL_MYSQLDUMP="/path/to/mysqldump"
export LOGINMOOD_LOCAL_DB="local"
export LOGINMOOD_LOCAL_DB_USER="root"
export LOGINMOOD_LOCAL_DB_PASSWORD="your-local-password"
npm run qa:compat:wordfence
```

The command clones the LocalWP files and database into disposable targets, creates a temporary administrator only inside the clone, activates Wordfence, runs the same four browser checks, and removes the cloned database, server, and files afterward. Do not commit a populated environment file or activate Wordfence merely to satisfy this test on a working development site.

## Release gate

```bash
npm run qa:release
```

This runs functional checks in Chromium, Firefox, and WebKit, visual regression on desktop and mobile, and the compatibility smoke matrix. Plugin Check and translation compilation remain part of the packaging command because they require system tools.

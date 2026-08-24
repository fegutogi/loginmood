# LoginMood

[![LoginMood QA](https://github.com/fegutogi/loginmood/actions/workflows/qa.yml/badge.svg)](https://github.com/fegutogi/loginmood/actions/workflows/qa.yml)
[![License: GPL v2 or later](https://img.shields.io/badge/License-GPL_v2_or_later-blue.svg)](LICENSE)

LoginMood is a focused WordPress plugin for customizing native login, registration, and password recovery screens without modifying authentication or depending on the active theme.

Created and maintained by [Fegutogi](https://fegutogi.com), a WordPress plugin and theme developer. LoginMood is an original Fegutogi project and is distributed as free software under the GPL.

Version `1.0.0-rc.3` is the release candidate under the final LoginMood identity. The stable `1.0.0` remains reserved for the completed UI and lifecycle review.

## Current capabilities

- Logo size, destination, clipping shape, border, and shadow, all following the source-image aspect ratio.
- Optional no-logo mode that preserves the selected image for later reuse.
- Configurable light or dark shadow color, with the logo layer kept behind messages and forms.
- Administrator-only SVG logo uploads, sanitized before storage and rendered as external images.
- Alpha-aware outlines and shadows for unclipped transparent logos, plus an optional background for clipped logos.
- Solid color, gradient, or image background.
- Visual picker, eyedropper, palette dropdown, and editable HEX value for every color.
- Compact, consistent color rows ordered as element, visual control, HEX value, and palette dropdown.
- Context-aware controls that hide logo and background properties when they do not apply.
- Branding palette imports from JSON, TXT, CSS, CSV, Adobe ASE, and GIMP GPL files.
- Drag-and-drop logo upload and palette import, with the traditional selectors preserved.
- Custom-logo spacing from the login panel, limited to a safe 0–80 px range.
- Collapsible palette table ordered by name and HEX, including swatches and optional color names.
- Independent field background and field-text colors.
- Independent text colors for content inside the panel and content over the page background.
- Radius-aware field padding and a password visibility icon that follows the field-text color.
- Independent 0–50 px panel radius and shared field/button radius controls.
- Responsive login layout and reduced-motion support.
- Optional, default-on entrance sequence using only compositor-friendly transform and opacity: logo, panel, then background text.
- Live admin preview and three visual presets.
- Indicative WCAG AA contrast ratios for panel text, background text, buttons, and fields.
- JSON import and export without unsafe cross-site attachment reuse.
- English source plus bundled Spanish translations for `es_AR` and `es_ES`.
- Theme-provided initial defaults through `fegutogi_loginmood_default_settings`.
- Optional data cleanup on uninstall.
- One-time migration of settings saved by the provisional Login Branding build.

## Development status

Validated with PHP syntax checks, WordPress Plugin Check, automated login-state and palette tests in Chromium, Firefox, and WebKit, desktop/mobile visual regression, and an isolated WordPress/PHP compatibility matrix. Native login, error, password-recovery, and administrator-authentication flows also pass with Wordfence Security 9.0.0, WP 2FA 4.1.0, Simple Cloudflare Turnstile 1.42.1, WooCommerce 11.0.1, and Paid Memberships Pro 3.8.4.

Service-backed challenges and account-dependent features still require real credentials: TOTP/email enrollment, Turnstile widgets, Wordfence licensing and firewall optimization, payment gateways, and paid membership checkout are outside the automated compatibility gate.

See [`tests/README.md`](tests/README.md) for the automated QA commands and [`docs/theme-integration.md`](docs/theme-integration.md) for the starter-theme contract.

Official logo, icon, WordPress.org banner, and GitHub social-preview specifications are documented in [`docs/brand-assets.md`](docs/brand-assets.md).

## Project links

- Author: [Fegutogi](https://fegutogi.com)
- Source: [github.com/fegutogi/loginmood](https://github.com/fegutogi/loginmood)
- Issues: [github.com/fegutogi/loginmood/issues](https://github.com/fegutogi/loginmood/issues)
- Security reports: see [`SECURITY.md`](SECURITY.md)
- Contributions: see [`CONTRIBUTING.md`](CONTRIBUTING.md)
- Authorship: see [`AUTHORS.md`](AUTHORS.md)

## License and authorship

Copyright © 2026 Fegutogi. LoginMood is licensed under the GNU General Public License v2.0 or later. The license permits use, modification, and redistribution under its terms; it does not remove the original project authorship recorded in the source and documentation.

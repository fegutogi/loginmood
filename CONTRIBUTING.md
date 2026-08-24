# Contributing to LoginMood

LoginMood is an original WordPress plugin created and maintained by [Fegutogi](https://fegutogi.com). Contributions that improve compatibility, accessibility, translations, documentation, or focused login-branding behavior are welcome.

## Before opening a pull request

1. Open an issue for substantial behavioral or interface changes.
2. Keep the plugin independent from the active theme and WordPress authentication logic.
3. Follow WordPress coding, escaping, sanitization, capability, nonce, accessibility, and internationalization practices.
4. Do not introduce remote tracking, telemetry, advertising, or account requirements.
5. Update documentation and tests alongside the change.

## Validation

Install Node.js 20 or newer, then run:

```bash
npm ci
npx playwright install chromium firefox webkit
npm run qa:quick
```

Release-affecting changes should also pass `npm run qa:release`. See [`tests/README.md`](tests/README.md) for the full matrix.

## Licensing

By contributing, you agree that your contribution may be distributed under the GNU General Public License v2.0 or later. Existing copyright and project authorship notices identifying Fegutogi must be preserved.

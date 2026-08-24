=== LoginMood ===
Contributors: fegutogi
Tags: login, branding, custom login, white label
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0-rc.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Customize the native WordPress login experience without changing the active theme or WordPress core files.

== Description ==

LoginMood provides focused controls for the native WordPress login, registration, and password recovery screens. Configure a PNG, JPG, WebP, or safely sanitized SVG logo—or choose no logo—with an aspect-aware outline and configurable shadow, a solid color, gradient or image background, visual and HEX color controls, independent panel, background and field text colors, panel and shared field/button corner radii, logo destination, language selector visibility, and optional footer copy.

The settings screen includes drag-and-drop logo and palette loading with traditional selectors preserved, an ordered collapsible palette table, a live preview, reusable visual presets, an indicative WCAG AA contrast check, portable JSON import and export, and a safe way to restore defaults.

The plugin does not modify authentication, change the login URL, collect personal data, or load external resources. Theme authors can provide initial defaults through the `fegutogi_loginmood_default_settings` filter while saved administrator choices continue to take precedence.

LoginMood is an original WordPress plugin created and maintained by Fegutogi.

== Installation ==

1. Upload the `loginmood` folder to `/wp-content/plugins/`, or install the ZIP file from the WordPress admin.
2. Activate LoginMood.
3. Go to `Settings > LoginMood`.
4. Configure the visual identity and save the changes.

== Frequently Asked Questions ==

= Does it depend on the active theme? =

No. The settings remain active when the site changes themes.

= Does it modify authentication? =

No. It only changes the presentation of native WordPress login-related screens.

= What happens when I uninstall it? =

The plugin preserves its settings by default. Enable the data-removal option before uninstalling if you want WordPress to delete the stored settings.

= Are media files included in JSON exports? =

No. Attachment identifiers are site-specific. Imported configurations preserve portable visual settings and require the destination site to select its own logo and background image.

= Can I use an SVG logo? =

Yes. Administrators can upload SVG logos. The plugin sanitizes them before storage, removes executable and externally referenced content, and displays them as external images rather than inline markup.

== Changelog ==

= 1.0.0-rc.3 =
* Added drag-and-drop upload for logos while preserving the WordPress media-library selector.
* Added drag-and-drop palette imports while preserving the file browser.
* Added a collapsible, alphabetically ordered palette table with swatches, optional names, and HEX codes.
* Added a custom-logo-only 0–80 px control for spacing between the logo and panel.

= 1.0.0-rc.2 =
* Added panel-radius-aware spacing to login notices, errors, and success messages.
* Added a repeatable automated QA kit for login states, responsive layouts, palettes, and compatibility checks.

= 1.0.0-rc.1 =
* Renamed the plugin, slug, text domain, code identifiers, assets, and settings to LoginMood by Fegutogi.
* Added migration of settings from the provisional Login Branding builds.
* Removed the red accent bar from login error panels.

= 0.9.0 =
* Added CSV palette imports with HEX or RGB columns.
* Added local Adobe ASE imports for RGB, CMYK, grayscale, and LAB swatches.
* Added a default-on animation switch and slowed the entrance sequence so background text appears last.

= 0.8.0 =
* Separated panel text from text displayed over the page background.
* Added a lightweight sequential entrance: logo upward first, then panel downward.
* Used only transform and opacity, with automatic reduced-motion support.

= 0.7.0 =
* Reorganized every color into a compact element, visual picker, HEX, and palette row.
* Separated logo geometry and effects from color rows.
* Hid logo styling, border color, clipped background, shadow color, and gradient angle whenever those controls do not apply.
* Added a responsive two-level color layout for narrow screens.

= 0.6.0 =
* Added a no-logo option that hides the complete logo area without deleting the selected media attachment.

= 0.5.0 =
* Added a customizable logo-shadow color for light and dark backgrounds.
* Kept logo effects behind informational messages and login forms.
* Added administrator-only SVG logo uploads with server-side sanitization and aspect-ratio support.

= 0.4.0 =
* Made unclipped logo outlines and shadows follow the transparent image silhouette.
* Added an optional background color for rounded and circular clipped logos.

= 0.3.2 =
* Removed the WordPress blue accent border from informational login messages while preserving error indicators.

= 0.3.1 =
* Increased horizontal field padding progressively with the shared field/button radius.
* Reserved responsive password-field space for the visibility control.
* Matched the password visibility icon to the customizable field-text color.

= 0.3.0 =
* Added a visual color swatch, native picker, explicit eyedropper, editable HEX field, and brand-palette dropdown to every color control.
* Made logo borders, rounded corners, and shadows follow the source image aspect ratio.
* Added 0–50 px sliders for panel radius and the shared field/button radius.

= 0.2.0 =
* Added unclipped, rounded, and circular logo shapes.
* Added optional thin or strong logo borders and soft or strong shadows.
* Replaced native color pickers with palette dropdowns and editable hexadecimal fields.
* Added branding palette imports from JSON, TXT, CSS, and GIMP GPL files.
* Separated field background and field text colors, including their own contrast check.
* Added square, rounded, and pill button shapes.
* Fixed logo width by respecting the selected image aspect ratio.

= 0.1.0 =
* Initial development release.
* Added login-screen identity and visual controls.
* Added a live admin preview, three presets, and an indicative WCAG AA contrast check.
* Added JSON import and export.
* Added safe settings reset and optional uninstall cleanup.
* Added theme-provided initial defaults through `fegutogi_loginmood_default_settings`.

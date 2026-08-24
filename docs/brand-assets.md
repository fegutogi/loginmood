# LoginMood brand asset specifications

LoginMood is an original WordPress plugin created and maintained by [Fegutogi](https://fegutogi.com). All public artwork should preserve that attribution in its metadata or surrounding publication copy.

## Recommended master files

Create the identity as vectors in RGB/sRGB and retain the editable source. The minimum master set is:

- `loginmood-symbol.svg`: symbol only, square viewBox, transparent background.
- `loginmood-logo.svg`: symbol plus the LoginMood wordmark.
- `loginmood-logo-by-fegutogi.svg`: horizontal lockup with a small “by Fegutogi” attribution.
- Light, dark, and one-color variants of the symbol and wordmark.

Convert text to outlines in final SVG exports. Do not embed scripts, external fonts, linked images, tracking code, or unnecessary editor metadata. Check the symbol at 32×32 px before approving it; fine detail that disappears there should be simplified.

## WordPress.org plugin icon

Required delivery set:

| Filename | Dimensions | Format | Limit |
| --- | ---: | --- | ---: |
| `icon.svg` | Square viewBox | SVG | Keep as small as practical |
| `icon-256x256.png` | 256×256 px | PNG | Under 1 MB |
| `icon-128x128.png` | 128×128 px | PNG | Under 1 MB |

WordPress.org accepts SVG icons but requires a PNG fallback for older browsers and social sharing. Use the symbol without the full wordmark. Keep important forms approximately 12.5% away from each edge, test on light and dark surroundings, and preserve transparency unless a deliberate background tile is part of the identity.

## WordPress.org plugin banner

Design the high-resolution version first and derive the standard version at exactly 50%:

| Filename | Dimensions | Format | Limit |
| --- | ---: | --- | ---: |
| `banner-1544x500.png` | 1544×500 px | PNG or JPG | Under 4 MB |
| `banner-772x250.png` | 772×250 px | PNG or JPG | Under 4 MB |

Recommended composition:

- LoginMood wordmark as the primary element.
- “by Fegutogi” as a secondary but legible attribution.
- Avoid small interface text and screenshots inside the banner.
- Keep essential elements away from the outer 64 px of the 1544×500 master.
- Make the composition work without depending on a left-to-right reading direction.
- Use PNG for flat color, transparency, and sharp vector-like artwork; use JPG only for photographic or heavily textured backgrounds.

The high-resolution banner supplements the 772×250 file; it does not replace it.

## GitHub social preview

| Filename | Dimensions | Format | Limit |
| --- | ---: | --- | ---: |
| `github-social-preview.png` | 1280×640 px | PNG, JPG, or GIF | Under 1 MB |

GitHub recommends at least 640×320 px and 1280×640 px for best display. Use an opaque background unless the transparent version has been checked on both light and dark platforms. Keep the symbol, LoginMood wordmark, and “Created by Fegutogi” within a central safe area with roughly 80 px of outer breathing room.

## WordPress.org screenshots

Screenshots are separate from the identity artwork but will be needed for the directory submission:

- Lowercase filenames: `screenshot-1.png`, `screenshot-2.png`, and so on.
- PNG or JPG, under 10 MB each; smaller optimized files are preferable.
- Each screenshot must have a matching numbered caption in `readme.txt`.
- Capture real plugin UI without browser extensions, private data, local paths, or unrelated administration notices.
- Recommended working size for this project: 1440×900 px for desktop screens, exported in sRGB.

## Delivery checklist

- Editable vector source.
- Symbol-only SVG.
- Full LoginMood wordmark SVG.
- Horizontal “LoginMood by Fegutogi” SVG.
- 256×256 and 128×128 PNG icons.
- 1544×500 and 772×250 WordPress.org banners.
- 1280×640 GitHub social preview.
- Palette values in HEX and a note naming the typeface or confirming that lettering is custom.
- Visual check at actual icon size and on light/dark backgrounds.

WordPress.org assets belong in the top-level `assets` directory of the future plugin SVN repository, not inside the installable plugin ZIP. GitHub's social preview is uploaded through the repository settings and is not committed as plugin runtime code.

## Official references

- [WordPress.org plugin asset requirements](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/)
- [GitHub social preview requirements](https://docs.github.com/en/repositories/managing-your-repositorys-settings-and-features/customizing-your-repository/customizing-your-repositorys-social-media-preview)

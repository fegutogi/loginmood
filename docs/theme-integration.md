# Theme integration

LoginMood must remain independent from the active theme. A theme may provide initial values, but it must not overwrite settings saved by an administrator.

## Initial defaults

Register the filter from the theme before the plugin is activated:

```php
add_filter(
	'fegutogi_loginmood_default_settings',
	function ( $defaults ) {
		$defaults['hide_logo']        = 0;
		$defaults['primary_color']    = '#e85d3f';
		$defaults['logo_background_color'] = '#ffffff';
		$defaults['logo_shadow_color'] = '#0f172a';
		$defaults['background_color'] = '#f7f3ed';
		$defaults['panel_color']      = '#ffffff';
		$defaults['text_color']       = '#24211f';
		$defaults['background_text_color'] = '#24211f';
		$defaults['link_color']       = '#a63f2b';
		$defaults['field_background_color'] = '#ffffff';
		$defaults['field_text_color'] = '#24211f';
		$defaults['border_radius']    = 16;
		$defaults['control_radius']   = 10;

		return $defaults;
	}
);
```

The plugin sanitizes filtered defaults before storing them. Once the option exists, saved administrator values take precedence. Restoring defaults intentionally reevaluates the active theme filter.

## Media

Themes should not provide attachment IDs because IDs are site-specific. Let the administrator select the logo and background from the destination site's Media Library.

## Distribution with a theme

Distribute LoginMood as a separate installable ZIP or managed dependency. Do not copy its PHP or assets into a theme. The theme must continue to work when the plugin is inactive, and the plugin must continue to work after a theme change.

## Public hooks

- `fegutogi_loginmood_default_settings`: filters the complete initial settings array.
- `login_body_class`: WordPress core filter; the plugin adds `loginmood-login` for compatible extensions.

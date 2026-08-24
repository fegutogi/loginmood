<?php
/**
 * Administration interface.
 *
 * @package LoginMood
 */

namespace Fegutogi\LoginMood;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Admin {
	/**
	 * Settings page hook suffix.
	 *
	 * @var string
	 */
	private $page_hook = '';

	/**
	 * Current saved brand palette.
	 *
	 * @var string[]
	 */
	private $brand_palette = array();

	/**
	 * Register admin hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_fegutogi_loginmood_export_settings', array( $this, 'export_settings' ) );
		add_action( 'admin_post_fegutogi_loginmood_import_settings', array( $this, 'import_settings' ) );
		add_action( 'admin_post_fegutogi_loginmood_reset_settings', array( $this, 'reset_settings' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( FEGUTOGI_LOGINMOOD_FILE ), array( $this, 'action_links' ) );
	}

	/**
	 * Add the settings screen.
	 */
	public function add_page() {
		$this->page_hook = add_options_page(
			__( 'LoginMood', 'loginmood' ),
			__( 'LoginMood', 'loginmood' ),
			'manage_options',
			'loginmood',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register the option with WordPress.
	 */
	public function register_settings() {
		register_setting(
			'fegutogi_loginmood_settings_group',
			Settings::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( Settings::class, 'sanitize' ),
				'default'           => Settings::defaults(),
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Load assets only on this plugin's screen.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( $hook !== $this->page_hook ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style(
			'loginmood-admin',
			FEGUTOGI_LOGINMOOD_URL . 'assets/css/admin.css',
			array(),
			FEGUTOGI_LOGINMOOD_VERSION
		);
		wp_enqueue_script(
			'loginmood-admin',
			FEGUTOGI_LOGINMOOD_URL . 'assets/js/admin.js',
			array(),
			FEGUTOGI_LOGINMOOD_VERSION,
			true
		);
		wp_localize_script(
			'loginmood-admin',
			'FegutogiLoginMood',
			array(
				'chooseLogo'       => __( 'Choose logo', 'loginmood' ),
				'chooseBackground' => __( 'Choose background', 'loginmood' ),
				'useImage'         => __( 'Use this image', 'loginmood' ),
				'contrast'         => __( 'Contrast', 'loginmood' ),
				'textLabel'        => __( 'text', 'loginmood' ),
				'backgroundLabel'  => __( 'background', 'loginmood' ),
				'buttonLabel'      => __( 'button', 'loginmood' ),
				'fieldLabel'       => __( 'field', 'loginmood' ),
				'aaPassed'         => __( 'AA passed', 'loginmood' ),
				'aaReview'         => __( 'review the colors to reach AA', 'loginmood' ),
				'resetConfirm'     => __( 'Reset all LoginMood settings to their defaults?', 'loginmood' ),
				'palettePlaceholder' => __( 'Choose from palette', 'loginmood' ),
				'paletteCustom'      => __( 'Custom', 'loginmood' ),
				'paletteLoaded'      => __( 'Palette loaded', 'loginmood' ),
				'paletteEmpty'       => __( 'No palette loaded', 'loginmood' ),
				'paletteNoColors'    => __( 'No valid colors were found in that file.', 'loginmood' ),
				'paletteReadError'   => __( 'The palette file could not be read.', 'loginmood' ),
				'paletteDropLabel'   => __( 'Drop the palette file here', 'loginmood' ),
				'paletteTableLabel'  => __( 'Imported colors', 'loginmood' ),
				'paletteColorLabel'  => __( 'Color', 'loginmood' ),
				'paletteNameLabel'   => __( 'Name', 'loginmood' ),
				'paletteHexLabel'    => __( 'HEX code', 'loginmood' ),
				'paletteUnnamed'     => __( 'No name', 'loginmood' ),
				'logoDropLabel'      => __( 'Drop the logo here', 'loginmood' ),
				'logoUploading'      => __( 'Uploading logo…', 'loginmood' ),
				'logoUploadError'    => __( 'The logo could not be uploaded.', 'loginmood' ),
			)
		);
	}

	/**
	 * Add a direct settings link on the Plugins screen.
	 *
	 * @param string[] $links Existing links.
	 * @return string[]
	 */
	public function action_links( $links ) {
		array_unshift(
			$links,
			sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( admin_url( 'options-general.php?page=loginmood' ) ),
				esc_html__( 'Settings', 'loginmood' )
			)
		);

		return $links;
	}

	/**
	 * Render the settings page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings       = Settings::get();
		$this->brand_palette = isset( $settings['brand_palette'] ) && is_array( $settings['brand_palette'] ) ? $settings['brand_palette'] : array();
		$logo_url       = wp_get_attachment_image_url( (int) $settings['logo_id'], 'medium' );
		if ( ! $logo_url ) {
			$logo_url = wp_get_attachment_url( (int) $settings['logo_id'] );
		}
		$background_url = wp_get_attachment_image_url( (int) $settings['background_image_id'], 'medium_large' );
		// Read-only status flag set after nonce-protected import processing.
		$status = isset( $_GET['loginmood-status'] ) ? sanitize_key( wp_unslash( $_GET['loginmood-status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<div class="wrap loginmood-admin-wrap">
			<h1><?php esc_html_e( 'LoginMood', 'loginmood' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Customize the login experience without depending on the active theme or modifying WordPress.', 'loginmood' ); ?></p>

			<?php if ( 'imported' === $status ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'The settings were imported successfully.', 'loginmood' ); ?></p></div>
			<?php elseif ( 'invalid-import' === $status ) : ?>
				<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'The file could not be imported. Make sure it is a JSON file exported by this plugin.', 'loginmood' ); ?></p></div>
			<?php elseif ( 'reset' === $status ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'The default settings were restored.', 'loginmood' ); ?></p></div>
			<?php endif; ?>

			<div class="loginmood-layout">
				<div class="loginmood-editor">
					<form action="options.php" method="post" id="loginmood-settings-form">
						<?php settings_fields( 'fegutogi_loginmood_settings_group' ); ?>
						<?php $this->render_identity_section( $settings, $logo_url ); ?>
						<?php $this->render_background_section( $settings, $background_url ); ?>
						<?php $this->render_style_section( $settings ); ?>
						<?php $this->render_content_section( $settings ); ?>
						<?php submit_button( __( 'Save changes', 'loginmood' ) ); ?>
					</form>

					<?php $this->render_tools_section(); ?>
				</div>

				<?php $this->render_preview( $settings, $logo_url, $background_url ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render identity controls.
	 *
	 * @param array<string, mixed> $settings Settings.
	 * @param string|false         $logo_url Logo URL.
	 */
	private function render_identity_section( $settings, $logo_url ) {
		?>
		<section class="loginmood-card">
			<h2><?php esc_html_e( 'Identity', 'loginmood' ); ?></h2>
			<div class="loginmood-field">
				<label><?php esc_html_e( 'Logo', 'loginmood' ); ?></label>
				<input type="hidden" name="fegutogi_loginmood_settings[logo_id]" id="loginmood-logo-id" value="<?php echo esc_attr( $settings['logo_id'] ); ?>">
				<div class="loginmood-media-control">
					<div class="loginmood-media-thumb loginmood-dropzone<?php echo $logo_url ? ' has-media' : ''; ?>" id="loginmood-logo-thumb" aria-label="<?php esc_attr_e( 'Drop the logo here', 'loginmood' ); ?>">
						<?php if ( $logo_url ) : ?><img src="<?php echo esc_url( $logo_url ); ?>" alt=""><?php endif; ?>
						<span class="loginmood-dropzone-label"><?php esc_html_e( 'Drop the logo here', 'loginmood' ); ?></span>
					</div>
					<span id="loginmood-logo-upload-proxy" class="screen-reader-text" aria-hidden="true"></span>
					<div>
						<button type="button" class="button loginmood-select-media" data-target="logo"><?php esc_html_e( 'Choose logo', 'loginmood' ); ?></button>
						<button type="button" class="button-link-delete loginmood-remove-media" data-target="logo"><?php esc_html_e( 'Remove', 'loginmood' ); ?></button>
						<p class="description"><?php esc_html_e( 'PNG, JPG, WebP, or sanitized SVG. SVG uploads are restricted to administrators.', 'loginmood' ); ?></p>
					</div>
				</div>
				<label class="loginmood-check"><input type="checkbox" name="fegutogi_loginmood_settings[hide_logo]" value="1" <?php checked( $settings['hide_logo'], 1 ); ?>> <?php esc_html_e( 'No logo', 'loginmood' ); ?></label>
				<p class="description"><?php esc_html_e( 'Hides the logo without removing the selected image, so it can be restored later.', 'loginmood' ); ?></p>
			</div>
			<div class="loginmood-logo-options" <?php echo ! empty( $settings['hide_logo'] ) ? 'hidden' : ''; ?>>
				<div class="loginmood-grid-two loginmood-property-grid">
					<?php $this->number_field( 'logo_width', __( 'Logo width', 'loginmood' ), $settings['logo_width'], 60, 320, 'px' ); ?>
					<div class="loginmood-logo-gap-option" <?php echo ( ! $logo_url || ! empty( $settings['hide_logo'] ) ) ? 'hidden' : ''; ?>>
						<?php $this->range_field( 'logo_panel_gap', __( 'Space between logo and panel', 'loginmood' ), $settings['logo_panel_gap'], 0, 80, 'px' ); ?>
						<p class="description"><?php esc_html_e( 'Limited to 80 px to keep the login composition balanced.', 'loginmood' ); ?></p>
					</div>
					<div class="loginmood-field">
						<label for="loginmood-logo-url"><?php esc_html_e( 'Logo link', 'loginmood' ); ?></label>
						<input type="url" class="regular-text" id="loginmood-logo-url" name="fegutogi_loginmood_settings[logo_url]" value="<?php echo esc_attr( $settings['logo_url'] ); ?>">
					</div>
					<div class="loginmood-field">
						<label for="loginmood-logo-shape"><?php esc_html_e( 'Logo shape', 'loginmood' ); ?></label>
						<select id="loginmood-logo-shape" name="fegutogi_loginmood_settings[logo_shape]">
							<option value="none" <?php selected( $settings['logo_shape'], 'none' ); ?>><?php esc_html_e( 'No clipping', 'loginmood' ); ?></option>
							<option value="rounded" <?php selected( $settings['logo_shape'], 'rounded' ); ?>><?php esc_html_e( 'Rounded', 'loginmood' ); ?></option>
							<option value="circle" <?php selected( $settings['logo_shape'], 'circle' ); ?>><?php esc_html_e( 'Circle', 'loginmood' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Use no clipping for transparent logos.', 'loginmood' ); ?></p>
					</div>
					<div class="loginmood-field">
						<label for="loginmood-logo-border-style"><?php esc_html_e( 'Logo border', 'loginmood' ); ?></label>
						<select id="loginmood-logo-border-style" name="fegutogi_loginmood_settings[logo_border_style]">
							<option value="none" <?php selected( $settings['logo_border_style'], 'none' ); ?>><?php esc_html_e( 'None', 'loginmood' ); ?></option>
							<option value="thin" <?php selected( $settings['logo_border_style'], 'thin' ); ?>><?php esc_html_e( 'Thin', 'loginmood' ); ?></option>
							<option value="strong" <?php selected( $settings['logo_border_style'], 'strong' ); ?>><?php esc_html_e( 'Strong', 'loginmood' ); ?></option>
						</select>
					</div>
					<div class="loginmood-field">
						<label for="loginmood-logo-shadow-style"><?php esc_html_e( 'Logo shadow', 'loginmood' ); ?></label>
						<select id="loginmood-logo-shadow-style" name="fegutogi_loginmood_settings[logo_shadow_style]">
							<option value="none" <?php selected( $settings['logo_shadow_style'], 'none' ); ?>><?php esc_html_e( 'None', 'loginmood' ); ?></option>
							<option value="soft" <?php selected( $settings['logo_shadow_style'], 'soft' ); ?>><?php esc_html_e( 'Soft', 'loginmood' ); ?></option>
							<option value="strong" <?php selected( $settings['logo_shadow_style'], 'strong' ); ?>><?php esc_html_e( 'Strong', 'loginmood' ); ?></option>
						</select>
					</div>
				</div>
				<div class="loginmood-logo-border-color-option" <?php echo 'none' === $settings['logo_border_style'] ? 'hidden' : ''; ?>>
					<?php $this->color_field( 'logo_border_color', __( 'Border color', 'loginmood' ), $settings['logo_border_color'] ); ?>
				</div>
				<div class="loginmood-logo-background-option" <?php echo 'none' === $settings['logo_shape'] ? 'hidden' : ''; ?>>
					<?php $this->color_field( 'logo_background_color', __( 'Clipped logo background', 'loginmood' ), $settings['logo_background_color'] ); ?>
					<p class="description loginmood-conditional-description"><?php esc_html_e( 'Used only for rounded and circular logos. No clipping always stays transparent.', 'loginmood' ); ?></p>
				</div>
				<div class="loginmood-logo-shadow-color-option" <?php echo 'none' === $settings['logo_shadow_style'] ? 'hidden' : ''; ?>>
					<?php $this->color_field( 'logo_shadow_color', __( 'Shadow color', 'loginmood' ), $settings['logo_shadow_color'] ); ?>
					<p class="description loginmood-conditional-description"><?php esc_html_e( 'Choose a light or dark shadow color to suit the background.', 'loginmood' ); ?></p>
				</div>
			</div>
		</section>
		<?php
	}

	/**
	 * Render background controls.
	 *
	 * @param array<string, mixed> $settings Settings.
	 * @param string|false         $background_url Background URL.
	 */
	private function render_background_section( $settings, $background_url ) {
		?>
		<section class="loginmood-card">
			<h2><?php esc_html_e( 'Background', 'loginmood' ); ?></h2>
			<div class="loginmood-field">
				<label for="loginmood-background-type"><?php esc_html_e( 'Background type', 'loginmood' ); ?></label>
				<select id="loginmood-background-type" name="fegutogi_loginmood_settings[background_type]">
					<option value="color" <?php selected( $settings['background_type'], 'color' ); ?>><?php esc_html_e( 'Color', 'loginmood' ); ?></option>
					<option value="gradient" <?php selected( $settings['background_type'], 'gradient' ); ?>><?php esc_html_e( 'Gradient', 'loginmood' ); ?></option>
					<option value="image" <?php selected( $settings['background_type'], 'image' ); ?>><?php esc_html_e( 'Image', 'loginmood' ); ?></option>
				</select>
			</div>
			<div class="loginmood-background-options" data-background-panel="color">
				<?php $this->color_field( 'background_color', __( 'Background color', 'loginmood' ), $settings['background_color'] ); ?>
			</div>
			<div class="loginmood-background-options" data-background-panel="gradient">
				<?php $this->color_field( 'gradient_start', __( 'Start color', 'loginmood' ), $settings['gradient_start'] ); ?>
				<?php $this->color_field( 'gradient_end', __( 'End color', 'loginmood' ), $settings['gradient_end'] ); ?>
				<div class="loginmood-property-grid loginmood-property-grid--single">
					<?php $this->number_field( 'gradient_angle', __( 'Angle', 'loginmood' ), $settings['gradient_angle'], 0, 360, '°' ); ?>
				</div>
			</div>
			<div class="loginmood-background-options" data-background-panel="image">
				<input type="hidden" name="fegutogi_loginmood_settings[background_image_id]" id="loginmood-background-id" value="<?php echo esc_attr( $settings['background_image_id'] ); ?>">
				<div class="loginmood-media-control">
					<div class="loginmood-media-thumb loginmood-media-thumb--wide" id="loginmood-background-thumb">
						<?php if ( $background_url ) : ?><img src="<?php echo esc_url( $background_url ); ?>" alt=""><?php endif; ?>
					</div>
					<div>
						<button type="button" class="button loginmood-select-media" data-target="background"><?php esc_html_e( 'Choose image', 'loginmood' ); ?></button>
						<button type="button" class="button-link-delete loginmood-remove-media" data-target="background"><?php esc_html_e( 'Remove', 'loginmood' ); ?></button>
					</div>
				</div>
			</div>
		</section>
		<?php
	}

	/**
	 * Render visual style controls.
	 *
	 * @param array<string, mixed> $settings Settings.
	 */
	private function render_style_section( $settings ) {
		?>
		<section class="loginmood-card">
			<h2><?php esc_html_e( 'Colors and shape', 'loginmood' ); ?></h2>
			<?php $this->render_palette_manager(); ?>
			<div class="loginmood-presets" aria-label="<?php esc_attr_e( 'Visual presets', 'loginmood' ); ?>">
				<span><?php esc_html_e( 'Presets:', 'loginmood' ); ?></span>
				<button type="button" class="button" data-loginmood-preset="light"><?php esc_html_e( 'Light', 'loginmood' ); ?></button>
				<button type="button" class="button" data-loginmood-preset="dark"><?php esc_html_e( 'Dark', 'loginmood' ); ?></button>
				<button type="button" class="button" data-loginmood-preset="ocean"><?php esc_html_e( 'Ocean', 'loginmood' ); ?></button>
			</div>
			<div class="loginmood-color-list">
				<?php $this->color_field( 'panel_color', __( 'Panel', 'loginmood' ), $settings['panel_color'] ); ?>
				<?php $this->color_field( 'primary_color', __( 'Button', 'loginmood' ), $settings['primary_color'] ); ?>
				<?php $this->color_field( 'text_color', __( 'Panel text', 'loginmood' ), $settings['text_color'] ); ?>
				<?php $this->color_field( 'background_text_color', __( 'Background text', 'loginmood' ), $settings['background_text_color'] ); ?>
				<?php $this->color_field( 'link_color', __( 'Links', 'loginmood' ), $settings['link_color'] ); ?>
				<?php $this->color_field( 'button_text_color', __( 'Button text', 'loginmood' ), $settings['button_text_color'] ); ?>
				<?php $this->color_field( 'field_background_color', __( 'Field background', 'loginmood' ), $settings['field_background_color'] ); ?>
				<?php $this->color_field( 'field_text_color', __( 'Field text', 'loginmood' ), $settings['field_text_color'] ); ?>
			</div>
			<div class="loginmood-grid-two loginmood-property-grid">
				<?php $this->range_field( 'border_radius', __( 'Panel corner radius', 'loginmood' ), $settings['border_radius'], 0, 50, 'px' ); ?>
				<?php $this->range_field( 'control_radius', __( 'Field and button corner radius', 'loginmood' ), $settings['control_radius'], 0, 50, 'px' ); ?>
			</div>
			<div id="loginmood-contrast-status" class="loginmood-contrast-status" role="status" aria-live="polite"></div>
		</section>
		<?php
	}

	/**
	 * Render brand-palette import controls.
	 */
	private function render_palette_manager() {
		?>
		<div class="loginmood-palette-manager">
			<h3><?php esc_html_e( 'Brand color palette', 'loginmood' ); ?></h3>
			<input type="hidden" id="loginmood-brand-palette" name="fegutogi_loginmood_settings[brand_palette]" value="<?php echo esc_attr( wp_json_encode( array_values( $this->brand_palette ) ) ); ?>">
			<div class="loginmood-palette-dropzone loginmood-dropzone" id="loginmood-palette-dropzone">
				<span class="dashicons dashicons-upload" aria-hidden="true"></span>
				<span class="loginmood-palette-drop-label"><?php esc_html_e( 'Drop the palette file here', 'loginmood' ); ?></span>
				<span aria-hidden="true">·</span>
				<label class="button" for="loginmood-palette-file"><?php esc_html_e( 'Browse files', 'loginmood' ); ?></label>
				<input type="file" id="loginmood-palette-file" class="screen-reader-text" accept=".json,.txt,.css,.gpl,.csv,.ase,application/json,text/plain,text/css,text/csv,application/octet-stream">
			</div>
			<div class="loginmood-palette-actions">
				<button type="button" class="button-link-delete" id="loginmood-clear-palette"><?php esc_html_e( 'Clear palette', 'loginmood' ); ?></button>
			</div>
			<p class="description"><?php esc_html_e( 'Supported formats: JSON, TXT, CSS, CSV, Adobe ASE, and GIMP GPL. Up to 32 colors are stored with the plugin settings.', 'loginmood' ); ?></p>
			<div class="loginmood-palette-swatches" id="loginmood-palette-swatches" aria-live="polite"></div>
			<details class="loginmood-palette-table" id="loginmood-palette-table" hidden>
				<summary id="loginmood-palette-table-summary"><?php esc_html_e( 'Imported colors', 'loginmood' ); ?></summary>
				<div class="loginmood-palette-table-scroll">
					<table>
						<thead><tr><th scope="col"><?php esc_html_e( 'Color', 'loginmood' ); ?></th><th scope="col"><?php esc_html_e( 'Name', 'loginmood' ); ?></th><th scope="col"><?php esc_html_e( 'HEX code', 'loginmood' ); ?></th></tr></thead>
						<tbody id="loginmood-palette-table-body"></tbody>
					</table>
				</div>
			</details>
		</div>
		<?php
	}

	/**
	 * Render content and data controls.
	 *
	 * @param array<string, mixed> $settings Settings.
	 */
	private function render_content_section( $settings ) {
		?>
		<section class="loginmood-card">
			<h2><?php esc_html_e( 'Content and behavior', 'loginmood' ); ?></h2>
			<div class="loginmood-field">
				<label for="loginmood-footer-text"><?php esc_html_e( 'Footer text', 'loginmood' ); ?></label>
				<input type="text" class="regular-text" id="loginmood-footer-text" name="fegutogi_loginmood_settings[footer_text]" value="<?php echo esc_attr( $settings['footer_text'] ); ?>" maxlength="160">
			</div>
			<input type="hidden" name="fegutogi_loginmood_settings[enable_animation]" value="0">
			<label class="loginmood-check"><input type="checkbox" name="fegutogi_loginmood_settings[enable_animation]" value="1" <?php checked( $settings['enable_animation'], 1 ); ?>> <?php esc_html_e( 'Enable entrance animation', 'loginmood' ); ?></label>
			<label class="loginmood-check"><input type="checkbox" name="fegutogi_loginmood_settings[hide_language_switcher]" value="1" <?php checked( $settings['hide_language_switcher'], 1 ); ?>> <?php esc_html_e( 'Hide the language selector', 'loginmood' ); ?></label>
			<label class="loginmood-check"><input type="checkbox" name="fegutogi_loginmood_settings[delete_data_on_uninstall]" value="1" <?php checked( $settings['delete_data_on_uninstall'], 1 ); ?>> <?php esc_html_e( 'Delete settings when uninstalling the plugin', 'loginmood' ); ?></label>
		</section>
		<?php
	}

	/**
	 * Render import and export tools.
	 */
	private function render_tools_section() {
		?>
		<section class="loginmood-card loginmood-tools">
			<h2><?php esc_html_e( 'Tools', 'loginmood' ); ?></h2>
			<p><?php esc_html_e( 'Reuse a visual identity on another site with a JSON file.', 'loginmood' ); ?></p>
			<p><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=fegutogi_loginmood_export_settings' ), 'fegutogi_loginmood_export_settings' ) ); ?>"><?php esc_html_e( 'Export settings', 'loginmood' ); ?></a></p>
			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" enctype="multipart/form-data">
				<input type="hidden" name="action" value="fegutogi_loginmood_import_settings">
				<?php wp_nonce_field( 'fegutogi_loginmood_import_settings' ); ?>
				<label for="loginmood-import-file" class="screen-reader-text"><?php esc_html_e( 'Settings file', 'loginmood' ); ?></label>
				<input type="file" id="loginmood-import-file" name="fegutogi_loginmood_import_file" accept="application/json,.json" required>
				<?php submit_button( __( 'Import', 'loginmood' ), 'secondary', 'submit', false ); ?>
			</form>
			<hr>
			<p><a class="button-link-delete loginmood-reset-link" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=fegutogi_loginmood_reset_settings' ), 'fegutogi_loginmood_reset_settings' ) ); ?>"><?php esc_html_e( 'Restore default settings', 'loginmood' ); ?></a></p>
		</section>
		<?php
	}

	/**
	 * Render the visual preview.
	 *
	 * @param array<string, mixed> $settings Settings.
	 * @param string|false         $logo_url Logo URL.
	 * @param string|false         $background_url Background URL.
	 */
	private function render_preview( $settings, $logo_url, $background_url ) {
		?>
		<aside class="loginmood-preview-column">
			<div class="loginmood-preview-heading">
				<h2><?php esc_html_e( 'Preview', 'loginmood' ); ?></h2>
				<a href="<?php echo esc_url( wp_login_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open login', 'loginmood' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'loginmood' ); ?></span></a>
			</div>
			<div class="loginmood-preview" id="loginmood-preview" data-background-image="<?php echo esc_url( $background_url ? $background_url : '' ); ?>" aria-hidden="true">
				<div class="loginmood-preview-content">
					<div class="loginmood-preview-logo" id="loginmood-preview-logo" <?php echo ! empty( $settings['hide_logo'] ) ? 'hidden' : ''; ?>>
						<?php if ( $logo_url ) : ?>
							<img src="<?php echo esc_url( $logo_url ); ?>" alt="">
						<?php else : ?>
							<span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
						<?php endif; ?>
					</div>
					<div class="loginmood-preview-form">
						<label><?php esc_html_e( 'Username or email address', 'loginmood' ); ?><input type="text" tabindex="-1" readonly></label>
						<label><?php esc_html_e( 'Password', 'loginmood' ); ?><span class="loginmood-preview-password"><input type="text" value="••••••••" tabindex="-1" readonly><span class="dashicons dashicons-visibility" aria-hidden="true"></span></span></label>
						<div class="loginmood-preview-row"><label><input type="checkbox" tabindex="-1"> <?php esc_html_e( 'Remember me', 'loginmood' ); ?></label><button type="button" tabindex="-1"><?php esc_html_e( 'Log in', 'loginmood' ); ?></button></div>
					</div>
					<a href="#" class="loginmood-preview-link" tabindex="-1"><?php esc_html_e( 'Lost your password?', 'loginmood' ); ?></a>
					<p class="loginmood-preview-footer" id="loginmood-preview-footer"><?php echo esc_html( $settings['footer_text'] ); ?></p>
				</div>
			</div>
		</aside>
		<?php
	}

	/**
	 * Render a color input.
	 *
	 * @param string $key Setting key.
	 * @param string $label Label.
	 * @param string $value Current value.
	 */
	private function color_field( $key, $label, $value ) {
		$field_id = 'loginmood-' . str_replace( '_', '-', $key );
		/* translators: %s: color setting label. */
		$palette_label = sprintf( __( 'Palette color for %s', 'loginmood' ), $label );
		/* translators: %s: color setting label. */
		$picker_label = sprintf( __( 'Visual color picker for %s', 'loginmood' ), $label );
		?>
		<div class="loginmood-field loginmood-color-field">
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
			<div class="loginmood-color-visual">
				<input type="color" class="loginmood-color-picker" value="<?php echo esc_attr( $value ); ?>" data-color-key="<?php echo esc_attr( $key ); ?>" aria-label="<?php echo esc_attr( $picker_label ); ?>">
				<button type="button" class="button loginmood-eyedropper" data-color-key="<?php echo esc_attr( $key ); ?>" aria-label="<?php echo esc_attr( __( 'Pick a color from the screen', 'loginmood' ) ); ?>"><span class="dashicons dashicons-admin-appearance" aria-hidden="true"></span></button>
			</div>
			<input type="text" id="<?php echo esc_attr( $field_id ); ?>" class="loginmood-color-value" name="fegutogi_loginmood_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( strtoupper( $value ) ); ?>" maxlength="7" pattern="#[0-9a-fA-F]{6}" spellcheck="false" data-color-key="<?php echo esc_attr( $key ); ?>" data-last-color="<?php echo esc_attr( strtoupper( $value ) ); ?>">
			<select class="loginmood-palette-select" data-color-key="<?php echo esc_attr( $key ); ?>" aria-label="<?php echo esc_attr( $palette_label ); ?>">
					<option value=""><?php esc_html_e( 'Choose from palette', 'loginmood' ); ?></option>
					<?php foreach ( $this->brand_palette as $palette_entry ) : ?>
						<?php
						$palette_color = is_array( $palette_entry ) ? $palette_entry['color'] : $palette_entry;
						$palette_name  = is_array( $palette_entry ) ? $palette_entry['name'] : '';
						$option_label  = '● ' . ( $palette_name ? $palette_name . ' · ' : '' ) . strtoupper( $palette_color );
						?>
						<option value="<?php echo esc_attr( $palette_color ); ?>" <?php selected( strtolower( $value ), strtolower( $palette_color ) ); ?>><?php echo esc_html( $option_label ); ?></option>
					<?php endforeach; ?>
			</select>
		</div>
		<?php
	}

	/**
	 * Render a range input with its current value.
	 *
	 * @param string $key Setting key.
	 * @param string $label Label.
	 * @param int    $value Current value.
	 * @param int    $min Minimum.
	 * @param int    $max Maximum.
	 * @param string $unit Unit label.
	 */
	private function range_field( $key, $label, $value, $min, $max, $unit ) {
		$field_id  = 'loginmood-' . str_replace( '_', '-', $key );
		$output_id = $field_id . '-value';
		?>
		<div class="loginmood-field">
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
			<div class="loginmood-range-control">
				<input type="range" id="<?php echo esc_attr( $field_id ); ?>" name="fegutogi_loginmood_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" data-range-output="<?php echo esc_attr( $output_id ); ?>">
				<output id="<?php echo esc_attr( $output_id ); ?>" for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $value . ' ' . $unit ); ?></output>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a number input.
	 *
	 * @param string $key Setting key.
	 * @param string $label Label.
	 * @param int    $value Current value.
	 * @param int    $min Minimum.
	 * @param int    $max Maximum.
	 * @param string $unit Unit label.
	 */
	private function number_field( $key, $label, $value, $min, $max, $unit ) {
		?>
		<div class="loginmood-field">
			<label for="loginmood-<?php echo esc_attr( str_replace( '_', '-', $key ) ); ?>"><?php echo esc_html( $label ); ?></label>
			<div class="loginmood-number"><input type="number" id="loginmood-<?php echo esc_attr( str_replace( '_', '-', $key ) ); ?>" name="fegutogi_loginmood_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>"><span><?php echo esc_html( $unit ); ?></span></div>
		</div>
		<?php
	}

	/**
	 * Download the current settings as JSON.
	 */
	public function export_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'loginmood' ) );
		}

		check_admin_referer( 'fegutogi_loginmood_export_settings' );

		$payload = array(
			'plugin'   => 'loginmood',
			'version'  => FEGUTOGI_LOGINMOOD_VERSION,
			'exported' => gmdate( 'c' ),
			'settings' => Settings::get(),
		);

		nocache_headers();
		header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		header( 'Content-Disposition: attachment; filename=loginmood-settings-' . gmdate( 'Y-m-d' ) . '.json' );
		echo wp_json_encode( $payload, JSON_PRETTY_PRINT ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download response.
		exit;
	}

	/**
	 * Import settings from an exported JSON file.
	 */
	public function import_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'loginmood' ) );
		}

		check_admin_referer( 'fegutogi_loginmood_import_settings' );
		$redirect = admin_url( 'options-general.php?page=loginmood&loginmood-status=invalid-import' );

		if ( empty( $_FILES['fegutogi_loginmood_import_file']['tmp_name'] ) || ! isset( $_FILES['fegutogi_loginmood_import_file']['size'], $_FILES['fegutogi_loginmood_import_file']['error'] ) ) {
			wp_safe_redirect( $redirect );
			exit;
		}

		$size = absint( $_FILES['fegutogi_loginmood_import_file']['size'] );
		if ( UPLOAD_ERR_OK !== (int) $_FILES['fegutogi_loginmood_import_file']['error'] || 0 === $size || $size > 1048576 ) {
			wp_safe_redirect( $redirect );
			exit;
		}

		$tmp_name = sanitize_text_field( wp_unslash( $_FILES['fegutogi_loginmood_import_file']['tmp_name'] ) );
		if ( ! is_uploaded_file( $tmp_name ) ) {
			wp_safe_redirect( $redirect );
			exit;
		}

		$contents = file_get_contents( $tmp_name ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Validated local PHP upload temporary file.
		$payload  = json_decode( (string) $contents, true );

		if ( ! is_array( $payload ) || 'loginmood' !== ( $payload['plugin'] ?? '' ) || ! isset( $payload['settings'] ) || ! is_array( $payload['settings'] ) ) {
			wp_safe_redirect( $redirect );
			exit;
		}

		$imported_settings                        = $payload['settings'];
		$imported_settings['logo_id']             = 0;
		$imported_settings['background_image_id'] = 0;
		update_option( Settings::OPTION_NAME, Settings::sanitize( $imported_settings ), false );
		wp_safe_redirect( admin_url( 'options-general.php?page=loginmood&loginmood-status=imported' ) );
		exit;
	}

	/**
	 * Restore the plugin defaults.
	 */
	public function reset_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'loginmood' ) );
		}

		check_admin_referer( 'fegutogi_loginmood_reset_settings' );
		update_option( Settings::OPTION_NAME, Settings::sanitize( Settings::defaults() ), false );
		wp_safe_redirect( admin_url( 'options-general.php?page=loginmood&loginmood-status=reset' ) );
		exit;
	}
}

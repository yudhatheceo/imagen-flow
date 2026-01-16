<?php
/**
 * Settings class for ImagenFlow.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ImagenFlow_Settings {

	/**
	 * Option group name.
	 *
	 * @var string
	 */
	private $option_group = 'imagen_flow_settings';

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private $option_name = 'imagen_flow_options';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add settings page to the menu.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'ImagenFlow Settings', 'imagen-flow' ),
			__( 'ImagenFlow', 'imagen-flow' ),
			'manage_options',
			'imagen-flow',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		register_setting( $this->option_group, $this->option_name, array( $this, 'sanitize_settings' ) );

		add_settings_section(
			'imagen_flow_api_section',
			__( 'API Configuration', 'imagen-flow' ),
			null,
			'imagen-flow'
		);

		add_settings_field(
			'gemini_api_key',
			__( 'Gemini API Key', 'imagen-flow' ),
			array( $this, 'render_api_key_field' ),
			'imagen-flow',
			'imagen_flow_api_section'
		);

		add_settings_section(
			'imagen_flow_image_section',
			__( 'Image Settings', 'imagen-flow' ),
			null,
			'imagen-flow'
		);

		add_settings_field(
			'default_quality',
			__( 'Default Image Quality', 'imagen-flow' ),
			array( $this, 'render_quality_field' ),
			'imagen-flow',
			'imagen_flow_image_section'
		);

		add_settings_field(
			'preferred_format',
			__( 'Preferred Format', 'imagen-flow' ),
			array( $this, 'render_format_field' ),
			'imagen-flow',
			'imagen_flow_image_section'
		);
	}

	/**
	 * Sanitize settings.
	 */
	public function sanitize_settings( $input ) {
		$output = array();

		if ( isset( $input['gemini_api_key'] ) ) {
			$output['gemini_api_key'] = sanitize_text_field( $input['gemini_api_key'] );
		}

		if ( isset( $input['default_quality'] ) ) {
			$output['default_quality'] = absint( $input['default_quality'] );
		}

		if ( isset( $input['preferred_format'] ) ) {
			$output['preferred_format'] = sanitize_text_field( $input['preferred_format'] );
		}

		return $output;
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'ImagenFlow Settings', 'imagen-flow' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( $this->option_group );
				do_settings_sections( 'imagen-flow' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render API Key field.
	 */
	public function render_api_key_field() {
		$options = get_option( $this->option_name );
		$value   = isset( $options['gemini_api_key'] ) ? $options['gemini_api_key'] : '';
		?>
		<input type="password" name="<?php echo esc_attr( $this->option_name ); ?>[gemini_api_key]" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
		<p class="description"><?php _e( 'Enter your Google Gemini API Key.', 'imagen-flow' ); ?></p>
		<?php
	}

	/**
	 * Render Quality field.
	 */
	public function render_quality_field() {
		$options = get_option( $this->option_name );
		$value   = isset( $options['default_quality'] ) ? $options['default_quality'] : 80;
		?>
		<input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[default_quality]" value="<?php echo esc_attr( $value ); ?>" min="1" max="100">
		<p class="description"><?php _e( 'Set the default compression level (1-100).', 'imagen-flow' ); ?></p>
		<?php
	}

	/**
	 * Render Format field.
	 */
	public function render_format_field() {
		$options = get_option( $this->option_name );
		$value   = isset( $options['preferred_format'] ) ? $options['preferred_format'] : 'webp';
		?>
		<select name="<?php echo esc_attr( $this->option_name ); ?>[preferred_format]">
			<option value="webp" <?php selected( $value, 'webp' ); ?>>WebP</option>
			<option value="jpeg" <?php selected( $value, 'jpeg' ); ?>>JPEG</option>
		</select>
		<p class="description"><?php _e( 'Select the preferred output format.', 'imagen-flow' ); ?></p>
		<?php
	}
}

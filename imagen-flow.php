<?php
/**
 * Plugin Name:       ImagenFlow
 * Plugin URI:        https://github.com/yudhatheceo/imagen-flow
 * Description:       AI-Powered Native Image Generation for Gutenberg using Gemini API.
 * Version:           1.0.0
 * Author:            The Beacon Team
 * Author URI:        https://beacon.co.id
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       imagen-flow
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'IMAGEN_FLOW_VERSION', '1.0.0' );

/**
 * The full path to the plugin directory.
 */
define( 'IMAGEN_FLOW_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The URL to the plugin directory.
 */
define( 'IMAGEN_FLOW_URL', plugin_dir_url( __FILE__ ) );

/**
 * The main plugin class.
 */
final class ImagenFlow {

	/**
	 * Instance of this class.
	 *
	 * @var ImagenFlow
	 */
	private static $instance;

	/**
	 * Get instance of this class.
	 *
	 * @return ImagenFlow
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof ImagenFlow ) ) {
			self::$instance = new ImagenFlow();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Do nothing.
	}

	/**
	 * Setup the plugin.
	 */
	private function setup() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required files.
	 */
	private function includes() {
		require_once IMAGEN_FLOW_PATH . 'includes/class-settings.php';
		require_once IMAGEN_FLOW_PATH . 'includes/class-gemini-api.php';
		require_once IMAGEN_FLOW_PATH . 'includes/class-media-processor.php';
		require_once IMAGEN_FLOW_PATH . 'includes/class-rest-api.php';
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'admin_notices', array( $this, 'check_requirements' ) );
	}

	/**
	 * Initialize the plugin after all plugins are loaded.
	 */
	public function init() {
		// Initialize settings.
		new ImagenFlow_Settings();

		// Initialize REST API.
		new ImagenFlow_REST_API();
		
		// Register block.
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Check for plugin requirements.
	 */
	public function check_requirements() {
		if ( ! extension_loaded( 'imagick' ) ) {
			?>
			<div class="notice notice-error">
				<p><?php _e( 'ImagenFlow requires the <strong>Imagick</strong> extension to be installed on your server.', 'imagen-flow' ); ?></p>
			</div>
			<?php
		}

		$options = get_option( 'imagen_flow_options' );
		if ( empty( $options['gemini_api_key'] ) ) {
			?>
			<div class="notice notice-warning is-dismissible">
				<p><?php printf( __( 'ImagenFlow is almost ready. Please <a href="%s">enter your Gemini API Key</a> to start generating images.', 'imagen-flow' ), admin_url( 'options-general.php?page=imagen-flow' ) ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Register the Gutenberg block.
	 */
	public function register_block() {
		register_block_type( IMAGEN_FLOW_PATH . 'build' );
	}
}

/**
 * Start the plugin.
 */
function imagen_flow() {
	return ImagenFlow::get_instance();
}

imagen_flow();

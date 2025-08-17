<?php
/**
 * Plugin Name: DomGats Customizable Product Filters
 * Description: An Elementor widget to display products with customizable filters.
 * Version:     1.3.0
 * Author:      DomGats
 * Author URI:  https://domgats.com/
 * Text Domain: domgats-customizable-product-filters
 * Elementor tested up to: 3.21.0
 * Elementor Pro tested up to: 3.21.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'DGCF_VERSION', '1.3.0' );
define( 'DGCF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'DGCF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main DomGats Customizable Product Filters Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.0.0
 */
final class DomGats_Customizable_Product_Filters {

	/**
	 * Plugin Version
	 *
	 * @since 1.3.0
	 * @var string The plugin version.
	 */
	const VERSION = '1.3.0';

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.0.0
	 * @var string Minimum Elementor version required to run the plugin.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '3.5.0';

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 * @var DomGats_Customizable_Product_Filters The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 * @return DomGats_Customizable_Product_Filters An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
	}

	/**
	 * Load Textdomain
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function i18n() {
		load_plugin_textdomain( 'domgats-customizable-product-filters' );
	}

	/**
	 * On Plugins Loaded
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function on_plugins_loaded() {
		if ( $this->is_compatible() ) {
			add_action( 'elementor/init', [ $this, 'init' ] );
		}
	}

	/**
	 * Check if Elementor is installed and activated.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return bool
	 */
	public function is_compatible() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return false;
		}

		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return false;
		}

		return true;
	}

	/**
	 * Initialize the plugin
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function init() {
		$this->i18n();
		
		$this->includes();

		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ] );
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ] );
	}
	
	/**
	 * Include Plugin Files
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function includes() {
		require_once DGCF_PLUGIN_PATH . 'includes/class-ajax.php';
		new DomGats_Custom_Filters_Ajax();

		if ( is_admin() ) {
			require_once DGCF_PLUGIN_PATH . 'includes/class-admin.php';
			new DomGats\ProductFilter\Admin();
		}
	}

	/**
	 * Enqueue frontend scripts and styles.
	 */
	public function enqueue_frontend_scripts() {
		wp_register_style(
			'flickity-css',
			'https://unpkg.com/flickity@2/dist/flickity.min.css',
			[],
			'2.3.0'
		);

		wp_enqueue_style(
			'dgcf-frontend-css',
			DGCF_PLUGIN_URL . 'assets/css/frontend.css',
			['flickity-css'],
			DGCF_VERSION
		);

		wp_register_script(
			'flickity-js',
			'https://unpkg.com/flickity@2/dist/flickity.pkgd.min.js',
			['jquery'],
			'2.3.0',
			true
		);

		wp_enqueue_script(
			'dgcf-frontend-script',
			DGCF_PLUGIN_URL . 'assets/js/frontend.js',
			[ 'jquery', 'imagesloaded', 'flickity-js' ],
			DGCF_VERSION,
			true
		);
	}

	/**
	 * Enqueue editor-specific scripts.
	 */
	public function enqueue_editor_scripts() {
		wp_enqueue_script(
			'dgcf-editor-script',
			DGCF_PLUGIN_URL . 'assets/js/editor.js',
			[ 'jquery', 'elementor-editor' ],
			DGCF_VERSION,
			true
		);

		wp_localize_script(
			'dgcf-editor-script',
			'dgcf_editor_data',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'dgcf_editor_nonce' ),
			]
		);
	}

	/**
	 * Admin notice for missing Elementor.
	 */
	public function admin_notice_missing_main_plugin() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
		$message = sprintf(
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'domgats-customizable-product-filters' ),
			'<strong>' . esc_html__( 'DomGats Customizable Product Filters', 'domgats-customizable-product-filters' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'domgats-customizable-product-filters' ) . '</strong>'
		);
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * Register Widgets
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 */
	public function register_widgets( $widgets_manager ) {
		require_once DGCF_PLUGIN_PATH . 'includes/widgets/class-widget.php';
		$widgets_manager->register( new \DomGats\ProductFilter\Widgets\DomGats_Filtered_Loop_Widget() );
	}
}

DomGats_Customizable_Product_Filters::instance();

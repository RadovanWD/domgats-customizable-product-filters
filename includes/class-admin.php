<?php
namespace DomGats\ProductFilter;

/**
 * Handles the admin-side settings page for the plugin.
 *
 * @package DomGats_Customizable_Product_Filters
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Admin {

	/**
	 * The unique ID for the settings page.
	 * @var string
	 */
	private $option_group = 'dgcpf_settings_group';

	/**
	 * Constructor. Hooks into WordPress admin actions.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Adds the plugin settings page to the "Settings" menu in WordPress.
	 */
	public function add_admin_menu() {
		add_options_page(
			esc_html__( 'DomGats Product Filters Settings', 'custom-product-filters' ), // Page Title
			esc_html__( 'Product Filters', 'custom-product-filters' ),                    // Menu Title
			'manage_options',                     // Capability required
			'dgcpf_settings',                     // Menu Slug
			[ $this, 'render_settings_page' ]      // Callback function to render the page
		);
	}

	/**
	 * Renders the HTML for the settings page.
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( $this->option_group );
				do_settings_sections( 'dgcpf_settings' );
				submit_button( esc_html__( 'Save Settings', 'custom-product-filters' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Registers the settings, sections, and fields using the Settings API.
	 */
	public function register_settings() {
		register_setting(
			$this->option_group,
			'dgcpf_options',
			[ $this, 'sanitize_options' ] // Optional sanitize callback
		);

		// Section for General Settings
		add_settings_section(
			'dgcpf_general_section',
			esc_html__( 'Display Text Settings', 'custom-product-filters' ),
			null, // No description needed for this section
			'dgcpf_settings'
		);

		// Field for "Filter by:" text
		add_settings_field(
			'dgcpf_filter_by_text',
			esc_html__( 'Filter Dropdown Label', 'custom-product-filters' ),
			[ $this, 'render_text_field' ],
			'dgcpf_settings',
			'dgcpf_general_section',
			[
				'id'      => 'filter_by_text',
				'label'   => esc_html__( 'The label shown above the list of tags in the dropdown.', 'custom-product-filters' ),
				'default' => esc_html__( 'Filter by:', 'custom-product-filters' ),
			]
		);

		// Field for "No products found" text
		add_settings_field(
			'dgcpf_no_products_text',
			esc_html__( '"No Products Found" Message', 'custom-product-filters' ),
			[ $this, 'render_text_field' ],
			'dgcpf_settings',
			'dgcpf_general_section',
			[
				'id'      => 'no_products_text',
				'label'   => esc_html__( 'The message displayed when no products match the selected filters.', 'custom-product-filters' ),
				'default' => esc_html__( 'There are no products with that combination of tags.', 'custom-product-filters' ),
			]
		);
	}

	/**
	 * Renders a standard text input field.
	 *
	 * @param array $args Arguments for the field.
	 */
	public function render_text_field( $args ) {
		$options = get_option( 'dgcpf_options', [] );
		$value   = isset( $options[ $args['id'] ] ) ? $options[ $args['id'] ] : $args['default'];
		?>
		<input type="text"
			   id="<?php echo esc_attr( $args['id'] ); ?>"
			   name="dgcpf_options[<?php echo esc_attr( $args['id'] ); ?>]"
			   value="<?php echo esc_attr( $value ); ?>"
			   class="regular-text">
		<p class="description"><?php echo esc_html( $args['label'] ); ?></p>
		<?php
	}

	/**
	 * Sanitizes the options array before saving to the database.
	 *
	 * @param array $input The input array from the form.
	 * @return array The sanitized array.
	 */
	public function sanitize_options( $input ) {
		$sanitized_input = [];
		if ( isset( $input['filter_by_text'] ) ) {
			$sanitized_input['filter_by_text'] = sanitize_text_field( $input['filter_by_text'] );
		}
		if ( isset( $input['no_products_text'] ) ) {
			$sanitized_input['no_products_text'] = sanitize_text_field( $input['no_products_text'] );
		}
		return $sanitized_input;
	}
}
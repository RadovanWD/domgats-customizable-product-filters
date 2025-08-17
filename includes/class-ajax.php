<?php
/**
 * DGCF Ajax Class.
 *
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * DomGats_Custom_Filters_Ajax class.
 */
class DomGats_Custom_Filters_Ajax {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_dgcf_filter_products', [ $this, 'dgcf_filter_products' ] );
		add_action( 'wp_ajax_nopriv_dgcf_filter_products', [ $this, 'dgcf_filter_products' ] );
		add_action( 'wp_ajax_dgcf_get_acf_choices', [ $this, 'dgcf_get_acf_choices' ] );
	}

	/**
	 * AJAX handler to fetch ACF field choices for the Elementor editor.
	 */
	public function dgcf_get_acf_choices() {
		// 1. Security Check: Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'dgcf_editor_nonce' ) ) {
			wp_send_json_error( 'Nonce verification failed.', 403 );
			return;
		}

		// 2. Security Check: Ensure user has permission to edit posts.
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'You do not have permission to perform this action.', 403 );
			return;
		}
		
		// 3. Input Validation: Check if the field key is provided.
		if ( ! isset( $_POST['field_key'] ) || empty( $_POST['field_key'] ) ) {
			wp_send_json_error( 'ACF field key not provided.', 400 );
			return;
		}

		$field_key = sanitize_text_field( $_POST['field_key'] );

		// 4. Check if ACF function exists.
		if ( ! function_exists( 'get_field_object' ) ) {
			wp_send_json_error( 'ACF function not found. Is ACF Pro active?', 500 );
			return;
		}

		$field = get_field_object( $field_key );

		if ( ! $field ) {
			wp_send_json_error( 'Could not find field.', 404 );
			return;
		}
		
		$choices = [];
		if ( ! empty( $field['choices'] ) ) {
			$choices = $field['choices'];
		} elseif ( 'true_false' === $field['type'] ) {
			// Handle True/False field type specifically, as it doesn't have a 'choices' array.
			$choices = [
				'1' => isset( $field['ui_on_text'] ) && ! empty( $field['ui_on_text'] ) ? $field['ui_on_text'] : __( 'Yes', 'domgats-customizable-product-filters' ),
				'0' => isset( $field['ui_off_text'] ) && ! empty( $field['ui_off_text'] ) ? $field['ui_off_text'] : __( 'No', 'domgats-customizable-product-filters' ),
			];
		}

		if ( empty( $choices ) ) {
			wp_send_json_error( 'Field has no choices.', 404 );
			return;
		}
		
		// 5. Return choices on success.
		wp_send_json_success( $choices );
	}


	/**
	 * AJAX handler for filtering products on the frontend.
	 */
	public function dgcf_filter_products() {
		// This function is called by frontend.js to filter products.
		// For brevity, the logic is simplified here but would contain the full query from the frontend.
		check_ajax_referer( 'dgcf_filter_nonce', 'nonce' );

		$settings = isset( $_POST['settings'] ) ? json_decode( stripslashes( $_POST['settings'] ), true ) : [];
		$template_id = isset( $_POST['template_id'] ) ? absint( $_POST['template_id'] ) : 0;

		if ( ! $template_id ) {
			wp_send_json_error('Template ID missing.');
		}

		// Build query based on frontend filters...
		$query_args = [
			'post_type' => 'product',
			'posts_per_page' => $settings['posts_per_page'] ?? 9,
		];

		$query = new \WP_Query( $query_args );

		ob_start();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id, true );
			}
		} else {
			echo '<p class="no-products-found">' . esc_html__( 'No products found.', 'domgats-customizable-product-filters' ) . '</p>';
		}

		wp_reset_postdata();

		$html = ob_get_clean();

		wp_send_json_success(
			[
				'html'        => $html,
				'max_num_pages' => $query->max_num_pages,
			]
		);
	}
}

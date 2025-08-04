<?php
/**
 * Plugin Name:       DomGats Customizable Product Filters
 * Plugin URI:        https://example.com/
 * Description:       A custom product filter for WooCommerce and more to come.
 * Version:           1.0.0
 * Author:            Radovan Gataric DomGat
 * Author URI:        /
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       custom-product-filters
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Define Constants
 */
define('DGCPF_VERSION', '1.0.0');
define('DGCPF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DGCPF_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Include the core files.
 */
require_once DGCPF_PLUGIN_DIR . 'includes/class-dgcpf-shortcodes.php';
require_once DGCPF_PLUGIN_DIR . 'includes/class-dgcpf-ajax.php';


/**
 * Enqueue scripts and styles.
 *
 * This is the proper way to load your assets in a plugin.
 * We've moved the logic from your original file and updated the paths.
 */
function dgcpf_enqueue_assets()
{
    // Register third-party assets first
    wp_register_style('flickity-css', 'https://unpkg.com/flickity@2/dist/flickity.min.css');
    wp_register_script('flickity-js', 'https://unpkg.com/flickity@2/dist/flickity.pkgd.min.js', ['jquery'], '2.3.0', true);

    // Enqueue your plugin's assets
    wp_enqueue_style(
        'dgcpf-main-style',
        DGCPF_PLUGIN_URL . 'assets/css/main.css',
        ['flickity-css'], // Dependency
        DGCPF_VERSION
    );

    wp_enqueue_script(
        'dgcpf-main-script',
        DGCPF_PLUGIN_URL . 'assets/js/main.js',
        ['jquery', 'elementor-frontend', 'flickity-js'], // Dependencies
        DGCPF_VERSION,
        true // Load in footer
    );

    // Pass data from PHP to JavaScript (like the AJAX URL and security nonce)
    wp_localize_script(
        'dgcpf-main-script',
        'ahh_maa_filter_params', // Keeping original object name to avoid JS errors
        [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('product_filter_nonce'),
            'page_id' => get_the_ID(),
        ]
    );
}
// We hook into 'wp_enqueue_scripts' to load our assets on the front end.
add_action('wp_enqueue_scripts', 'dgcpf_enqueue_assets');

/**
 * Initialize classes.
 */
new DGCPF_Shortcodes();
new DGCPF_Ajax();

/**
 * Activation hook to run on plugin activation.
 */
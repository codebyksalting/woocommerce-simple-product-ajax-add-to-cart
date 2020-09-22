<?php
/**
 * Plugin Name:       Woocommerce Simple Product AJAX Add-to-cart
 * Plugin URI:        https://github.com/codebyksalting/woocommerce-simple-product-ajax-add-to-cart
 * Description:       Enables AJAX Add-to-cart on the product details page. Also includes a configurable popup confirmation.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            KSalting
 * Author URI:        https://github.com/codebyksalting
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       woocommerce-simple-product-ajax-add-to-cart
 **/

defined( 'ABSPATH' ) or die( 'You are not allowed to access this resource.' );

/**
 * @desc Create the plugin settings management page
 * @return none
 */
add_action('admin_menu', 'simple_ajax_add_to_cart_create_menu');
function simple_ajax_add_to_cart_create_menu() {
	// Add the options page to the Settings menu
	add_options_page('Simple Product Ajax Add-to-Cart Settings', 'Ajax Add-to-Cart', 'manage_options', 'simple-ajax-add-to-cart-settings-page' , 'simple_ajax_add_to_cart_settings_page' );
	// Register the settings
	add_action( 'admin_init', 'simple_ajax_add_to_cart_settings' );
}

function simple_ajax_add_to_cart_settings() {
	//register our settings
	register_setting( 'simple-ajax-cart-settings-group', 'simple_ajax_popup_position' );
	register_setting( 'simple-ajax-cart-settings-group', 'simple_ajax_popup_style' );
	register_setting( 'simple-ajax-cart-settings-group', 'simple_ajax_popup_duration' );
}

function simple_ajax_add_to_cart_settings_page() {
?>
<div class="wrap">
<h1>Woocommerce Product Page Simple Ajax Add-to-Cart</h1>
<form method="post" action="options.php">
    <?php settings_fields( 'simple-ajax-cart-settings-group' ); ?>
    <?php do_settings_sections( 'simple-ajax-cart-settings-group' ); ?>
    <table class="form-table">

        <tr valign="top">
            <th scope="row">Popup Position</th>
            <td>
                <select name="simple_ajax_popup_position">
                    <option value="top-right"<?php echo ( (esc_attr( get_option('simple_ajax_popup_position') ) == 'top-right') ? ' selected="selected"' : '' ); ?>>Top, Right</option>
                    <option value="top-left"<?php echo ( (esc_attr( get_option('simple_ajax_popup_position') ) == 'top-left') ? ' selected="selected"' : '' ); ?>>Top, Left</option>
                    <option value="bottom-right"<?php echo ( (esc_attr( get_option('simple_ajax_popup_position') ) == 'bottom-right') ? ' selected="selected"' : '' ); ?>>Bottom, Right</option>
                    <option value="bottom-left"<?php echo ( (esc_attr( get_option('simple_ajax_popup_position') ) == 'bottom-left') ? ' selected="selected"' : '' ); ?>>Bottom, Left</option>
                </select>
                <em>Default: Top, Right</em>
            </td>
        </tr>
         
        <tr valign="top">
            <th scope="row">Popup Style</th>
            <td>
                <select name="simple_ajax_popup_style">
                    <option value="dark-mode"<?php echo ( (esc_attr( get_option('simple_ajax_popup_style') ) == 'dark-mode') ? ' selected="selected"' : '' ); ?>>Dark Mode</option>
                    <option value="light-mode"<?php echo ( (esc_attr( get_option('simple_ajax_popup_style') ) == 'light-mode') ? ' selected="selected"' : '' ); ?>>Light Mode</option>
                </select>
                <em>Default: Dark Mode</em>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">Popup Duration</th>
            <td>
                <input type="number" name="simple_ajax_popup_duration" step="1" value="<?php echo ( esc_attr( get_option('simple_ajax_popup_duration') ) !== '' ) ? esc_attr( get_option('simple_ajax_popup_duration') ) : '5'; ?>">
                <em>Default: 5 seconds</em>
            </td>
        </tr>

    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php }

/**
 * @desc checks whether the current product is a simple or variable product
 * @return bool - true or false
 */
function checkProductType(){
    global $post;

    $current_product = wc_get_product( $post->ID );

    if (function_exists('is_product') && is_product()) {
        if ($current_product->get_type() === 'simple' || $current_product->get_type() === 'variable') {
            return true;
        }
        return false;
    }
    
    return false;
}

/**
 * @desc adds the styles and javascript (including plugin options) on the front-end
 * @return none
 */
function cbks_woo_simple_ajax_add_to_cart_resources() { 

    if (checkProductType()) { 
        wp_enqueue_style('wsaatc_css', plugins_url( 'css/styles.css', __FILE__ ));
        wp_enqueue_script('wsaatc_js', plugins_url( 'js/main.js', __FILE__ ), array(), '1.0', true ); 
        $plugin_options  = 'popup_position = '. json_encode( esc_attr( get_option('simple_ajax_popup_position') ) ) .'; ';
        $plugin_options .= 'popup_style = '. json_encode( esc_attr( get_option('simple_ajax_popup_style') ) ) .'; ';
        $plugin_options .= 'popup_duration = '. (esc_attr( get_option('simple_ajax_popup_duration') ) * 1000) .'; ';
        wp_add_inline_script('wsaatc_js', $plugin_options, 'before');
    }

}
add_action('wp_enqueue_scripts', 'cbks_woo_simple_ajax_add_to_cart_resources');

/**
 * @desc adds the notice container on the footer
 * @return none
 */
function add_notice_container() { 

    if (checkProductType()) { 
        global $product;
        echo '<div id="woo_ajax_notice">' . 
            'The product has been successfully added to the cart.' .
            '<div class="btn-row-notices">' .
            '<a class="btn-notice btn-continue" href="#">Continue</a>' .
            '<a class="btn-notice btn-cart" href="' . wc_get_cart_url() . '">View Cart</a>' .
            '</div>' .
            '</div>'; 
    }

}
add_action('wp_footer', 'add_notice_container');

// Ajax function
add_action('wp_ajax_codebyksalting_woo_simple_ajax', 'codebyksalting_woo_simple_ajax'); 
add_action('wp_ajax_nopriv_codebyksalting_woo_simple_ajax', 'codebyksalting_woo_simple_ajax');          
function codebyksalting_woo_simple_ajax() {  

    $product_id = apply_filters('codebyksalting_woo_simple_ajax_product_id', absint($_POST['product_id'])); 
    $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']); 
    $variation_id = absint($_POST['variation_id']); 
    $passed_validation = apply_filters('codebyksalting_woo_simple_ajax_validation', true, $product_id, $quantity); 
    $product_status = get_post_status($product_id); 

    if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id) && 'publish' === $product_status) { 

        do_action('codebyksalting_woo_simple_ajax', $product_id); 

            if ('yes' === get_option('codebyksalting_woo_simple_ajax_redirect_after_add')) { 
                wc_add_to_cart_message(array($product_id => $quantity), true); 
            } 

            WC_AJAX :: get_refreshed_fragments(); 
            } else { 
                $data = array( 
                    'error' => true, 
                    'product_url' => apply_filters('codebyksalting_woo_simple_ajax_redirect_after_error', get_permalink($product_id), $product_id)); 
                echo wp_send_json($data); 
            } 

    wp_die(); 

}
        

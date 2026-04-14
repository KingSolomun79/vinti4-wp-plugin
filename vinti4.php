<?php
/*
Plugin Name: vinti4
Plugin URI: http://vinti4.cv/
Description: Payment plugin to implement payment process of vinti4 and visa card on your wordpress website.
Version: 1.0.16
Author: Rede vinti4
Author URI: https://www.vinti4.cv/
*/

//require_once('../../../wp-config.php');
//global $wpdb;
//define("WP_DEBUG", true);

// PLUGIN VINT4
class vinti4Plugin
{
	function __construct()
	{
		// INIT
		add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );
		add_action( 'admin_init', array( $this, 'setupSettings' ) );
		add_action( 'admin_init', array( $this, 'setup_fields' ) );
		
	}

	public static function activate()
	{

	    $my_post2 = array(
	      'post_title'    => wp_strip_all_tags('Checkout com sucesso'),

	      'post_content'  => 'Pagamento efetuado com sucesso :)',

	      'post_status'   => 'publish',
	      'post_author'   => 1,
	      'post_type'     => 'page',
	    );

	    $my_post3 = array(
	      'post_title'    => wp_strip_all_tags('Checkout sem sucesso'),

	      'post_content'  => 'Pagamento efetuado sem sucesso :(',

	      'post_status'   => 'publish',
	      'post_author'   => 1,
	      'post_type'     => 'page',
	    );

	    // Insert the post into the database
	    wp_insert_post( $my_post );
	    wp_insert_post( $my_post2 );
	    wp_insert_post( $my_post3 );
	}

	public static function deactivate()
	{ 
		global $wpdb;
    	$wpdb->query("DELETE FROM wp_posts WHERE post_title LIKE '%vinti4%'");
    	$wpdb->query("DELETE FROM wp_posts WHERE post_content LIKE '%Pagamento efetuado com sucesso%'");
    	$wpdb->query("DELETE FROM wp_posts WHERE post_content LIKE '%Pagamento efetuado sem sucesso%'");

    	delete_option( 'pos_id' );
    	delete_option( 'pos_auth_code' );
    	delete_option( 'vbv2_url' );
    	
	}

	public static function unninstall()
	{
		
	}	

	public function create_plugin_settings_page() {

	    $page_title = 'vinti4';
	    $menu_title = 'vinti4';
	    $capability = 'manage_options';
	    $slug = 'vinti4_fields';
	    $callback = array( $this, 'settingsPage' );
	    $icon = 'dashicons-cart';
	    $position = 100;

	    add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
	}

	public function settingsPage()
	{			    
		include("admin/view.php");

		if(!empty($_POST['updated']) && $_POST['updated'] === 'true')
	        $this->handle_form();
	}

	public function setupSettings()
	{
		add_settings_section( 'pos_section', 'POS', array( $this, 'section_callback' ), 'vinti4_fields' );
	}

	public function section_callback($arguments)
	{    
    	echo '<hr>';
	}

	public function setup_fields()
	{
		$fields = array(
	        array(
	            'uid' => 'pos_id',
	            'label' => 'POS ID',
	            'section' => 'pos_section',
	            'type' => 'text',
	            'options' => false,
	            'placeholder' => '',
	            'helper' => 'Identificador do POS Web',
	            'supplemental' => 'Essa informação é disponibilizada pela SISP',
	            'default' => ''
	        ),
	        array(
	            'uid' => 'pos_auth_code',
	            'label' => 'POS AUTH CODE',
	            'section' => 'pos_section',
	            'type' => 'text',
	            'options' => false,
	            'placeholder' => '',
	            'helper' => 'Código de Autenticação do POS Web',
	            'supplemental' => 'Essa informação é disponibilizada pela SISP',
	            'default' => ''
	        ),
	        array(
	            'uid' => 'vbv2_url',
	            'label' => 'VBV2 URL',
	            'section' => 'pos_section',
	            'type' => 'text',
	            'options' => false,
	            'placeholder' => '',
	            'helper' => 'URL do Request',
	            'supplemental' => 'Essa informação é disponibilizada pela SISP (O url de teste e produção são diferentes)',
	            'default' => ''
	        ),
	    );

	    foreach( $fields as $field ){
	        add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'vinti4_fields', $field['section'], $field );
	        register_setting( 'pos_section', $field['uid'] );
	    }

	}

	public function field_callback( $arguments )
	{
		$value = get_option( $arguments['uid'] );

		printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );

		if( $helper = $arguments['helper'] )
	        printf( '<span class="helper"> %s</span>', $helper );
	    
	    if( $supplimental = $arguments['supplemental'] )
        	printf( '<p class="description">%s</p>', $supplimental );
    
	}

	public function handle_form()
	{
		if(
	        ! isset( $_POST['vinti4_form'] ) ||
	        ! wp_verify_nonce( $_POST['vinti4_form'], 'vinti4_update' )
	    ){ ?>
	        <div class="error">
	           <p>Sorry, your nonce was not correct. Please try again.</p>
	        </div> <?php
	        exit;
	    }

	    $pos_id = sanitize_text_field( $_POST['pos_id'] );
	    $pos_auth_code = sanitize_text_field( $_POST['pos_auth_code'] );
	    $vbv2_url = sanitize_text_field( $_POST['vbv2_url'] );

	    if(is_numeric($pos_id) && $pos_id > 0 && !empty($pos_auth_code) && !empty($vbv2_url))
	    {
	    	update_option( 'pos_id', $pos_id );
	    	update_option( 'pos_auth_code', $pos_auth_code );
	    	update_option( 'vbv2_url', $vbv2_url );
			?>
			<br>
		    <div class="updated">
		        <p>Informações atualizadas com sucesso!</p>
		    </div>
	    	<?php
	    }
	    else
	    {
			?>
			<br>
		    <div class="error">
		        <p>Informações inválidas!</p>
		        <p>*Por favor preencher todos os campos!</p>
		    </div>
	    	<?php
	    }
	    
	}
	
}

// GATEWAY PAGAMENTO VINTI4
function wc_offline_gateway_init() {

	if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

    class WC_Gateway_vinti4 extends WC_Payment_Gateway {

		public function __construct()
		{
			$this->id = 2424;
			$this->icon = '../wp-content/plugins/vinti4/logo_vbv.png';
			$this->has_fields = true;
			$this->method_title = "vinti4 GateWay";
			$this->method_description = "Gateway de pagamento vinti4 que permite integrar o pagamento no WooCommerce utilizando cartões vinti4 e visa";

			$this->init_form_fields();
			$this->init_settings();
			//add_action( 'woocommerce_api_wc_gateway_paypal', array( $this, 'check_ipn_response' ) );
		}

	    public function init_form_fields()
	    {
	    	$this->form_fields = apply_filters( 'wc_offline_form_fields', array(
	  
		        'enabled' => array(
		            'title'   => __( 'Enable/Disable', 'wc-gateway-offline' ),
		            'type'    => 'checkbox',
		            'label'   => __( 'Enable Offline Payment', 'wc-gateway-offline' ),
		            'default' => 'yes'
		        ),

		        'title' => array(
		            'title'       => __( 'Title', 'wc-gateway-offline' ),
		            'type'        => 'text',
		            'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-offline' ),
		            'default'     => __( 'Offline Payment', 'wc-gateway-offline' ),
		            'desc_tip'    => true,
		        ),

		        'description' => array(
		            'title'       => __( 'Description', 'wc-gateway-offline' ),
		            'type'        => 'textarea',
		            'description' => __( 'Payment method description that the customer will see on your checkout.', 'wc-gateway-offline' ),
		            'default'     => __( 'Please remit payment to Store Name upon pickup or delivery.', 'wc-gateway-offline' ),
		            'desc_tip'    => true,
		        ),

		        'instructions' => array(
		            'title'       => __( 'Instructions', 'wc-gateway-offline' ),
		            'type'        => 'textarea',
		            'description' => __( 'Instructions that will be added to the thank you page and emails.', 'wc-gateway-offline' ),
		            'default'     => '',
		            'desc_tip'    => true,
		        ),
		    ) );
	    }

	    public function init_settings()
	    {
	    	$this->title = "Pagamento com cartão vinti4, Visa, Mastercard ou American Express";
	    	add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	    }

	    public function process_payment( $order_id ) {
		    global $woocommerce;

		    return [
	            'result'   => 'success',
	            'redirect' => plugins_url('', __FILE__ ) . '/api/postback.php?order_id=' . $order_id
	        ]; 
		}


		public function end_process_payment( $order_id ) {
		    global $woocommerce;
		    $order = new WC_Order( $order_id );

		    // Mark as on-hold (we're awaiting the cheque)
		    $order->update_status('on-hold', __( 'Awaiting cheque payment', 'woocommerce' ));

		    // Reduce stock levels
		    $order->reduce_order_stock();

		    // Remove cart
		    $woocommerce->cart->empty_cart();

		    // Return thankyou redirect
		    return array(
		        'result' => 'success',
		        'redirect' => $this->get_return_url( $order )
		    );
		}
	}

	new WC_Gateway_vinti4();
}

function add_your_gateway_class( $methods ) {
    $methods[] = 'WC_Gateway_vinti4'; 
    return $methods;
}

// INICIALIZAÇÃO DAS COISAS
new vinti4Plugin();
add_action('plugins_loaded', 'wc_offline_gateway_init', 11 );
add_filter( 'woocommerce_payment_gateways', 'add_your_gateway_class' );

// ATIVAÇÃO E DESATIVAÇÃO DO PLUGGIN
register_activation_hook( __FILE__, 'vinti4Plugin::activate' );
register_deactivation_hook( __FILE__, 'vinti4Plugin::deactivate' );
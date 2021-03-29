<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 */
class Ppgw_Plugin {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Instance of our settings object.
	 *
	 * @var Ppgw_Settings
	 */
	private $settings;
	
	/**
	 * Instance of our checkout handler.
	 *
	 * @var Ppgw_Checkout
	 */
	private $checkout;
	
	/**
	 * The gateway that handles the payments and the admin setup.
	 *
	 * @var Ppgw_Gateway
	 */
	private $gateway;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PPGW_VERSION' ) ) {
			$this->version = PPGW_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'paddle-woocommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_hook_or_initialize();

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

	}
	
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'paddle-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

	/**
	 * Include files.
	 *
	 * @return void
	 */
	private function load_dependencies() {

	}

	/**
	 * Defines hook or initializes any class.
	 *
	 * @return void
	 */
	public function define_hook_or_initialize() {

		//Admin enqueue script
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action('plugins_loaded', array($this, 'plugins_loaded'));

		add_filter( 'auto_update_plugin', array($this, 'auto_update_this_plugin'), 10, 2 );
	}

	/**
	 * Enable auto update.
	 *
	 * @param mixed $update
	 * @param mixed $item
	 * @return mixed
	 */
	public function auto_update_this_plugin ( $update, $item ) {
		// Array of plugin slugs to always auto-update
		$plugins = array (
			$this->plugin_name
		);
		if ( in_array( $item->slug, $plugins ) ) {
			return true;
		} else {
			return $update;
		}
	}

	/**
	 * Load and initialize everything after WC is loaded.
	 *
	 * @return void
	 */
	public function plugins_loaded() {
		// Don't load extension if WooCommerce is not active
		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			
			include_once __DIR__ . '/class-api.php';
			include_once __DIR__ . '/class-checkout.php';
			include_once __DIR__ . '/class-gateway.php';
			include_once __DIR__ . '/class-settings.php';

			// Register the Paddle gateway with WC
			add_filter('woocommerce_payment_gateways', array($this, 'register_gateway'));

			// Add the checkout scripts and actions, if enabled
			$this->settings = new Ppgw_Settings();
			if($this->settings->get('enabled') == 'yes') {
				
				// Setup checkout object and register intercepts to render page content 
				$this->checkout = new Ppgw_Checkout($this->settings);
				$this->checkout->register_callbacks();
				
			}
			
			// Always setup the gateway as its needed to change admin settings
			$this->gateway = new Ppgw_Gateway($this->settings);
			$this->gateway->register_callbacks();
		}
	}
	
	/**
	 * Callback called during plugin load to setup the Paddle_WC.
	 */
	public function register_gateway($methods) {
		$methods[] = 'Ppgw_Gateway';
		return $methods;
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @return void
	 */
	public function admin_scripts() {
		
	}

}

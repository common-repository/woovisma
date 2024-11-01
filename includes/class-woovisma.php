<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       woovisma.com
 * @since      1.0.0
 *
 * @package    Woovisma
 * @subpackage Woovisma/includes
 */

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
 * @package    Woovisma
 * @subpackage Woovisma/includes
 * @author     WooVisma <info@woovisma.com>
 */
class Woovisma {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woovisma_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

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
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'woovisma';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woovisma_Loader. Orchestrates the hooks of the plugin.
	 * - Woovisma_i18n. Defines internationalization functionality.
	 * - Woovisma_Admin. Defines all hooks for the admin area.
	 * - Woovisma_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woovisma-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woovisma-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woovisma-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woovisma-public.php';

		$this->loader = new Woovisma_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woovisma_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woovisma_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}
        
        public function settings_toplevel_menu()
        {
            //echo "===";
        }

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() { 
                        include WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/config.php";
                        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                        if ( is_plugin_active($required_plugin.'/'.$required_plugin.'.php')  || $required_theme== wp_get_theme()->get('Name'))
                        {
                            $options = get_option( 'woovisma_options' );
                            include_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-module.php");
                            require_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/visma/OAuth2/Client.php");
                            require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/IGrantType.php');
                            require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/AuthorizationCode.php');
                            require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/RefreshToken.php');
                            require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/DSVisma.php');
                            require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaDBLayer.php');
                            require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaClient.php');
                            include_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-product.php");
                            require_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-articlecode.php");
require_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-termsofpayment.php");
require_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-settings.php");
require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-customer.php");
require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-customerr.php");
require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-dscustomer.php");
require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-orderbase.php");
require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-order.php");
require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-orderr.php");
require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-dsorder.php");
require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-orderlineitem.php");
require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-orderlineitemr.php");
require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-dsorderlineitem.php");

		$plugin_admin = new Woovisma_Admin( $this->get_plugin_name(), $this->get_version() );
                            $this->loader->add_action("admin_init", $plugin_admin, "admin_init");
                            $this->loader->add_action( 'admin_menu',$plugin_admin, 'woovisma_main_menu',1 );
                             $this->loader->add_action( 'admin_post_save_woovisma_options',$plugin_admin,'process_page_options' );
                             $this->loader->add_action( 'admin_post_save_woovisma_settings',$plugin_admin,'process_page_settings' );
                             if(isset($_REQUEST["action"]) && $_REQUEST["action"]=="untrash"){}
                             else 
                             {
                                 $this->loader->add_action( 'admin_post_save_woovisma_sync',$plugin_admin,'process_page_sync' );
                                 $this->loader->add_action( 'admin_post_save_woovisma_sync_status',$plugin_admin,'process_page_sync_status' );
                             }
                             $this->loader->add_action( 'wp_ajax_send_support_mail',$plugin_admin, 'woovisma_send_support_mail_callback' ); 
                    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
                    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
                    //$this->loader->add_action( 'woocommerce_checkout_order_processed', $plugin_admin, 'onCheckoutValidation' ,10,2);
                    if(!isset($options["woovismaoptname"]["manualsynconly"]) || $options["woovismaoptname"]["manualsynconly"]>0)
                    {
                        $this->loader->add_action( 'woocommerce_order_status_changed', $plugin_admin, 'onCheckoutChangeStatus' ,10,3);
                    }
                            if(isset($_REQUEST["action"]) && $_REQUEST["action"]=="untrash"){} 
                            else 
                            {
                                $this->loader->add_action( 'save_post', Woovisma_ArticleCode::getInstance(), 'product_taxrate_save' );  
                                if(!isset($options["woovismaoptname"]["manualsynconly"]) || $options["woovismaoptname"]["manualsynconly"]>0)
                                {
                                    $this->loader->add_action( 'save_post', $plugin_admin, 'automatic_sync' );  
                                    $this->loader->add_action( 'onUserSave',$plugin_admin, 'onUserSave');
                                    $this->loader->add_action( 'profile_update',$plugin_admin, 'onUserUpdate',10,2);
                                }
                            }
                            
                            woovisma_addlog("Action loading finished");
                         }
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woovisma_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
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
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woovisma_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
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

}

<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       woovisma.com
 * @since      1.0.0
 *
 * @package    Woovisma
 * @subpackage Woovisma/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woovisma
 * @subpackage Woovisma/public
 * @author     WooVisma <info@woovisma.com>
 */
class Woovisma_Public 
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) 
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        if(isset($_REQUEST["serialized_token"]))
        {
            if (isset($_REQUEST["source"]) && $_REQUEST["source"]=="uniwin")
            {
                header("Location:".admin_url("admin.php?page=woo-visma-settings")."&submit=Authenticate&tab=visma&redirect=1&refresh_token={$_REQUEST["refresh_token"]}&token_type={$_REQUEST["token_type"]}&serialized_token=".urlencode($_REQUEST["serialized_token"])."&authenticate=1");exit;
            }
        }
        else if(isset($_REQUEST["state"]) && $_REQUEST["state"]=="abcduniwin")
        {
            if(isset($_REQUEST["code"]))
            {
                require_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/visma/OAuth2/Client.php");
                require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/IGrantType.php');
                require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/AuthorizationCode.php');
                require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/RefreshToken.php');
                require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/DSVisma.php');
                require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaDBLayer.php');
                require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaClient.php');

                ///visma///
                $client=WooVismaClient::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC,null,true);
                header("Location:".admin_url("admin.php?page=woo-visma-settings")."&authenticate=1&tab=visma");
            }
            else if(isset($_REQUEST["error"]))
            {
                header("Location:".admin_url("admin.php?page=woo-visma-settings")."&authenticate=-1&tab=visma&error=".$_REQUEST["error"]);
            }
            else
            {
                header("Location:".admin_url("admin.php?page=woo-visma-settings")."&authenticate=0&tab=visma");
            }
            exit;
        }       
    }

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woovisma_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woovisma_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woovisma-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woovisma_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woovisma_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woovisma-public.js', array( 'jquery' ), $this->version, false );

	}

}
<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       woovisma.com
 * @since      1.0.0
 *
 * @package    Woovisma
 * @subpackage Woovisma/admin
 */
require_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-utils.php");
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woovisma
 * @subpackage Woovisma/admin
 * @author     WooVisma <info@woovisma.com>
 */
class Woovisma_Admin extends Woovisma_Utils {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}
                    /**
                     * setting menu and options page
                     */
                    public function woovisma_settings_menu()
                    {
                    }
                    
                    
    public function onUserSave($user_id)
    {
        include_once(WP_PLUGIN_DIR."/woovisma/includes/class-woovisma-customer.php");
//trace($postInfo["billing_email"]);
$objUser=get_user_by("id", $user_id);
if(!woovisma_is_user_role_customer($objUser)) return false;
return false;
    }

    public function onUserUpdate($user_id,$old_user_data)
    {
        include_once(WP_PLUGIN_DIR."/woovisma/includes/class-woovisma-customer.php");
//trace($postInfo["billing_email"]);
$objUser=get_user_by("id", $user_id);
if(!woovisma_is_user_role_customer($objUser)) return false;
/*$customer = new WC_Customer();

trace($customer);*/
$obj=new Woovisma_Customer();
$obj->init();
$objVismaCustomer=new DSVismaCustomer();
$objVismaCustomer->Name=$objUser->get("billing_first_name")." ".$objUser->get("billing_last_name");
$objVismaCustomer->InvoiceCity=$objUser->get("billing_city");
$objVismaCustomer->InvoicePostalCode=$objUser->get("billing_postcode");
$objVismaCustomer->InvoiceAddress1=$objUser->get("billing_address_1");
$objVismaCustomer->InvoiceAddress2=$objUser->get("billing_address_2");
$objVismaCustomer->InvoiceCountryCode=$objUser->get("billing_country");
$objVismaCustomer->DeliveryName=$objUser->get("shipping_first_name")." ".$objUser->get("shipping_last_name");
$objVismaCustomer->DeliveryCity=$objUser->get("shipping_city");
$objVismaCustomer->DeliveryPostalCode=$objUser->get("shipping_postcode");
$objVismaCustomer->DeliveryAddress1=$objUser->get("shipping_address_1");
$objVismaCustomer->DeliveryAddress2=$objUser->get("shipping_address_2");
$objVismaCustomer->DeliveryCountryCode=$objUser->get("shipping_country");
$objVismaCustomer->TermsOfPaymentId=$obj->getPaymentTermsFromSettings();
$objVismaCustomer->IsPrivatePerson="true";
$objVismaCustomer->MobilePhone=$objUser->get("billing_phone");
$objVismaCustomer->EmailAddress=$objUser->get("user_email");//trace($objVismaCustomer);
$success=$obj->syncCustomer($objVismaCustomer,$user_id);
if($success==false) 
{
    woovisma_addlog("Updation failed");
}
    }


                    function woovisma_main_menu() {
                        add_menu_page (
        'WooVisma Settings Page',
        'WooVisma',
        'manage_options',
        'woo-visma-settings',
                                array($this,"woovisma_visma_settings")
    );
                        /*add_submenu_page( "woo-visma-sync", "Settings", "Settings", "manage_options", "woo-visma-settings", 
        array($this,'woovisma_visma_settings'),
        plugin_dir_url( __FILE__ ).'icons/my_icon.png',
        '23.56');*/
}
                    
                    public function admin_init()
                    {
                        /*set_time_limit(600);
                        require_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/visma/OAuth2/Client.php");
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/IGrantType.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/AuthorizationCode.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/RefreshToken.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/DSVisma.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaDBLayer.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaClient.php');
                        ///visma
                        $client=WooVismaClient::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
                        ///update the article tax code in woovisma
                        $objArticleCode=Woovisma_ArticleCode::getInstance();
                        $objArticleCode->loadFromVisma($client);
                        $objArticleCode->save(); */
                        add_meta_box("vismaProductRate", "Visma Product Tax Rate", array(Woovisma_ArticleCode::getInstance(),'visma_product_group'), "product", "side", "high" );
                    }
                    
                    public function process_page_sync_status()
                    {
                        trace("===");
                    }
                    
                    public function process_page_sync()
                    {
                        woovisma_addlog("Inside Woovisma_Admin:process_page_sync".print_r($_REQUEST,true));
                        $options = get_option( 'woovisma_options' );
                        //woovisma_addlog($options);
                        if(!isset($options["nossl"]) || $options["nossl"]<1)
                        {
                            if($options["client_id"]=="Your Visma Client ID" || $options["client_secret"]=="Your Visma Client Secret" || $options["redirect_uri"]=="https://your/registered/base/url/")
                            {
                                wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings','tab'=>'manual','configuration' => '1' ),admin_url( 'admin.php' ) ) );exit;
                            }
                        }
                        // Check that user has proper security level
                        if ( !current_user_can( 'manage_options' ) ) wp_die( 'Not allowed' );
                        
                       $moduleURLKey=WOOVISMA_PLUGIN_DIRECTORY."-module";
                        $actionURLKey=WOOVISMA_PLUGIN_DIRECTORY."-action";
                        if(isset($_REQUEST[$moduleURLKey]))
                        {
                            $module=$_REQUEST[$moduleURLKey];
                            $obj= Woovisma_Module::getModule($module) ;
                            if($obj)
                            {
                                $action=$_REQUEST[$actionURLKey];
                                if(method_exists($obj, $action))
                                {
                                    $obj->$action();
                                }
                                else if(method_exists($obj, "render"))
                                {
                                    $obj->render();
                                }
                                else
                                {

                                }
                            }
                        }
                        ///visma///
                        $client=WooVismaClient::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
                        if(isset($_REQUEST["submit"]) && $_REQUEST["submit"]=="Product Sync Visma -> WooCommerce")
                        {
                             woovisma_addlog("process_page_sync:pop product started");
                            $obj=new Woovisma_Product();
                            $obj->init($client);
                            $obj->bulkPopProduct();
                            wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings',"tab"=>"manual",'product_sync_status' => '1' ),admin_url( 'admin.php' ) ) );
                            exit;
                        }
                                                       else if(isset($_REQUEST["submit"]) && $_REQUEST["submit"]=="Customer Sync Visma -> WooCommerce")
                            {
                                                         include_once(WP_PLUGIN_DIR."/woovisma/includes/class-woovisma-customer.php");
                            woovisma_addlog("process_page_sync:pop customer started");
                            $obj=new Woovisma_Customer();
                            $obj->init();
                            $obj->sync_visma_to_woocommerce();
                            wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings',"tab"=>"manual",'product_sync_status' => '2' ),admin_url( 'admin.php' ) ) );
                            exit;
                            }
                               else if(isset($_REQUEST["submit"]) && $_REQUEST["submit"]=="Customer Sync WooCommerce -> Visma" || isset($_REQUEST["wvaction"]) && $_REQUEST["wvaction"]=="customer")
                            {
                                include_once(WP_PLUGIN_DIR."/woovisma/includes/class-woovisma-customer.php");
                                woovisma_addlog("process_page_sync:pop customer started");
                                $obj=new Woovisma_Customer();
                                $obj->init();
                                ///if sync request is coming from the Synced status tab
                                if(isset($_REQUEST["syncid"])) 
                                {
                                    $ret=$obj->sync_woocommerce_to_visma($_REQUEST["syncid"]);
                                    if(is_array($ret) && !empty($ret))
                                    {
                                        wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings',"tab"=>"not_synced",'wvactiontype'=>'0','wvaction'=>'customer','product_sync_status' => '2' ,'not_synced'=>implode(",",$ret)),admin_url( 'admin.php' ) ) );
                                    }
                                    else
                                    {
                                        wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings',"tab"=>"not_synced",'wvactiontype'=>'0','wvaction'=>'customer','product_sync_status' => '2' ),admin_url( 'admin.php' ) ) );
                                    }
                                }
                                else
                                {
                                    $obj->sync_woocommerce_to_visma();
                                    wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings',"tab"=>"manual",'product_sync_status' => '2' ),admin_url( 'admin.php' ) ) );
                                }
                                exit;
                            }
                               else if(isset($_REQUEST["submit"]) && $_REQUEST["submit"]=="Order Sync Visma -> WooCommerce")
                            {
                                include_once(WP_PLUGIN_DIR."/woovisma/includes/class-woovisma-order.php");
                            woovisma_addlog("process_page_sync:pop order started");
                            $obj=new Woovisma_Order();
                            $obj->init(); 
                            $count=$obj->sync_visma_to_woocommerce();
                            if($count>0)
                            {
                                wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings',"tab"=>"manual",'product_sync_status' => '3' ),admin_url( 'admin.php' ) ) );
                            }
                            else
                            {
                                wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings',"tab"=>"manual",'product_sync_status' => '-3' ),admin_url( 'admin.php' ) ) );
                            }
                            exit;
                            }
                            else if(isset($_REQUEST["submit"]) && $_REQUEST["submit"]=="Order Sync WooCommerce -> Visma" || isset($_REQUEST["wvaction"]) && $_REQUEST["wvaction"]=="order")
                            {
                                
                            include_once(WP_PLUGIN_DIR."/woovisma/includes/class-woovisma-order.php");
                            woovisma_addlog("process_page_sync:pop customer started");
                            
                            $options = get_option( 'woovisma_options' );
                            if(isset($options["woovismaoptname"]["oncheckout"]) && $options["woovismaoptname"]["oncheckout"]=="invoice")
                            {
                                $obj=Woovisma_Module::getModule("invoice");
                                $obj->init();
                            }
                            else
                            {
                                $obj=new Woovisma_Order();
                                $obj->init();
                            }
                            ///if sync request is coming from the Synced status tab
                            if(isset($_REQUEST["syncid"]))
                            {
                                $ret=$obj->sync_woocommerce_to_visma($_REQUEST["syncid"]);
                                if(is_array($ret) && !empty($ret))
                                {
                                    wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings',"tab"=>"not_synced",'wvactiontype'=>'0','wvaction'=>'order','product_sync_status' => '3' ,'not_synced'=>implode(",",$ret)),admin_url( 'admin.php' ) ) );
                                }
                                else
                                {
                                    wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings',"tab"=>"not_synced",'wvactiontype'=>'0','wvaction'=>'order','product_sync_status' => '3' ),admin_url( 'admin.php' ) ) );
                                }
                            }
                            else
                            {
                                $ret=$obj->sync_woocommerce_to_visma();
                                wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings',"tab"=>"manual",'product_sync_status' => '3' ),admin_url( 'admin.php' ) ) );
                            }
                            exit;
                        
                            }
                        else
                        {
                                woovisma_addlog("process_page_sync:push product started");
                                $obj=new Woovisma_Product();
                                $obj->init($client);
                                ///if sync request is coming from the Synced status tab
                                if(isset($_REQUEST["syncid"]))
                                {
                                    $obj->bulkPushProduct($_REQUEST["syncid"]);
                                    wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings',"tab"=>"not_synced",'wvactiontype'=>'0','wvaction'=>'product','product_sync_status' => '1' ),admin_url( 'admin.php' ) ) );
                                }
                                else
                                {
                                    $obj->bulkPushProduct();
                                    wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings',"tab"=>"manual",'product_sync_status' => '1' ),admin_url( 'admin.php' ) ) );
                                }
                            exit;
                        }
                    }
                    /**
                     * for automatic sync
                     * @param type $post
                     * @return type
                     */
                    public function automatic_sync($post)
                    {
                        ///when manual sync from woovisma to woocommerce triggerd, return without processing.
                        if(isset($_REQUEST["action"]) && $_REQUEST["action"]=="save_woovisma_sync") return;
                        if(isset($_REQUEST["action"]) && $_REQUEST["action"]=="save_woovisma_sync_status") return;
                        $objPost=get_post($post);
                        woovisma_addlog($objPost);  
                        $arrAutomaticSyncPost=getConfigVal("arrAutomaticSyncPost");
                        if (!in_array($objPost->post_type, $arrAutomaticSyncPost) || $objPost->post_status != 'publish') 
                        {
                            woovisma_addlog($objPost->post_status);  
                            return;
                        }
                        woovisma_addlog("Inside process_page_sync");
                        // Check that user has proper security level
                        if ( !current_user_can( 'manage_options' ) ) wp_die( 'Not allowed' );
                        require_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/visma/OAuth2/Client.php");
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/IGrantType.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/AuthorizationCode.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/RefreshToken.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/DSVisma.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaDBLayer.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaClient.php');

                        require_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-product.php");

                        ///visma///
                        $client=WooVismaClient::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
                            $obj=new Woovisma_Product(); 
                            $obj->init($client);
                            $obj->automaticSync($post);
                        }
                    public function process_page_settings()
                    {
                        woovisma_addlog("Inside process_page_settings");
                        // Check that user has proper security level
                        if ( !current_user_can( 'manage_options' ) ) wp_die( 'Not allowed' );
                        $options = get_option( 'woovisma_options' );
                        $moduleURLKey=WOOVISMA_PLUGIN_DIRECTORY."-module";
                        $actionURLKey=WOOVISMA_PLUGIN_DIRECTORY."-action";
                        if(isset($_REQUEST[$moduleURLKey]))
                        {
                            $module=$_REQUEST[$moduleURLKey];
                            $obj= Woovisma_Module::getModule($module);
                            if($obj)
                            {
                                $obj->init();
                                $action=$_REQUEST[$actionURLKey];
                                if(method_exists($obj, $action))
                                {
                                    $obj->$action();
                                }
                                else if(method_exists($obj, "render"))
                                {
                                    $obj->render();
                                }
                                else
                                {

                                }
                            }
                            $message= method_exists($obj, "getMessage")? $obj->getMessage():"";
                            wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings','tab'=>$_REQUEST["tab"],'message' => $message ),admin_url( 'admin.php' ) ) );
                            exit; 
                        }
                        else if(isset($_REQUEST["settings_submit"]) && ($_REQUEST["settings_submit"]=="Save"))
                        {
                            woovisma_addlog("URL param submit has value Save");
                            // Retrieve original plugin options array
                            // Cycle through all text form fields and store their values
                            // in the options array
                            if ( isset( $_POST["productGroup"] ) )
                            {
                                // Store updated options array to database
                                $options["product-group"] = $_POST["productGroup"];
                            }
                            $storedPrefix="";
                            if ( isset( $_POST["woovismaoptname"] ) && !empty($_POST["woovismaoptname"]))
                            {
                                $storedPrefix=isset($options["woovismaoptname"]["product_prefix"])?$options["woovismaoptname"]["product_prefix"]:"";
                                // Store updated options array to database
                                foreach($_POST["woovismaoptname"] as $pkey=>$pval)
                                {
                                    $options["woovismaoptname"][$pkey] = $pval;
                                }
                            }
                            if(isset($_POST["license_key"]))
                            {
                                // Store license to database
                                $options["license_key"] = $_POST["license_key"];
                            }
                            update_option( 'woovisma_options', $options );
                            if ( isset( $_POST["woovismaoptname"] ) )
                            {
                                if($_POST["woovismaoptname"]["product_prefix"]!=$storedPrefix)
                                {
                                    $obj=new Woovisma_Product(); 
                                    $client=WooVismaClient::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
                                    $obj->init($client);
                                    $obj->onProductPrefixChange($storedPrefix);
                                }
                            }
                        }
                        // Redirect the page to the configuration form that was
                        wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings','tab'=>$_POST["tab"],'message' => '2' ),admin_url( 'admin.php' ) ) );
                        exit;
                    }
                    public function process_page_options()
                    {
                        woovisma_addlog("Inside process_page_option");
                        // Check that user has proper security level
                        if ( !current_user_can( 'manage_options' ) ) wp_die( 'Not allowed' );
                        $options = get_option( 'woovisma_options' );
                        if(!isset($_REQUEST["serialized_token"]))
                        {
                            if(isset($_REQUEST["submit"]) && ($_REQUEST["submit"]=="Save"  || $_REQUEST["submit"]=="Test"))
                            {
                                woovisma_addlog("URL param submit has value either Submit  or Test");
                                // Retrieve original plugin options array
                                // Cycle through all text form fields and store their values
                                // in the options array
                                foreach ( array( 'client_id',"client_secret" ,"redirect_uri","nossl") as $option_name )
                                {
                                    if ( isset( $_POST[$option_name] ) )
                                    {
                                        if($option_name=="nossl")
                                        {
                                            if(!isset($options["nossl"]))
                                            { 
                                                $options["visma_token_expire"]=0;
                                            }
                                            else if($_POST[$option_name] !=$options["nossl"])
                                            {
                                                $options["visma_token_expire"]=0;
                                            }
                                        }
                                        $options[$option_name] = sanitize_text_field( $_POST[$option_name] );//print_r($options);exit;
                                    }
                                }
                                // Store updated options array to database
                                update_option( 'woovisma_options', $options );
                            }
                        }
                        if(isset($_REQUEST["submit"]) && $_REQUEST["submit"]=="Test")
                        {
                            $valid=0;
                            if(is_license_key_valid() == "Active")
                            {
                                $valid=$valid+10;
                            }
                            if(isset($options["visma_access_token"]) && !empty($options["visma_access_token"]))
                            {
                                $valid=$valid+1;
                            }
                            wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings','tab'=>'visma','test' =>$valid ),admin_url( 'admin.php' ) ) );
                        }
                        else if(isset($_REQUEST["submit"]) && $_REQUEST["submit"]=="Authenticate")
                        {
                            woovisma_addlog("URL param submit has value Test");
                            require_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/visma/OAuth2/Client.php");
                            require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/IGrantType.php');
                            require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/AuthorizationCode.php');
                            require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/RefreshToken.php');
                            require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/DSVisma.php');
                            require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaDBLayer.php');
                            require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaClient.php');
                            ///visma
                            $client=WooVismaClient::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
                            ///update the article tax code in woovisma
                            $objArticleCode=Woovisma_ArticleCode::getInstance();
                            $objArticleCode->loadFromVisma($client);
                            $objArticleCode->save();
                            if(isset($_REQUEST["serialized_token"])) return;
                            // Redirect the page to the configuration form that was
                            wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings','tab'=>'visma','authenticate' => '1' ),admin_url( 'admin.php' ) ) );
                        }
                        else
                        {
                            // Redirect the page to the configuration form that was
                            wp_redirect( add_query_arg(array( 'page' => 'woo-visma-settings','tab'=>'visma','message' => '1' ),admin_url( 'admin.php' ) ) );
                        }
                        exit;
                    }
function onCheckoutChangeStatus($orderID,$oldStatus,$newStatus)
{
    $objOrder=new WC_Order($orderID);
    $postInfo=array();
    $postInfo["billing_email"]=$objOrder->billing_email;
    $this->onCheckoutValidation($orderID, $postInfo);
}
function onCheckoutValidation($orderID,$postInfo)
{
    woovisma_addlog("on checking out, order started to push to visma");
        include_once(WP_PLUGIN_DIR."/woovisma/includes/class-woovisma-customer.php");
        //trace($postInfo["billing_email"]);
        /*$objUser=get_user_by("email", $postInfo["billing_email"]);
        if(empty($objUser)) return false;
        if(!woovisma_is_user_role_customer($objUser)) return false;*/
        /*$customer = new WC_Customer();
        
        trace($customer);*/
        $options = get_option( 'woovisma_options' );
        if(isset($options["woovismaoptname"]["oncheckout"]) && $options["woovismaoptname"]["oncheckout"]=="invoice")
        {
            $obj=Woovisma_Module::getModule("invoice");
            $obj->init();
            $ret=$obj->pushInvoice($orderID);
            woovisma_addlog("invoice pushed");
        }
        else
        {
            $obj=new Woovisma_Order();
            $obj->init();
            $ret=$obj->pushOrder($orderID);
            woovisma_addlog("order pushed");
        }
}
                    function woovisma_visma_settings()
                    {
                        if(isset($_REQUEST["serialized_token"]))
                        {
                            $this->process_page_options();
                        }
                        // Retrieve plugin configuration options from database
                        $options = get_option( 'woovisma_options' );
                        if ( isset( $_GET['configuration'] ) && $_GET['configuration'] == '1' )
                        {
                            echo "<div id='message' class='updated fade'><p><strong>Please Update Proper Visma Settings</strong></p></div>";
                        }
                        else if ( isset( $_GET['message'] ) && $_GET['message'] == '1' )
                        {
                            echo "<div id='message' class='updated fade'><p><strong>Settings Saved</strong></p></div>";
                        }
                        else if ( isset( $_GET['test'] ))
                        {
                            if( $_GET['test']==1)
                            {
                                echo "<div id='message' class='updated fade'><p><strong>Your Integration Works Fine. But license key is invalid</strong></p></div>";
                            }
                            else if( $_GET['test']==11)
                            {
                                echo "<div id='message' class='updated fade'><p><strong>Your Integration Works Fine</strong></p></div>";
                            }
                            else if( $_GET['test']==10)
                            {
                                echo "<div id='message' class='updated fade'><p><strong>Your license key valid. But visma connection not valid</strong></p></div>";
                            }
                            else
                            {
                                echo "<div id='message' class='updated fade'><p><strong>Your Visma authentication or license key not valid</strong></p></div>";
                            }
                        }
                        else if ( isset( $_GET['authenticate'] ))
                        {
                            if($_GET['authenticate'] == '1' )
                            {
                                echo "<div id='message' class='updated fade'><p><strong>Authentication Success</strong></p></div>";
                                if(getConfigVal("onAuthenticationHook")===true)
                                {
                                    $client=WooVismaClient::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
                                    $objArticleCode=Woovisma_ArticleCode::getInstance();
                                    $objArticleCode->loadFromVisma($client);
                                    $objArticleCode->save();
                                }
                            }
                            else if($_GET['authenticate'] == '-1' )
                            {
                                echo "<div id='message' class='updated fade'><p><strong>{$_GET['error']}</strong></p></div>";
                            }
                            else
                            {
                                echo "<div id='message' class='updated fade'><p><strong>Authentication Failed</strong></p></div>";
                            }
                        }
                        echo $this->getSettingsForm($options); 
                        //exit;
                    }
                    
                    public function getSettingsForm($options,$type="https")
                    {
                        $tabPage=$this->getTabPage();
                        if($tabPage!==false)
                        {
                            return $tabPage;
                        }
                        ob_start();
                        if($type=="https")
                        {
                            include(dirname(__DIR__)."/templates/settings.php");
                        }
                        else
                        {
                            include(dirname(__DIR__)."/templates/settings_http.php");
                        }
                        $form=ob_get_clean();
                        return $form; 
                    }

                    function woovisma_send_support_mail_callback() {
woovisma_addlog("------------------------EMail Sending Start----------------------");
                        woovisma_addlog(print_r($_POST,true));
			$message = '<html><body><table rules="all" style="border-color: #91B9F6; width:70%; font-family:Calibri, Arial, sans-serif;" cellpadding="10">';
			if(isset($_POST['supportForm']) && $_POST['supportForm'] ==  "support"){
				$message .= '<tr><td align="right">Type: </td><td align="left" colspan="1"><strong>Support</strong></td></tr>';
			}else{
				$message .= '<tr><td align="right">Type: </td><td align="left" colspan="1"><strong>Installationssupport</strong></td></tr>';
			}
			$message .= '<tr><td align="right">Företag: </td><td align="left">'.$_POST['company'].'</td></tr>';
			$message .= '<tr><td align="right">Namn: </td><td align="left">'.$_POST['name'].'</td></tr>';
			$message .= '<tr><td align="right">Telefon: </td><td align="left">'.$_POST['telephone'].'</td></tr>';
			$message .= '<tr><td align="right">Email: </td><td align="left">'.$_POST['email'].'</td></tr>';
			$message .= '<tr><td align="right">Ärende: </td><td align="left">'.$_POST['subject'].'</td></tr>';
                            $options = get_option( 'woovisma_options' );
                            $message .= '<tr><td align="right">Client ID: </td><td align="left">'.(isset($options['client_id'])?$options['client_id']:"").'</td></tr>';
                            $message .= '<tr><td align="right">Client Secret: </td><td align="left">'.(isset($options['client_secret'])?$options['client_secret']:"").'</td></tr>';
                            $message .= '<tr><td align="right">Redirect URI: </td><td align="left">'.(isset($options['redirect_uri'])?$options['redirect_uri']:"").'</td></tr>';	/*
			if(isset($_POST['supportForm']) && $_POST['supportForm'] ==  "support"){
				$options = get_option('woocommerce_economic_general_settings');
				//echo array_key_exists('activate-oldordersync', $options)? 'key exist' : 'key doesnt exist';
				$order_options = get_option('woocommerce_economic_order_settings');
				$message .= '<tr><td align="right" colspan="1"><strong>Allmänna inställningar</strong></td></tr>';
				if(array_key_exists('token', $options)){
					$message .= '<tr><td align="right">Token ID: </td><td align="left">'.$options['token'].'</td></tr>';
				}
				if(array_key_exists('license-key', $options)){
					$message .= '<tr><td align="right">License Nyckel: </td><td align="left">'.$options['license-key'].'</td></tr>';
				}
				if(array_key_exists('other-checkout', $options)){
					$message .= '<tr><td align="right">Other checkout: </td><td align="left">'.$options['other-checkout'].'</td></tr>';
				}
				if(array_key_exists('economic-checkout', $options)){
					$message .= '<tr><td align="right">e-conomic checkout: </td><td align="left">'.$options['economic-checkout'].'</td></tr>';
				}				
				if(array_key_exists('activate-oldordersync', $options)){
					$message .= '<tr><td align="right">Activate old orders sync: </td><td align="left">'.$options['activate-oldordersync'].'</td></tr>';
				}
				if(array_key_exists('product-sync', $options)){
					$message .= '<tr><td align="right">Activate product sync: </td><td align="left">'.$options['product-sync'].'</td></tr>';
				}
				if(array_key_exists('scheduled-product-sync', $options)){
					$message .= '<tr><td align="right">Run scheduled product stock sync: </td><td align="left">'.$options['scheduled-product-sync'].'</td></tr>';
				}
				if(array_key_exists('product-group', $options)){
					$message .= '<tr><td align="right">Product group: </td><td align="left">'.$options['product-group'].'</td></tr>';
				}
				if(array_key_exists('product-prefix', $options)){
					$message .= '<tr><td align="right">Product prefix: </td><td align="left">'.$options['product-prefix'].'</td></tr>';
				}
				if(array_key_exists('customer-group', $options)){
					$message .= '<tr><td align="right">Customer group: </td><td align="left">'.$options['customer-group'].'</td></tr>';
				}
				if(array_key_exists('shipping-group', $options)){
					$message .= '<tr><td align="right">Shipping group: </td><td align="left">'.$options['shipping-group'].'</td></tr>';
				}
				if(array_key_exists('coupon-group', $options)){
					$message .= '<tr><td align="right">Coupon group: </td><td align="left">'.$options['coupon-group'].'</td></tr>';
				}
				if(array_key_exists('order-reference-prefix', $options)){
					$message .= '<tr><td align="right">Order reference prefix: </td><td align="left">'.$options['order-reference-prefix'].'</td></tr>';
				}
			}
			*/$message .= '</table></html></body>';	
                       woovisma_addlog($message);
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=utf-8 \r\n";
			//$headers .= "From:".get_option('admin_email')."\r\n";
			
            echo wp_mail( 'woovisma@uniwin.se', 'WooVisma Support', $message , $headers) ? "success" : "error";
            woovisma_addlog("------------------------EMail Sending End----------------------");
            die(); // this is required to return a proper result
        }
		
	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woovisma-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woovisma-admin.js', array( 'jquery' ), $this->version, false );

	}

}

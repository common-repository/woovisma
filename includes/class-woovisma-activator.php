<?php
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
/**
 * Fired during plugin activation
 *
 * @link       woovisma.com
 * @since      1.0.0
 *
 * @package    Woovisma
 * @subpackage Woovisma/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Woovisma
 * @subpackage Woovisma/includes
 * @author     WooVisma <info@woovisma.com>
 */
class Woovisma_Activator {

        /**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
        public static function activate() {
            
            self::version050();
            self::version010102();
            self::version01010202();
            self::version01010400();
            self::version01010500();
            $arrInstaller=getModuleInstaller();
            if($arrInstaller)
            {
                foreach($arrInstaller as $installer)
                {
                    include_once $installer;
                }
            }
            woovisma_addlog("Before Set Option");
            self::setOptions();
        }
        
        public static function version050()
        {
            global $wpdb;
            $tbl_name=$wpdb->prefix ."woovisma_product_sync";woovisma_addlog($tbl_name);            
            if($wpdb->get_var("show tables like '$tbl_name'") != $tbl_name) 
            {
                $sql = "CREATE TABLE " . $tbl_name . " (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `article_id` varchar(255) NOT NULL,
                UNIQUE KEY id (id)
                );";
                woovisma_addlog($sql);
                dbDelta($sql);
            } 
            
            $tbl_name=$wpdb->prefix ."woovisma_settings";woovisma_addlog($tbl_name);
            if($wpdb->get_var("show tables like '$tbl_name'") != $tbl_name) 
            {
                $sql = "CREATE TABLE " . $tbl_name . " (
                `data` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
                );";
                woovisma_addlog($sql);
                dbDelta($sql);
            }  
        }
        
        public static function version010102()
        {
            global $wpdb;
            $tbl_name=$wpdb->prefix ."woovisma_articlecode";woovisma_addlog($tbl_name);            
            if($wpdb->get_var("show tables like '$tbl_name'") != $tbl_name) 
            {
                $sql = "CREATE TABLE " . $tbl_name . " (
                `id` varchar(100) NOT NULL,
                `name` varchar(255) NOT NULL,
                `type` varchar(255) NOT NULL,
                `vatrate` varchar(255) NOT NULL,
                `isactive` int(1),
                `vatratepercent` float NOT NULL,
                UNIQUE KEY id (id)
                );";
                woovisma_addlog($sql);
                dbDelta($sql);
            }
        }
        
        public static function version01010202()
        {
            global $wpdb;
            $tbl_name=$wpdb->prefix ."woovisma_product_taxrate";woovisma_addlog($tbl_name);            
            if($wpdb->get_var("show tables like '$tbl_name'") != $tbl_name) 
            {
                $sql = "CREATE TABLE IF NOT EXISTS `{$tbl_name}` (
`id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `tax_id` varchar(255) NOT NULL
);";
                woovisma_addlog($sql);
                dbDelta($sql);
            }
        }
        
        public static function version01010400()
        {
            global $wpdb;
            /*$tbl_name=$wpdb->prefix ."woovisma_settings";woovisma_addlog($tbl_name);
            $sql = "CREATE TABLE " . $tbl_name . " (
            `name` varchar(255) NOT NULL,
            `data` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
            );";
            woovisma_addlog($sql);
            dbDelta($sql);*/
            $tbl_name=$wpdb->prefix ."woovisma_settings";
            $sql="ALTER TABLE {$tbl_name} ADD `name` varchar(255) NOT NULL";
            $wpdb->query($sql);

            $sql="UPDATE {$tbl_name} SET  `name`='woocommerce_product_sync_time'";
            woovisma_addlog($sql);
            $wpdb->query($sql);
            
            $tbl_name=$wpdb->prefix ."woovisma_customer_sync";woovisma_addlog($tbl_name);            
            $sql = "CREATE TABLE " . $tbl_name . " (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `customer_id` int(11) NOT NULL,
            `rcustomer_id` varchar(255) NOT NULL,
            UNIQUE KEY id (id)
            );";
            woovisma_addlog($sql);
            dbDelta($sql);
        }
        
         public static function version01010500()
        {
            global $wpdb;            
            $tbl_name=$wpdb->prefix ."woovisma_order_sync";woovisma_addlog($tbl_name);            
            $sql = "CREATE TABLE " . $tbl_name . " (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `rorder_id` varchar(255) NOT NULL,
            `itemmapping` TEXT,
            UNIQUE KEY id (id)
            );";
            woovisma_addlog($sql);
            dbDelta($sql);
        }

        /**
         * set options for authenticating with visma accounting
         */
        public static function setOptions()
        {
            if ( get_option( 'woovisma_options' ) === false ) 
            {
                $new_options['client_id'] = "Your Visma Client ID";
                $new_options['client_secret'] = "Your Visma Client Secret";
                $new_options['version'] = "1.0";
                $new_options['redirect_uri']="https://your/registered/base/url/";
                add_option( 'woovisma_options', $new_options );
            } 
            else 
            {
                $existing_options = get_option( 'woovisma_options' );
                if ( $existing_options['version'] < 1.0 ) 
                {
                    $existing_options['client_id'] = "UA-000000-0";
                    $existing_options['client_secret'] = "abcdefgh";
                    $existing_options['version'] = "1.0";
                    $new_options['redirect_uri']="https://your/registered/base/url/";
                    update_option( 'woovisma_options', $existing_options );
                }
            }
        }
}
?>

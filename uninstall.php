<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       woovisma.com
 * @since      1.0.0
 *
 * @package    Woovisma
 */ 

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
if(!function_exists("woovisma_addlog"))
{
    function woovisma_addlog($message)
    {
        //$message=print_r($message,true);
        //file_put_contents(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/woovisma.html", "<br />{$message}<br />",FILE_APPEND); 
    }
}
global $wpdb;
woovisma_addlog("DROP TABLE IF EXISTS `{$tbl_name}`");
$tbl_name=$wpdb->prefix ."woovisma_product_sync";
woovisma_addlog("DROP TABLE IF EXISTS `{$tbl_name}`");
$wpdb->query("DROP TABLE IF EXISTS `{$tbl_name}`");
$tbl_name=$wpdb->prefix ."woovisma_settings";
woovisma_addlog("DROP TABLE IF EXISTS `{$tbl_name}`");
$wpdb->query("DROP TABLE IF EXISTS `{$tbl_name}`");
$tbl_name=$wpdb->prefix ."woovisma_articlecode";
woovisma_addlog("DROP TABLE IF EXISTS `{$tbl_name}`");
$wpdb->query("DROP TABLE IF EXISTS `{$tbl_name}`");
$tbl_name=$wpdb->prefix ."woovisma_product_taxrate";
woovisma_addlog("DROP TABLE IF EXISTS `{$tbl_name}`");
$wpdb->query("DROP TABLE IF EXISTS `{$tbl_name}`");
$tbl_name=$wpdb->prefix ."woovisma_customer_sync";
woovisma_addlog("DROP TABLE IF EXISTS `{$tbl_name}`");
$wpdb->query("DROP TABLE IF EXISTS `{$tbl_name}`");
$tbl_name=$wpdb->prefix ."woovisma_order_sync";
$wpdb->query("DROP TABLE IF EXISTS `{$tbl_name}`");
delete_option( 'woovisma_options');  
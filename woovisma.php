<?php

/** 
 === WooVisma ===
Contributors:  Onlineforce
Plugin Name:  WooCommerce Visma integration (WooVisma)
Plugin URI:   www.woovisma.com
Tags:     Visma, WooCommerce Visma integration, Visma integration, Visma eAccounting, eAccounting, WooVisma, e-ekonomi, visma eekonomi, visma e-ekonomi, 
Author URI:   www.uniwin.se
Author:    Uniwin
Requires at least: 3.8
Tested up to:  4.8.1
Stable tag:   1.3.0
Version:   1.3.0
License:   GPLv2 or later
License URI:  http://www.gnu.org/licenses/gpl-2.0.html
 */
include_once(__DIR__."/utils.php");
include_once(__DIR__."/visma/Config/WooVismaConfigLive.php");
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
define("WOOVISMA_PLUGIN_DIRECTORY",basename(__DIR__));
define("WOOVISMA_PLUGIN_ABS_DIRECTORY",__DIR__);
define("WOOVISMA_PLUGIN_UNIX_NAME","woovisma");
define("WOOVISMA_PLUGIN_URL",plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woovisma-activator.php
 */
function activate_woovisma() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woovisma-activator.php';
	Woovisma_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woovisma-deactivator.php
 */
function deactivate_woovisma() {
    woovisma_addlog("REQUEST:".print_r($_REQUEST,true)); 
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woovisma-deactivator.php';
	Woovisma_Deactivator::deactivate();
}


woovisma_addlog("----------------------------------------------------------------Start--------------------------------------------------------------------------------------<br /><br />");
register_activation_hook( __FILE__, 'activate_woovisma' );
register_deactivation_hook( __FILE__, 'deactivate_woovisma' );
register_uninstall_hook( __FILE__, 'uninstall_woovisma' );
$pluginInfo=getWoocommercePluginInfo();
woovisma_addlog("Woocommerce Version: ".(isset($pluginInfo["Version"])?$pluginInfo["Version"]:""));
$pluginInfo=getWoovismaPluginInfo();
woovisma_addlog("Woovisma Version: ".(isset($pluginInfo["Version"])?$pluginInfo["Version"]:""));
woovisma_addlog("Wordpress Version: ".get_bloginfo("version"));
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woovisma.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woovisma() { 
    woovisma_addlog("REQUEST:".print_r($_REQUEST,true)); 
	$plugin = new Woovisma();
	$plugin->run();

}
run_woovisma();
woovisma_addlog("<br /><br />----------------------------------------------------------------End--------------------------------------------------------------------------------------<br /><br />");

?>

<?php
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
global $wpdb;            
$tbl_name=$wpdb->prefix ."woovisma_invoice_sync";woovisma_addlog($tbl_name);            
$sql = "CREATE TABLE " . $tbl_name . " (
`id` int(11) NOT NULL AUTO_INCREMENT,
`invoice_id` int(11) NOT NULL,
`rinvoice_id` varchar(255) NOT NULL,
`itemmapping` TEXT,
UNIQUE KEY id (id)
);";
woovisma_addlog($sql);
dbDelta($sql);
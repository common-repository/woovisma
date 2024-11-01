<?php
class Woovisma_Orderbase
{
    public function __construct()
    {
        
    }
    public function getOrdersNotSynced($page=1)
    {
        global $wpdb;
        $tbl_order_sync=$wpdb->prefix ."woovisma_order_sync";
        $tbl_invoice_sync=$wpdb->prefix ."woovisma_invoice_sync";
        $sql="Select order_id as oid, rorder_id as roid,0 as order_type from {$tbl_order_sync} union Select invoice_id as oid, rinvoice_id as roid,1 as order_type from {$tbl_invoice_sync}";
        $arrObj=$wpdb->get_results($sql);
        $arrSyncRow=array();
        $arrSyncID=array();
        if($arrObj)
        foreach($arrObj as $obj)
        {
            $arrSyncRow[$obj->oid]=$obj;
            $arrSyncID[]=$obj->oid;
        }
        
        $tbl_order_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_order_sync";
        $tbl_invoice_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_invoice_sync";
        $tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";
        $objucSettings=Woovisma_Settings::getInstance();
        $startTime=time();
        $modifiedTime=$objucSettings->getData("woocommerce_order_modified_time","0000-00-00 00:00:00");
        woovisma_addlog("sync based on the last synced date");
        $lastdate=$modifiedTime;
        $coma="'";
        //$lastdate=$coma.$lastdate.$coma;
        $lastdate=$lastdate;

        $lastdate=explode(" ",$lastdate);
        $expDate=explode("-",$lastdate[0]);
        $year=$expDate[0];
        $month=(int)$expDate[1];
        $date=$expDate[2];
        $expTime=explode(":",$lastdate[1]);
        $hour=$expTime[0];
        $min=$expTime[1];
        $sec=$expTime[2];
        if((isset($_REQUEST["wvactiontype"]) && empty($_REQUEST["wvactiontype"])) || !isset($_REQUEST["wvactiontype"]))
        {
            $args = array('post_type' => array('shop_order'),'post_status' => array('wc-completed','wc-on-hold','wc-processing','wc-pending'), 'nopaging' => false, 'posts_per_page' =>10, 'paged'=>$page,  'fields' => 'ids','post__not_in'=>$arrSyncID);
        }
        else
        {
            $args = array('post_type' => array('shop_order'),'post_status' => array('wc-completed','wc-on-hold','wc-processing','wc-pending'), 'nopaging' => false, 'posts_per_page' =>10, 'paged'=>$page,  'fields' => 'ids','post__in'=>$arrSyncID);
        }
        /*$args = array('post_type' => array('order'),'post_status' => array('publish'),'date_query'    => array(
    'column'  => 'post_modified',
    'after' => array('year' => $year,'month' => $month,'day'=>$date,'hour'=>$hour,'minute'=>$min,'second'=>$sec)
), 'nopaging' => true, 'fields' => 'ids');*/
        $arrObjPost = new WP_Query($args);
        $arrSync=array();
        $arrSyncType=array();
        if(($arrObjPost->post_count)>0)
        {
            woovisma_addlog("products exist for sync");
            foreach($arrObjPost->posts as $productID)
            {
                $arrSync[]=$productID;
                $arrSyncType[$productID]=$arrSyncRow[$productID]->order_type;
            }
            woovisma_addlog("End not empty order_ids");
        }
        return array("data"=>$arrSync,"sync_type"=>$arrSyncType,"page"=>$page,"total"=>$arrObjPost->found_posts,"pages"=>$arrObjPost->max_num_pages);
        /*trace($arrSync);
        $arrNotSync=array();
        if(count($order_ids)>0)
        {
            woovisma_addlog("orders exist for sync");
            foreach($order_ids as $orderID)
            {
                if(!isset($arrSync[$orderID]))
                {
                    $arrNotSync[]=$orderID;
                }
            }
            woovisma_addlog("End not empty order_ids");
        }
        woovisma_addlog("end not empty arrRow");
        return $arrNotSync;*/
    }
}
?>
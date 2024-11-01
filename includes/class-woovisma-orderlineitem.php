<?php
class Woovisma_Orderlineitem
{
    private $orderlineitem;
    private $client=null;
    
    protected $arrMessage=array();
    public function __construct()
    {
    }
    public function init()
    {
        include_once(__DIR__."/class-woovisma-orderlineitemr.php");
        $this->client=Woovisma_Orderlineitemr::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
        
    }
    public static function &getInstance()
    {
        static $objOrderlineitem=null;
        if(is_null($objOrderlineitem))
        {
            $objOrderlineitem=new Woovisma_Orderlineitem();
        }
        return $objOrderlineitem;
    }
    public function loadFromVisma($client)
    {
        woovisma_addlog("Orderlineitem".print_r($this->orderlineitem,true));
        $this->orderlineitem=$client->getOrderlineitems();
    }
    public function getOrderlineitems()
    {
        return $this->orderlineitem;
    }
    function sync_visma_to_woocommerce()
    {
        global $wpdb;
        woovisma_addlog("started visma_to_woocommerce");
        $objSettings=Woovisma_Settings::getInstance();
        $startTime=time();
        $modifiedTime=$objSettings->getData("visma_orderlineitem_modified_time","0000-00-00 00:00:00");
        $ret=$this->client->getOrderlineitems($modifiedTime);
        if($ret)
        {
            $count=count($ret);
            $arrFailedOrderlineitem=array();
            $tbl_product_sync=$wpdb->prefix ."woovisma_orderlineitem_sync";
            foreach($ret as $key=>$value)
            {
                $this->popOrderlineitem($value['Id']);
            }
        }
        else
        {
            $count=0;
        }
        ///-100 to adjust the time delay in updating the database
        ///if all orderlineitems updated successfully update the time
        if(empty($arrFailedOrderlineitem))
        {
            $objSettings->setTimeData("visma_orderlineitem_modified_time", date("Y-m-d H:i:s"));
        }
        return $count;
    }
    function automaticSync($post)
    {   woovisma_addlog("inside automaticSync");
        woovisma_addlog("post Id:".$post);
	$objPost=get_post($post);
        woovisma_addlog($objPost);
        if($objPost->post_type == "orderlineitem" || $objPost->post_status == "publish")
        {
            woovisma_addlog($objPost->post_status);  
            $this->syncOrderlineitem($post); 
        }
  
     woovisma_addlog("End Of automaticSync");   
    }
    public function popOrderlineitem($remoteOrderlineitemID)
    {
        global $wpdb;

    }
    
    public function getOrderlineitemsNotSynced()
    {
        global $wpdb;
        $tbl_orderlineitem_sync=$wpdb->prefix ."woovisma_orderlineitem_sync";
        $sql="Select orderlineitem_id, rorderlineitem_id from {$tbl_orderlineitem_sync}";
        $arrObj=$wpdb->get_results($sql);
        $arrSync=array();
        if($arrObj)
        foreach($arrObj as $obj)
        {
            $arrSync[$obj->orderlineitem_id]=$obj->rorderlineitem_id;
        }
        $tbl_orderlineitem_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_orderlineitem_sync";
        $tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";
        $objucSettings=Woovisma_Settings::getInstance();
        $startTime=time();
        $modifiedTime=$objucSettings->getData("woocommerce_orderlineitem_modified_time","0000-00-00 00:00:00");
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
        $args = array('post_type' => array('orderlineitem'),'post_status' => array('publish'),'date_query'    => array(
    'column'  => 'post_modified',
    'after' => array('year' => $year,'month' => $month,'day'=>$date,'hour'=>$hour,'minute'=>$min,'second'=>$sec)
), 'nopaging' => true, 'fields' => 'ids');
        $arrObjPost = new WP_Query($args);
        $orderlineitem_ids=$arrObjPost->posts;
        $arrNotSync=array();
        if(count($orderlineitem_ids)>0)
        {
            woovisma_addlog("orderlineitems exist for sync");
            foreach($orderlineitem_ids as $orderlineitemID)
            {
                if(!isset($arrSync[$orderlineitemID]))
                {
                    $arrNotSync[]=$orderlineitemID;
                }
            }
            woovisma_addlog("End not empty orderlineitem_ids");
        }
        woovisma_addlog("end not empty arrRow");
        return $arrNotSync;
    }
    function syncOrderlineitem($objDSOrderlineitem,$elementID)
    {
        
        if(!$objDSOrderlineitem->isValid()) 
        {
            woovisma_addlog("Invalid/Missing Datas in Orderlineitem datastructure");
            return false;
        }
        global $wpdb;
        $tbl_orderlineitem_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_orderlineitem_sync";
        $tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";
        //$orderlineitem = new WC_Orderlineitem();
        $sql="SELECT rorderlineitem_id FROM {$tbl_orderlineitem_sync} WHERE orderlineitem_id={$elementID}";
        woovisma_addlog("SELECT orderlineitem_id SQL:".$sql);
        $arrOrderlineitem = $wpdb->get_results($sql);
        woovisma_addlog(__FUNCTION__.":orderlineitemID:".print_r($elementID,true));
        if($arrOrderlineitem)
        {
            $objDSOrderlineitem->Id=$arrOrderlineitem[0]->rorderlineitem_id;
        }
        woovisma_addlog("Orderlineitem before sending to remote :".print_r($objDSOrderlineitem,true));
        $ret=$this->client->setOrderlineitem($objDSOrderlineitem);
        if($ret && empty($arrOrderlineitem))
        {
            woovisma_addlog(__FUNCTION__.":ret from setOrderlineitem:".$ret);
            $wpdb->insert($tbl_orderlineitem_sync, array("orderlineitem_id" => $elementID, "rorderlineitem_id" => $ret));
	    woovisma_addlog($wpdb->last_query);
            woovisma_addlog("end empty articleID");
        }
        return $ret;
    }
    public function pushOrderlineitem($orderlineitem_id,$objUser=false)
    {

    }
    public function woo_get_product_sku($id)
    {     
        global $wpdb;
        $sql="SELECT meta_value FROM {$wpdb->prefix}postmeta where meta_key='_sku' AND post_id=$id";
        woovisma_addlog($sql);
        $arrRow=$wpdb->get_results($sql);
        return $arrRow[0]->meta_value;
    }
    function sync_woocommerce_to_visma()
    {
        
        woovisma_addlog("woocommerce to orderlineitem sync started");
        global $wpdb;
        $tbl_orderlineitem_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_orderlineitem_sync";
        $tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";
        $objucSettings=Woovisma_Settings::getInstance();
        $startTime=time();
        $modifiedTime=$objucSettings->getData("woocommerce_orderlineitem_modified_time","0000-00-00 00:00:00");
        
        woovisma_addlog("end not empty arrRow");
        ///-100 to adjust the time delay in updating the database
        $timediff=time()-$startTime+100;
        $objucSettings->setTimeData("woocommerce_orderlineitem_modified_time", -1*$timediff);
        woovisma_addlog("End bulkPushOrderlineitem");
        return true;
    }
}
?>
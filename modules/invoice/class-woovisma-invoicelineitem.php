<?php
class Woovisma_Invoicelineitem
{
    private $invoicelineitem;
    private $client=null;
    
    protected $arrMessage=array();
    public function __construct()
    {
    }
    public function init()
    {
        include_once(__DIR__."/class-woovisma-invoicelineitemr.php");
        $this->client=Woovisma_Invoicelineitemr::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
        
    }
    public static function &getInstance()
    {
        static $objInvoicelineitem=null;
        if(is_null($objInvoicelineitem))
        {
            $objInvoicelineitem=new Woovisma_Invoicelineitem();
        }
        return $objInvoicelineitem;
    }
    public function loadFromVisma($client)
    {
        woovisma_addlog("Invoicelineitem".print_r($this->invoicelineitem,true));
        $this->invoicelineitem=$client->getInvoicelineitems();
    }
    public function getInvoicelineitems()
    {
        return $this->invoicelineitem;
    }
    function sync_visma_to_woocommerce()
    {
        global $wpdb;
        woovisma_addlog("started visma_to_woocommerce");
        $objSettings=Woovisma_Settings::getInstance();
        $startTime=time();
        $modifiedTime=$objSettings->getData("visma_invoicelineitem_modified_time","0000-00-00 00:00:00");
        $ret=$this->client->getInvoicelineitems($modifiedTime);
        if($ret)
        {
            $count=count($ret);
            $arrFailedInvoicelineitem=array();
            $tbl_product_sync=$wpdb->prefix ."woovisma_invoicelineitem_sync";
            foreach($ret as $key=>$value)
            {
                $this->popInvoicelineitem($value['Id']);
            }
        }
        else
        {
            $count=0;
        }
        ///-100 to adjust the time delay in updating the database
        ///if all invoicelineitems updated successfully update the time
        if(empty($arrFailedInvoicelineitem))
        {
            $objSettings->setTimeData("visma_invoicelineitem_modified_time", date("Y-m-d H:i:s"));
        }
        return $count;
    }
    function automaticSync($post)
    {   woovisma_addlog("inside automaticSync");
        woovisma_addlog("post Id:".$post);
	$objPost=get_post($post);
        woovisma_addlog($objPost);
        if($objPost->post_type == "invoicelineitem" || $objPost->post_status == "publish")
        {
            woovisma_addlog($objPost->post_status);  
            $this->syncInvoicelineitem($post); 
        }
  
     woovisma_addlog("End Of automaticSync");   
    }
    public function popInvoicelineitem($remoteInvoicelineitemID)
    {
        global $wpdb;

    }
    
    public function getInvoicelineitemsNotSynced()
    {
        global $wpdb;
        $tbl_invoicelineitem_sync=$wpdb->prefix ."woovisma_invoicelineitem_sync";
        $sql="Select invoicelineitem_id, rinvoicelineitem_id from {$tbl_invoicelineitem_sync}";
        $arrObj=$wpdb->get_results($sql);
        $arrSync=array();
        if($arrObj)
        foreach($arrObj as $obj)
        {
            $arrSync[$obj->invoicelineitem_id]=$obj->rinvoicelineitem_id;
        }
        $tbl_invoicelineitem_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_invoicelineitem_sync";
        $tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";
        $objucSettings=Woovisma_Settings::getInstance();
        $startTime=time();
        $modifiedTime=$objucSettings->getData("woocommerce_invoicelineitem_modified_time","0000-00-00 00:00:00");
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
        $args = array('post_type' => array('invoicelineitem'),'post_status' => array('publish'),'date_query'    => array(
    'column'  => 'post_modified',
    'after' => array('year' => $year,'month' => $month,'day'=>$date,'hour'=>$hour,'minute'=>$min,'second'=>$sec)
), 'nopaging' => true, 'fields' => 'ids');
        $arrObjPost = new WP_Query($args);
        $invoicelineitem_ids=$arrObjPost->posts;
        $arrNotSync=array();
        if(count($invoicelineitem_ids)>0)
        {
            woovisma_addlog("invoicelineitems exist for sync");
            foreach($invoicelineitem_ids as $invoicelineitemID)
            {
                if(!isset($arrSync[$invoicelineitemID]))
                {
                    $arrNotSync[]=$invoicelineitemID;
                }
            }
            woovisma_addlog("End not empty invoicelineitem_ids");
        }
        woovisma_addlog("end not empty arrRow");
        return $arrNotSync;
    }
    function syncInvoicelineitem($objDSInvoicelineitem,$elementID)
    {
        
        if(!$objDSInvoicelineitem->isValid()) 
        {
            woovisma_addlog("Invalid/Missing Datas in Invoicelineitem datastructure");
            return false;
        }
        global $wpdb;
        $tbl_invoicelineitem_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_invoicelineitem_sync";
        $tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";
        //$invoicelineitem = new WC_Invoicelineitem();
        $sql="SELECT rinvoicelineitem_id FROM {$tbl_invoicelineitem_sync} WHERE invoicelineitem_id={$elementID}";
        woovisma_addlog("SELECT invoicelineitem_id SQL:".$sql);
        $arrInvoicelineitem = $wpdb->get_results($sql);
        woovisma_addlog(__FUNCTION__.":invoicelineitemID:".print_r($elementID,true));
        if($arrInvoicelineitem)
        {
            $objDSInvoicelineitem->Id=$arrInvoicelineitem[0]->rinvoicelineitem_id;
        }
        woovisma_addlog("Invoicelineitem before sending to remote :".print_r($objDSInvoicelineitem,true));
        $ret=$this->client->setInvoicelineitem($objDSInvoicelineitem);
        if($ret && empty($arrInvoicelineitem))
        {
            woovisma_addlog(__FUNCTION__.":ret from setInvoicelineitem:".$ret);
            $wpdb->insert($tbl_invoicelineitem_sync, array("invoicelineitem_id" => $elementID, "rinvoicelineitem_id" => $ret));
	    woovisma_addlog($wpdb->last_query);
            woovisma_addlog("end empty articleID");
        }
        return $ret;
    }
    public function pushInvoicelineitem($invoicelineitem_id,$objUser=false)
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
        
        woovisma_addlog("woocommerce to invoicelineitem sync started");
        global $wpdb;
        $tbl_invoicelineitem_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_invoicelineitem_sync";
        $tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";
        $objucSettings=Woovisma_Settings::getInstance();
        $startTime=time();
        $modifiedTime=$objucSettings->getData("woocommerce_invoicelineitem_modified_time","0000-00-00 00:00:00");
        
        woovisma_addlog("end not empty arrRow");
        ///-100 to adjust the time delay in updating the database
        $timediff=time()-$startTime+100;
        $objucSettings->setTimeData("woocommerce_invoicelineitem_modified_time", -1*$timediff);
        woovisma_addlog("End bulkPushInvoicelineitem");
        return true;
    }
}
?>
<?php
class Woovisma_Customer
{
    private $customer;
    private $client=null;
    protected $arrTermsOfPaymentId=array();
    protected $arrMessage=array();
    protected $initialized=false;
    public function __construct()
    {
    }
    public function init()
    {
        if($this->initialized) return;
        include_once(__DIR__."/class-woovisma-customerr.php");
        $this->client=Woovisma_Customerr::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
        
    $objTermsofpayment=Woovisma_Termsofpayment::getInstance();
    $objTermsofpayment->loadFromVisma($this->client);
    $this->arrTermsOfPaymentId=$objTermsofpayment->getTermsofpayment();

    }
    public function paymentOptionsList()
    { 
        include_once(__DIR__."/class-woovisma-customerr.php");
        $this->client=Woovisma_Customerr::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
        $paymentOptions=Woovisma_Termsofpayment::getInstance();
        $paymentOptions->loadFromVisma($this->client);
        $list=$paymentOptions->getTermsofpayment();
        
        return  $list;
    
    }
    public static function &getInstance()
    {
        static $objCustomer=null;
        if(is_null($objCustomer))
        {
            $objCustomer=new Woovisma_Customer();
        }
        return $objCustomer;
    }
    public function loadFromVisma($client)
    {
        woovisma_addlog("Customer".print_r($this->customer,true));
        $this->customer=$client->getCustomers();
    }
    public function getCustomers()
    {
        return $this->customer;
    }
    function sync_visma_to_woocommerce()
    {
        global $wpdb;
        woovisma_addlog("started visma_to_woocommerce");
        $objSettings=Woovisma_Settings::getInstance();
        $startTime=time();
        $modifiedTime=$objSettings->getData("visma_customer_modified_time","0000-00-00 00:00:00");
        $ret=$this->client->getCustomers($modifiedTime);
        if($ret)
        {
            $count=count($ret);
            $arrFailedCustomer=array();
            $tbl_product_sync=$wpdb->prefix ."woovisma_customer_sync";
            $wpdb->query("TRUNCATE TABLE  {$tbl_product_sync}");
            foreach($ret as $key=>$value)
            {
                $this->popCustomer($value['Id']);
            }
        }
        else
        {
            $count=0;
        }
        ///-100 to adjust the time delay in updating the database
        ///if all customers updated successfully update the time
        if(empty($arrFailedCustomer))
        {
            $objSettings->setTimeData("visma_customer_modified_time", date("Y-m-d H:i:s"));
        }
        return $count;
    }
    function automaticSync($post)
    {   print_r($post);exit;
        woovisma_addlog("inside automaticSync");
        woovisma_addlog("post Id:".$post);
        
	$objPost=get_post($post);
        woovisma_addlog($objPost);
        if($objPost->post_type == "customer" || $objPost->post_status == "publish")
        {
            woovisma_addlog($objPost->post_status);  
            $this->syncCustomer($post); 
        }
  
     woovisma_addlog("End Of automaticSync");   
    }
    public function popCustomer($remoteCustomerID)
    {
        global $wpdb;
global $wpdb;
        $tbl_product_sync=$wpdb->prefix ."woovisma_customer_sync";
        $remoteCustomer=$this->client->getCustomer($remoteCustomerID);
        $alreadySynced=false;
        $sql="SELECT customer_id FROM {$tbl_product_sync} WHERE rcustomer_id='{$remoteCustomerID}'";
        woovisma_addlog("SELECT customer_id SQL:".$sql);
        $itemID = $wpdb->get_results($sql);
        $customer_id=false;
        if(isset($itemID[0]))
        {
            $customer_id=$itemID[0]->customer_id;
            $alreadySynced=true;
        }
        woovisma_addlog("customer id is ".$customer_id);
        ///assuming user email not exist before. so new customer
        $objUserByEmail=false;
        if($customer_id===false)
        {
            
           $login=  strtolower($remoteCustomer["Name"]);
            $login=  str_replace(" ", "", $login);
            if(empty($remoteCustomer["EmailAddress"])) return false;
            $objUserByEmail=WP_User::get_data_by("email", $remoteCustomer["EmailAddress"]);
            if($objUserByEmail===false)
            {
                $customer_id=register_new_user($login, $remoteCustomer["EmailAddress"]);
                $user = new WP_User($customer_id);
                $user->add_role('customer');
                $user->remove_role("subscriber");
                if(is_wp_error($customer_id))
                {
                   return false;
                }
            }
            else
            {
                $customer_id=$objUserByEmail->ID;
            }
        }
        //trace($remoteCustomer);
        foreach($remoteCustomer as $vk=>$vv)
        {
            $dbkey=false;
            if($vk=="InvoiceCity") $dbkey="billing_city";
            else if($vk=="InvoicePostalCode") $dbkey="billing_postcode";
            else if($vk=="Name") $dbkey="billing_last_name";
            else if($vk=="InvoiceAddress1") $dbkey="billing_address_1";
            else if($vk=="InvoiceAddress2") $dbkey="billing_address_2";
            else if($vk=="InvoiceCountryCode") $dbkey="billing_country";
            else if($vk=="MobilePhone") $dbkey="billing_phone";
            else if($vk=="EmailAddress") $dbkey="billing_email";
            if($dbkey!==false) 
            {
                woovisma_addlog("update user meta with $customer_id,$dbkey,$vv");
                $retID=  update_user_meta($customer_id,$dbkey,$vv);
            }
        }
        if($alreadySynced===false)
        {
            $wpdb->insert($tbl_product_sync, array("customer_id" => $customer_id, "rcustomer_id" => $remoteCustomerID));
        }
        return $customer_id;
    }
     public function getPaymentTermsFromSettings()
    {	 woovisma_addlog("getPaymentTermsFromSettings Start");
    
        $options = get_option( 'woovisma_options' );
		if(!isset($options["woovismaoptname"]["paymentterms"]))
		{
			return $this->getFirstTermsOfPaymentId();
		}
		woovisma_addlog($options);
        foreach($this->arrTermsOfPaymentId as $key=>$value)
        {
            
            $terms[]=$value;
        }
        $count=count($terms);
        for($i=0;$i<=$count;$i++)
        {
            if(($terms[$i]["Id"])==$options["woovismaoptname"]["paymentterms"])
            {   woovisma_addlog("getPaymentTermsFromSettings Start");
                return $terms[$i]["Id"];
            }
            
        }
		return $this->getFirstTermsOfPaymentId();
        
    }
    public function getFirstTermsOfPaymentId()
    {
        return isset($this->arrTermsOfPaymentId[0]["Id"])?$this->arrTermsOfPaymentId[0]["Id"]:false;
    }
    public function getCustomersNotSynced($page=1)
    {//print_r("gggg");exit;
        global $wpdb;
        $tbl_customer_sync=$wpdb->prefix ."woovisma_customer_sync";
        $sql="Select customer_id, rcustomer_id from {$tbl_customer_sync}";
        $arrObj=$wpdb->get_results($sql);
        $arrSync=array();
        $arrSyncID=array();
        if($arrObj)
        foreach($arrObj as $obj)
        {
            $arrSync[$obj->customer_id]=$obj->rcustomer_id;
            $arrSyncID[]=$obj->customer_id;
        }
        $tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";
        $objucSettings=Woovisma_Settings::getInstance();
        $startTime=time();
        $modifiedTime=$objucSettings->getData("woocommerce_customer_modified_time","0000-00-00 00:00:00");
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
            $args = array('role' => 'customer', 'nopaging' => false, 'posts_per_page' =>3, 'paged'=>$page,  'fields' => 'ids','exclude'=>$arrSyncID);
        }
        else
        {
            $args = array('role' => 'customer', 'nopaging' => false, 'posts_per_page' =>3, 'paged'=>$page,  'fields' => 'ids','include'=>$arrSyncID);
        }
        $user_query = new WP_User_Query($args);
        $users = $user_query->get_results();
        $total_users=$user_query->total_users;
        $total_pages=ceil($total_users/10);
        return array("data"=>$users,"page"=>$page,"total"=>$total_users,"pages"=>$total_pages);
        /*trace($users);
        trace($user_query);
        $args1 = array(
        'role' => 'customer',
        'orderby' => 'user_nicename',
        'order' => 'ASC'
       );
       $customer_ids=array();
        $arrObjCustomer = get_users($args1);
        foreach($arrObjCustomer as $objUser)
        {
            $customer_ids[]=$objUser->ID;
        }
        $arrNotSync=array();
        if(count($customer_ids)>0)
        {
            woovisma_addlog("customers exist for sync");
            foreach($customer_ids as $customerID)
            {
                if(!isset($arrSync[$customerID]))
                {
                    $arrNotSync[]=$customerID;
                }
            }
            woovisma_addlog("End not empty customer_ids");
        }
        woovisma_addlog("end not empty arrRow");
        return $arrNotSync;*/
    }
    /**
     * if $elementID is false, the cross check in the sync table is ignored and will be proceeded to sync
     * @global type $wpdb
     * @param type $objDSCustomer
     * @param type $elementID
     * @return boolean
     */
    function syncCustomer($objDSCustomer,$elementID=false)
    {
       
        if(!$objDSCustomer->isValid()) 
        {
            woovisma_addlog("Invalid/Missing Datas in Customer datastructure");
            return false;
        }
        global $wpdb;
        $tbl_customer_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_customer_sync";
        $tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";
        
       	
        if($elementID!==false)
        {
        $sql="SELECT rcustomer_id FROM {$tbl_customer_sync} WHERE customer_id={$elementID}";
        woovisma_addlog("SELECT customer_id SQL:".$sql);
        $arrCustomer = $wpdb->get_results($sql);
        woovisma_addlog(__FUNCTION__.":customerID:".print_r($elementID,true));
        if($arrCustomer)
        {
                $remoteCustomer=$this->client->getCustomer($arrCustomer[0]->rcustomer_id);
                ///if customer not exist in visma and the sync table is having the sync record(unstable situation)
                if($remoteCustomer===false)
                {
                    woovisma_addlog("Remote customer not exist");
                    ///delete the sync record
                    $sql="DELETE FROM {$tbl_customer_sync} WHERE customer_id={$elementID}";
                    $wpdb->query($sql);
                    woovisma_addlog("Customer sync table record deleted and started sync again");
                    $ret = $this->syncCustomer($objDSCustomer, $elementID);
                    woovisma_addlog($ret);
                    return $ret;
                }
                else
                {
                    $objDSCustomer->Id=$arrCustomer[0]->rcustomer_id;
                    if($remoteCustomer['Id']==$arrCustomer[0]->rcustomer_id)
                    {
                        $objDSCustomer->TermsOfPaymentId=$remoteCustomer['TermsOfPaymentId'];
                    }
                }
            }
        }
        woovisma_addlog("Customer before sending to remote :".print_r($objDSCustomer,true));
        $ret=$this->client->setCustomer($objDSCustomer);
        ///ignore insert into sync table if there is no customer in the woocommerce
        if($elementID===false)
        {
            
        }
        /**
        * if customer is mmissing in remote, try to create new customer
        */
        else if($ret===false)
        {
            $wpdb->delete($tbl_customer_sync,array("customer_id" => $elementID));
            woovisma_addlog($wpdb->last_query,true);
            $objDSCustomer->Id=null;
            $ret=$this->client->setCustomer($objDSCustomer);
            woovisma_addlog(__FUNCTION__.":ret from setCustomer:".$ret);
            $wpdb->insert($tbl_customer_sync, array("customer_id" => $elementID, "rcustomer_id" => $ret));
	    woovisma_addlog($wpdb->last_query,true);
        }
        else if($ret && empty($arrCustomer))
        {
            woovisma_addlog(__FUNCTION__.":ret from setCustomer:".$ret);
            $wpdb->insert($tbl_customer_sync, array("customer_id" => $elementID, "rcustomer_id" => $ret));
	    woovisma_addlog($wpdb->last_query,true);
            //woovisma_addlog("end empty articleID");
        }
        return $ret;
    }
    /**
     * create or update customer based on the visma_id attribute in $arrData
     * @global type $wpdb
     * @param type $arrData
     * @return boolean
     */
    public function createCustomer($arrData)
    {
        woovisma_addlog("Create customer started");
global $wpdb;
        $obj=new Woovisma_Customer();
        $obj->init();
        $objVismaCustomer=new DSVismaCustomer("InvoiceCity", "InvoicePostalCode", "Name", "TermsOfPaymentId", "IsPrivatePerson");
        if(isset($arrData["visma_id"])) $objVismaCustomer->Id=$arrData["visma_id"];
        $objVismaCustomer->Name=$arrData["billing_first_name"]." ".$arrData["billing_last_name"];
        $objVismaCustomer->InvoiceCity=$arrData["billing_city"];
        $objVismaCustomer->InvoicePostalCode=$arrData["billing_postcode"];
        $objVismaCustomer->InvoiceAddress1=$arrData["billing_address_1"];
        $objVismaCustomer->InvoiceAddress2=$arrData["billing_address_2"];
        $objVismaCustomer->InvoiceCountryCode=$arrData["billing_country"];
        $objVismaCustomer->TermsOfPaymentId=$obj->getPaymentTermsFromSettings();
        $objVismaCustomer->EmailAddress=$arrData["billing_email"];
        $objVismaCustomer->IsPrivatePerson="true";
        $objVismaCustomer->MobilePhone=$arrData["billing_phone"];
        
        if(isset($arrData["customer_number"]))
        {
            woovisma_addlog("Customer Number is ".$arrData["customer_number"],true);
            $objVismaCustomer->CorporateIdentityNumber  =$arrData["customer_number"];
        }
        if(!$objVismaCustomer->isValid()) 
        {
            woovisma_addlog("Create customer validation failed");
            return false;
        }
        woovisma_addlog("calling is_customer_exist filter");
        $visma_id = apply_filters( 'is_customer_exist', $objVismaCustomer, 10, 1 );
        woovisma_addlog($visma_id);
        woovisma_addlog("calling is_customer_exist filter");
        ///if visma_is is not object and visma_id value exist
        if(!is_object($visma_id) && $visma_id)
        {
            woovisma_addlog("update visma id");
            $objVismaCustomer->Id=$visma_id;
        }
        woovisma_addlog("Sync customer started");
        return $obj->syncCustomer($objVismaCustomer);
    }
    public function pushCustomer($customer_id,$objUser=false,$customer_number=false)
    {
	 $objSettings=Woovisma_Settings::getInstance();
        $startTime=time();
        $modifiedTime=$objSettings->getData("visma_customer_modified_time","0000-00-00 00:00:00");
        $ret=$this->client->getCustomers($modifiedTime);
        
global $wpdb;
        $tbl_customer_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_customer_sync";
        $tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";
        if($objUser===false)
        {
            $objUser=  get_user_by("id", $customer_id);
            if(empty($objUser))
            {
                return false;
            }
        }
        
        $obj=new Woovisma_Customer();
        $obj->init();
        
        $sql="SELECT rcustomer_id FROM {$tbl_customer_sync} WHERE customer_id={$customer_id}";
        woovisma_addlog("SELECT customer_id");
        woovisma_addlog("SQL:".$sql,true);
        $arrRCustomer = $wpdb->get_results($sql);
        woovisma_addlog($arrRCustomer,true); 
        $objVismaCustomer=new DSVismaCustomer("InvoiceCity", "InvoicePostalCode", "Name", "TermsOfPaymentId", "IsPrivatePerson");
        if($arrRCustomer)
        {
            $objVismaCustomer->Id=$arrRCustomer[0]->rcustomer_id;
            $remoteCustomer=$this->client->getCustomer($objVismaCustomer->Id);
        }
        $objVismaCustomer->Name=$objUser->get("billing_first_name")." ".$objUser->get("billing_last_name");
        $objVismaCustomer->InvoiceCity=$objUser->get("billing_city");
        $objVismaCustomer->InvoicePostalCode=$objUser->get("billing_postcode");
        $objVismaCustomer->InvoiceAddress1=$objUser->get("billing_address_1");
        $objVismaCustomer->InvoiceAddress2=$objUser->get("billing_address_2");
        $objVismaCustomer->InvoiceCountryCode=$objUser->get("billing_country");
       
        $objVismaCustomer->IsPrivatePerson="true";
        $objVismaCustomer->MobilePhone=$objUser->get("billing_phone");
        $objVismaCustomer->EmailAddress=$objUser->data->user_email;
        
		if(isset($remoteCustomer['EmailAddress']) && $remoteCustomer['EmailAddress']==$objVismaCustomer->EmailAddress)
		{
			$objVismaCustomer->TermsOfPaymentId=$remoteCustomer['TermsOfPaymentId'];

		}
		else 
		{
			$objVismaCustomer->TermsOfPaymentId=$obj->getPaymentTermsFromSettings();
		}
          
        
        
       
        if($customer_number!==false)
        {
            woovisma_addlog("Customer Number is ".$customer_number,true);
            $objVismaCustomer->CorporateIdentityNumber  =$customer_number;
        }
	woovisma_addlog($objVismaCustomer);	
        if(!$objVismaCustomer->isValid()) return false;
        return $obj->syncCustomer($objVismaCustomer,$customer_id);
    }
    public function woo_get_product_sku($id)
    {     
        global $wpdb;
        $sql="SELECT meta_value FROM {$wpdb->prefix}postmeta where meta_key='_sku' AND post_id=$id";
        woovisma_addlog($sql);
        $arrRow=$wpdb->get_results($sql);
        return $arrRow[0]->meta_value;
    }
    function sync_woocommerce_to_visma($arrSyncID=false)
    {
        $arrNotSynced=array();
        ///if $arrSyncid is not false, skip all the preprocess and sync directly. Since partly executed sync, the synced time should not be updated.
        if($arrSyncID!==false)
        {
            foreach($arrSyncID as $orderID)
            {
                $ret=$this->pushCustomer($orderID);
                if($ret===false)
                {
                    $arrNotSynced[]=$orderID;
                }
            }
            return $arrNotSynced;
        }
        
        woovisma_addlog("woocommerce to customer sync started");
        global $wpdb;
        $tbl_customer_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_customer_sync";
        $tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";
        $objucSettings=Woovisma_Settings::getInstance();
        $startTime=time();
        $modifiedTime=$objucSettings->getData("woocommerce_customer_modified_time","0000-00-00 00:00:00");
        $args1 = array(
        'role' => 'customer',
        'orderby' => 'user_nicename',
        'order' => 'ASC'
       );
        $customers = get_users($args1);
 
        if(count($customers)==0)
        {
            woovisma_addlog("no customer exist for sync");
            wp_redirect( add_query_arg(array( "page" => "woo-visma-settings","tab"=>"manual","product_sync_status" => "98" ),admin_url( "admin.php" ) ) );
            exit;
        }
        else
        {woovisma_addlog("customers exist for sync");
            foreach($customers as $objUser)
            {woovisma_addlog("start not empty customer_ids==inside foreach:".$objUser->ID);
                $ret=$this->pushCustomer($objUser->ID,$objUser);
                if($ret===false)
                {
                    $arrNotSynced[$objUser->ID]=false;
                }
            }
            ///if atleast one customer not synced, the customer modified time should not be updated. else the missed customer will never synced until some modification happen in that customer 
            if(!empty($arrNotSynced)) return $arrNotSynced;
            woovisma_addlog("End not empty customer_ids");
        }
        woovisma_addlog("end not empty arrRow");
        ///-100 to adjust the time delay in updating the database
        $timediff=time()-$startTime+100;
        $objucSettings->setTimeData("woocommerce_customer_modified_time", -1*$timediff);
        woovisma_addlog("End bulkPushCustomer");
        return true;
    }
}
?>
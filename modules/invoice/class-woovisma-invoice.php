<?php
class Woovisma_Invoice extends Woovisma_Orderbase
{
    private $invoice;
    private $client=null;
    protected $arrCustomerId=array();
    protected $arrMessage=array();
    protected $initialized=false;
    public function __construct()
    {
    }
    public function init()
    {
        if($this->initialized) return;
        include_once(__DIR__."/class-woovisma-invoicer.php");
        $this->client=Woovisma_Invoicer::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
        
        include_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-customer.php");
        include_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-customerr.php");
                            $client=new Woovisma_Customerr(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
    $objCustomer=Woovisma_Customer::getInstance();
    $objCustomer->loadFromVisma($client);
    $this->arrCustomerId=$objCustomer->getCustomers();

    }
    public static function &getInstance()
    {
        static $objInvoice=null;
        if(is_null($objInvoice))
        {
            $objInvoice=new Woovisma_Invoice();
        }
        return $objInvoice;
    }
    public function loadFromVisma($client)
    {
        woovisma_addlog("Invoice".print_r($this->invoice,true));
        $this->invoice=$client->getInvoices();
    }
    public function getInvoices()
    {
        return $this->invoice;
    }
    function sync_visma_to_woocommerce()
    {
        if(is_license_key_valid() != "Active")
        {
            woovisma_addlog("exiting because license key validation not passed.");
            return false;
        }
global $wpdb;
        woovisma_addlog("started visma_to_woocommerce");
        $objSettings=Woovisma_Settings::getInstance();
        $startTime=time();
        $modifiedTime=$objSettings->getData("visma_invoice_modified_time","0000-00-00 00:00:00");
        if(!method_exists($this->client, "getInvoices")) trace("===");
        $ret=$this->client->getInvoices($modifiedTime);
        if($ret)
        {
            $count=count($ret);
            $arrFailedInvoice=array();
            $tbl_product_sync=$wpdb->prefix ."woovisma_invoice_sync";
            $wpdb->query("TRUNCATE TABLE  {$tbl_product_sync}");
            foreach($ret as $key=>$value)
            {
                $this->popInvoice($value['Id']);
            }
        }
        else
        {
            $count=0;
        }
        ///-100 to adjust the time delay in updating the database
        ///if all invoices updated successfully update the time
        if(empty($arrFailedInvoice))
        {
            $objSettings->setTimeData("visma_invoice_modified_time", date("Y-m-d H:i:s"));
        }
        return $count;
    }
    function automaticSync($post)
    {   woovisma_addlog("inside automaticSync");
        woovisma_addlog("post Id:".$post);
	$objPost=get_post($post);
        woovisma_addlog($objPost);
        if($objPost->post_type == "invoice" || $objPost->post_status == "publish")
        {
            woovisma_addlog($objPost->post_status);  
            $this->syncInvoice($post); 
        }
  
     woovisma_addlog("End Of automaticSync");   
    }
    public function popInvoice($remoteInvoiceID)
    {
        global $wpdb;

        $remoteInvoice=$this->client->getInvoice($remoteInvoiceID);
        woovisma_addlog("Remote Invoice:".print_r($remoteInvoice,true));
        $objCustomerR=Woovisma_Customerr::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
        $objCustomer=Woovisma_Customer::getInstance();
        $objCustomer->init();
        $customer_id=$objCustomer->popCustomer($remoteInvoice["CustomerId"]);
        $remoteInvoiceCustomer=$objCustomerR->getCustomer($remoteInvoice["CustomerId"]);
        $tbl_product_sync=$wpdb->prefix ."woovisma_invoice_sync";
        $sql="SELECT * FROM {$tbl_product_sync} WHERE rinvoice_id='{$remoteInvoiceID}'";
        woovisma_addlog("SELECT invoice_id SQL:".$sql);
        $arrSyncData = $wpdb->get_results($sql);
        $invoice_id=false;
        $alreadySynced=false;
        if(isset($arrSyncData[0]))
        {
            $alreadySynced=true;
            $invoice_id=$arrSyncData[0]->invoice_id;
            woovisma_addlog("Existing Invoice from visma. invoice id is ".$invoice_id);
        }
        else
        {
            woovisma_addlog("New Invoice from visma");
        }
        //$invoiceID= $value["invoiceID"];
        //$author_id=$this->updateInvoice($value);
        ///the user cannot be  added. error occurs
        $clientProduct=WooVismaClient::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
        $invoiceProductStatus=false;
        ///This  is a new invoice. Not synced before
        if($invoice_id===false)
        {
            $arrItemMapping=array();
            ///ignore if there is no product. Later has to delete all the  products from woocomerce, if exist
            if(empty($remoteInvoice["Rows"])) 
            {
                return false;
            }
            foreach($remoteInvoice["Rows"] as $ind=>$invoiceProduct)
            {
                $objInvoiceProduct=new Woovisma_Product();
                $objInvoiceProduct->init($clientProduct);
                $product_id=$objInvoiceProduct->popProduct($invoiceProduct["ArticleId"]);
                if($product_id===false)
                {
                    $invoiceProductStatus=false;
                    break;
                }
                else
                {
                    $invoiceProductStatus=true;
                }
                ///adding the woocommerce product id for mapping the remote product id with local product id
                $remoteInvoice["Rows"][$ind]["product_id"]=$product_id;
            }
            ///if atleast one of the product not in proper format, the invoice has to be ignored
            if($invoiceProductStatus===false) 
            {
                $arrFailedInvoice[]=$remoteInvoiceID;
                return;
            }
            $invoiceData=array();
            $invoiceData["customer_id"]=$customer_id;
            $objWCInvoice=wc_create_invoice($invoiceData);
            $objWCInvoice->set_total($remoteInvoice["Amount"]);
            woovisma_addlog("started processing the line items. the line items are as follows");
            woovisma_addlog($remoteInvoice["Rows"]);
            foreach($remoteInvoice["Rows"] as $ind=>$invoiceProduct)
            {
                $objProduct=wc_get_product($invoiceProduct["product_id"]);
                $itemID=$objWCInvoice->add_product($objProduct,$invoiceProduct["Quantity"]);
                $arrItemMapping[$itemID]=$invoiceProduct["Id"];
            }
        }
        else
        {
            $arrInvoice=array();
            $arrInvoice["invoice_id"]=$invoice_id;
            $objWCInvoice=new WC_Invoice($invoice_id);
            $objWCInvoice->set_total($remoteInvoice["Amount"]);
            //$objWCInvoice= wc_update_invoice($arrInvoice);
            $arrItems=$objWCInvoice->get_items();
            $arrItemMapping=json_decode($arrSyncData[0]->itemmapping,true);
            woovisma_addlog("Item Mapping".print_r($arrItemMapping,true));
            $arrProductNotProcessed=$remoteInvoice["Rows"];
            foreach($remoteInvoice["Rows"] as $ind=>$invoiceProduct)
            {
                $objInvoiceProduct=new Woovisma_Product();
                $objInvoiceProduct->init($clientProduct);
                ///ArticleId is product id and Id is line item id
                woovisma_addlog("Pop Product Start for {$invoiceProduct["ArticleId"]}");
                $product_id=$objInvoiceProduct->popProduct($invoiceProduct["ArticleId"]);
                woovisma_addlog("Pop Product End for {$invoiceProduct["ArticleId"]}");
                $objProductLine=new WC_Product($product_id);
                ///if the remote line item already exist in local invoice, it has to be udpated, if not exist, it has to be created
                $productItemExistInSync=false;
                ///update with the visma invoice's line item id. Later keep the line item updated with remote litem. And remove others
                if($arrItems)
                {
                    foreach($arrItems as $item_id=>$itemData)
                    {
                        ///if the local line item id and remote line item id matches, update the quantity
                        if($arrItems[$item_id]==$invoiceProduct["Id"])
                        {
                            $productItemExistInSync=true;
                            $arrItems[$item_id]["remote_line_item_id"]=$arrItems[$item_id];
                            woovisma_addlog("The product id {$invoiceProduct["ArticleId"]} is already exist and start updating it");
                            $objWCInvoice->update_product($item_id, $objProductLine, array("qty"=>$invoiceProduct["Quantity"]));
                            woovisma_addlog("The product id {$invoiceProduct["ArticleId"]} is updaed to invoice");
                            unset($arrProductNotProcessed[$nd]);
                            unset($arrItems[$item_id]);
                            break;
                        }
                    }
                }
                ///if the line item is new from visma
                if(!$productItemExistInSync)
                {
                    woovisma_addlog("The product id {$invoiceProduct["ArticleId"]} is new and start adding it");
                    $objWCInvoice->add_product($objProductLine,$invoiceProduct["Quantity"]);
                    woovisma_addlog("The product id {$invoiceProduct["ArticleId"]} is added to invoice");
                    unset($arrItems[$item_id]);
                    unset($arrProductNotProcessed[$nd]);
                }
            }
            $arrItemMapping=array();
            ///remove the line item which is not needed
            if($arrItems)
            {
                woovisma_addlog("Items to be removed:".print_r($arrItems,true));
                foreach($arrItems  as $item_id=>$itemData)
                {
                    if(!isset($arrItems[$item_id]["remote_line_item_id"]))
                    {
                        wc_delete_invoice_item($item_id);
                    }
                    else
                    {
                        $arrItemMapping[$item_id]=$invoiceProduct["Id"];
                    }
                }
            }
            else
            {
                woovisma_addlog("There is no change in Items");
            }
            $objWCInvoice=new WC_Invoice($invoice_id);
        }
        $invoice_id=$objWCInvoice->id;
        if($alreadySynced===false)
        {
            $wpdb->insert($tbl_product_sync, array("invoice_id" => $invoice_id, "rinvoice_id" => $remoteInvoiceID, "itemmapping"=>  json_encode($arrItemMapping)));
            woovisma_addlog("Last Query:".$wpdb->last_query);
        }
        else
        {
            $wpdb->update($tbl_product_sync, array("rinvoice_id" => $remoteInvoiceID, "itemmapping"=>  json_encode($arrItemMapping)),array("invoice_id"=>$invoice_id));
            woovisma_addlog("Last Query:".$wpdb->last_query);
        }
    }
    
    public function getFirstCustomerId()
    {
        return isset($this->arrCustomerId[0]["Id"])?$this->arrCustomerId[0]["Id"]:false;
    }
    public function getInvoicesNotSynced($page=1)
    {
        global $wpdb;
        $tbl_invoice_sync=$wpdb->prefix ."woovisma_invoice_sync";
        $sql="Select invoice_id, rinvoice_id from {$tbl_invoice_sync}";
        $arrObj=$wpdb->get_results($sql);
        $arrSync=array();
        $arrSyncID=array();
        if($arrObj)
        foreach($arrObj as $obj)
        {
            $arrSync[$obj->invoice_id]=$obj->rinvoice_id;
            $arrSyncID[]=$obj->invoice_id;
        }
        $tbl_invoice_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_invoice_sync";
        $tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";
        $objucSettings=Woovisma_Settings::getInstance();
        $startTime=time();
        $modifiedTime=$objucSettings->getData("woocommerce_invoice_modified_time","0000-00-00 00:00:00");
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
            $args = array('post_type' => array('shop_invoice'),'post_status' => array('wc-completed','wc-on-hold','wc-processing','wc-pending'), 'nopaging' => false, 'posts_per_page' =>10, 'paged'=>$page,  'fields' => 'ids','post__not_in'=>$arrSyncID);
        }
        else
        {
            $args = array('post_type' => array('shop_invoice'),'post_status' => array('wc-completed','wc-on-hold','wc-processing','wc-pending'), 'nopaging' => false, 'posts_per_page' =>10, 'paged'=>$page,  'fields' => 'ids','post__in'=>$arrSyncID);
        }
        /*$args = array('post_type' => array('invoice'),'post_status' => array('publish'),'date_query'    => array(
    'column'  => 'post_modified',
    'after' => array('year' => $year,'month' => $month,'day'=>$date,'hour'=>$hour,'minute'=>$min,'second'=>$sec)
), 'nopaging' => true, 'fields' => 'ids');*/
        $arrObjPost = new WP_Query($args);
        $arrSync=array();
        if(($arrObjPost->post_count)>0)
        {
            woovisma_addlog("products exist for sync");
            foreach($arrObjPost->posts as $productID)
            {
                $arrSync[]=$productID;
            }
            woovisma_addlog("End not empty invoice_ids");
        }
        return array("data"=>$arrSync,"page"=>$page,"total"=>$arrObjPost->found_posts,"pages"=>$arrObjPost->max_num_pages);
        /*trace($arrSync);
        $arrNotSync=array();
        if(count($invoice_ids)>0)
        {
            woovisma_addlog("invoices exist for sync");
            foreach($invoice_ids as $invoiceID)
            {
                if(!isset($arrSync[$invoiceID]))
                {
                    $arrNotSync[]=$invoiceID;
                }
            }
            woovisma_addlog("End not empty invoice_ids");
        }
        woovisma_addlog("end not empty arrRow");
        return $arrNotSync;*/
    }
    function syncInvoice($objDSInvoice,$elementID)
    {
        if(is_license_key_valid() != "Active")
        {
            woovisma_addlog("exiting because license key validation not passed.");
            return false;
        }

        if(!$objDSInvoice->isValid()) 
        {
            woovisma_addlog("Invalid/Missing Datas in Invoice datastructure");
            return false;
        }
        global $wpdb;
        $tbl_invoice_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_invoice_sync";
        $tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";
        //$invoice = new WC_Invoice();
        $sql="SELECT rinvoice_id FROM {$tbl_invoice_sync} WHERE invoice_id={$elementID}";
        woovisma_addlog("SELECT invoice_id SQL:".$sql);
        $arrInvoice = $wpdb->get_results($sql);
        woovisma_addlog(__FUNCTION__.":invoiceID:".print_r($elementID,true));
        if($arrInvoice)
        {
            $objDSInvoice->Id=$arrInvoice[0]->rinvoice_id;
        }
        woovisma_addlog("Invoice before sending to remote :".print_r($objDSInvoice,true));
        $ret=$this->client->setInvoice($objDSInvoice);
        if($ret && empty($arrInvoice))
        {
            woovisma_addlog(__FUNCTION__.":ret from setInvoice:".$ret);
            $wpdb->insert($tbl_invoice_sync, array("invoice_id" => $elementID, "rinvoice_id" => $ret));
	    woovisma_addlog($wpdb->last_query);
            woovisma_addlog("end empty articleID");
        }
        return $ret;
    }
    public function pushInvoice($invoice_id,$objUser=false)
    {
        global $wpdb;
        $options = get_option( 'woovisma_options' );
        $objWCInvoice=new WC_Order($invoice_id);
        woovisma_addlog($objWCInvoice); 
        $orderType=isset($options["woovismaoptname"]["initiateordersync"]["selected"]["type"])?$options["woovismaoptname"]["initiateordersync"]["selected"]["type"]:false;//trace($options["woovismaoptname"]["initiateordersync"]["type"]);
        if($orderType && $orderType=="status")
        {
            if(!isset($options["woovismaoptname"]["initiateordersync"]["type"][$orderType])) return false;
            if(empty($options["woovismaoptname"]["initiateordersync"]["type"]["status"])) return false;
            $arrStatus=array('wc-completed'=>3,'wc-on-hold'=>2,'wc-processing'=>1,'wc-pending'=>0);
            $statusindex=$arrStatus[$objWCInvoice->post_status];
            if(!isset($options["woovismaoptname"]["initiateordersync"]["type"]["status"][$statusindex]))
            {
                return false;
            }
        }
        $tbl_product_sync=$wpdb->prefix ."woovisma_invoice_sync";
        $sql="SELECT * FROM {$tbl_product_sync} WHERE invoice_id='{$invoice_id}'";
        woovisma_addlog("SELECT invoice_id SQL:".$sql);
        $arrSyncData = $wpdb->get_results($sql);
        $rinvoice_id=false;
        $alreadySynced=false;
        if(isset($arrSyncData[0]))
        {
            $alreadySynced=true;
            $rinvoice_id=$arrSyncData[0]->rinvoice_id;
        }
        woovisma_addlog("PushOrder-Preparing Datastructure for pushing invoice");
            $DSInvoice=new DSVismaInvoice("Amount", "CustomerId", "CurrencyCode", "VatAmount", "RoundingsAmount", "InvoiceCity", "InvoiceCountryCode", "InvoiceCustomerName", "InvoicePostalCode", "EuThirdParty", "CustomerIsPrivatePerson", "InvoiceDate", "Status", "RotReducedInvoicingType", "ReverseChargeOnConstructionServices");
            //$DSInvoice->OurReference="Invoice ID: ".$invoice_id.", Payment Method: ".get_post_meta( $invoice_id, '_payment_method', true ).' ('.current_time('Y-m-d').')';
            $DSInvoice->OurReference="ID: ".$invoice_id.", Method: ".get_post_meta( $invoice_id, '_payment_method', true ).' ('.current_time('Y-m-d').')';
            $DSInvoice->OurReference=substr($DSInvoice->OurReference,0,50);
            $DSInvoice->Amount=$objWCInvoice->get_total();
            $invoiceAddress=$objWCInvoice->get_address("billing");
            $shippingAddress=$objWCInvoice->get_address("shipping");woovisma_addlog($shippingAddress,true);
            woovisma_addlog("PushOrder-Checking whether invoice placed by guest user or existing user");
            $customer_number=get_post_meta( $invoice_id, "billing_ssn", true );
            if(empty($objWCInvoice->customer_user)) 
            {
                $this->arrMessage["invoice"][$invoice_id][]="Customer not exist";
                woovisma_addlog("Customer not exist");
                $objCustomer=new Woovisma_Customer();
                $objCustomer->init();
                woovisma_addlog("Customer creation started");
                $arrCustData=array();
                $arrCustData["billing_email"]=$objWCInvoice->billing_email;
                $arrCustData["billing_first_name"]=$objWCInvoice->billing_first_name;
                $arrCustData["billing_last_name"]=$objWCInvoice->billing_last_name;
                $arrCustData["billing_address_1"]=$objWCInvoice->billing_address_1;
                $arrCustData["billing_address_2"]=$objWCInvoice->billing_address_2;
                $arrCustData["billing_city"]=$objWCInvoice->billing_city;
                $arrCustData["billing_state"]=$objWCInvoice->billing_state;
                $arrCustData["billing_country"]=$objWCInvoice->billing_country;
                $arrCustData["billing_phone"]=$objWCInvoice->billing_phone;
                $arrCustData["billing_postcode"]=$objWCInvoice->billing_postcode;
                $arrCustData["billing_company"]=$objWCInvoice->billing_company;
                ///if ssn field exist in invoice
                if(isset($customer_number) && !empty($customer_number))
                {
                    woovisma_addlog("Customer Number is ".$customer_number, true);
                    $arrCustData["customer_number"]=$customer_number;
                }
                ///get customer id from visma by not pushing customer from wordpress. Because, the order is created by guest user
                $rcustid=$objCustomer->createCustomer($arrCustData);
                woovisma_addlog("Customer created");
                woovisma_addlog("The remote id is {$rcustid}",true);
            }
            ///if the customer already exist in wordpress, push to visma.
            else
            {
                $objCustomer=new Woovisma_Customer();
                $objCustomer->init();
                woovisma_addlog("Customer pushing started");
                ///if ssn field exist in invoice
                if(isset($customer_number) && !empty($customer_number))
                {
                    $rcustid=$objCustomer->pushCustomer($objWCInvoice->customer_user,false,$customer_number);
                }
                else
                {
                    $rcustid=$objCustomer->pushCustomer($objWCInvoice->customer_user);
                }
                if(empty($rcustid))
                {
                    woovisma_addlog("Customer push failed");
                    woovisma_addlog("The failed customer is {$objWCInvoice->customer_user}",true);
                }
                else
                {
                    woovisma_addlog("Customer pushed");
                    woovisma_addlog("The remote id of customer({$objWCInvoice->customer_user}) is {$rcustid}",true);
                }
            }
            if($rcustid===false) 
            {
                woovisma_addlog("Customer not exist in remote");
                $this->arrMessage["invoice"][$invoice_id][]="Customer not exist in visma";
                return false;
            }
            woovisma_addlog("Customer pushed and remote id is {$rcustid}", true);
            $DSInvoice->CustomerId=$rcustid;
            $DSInvoice->CurrencyCode=$objWCInvoice->order_currency;
            $woocommerce_tax=getConfigVal("woocommerce_tax");
            $DSInvoice->Amount=$objWCInvoice->get_total();
            if(!$DSInvoice->Amount===0 && empty($DSInvoice->Amount))
            {
                woovisma_addlog("Amount is empty");
                $this->arrMessage["invoice"][$invoice_id][]="Amount is empty";
                return false;
            }
            $DSInvoice->InvoiceAddress1=$invoiceAddress["address_1"];
            $DSInvoice->InvoiceAddress2=$invoiceAddress["address_2"];
            $DSInvoice->InvoiceCity=$invoiceAddress["city"];
            $DSInvoice->InvoiceCountryCode=$invoiceAddress["country"];
            $DSInvoice->InvoiceCustomerName=$invoiceAddress["first_name"]." ".$invoiceAddress["last_name"];
            $DSInvoice->InvoicePostalCode=$invoiceAddress["postcode"];
            $DSInvoice->DeliveryAddress1=$shippingAddress["address_1"];
            $DSInvoice->DeliveryAddress2=$shippingAddress["address_2"];
            $DSInvoice->DeliveryCity=$shippingAddress["city"];
            $DSInvoice->DeliveryCountryCode=$shippingAddress["country"];
            $DSInvoice->DeliveryCustomerName=$shippingAddress["first_name"]." ".$shippingAddress["last_name"];
            $DSInvoice->DeliveryPostalCode=$shippingAddress["postcode"];
            $DSInvoice->EuThirdParty="true";
            $DSInvoice->CustomerIsPrivatePerson="true";
            $DSInvoice->InvoiceDate=$objWCInvoice->order_date;
            $DSInvoice->Status=1;
            $DSInvoice->RotReducedInvoicingType="Normal";
            $DSInvoice->ReverseChargeOnConstructionServices="false";
           
            $arrDSItem=array();
            if($rinvoice_id!==false)
            {
                $arrItemMapping=  json_decode($arrSyncData[0]->itemmapping,true);

                if($arrItemMapping)
                {
                    foreach($arrItemMapping as $itemMapping)
                    {
                        //trace("====");
                    }
                }
            }
            woovisma_addlog("Invoice Object");
            woovisma_addlog($objWCInvoice);
            woovisma_addlog("Shipping cost: ".$objWCInvoice->order_shipping);
            woovisma_addlog("Order Tax: ".$objWCInvoice->order_tax);
            $arrItems=$objWCInvoice->get_items();
            woovisma_addlog("Items to be added");
            woovisma_addlog($arrItems);
            require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaClient.php');
            require_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-product.php");
            $taxAmount=0;
            $DSInvoice->Amount=0;
            if($arrItems)
            {
                ///update with the visma invoice's line item id. Later keep the line item updated with remote litem. And remove others
                $client=WooVismaClient::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
                $count=0;
                foreach($arrItems as $item_id=>$itemData)
                {
                    $itemData=processItemData($itemData);
                    woovisma_addlog($itemData);
                    $objProd=new Woovisma_Product();
                    $objProd->init($client);
                    woovisma_addlog("Product sync started");
                    woovisma_addlog("Variation ID:".$itemData["variation_id"]);
                    if(isset($itemData["variation_id"])&& $itemData["variation_id"] != 0)
                    {
                        $itemData["product_id"]=$itemData["variation_id"];
                    }
                    $product_remote_id=$objProd->syncProduct($itemData["product_id"]);
                    //$objP=new WC_Product($itemData["product_id"]);
                    $product = wc_get_product( $itemData["product_id"] );
                    $objP=processProductObject($product);
                    $product_sku=$this->woo_get_product_sku($itemData["product_id"]);
                    if(empty($product_sku))
                    {
                        $product_sku=$itemData["product_id"];
                    }
                    if(empty($product_remote_id)) 
                    {
                        $this->arrMessage["invoice"][$invoice_id][]="Product not exist in  remote";
                        //trace($itemData);
                        continue;
                    }
                    woovisma_addlog("Product sync finished and the  product remote id is ".$product_remote_id);
                    //$objWP=new WC_Product($itemData["product_id"]);
                    $objArticleCode=Woovisma_ArticleCode::getInstance();
                    ///calculating the percentage of discount
                    $discountPercentage=0;
                    woovisma_addlog("Product unit price before discount calculation: {$itemData["line_subtotal"]}/{$itemData["qty"]}");
                    $productUnitPriceBeforeDiscount=$itemData["line_subtotal"]/$itemData["qty"];
                    woovisma_addlog("Discount calculation: (({$itemData["line_subtotal"]}-{$itemData["line_total"]})/{$itemData["qty"]})/{$productUnitPriceBeforeDiscount}");
                    $discountPercentage=(($itemData["line_subtotal"]-$itemData["line_total"])/$itemData["qty"])/$productUnitPriceBeforeDiscount;
                    $discountPercentage=round($discountPercentage,2);
                    $productUnitPrice=$objP->get_price_excluding_tax();
                    $prodTaxCode=$objArticleCode->getProductTaxRateCodeByPostID($itemData["product_id"]);
                    $taxPercentage=$objArticleCode->getTaxPercentageByCode($prodTaxCode);
                    woovisma_addlog("Product Tax Amount: {$taxPercentage}*".$objP->get_price_excluding_tax());
                    $productTaxAmount=$taxPercentage*$objP->get_price_excluding_tax();
                    woovisma_addlog("Product Unit Price: {$productUnitPrice}+{$productTaxAmount}");
                    $DSInvoice->Rows[]=array(
                        "LineNumber"=>$count++,
                        "IsTextRow"=>"false",
                        "Text"=>$itemData["name"],
                        "IsWorkCost"=>"true",
                        "UnitPrice"=>round($productUnitPrice+$productTaxAmount,2),
                        "DiscountPercentage"=>$discountPercentage,
                        "IsVatFree"=>"false",
                        "ArticleId"=>$product_remote_id,
                        "ArticleNumber"=>$product_sku,
                        "Quantity"=>$itemData["qty"],
                        "DeliveredQuantity"=>$itemData["qty"]
                    );
                    woovisma_addlog("Product Tax Amount accumulated: {$taxAmount}+{$taxPercentage}*".$itemData["line_total"]);
                    $taxAmount=$taxAmount+$taxPercentage*$itemData["line_total"];
                    woovisma_addlog("Invoice amount on each product accumulated = precious amount:{$DSInvoice->Amount} + product price after discount:{$itemData["line_total"]}");
                    $DSInvoice->Amount=$DSInvoice->Amount+$itemData["line_total"]; 
                }
            }
            if($objWCInvoice->order_shipping)
            {
                woovisma_addlog("create product object");
                $objProd=new Woovisma_Product();
                $objProd->init($client);
                woovisma_addlog("Shipping cost sync started");
                $product_sku="shippingcost"; 
                $product_name=$product_sku;
                $objShippingCostProduct=$objProd->getEmptyProductIfNotExist($product_sku, $product_name);
                $product_remote_id=$objShippingCostProduct->Id;
                //$shipcostid=$objProd->get_productid_by_sku("shippingcost");
                //$product_remote_id=$objProd->syncProduct($shipcostid);
                //$objP=new WC_Product($shipcostid);
                //$product = wc_get_product( $shipcostid );
                //$objP=processProductObject($product);
                //$product_sku=$this->woo_get_product_sku($shipcostid);
                if(!empty($product_sku) && !empty($product_remote_id)) 
                {
                    woovisma_addlog("Product sync finished and the  product remote id is ".$product_remote_id);
                    //$objWP=new WC_Product($itemData["product_id"]);
                    $objArticleCode=Woovisma_ArticleCode::getInstance();
                    $productUnitPrice=$objWCInvoice->order_shipping;
                    woovisma_addlog("Product Tax Amount: {$objWCInvoice->order_shipping_tax}");
                    $productTaxAmount=$objWCInvoice->order_shipping_tax;
                    woovisma_addlog("Product Unit Price: {$productUnitPrice}+{$productTaxAmount}");
                    /*$prodTaxCode=$objArticleCode->getProductTaxRateCodeByPostID($postID);
                    $taxPercentage=$objArticleCode->getTaxPercentageByCode($prodTaxCode);*/
                    $DSInvoice->Rows[]=array(
                        "LineNumber"=>$count++,
                        "IsTextRow"=>"false",
                        "Text"=>$product_sku,
                        "IsWorkCost"=>"true",
                        "UnitPrice"=>$productUnitPrice+$productTaxAmount,
                        "IsVatFree"=>"false",
                        "ArticleId"=>$product_remote_id,
                        "ArticleNumber"=>$product_sku,
                        "Quantity"=>1,
                        "DeliveredQuantity"=>1
                    );
                    //$taxAmount=$taxAmount+$taxPercentage*$objP->get_price_excluding_tax();
                    woovisma_addlog("Product Tax Amount accumulated: {$taxAmount}+{$productTaxAmount}");
                    $taxAmount=$taxAmount+$productTaxAmount;
                    woovisma_addlog("Invoice amount on each product accumulated = precious amount:{$DSInvoice->Amount} + {$productUnitPrice}+{$productTaxAmount}");
                    $DSInvoice->Amount=$DSInvoice->Amount+$productUnitPrice;
                }
            }
            ///add tax with the amount
            woovisma_addlog("Invoice amount is amount:{$DSInvoice->Amount}+tax:{$taxAmount}");
            $DSInvoice->Amount=round($DSInvoice->Amount+$taxAmount);
            $DSInvoice->VatAmount=round($taxAmount,2);
			$DSInvoice->DeliveredVatAmount=round($taxAmount,2);
            $DSInvoice->RoundingsAmount=$DSInvoice->Amount;
            if(!$DSInvoice->isValid())
            {
                $arrMissingFields=$DSInvoice->getMissingFields();
                woovisma_addlog ("The fields \"".(implode("\", \"",$arrMissingFields))."\" are missing");
                woovisma_addlog($DSInvoice); 
                return false;
            }
            woovisma_addlog("Invoice datastructure is valid and proceed to sync");
            woovisma_addlog($DSInvoice);
            $this->syncInvoice($DSInvoice, $invoice_id);
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
        /*if(is_license_key_valid() != "Active" || create_license_validation_request())
        {
            woovisma_addlog("exiting because license key validation not passed.");
            return false;
        }*/
        $arrInvoiceNotSynced=array();
        ///if $arrSyncid is not false, skip all the preprocess and sync directly.  Since partly executed sync, the synced time should not be updated.
        if($arrSyncID!==false)
        {
            foreach($arrSyncID as $invoiceID)
            {
                $ret=$this->pushInvoice($invoiceID);
                if($ret===false)
                {
                    $arrInvoiceNotSynced[]=$invoiceID;
                }
            }
            return $arrInvoiceNotSynced;
        }

        woovisma_addlog("woocommerce to invoice sync started");
        global $wpdb;
        $tbl_invoice_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_invoice_sync";
        /*
         * 
        $tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";
        $objucSettings=Woovisma_Settings::getInstance();
        $startTime=time();
        $modifiedTime=$objucSettings->getData("woocommerce_order_modified_time","0000-00-00 00:00:00");*/
        $tbl_settings=$wpdb->prefix ."woovisma_settings";
        $now = new DateTime();
        $datesent=$now->format('Y-m-d H:i:s');  
        $sql="SELECT data FROM $tbl_settings where name='woocommerce_invoice_modified_time' ";
        $arrRow=$wpdb->get_results($sql);
        if(empty($arrRow))
        {woovisma_addlog("Fresh sync");
            $sql = "Insert INTO $tbl_settings(data,name) Values ('$datesent','woocommerce_invoice_modified_time')";
            $wpdb->query($sql);
        $args = array('post_type' => array('shop_order'),'post_status'=>array('wc-completed','wc-on-hold','wc-processing','wc-pending'), 'nopaging' => true, 'fields' => 'ids');
        
        $invoice_ids = new WP_Query($args);
        foreach($invoice_ids->posts as $invoice_id)
        {
            $success=$this->pushInvoice($invoice_id);
            if($success===false)
            {
                $arrInvoiceNotSynced[]=$invoiceID;
                continue;
            }
        }
			
			///if order updated partly, the modified time should not be updated. else the failed order never synced unless there is a modification in order
        if(!empty($arrInvoiceNotSynced)) return $arrInvoiceNotSynced;
            woovisma_addlog("end empty arrRow");
        }
        else
        {woovisma_addlog("sync based on the last synced date");
            $lastdate=$arrRow[0]->data;
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
            $args = array('post_type' => array('shop_order'),'post_status'=>array('wc-completed','wc-on-hold','wc-processing','wc-pending'),'date_query'    => array(
        'column'  => 'post_modified',
        'after' => array('year' => $year,'month' => $month,'day'=>$date,'hour'=>$hour,'minute'=>$min,'second'=>$sec)
    ), 'nopaging' => true, 'fields' => 'ids');
           
            $invoice_ids = new WP_Query($args);
            if(($invoice_ids->post_count)==0)
            {
                woovisma_addlog("no invoice exist for sync");
                wp_redirect( add_query_arg(array(  'page' => 'woo-visma-settings',"tab"=>"manual",'product_sync_status' => '97' ),admin_url( 'admin.php' ) ) );
                exit;
            }
            else
            {
                woovisma_addlog("invoice exist for sync");
                foreach($invoice_ids->posts as $invoice_id)
                {
                $success=$this->pushInvoice($invoice_id);
                if($success===false)
                {
                    $arrInvoiceNotSynced[]=$invoiceID;
                    continue;
                }
                }
                woovisma_addlog("End not empty order_ids");
                ///if order updated partly, the modified time should not be updated. else the failed order never synced unless there is a modification in order
                if(!empty($arrInvoiceNotSynced)) return $arrInvoiceNotSynced;

            }
        woovisma_addlog("end not empty arrRow");
        }
        $date=date('Y-m-d h:i:s');
        $newDate = strtotime($date) - 1;
        $date=date('Y-m-d h:i:s',$newDate);

        $sql="Update $tbl_settings SET `data` = '$date' where `name`= 'woocommerce_invoice_modified_time' ";
        
        $wpdb->query($sql);

        woovisma_addlog("End bulkPushInvoice");
        
        return true;
        
    }
         
        
    
        
}
?>
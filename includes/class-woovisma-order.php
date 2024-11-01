<?php
class Woovisma_Order extends Woovisma_Orderbase
{
    private $order;
    private $client=null;
    protected $arrCustomerId=array();
    protected $arrMessage=array();
    protected $initialized=false;
    public function __construct()
    {
        parent::__construct();
    }
    public function init()
    {
        if($this->initialized) return;
        include_once(__DIR__."/class-woovisma-orderr.php");
        $this->client=Woovisma_Orderr::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
        
        

                        include_once(__DIR__."/class-woovisma-customer.php");
                        include_once(__DIR__."/class-woovisma-customerr.php");
                            $client=new Woovisma_Customerr(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
    $objCustomer=Woovisma_Customer::getInstance();
    $objCustomer->loadFromVisma($client);
    $this->arrCustomerId=$objCustomer->getCustomers();

    }
    public static function &getInstance()
    {
        static $objOrder=null;
        if(is_null($objOrder))
        {
            $objOrder=new Woovisma_Order();
        }
        return $objOrder;
    }
    public function loadFromVisma($client)
    {
        woovisma_addlog("Order".print_r($this->order,true));
        $this->order=$client->getOrders();
    }
    public function getOrders()
    {
        return $this->order;
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
        $modifiedTime=$objSettings->getData("visma_order_modified_time","0000-00-00 00:00:00");
        if(!method_exists($this->client, "getOrders")) return false;
        $ret=$this->client->getOrders($modifiedTime);
        if($ret)
        {
            $count=count($ret);
            $arrFailedOrder=array();
            $tbl_product_sync=$wpdb->prefix ."woovisma_order_sync";
            $wpdb->query("TRUNCATE TABLE  {$tbl_product_sync}");
            foreach($ret as $key=>$value)
            {
                $this->popOrder($value['Id']);
            }
        }
        else
        {
            $count=0;
        }
        ///-100 to adjust the time delay in updating the database
        ///if all orders updated successfully update the time
        if(empty($arrFailedOrder))
        {
            $objSettings->setTimeData("visma_order_modified_time", date("Y-m-d H:i:s"));
        }
        return $count;
    }
    function automaticSync($post)
    {   woovisma_addlog("inside automaticSync");
        woovisma_addlog("post Id:".$post);
	$objPost=get_post($post);
        woovisma_addlog($objPost);
        if($objPost->post_type == "order" || $objPost->post_status == "publish")
        {
            woovisma_addlog($objPost->post_status);  
            $this->syncOrder($post); 
        }
  
  

     woovisma_addlog("End Of automaticSync");   
    }
    public function popOrder($remoteOrderID)
    {
        global $wpdb;
include_once(__DIR__."/class-woovisma-customerr.php");
        $remoteOrder=$this->client->getOrder($remoteOrderID);
        woovisma_addlog("Remote Order:".print_r($remoteOrder,true));
        $objCustomerR=Woovisma_Customerr::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
        $objCustomer=Woovisma_Customer::getInstance();
        $objCustomer->init();
        $customer_id=$objCustomer->popCustomer($remoteOrder["CustomerId"]);
        $remoteOrderCustomer=$objCustomerR->getCustomer($remoteOrder["CustomerId"]);
        $tbl_product_sync=$wpdb->prefix ."woovisma_order_sync";
        $sql="SELECT * FROM {$tbl_product_sync} WHERE rorder_id='{$remoteOrderID}'";
        woovisma_addlog("popOrder:SELECT order_id SQL");
        $arrSyncData = $wpdb->get_results($sql);
        $order_id=false;
        $alreadySynced=false;
        if(isset($arrSyncData[0]))
        {
            $alreadySynced=true;
            $order_id=$arrSyncData[0]->order_id;
            woovisma_addlog("Existing Order from visma. order id is ".$order_id);
        }
        else
        {
            woovisma_addlog("New Order from visma");
        }
        //$orderID= $value["orderID"];
        //$author_id=$this->updateOrder($value);
        ///the user cannot be  added. error occurs
        $clientProduct=WooVismaClient::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
        $orderProductStatus=false;
        ///This  is a new order. Not synced before
        if($order_id===false)
        {
            $arrItemMapping=array();
            ///ignore if there is no product. Later has to delete all the  products from woocomerce, if exist
            if(empty($remoteOrder["Rows"])) 
            {
                return false;
            }
            foreach($remoteOrder["Rows"] as $ind=>$orderProduct)
            {
                $objOrderProduct=new Woovisma_Product();
                $objOrderProduct->init($clientProduct);
                $product_id=$objOrderProduct->popProduct($orderProduct["ArticleId"]);
                if($product_id===false)
                {
                    $orderProductStatus=false;
                    break;
                }
                else
                {
                    $orderProductStatus=true;
                }
                ///adding the woocommerce product id for mapping the remote product id with local product id
                $remoteOrder["Rows"][$ind]["product_id"]=$product_id;
            }
            ///if atleast one of the product not in proper format, the order has to be ignored
            if($orderProductStatus===false) 
            {
                $arrFailedOrder[]=$remoteOrderID;
                return;
            }
            $orderData=array();
            $orderData["customer_id"]=$customer_id;
            $objWCOrder=wc_create_order($orderData);
            $objWCOrder->set_total($remoteOrder["Amount"]);
            woovisma_addlog("started processing the line items. the line items are as follows");
            woovisma_addlog($remoteOrder["Rows"]);
            foreach($remoteOrder["Rows"] as $ind=>$orderProduct)
            {
                $objProduct=wc_get_product($orderProduct["product_id"]);
                $itemID=$objWCOrder->add_product($objProduct,$orderProduct["Quantity"]);
                $arrItemMapping[$itemID]=$orderProduct["Id"];
            }
        }
        else
        {
            $arrOrder=array();
            $arrOrder["order_id"]=$order_id;
            $objWCOrder=new WC_Order($order_id);
            $objWCOrder->set_total($remoteOrder["Amount"]);
            //$objWCOrder= wc_update_order($arrOrder);
            $arrItems=$objWCOrder->get_items();
            $arrItemMapping=json_decode($arrSyncData[0]->itemmapping,true);
            woovisma_addlog("Item Mapping".print_r($arrItemMapping,true));
            $arrProductNotProcessed=$remoteOrder["Rows"];
            foreach($remoteOrder["Rows"] as $ind=>$orderProduct)
            {
                $objOrderProduct=new Woovisma_Product();
                $objOrderProduct->init($clientProduct);
                ///ArticleId is product id and Id is line item id
                woovisma_addlog("Pop Product Start for {$orderProduct["ArticleId"]}");
                $product_id=$objOrderProduct->popProduct($orderProduct["ArticleId"]);
                woovisma_addlog("Pop Product End for {$orderProduct["ArticleId"]}");
                $objProductLine=new WC_Product($product_id);
                ///if the remote line item already exist in local order, it has to be udpated, if not exist, it has to be created
                $productItemExistInSync=false;
                ///update with the visma order's line item id. Later keep the line item updated with remote litem. And remove others
                if($arrItems)
                {
                    foreach($arrItems as $item_id=>$itemData)
                    {
                        ///if the local line item id and remote line item id matches, update the quantity
                        if($arrItems[$item_id]==$orderProduct["Id"])
                        {
                            $productItemExistInSync=true;
                            $arrItems[$item_id]["remote_line_item_id"]=$arrItems[$item_id];
                            woovisma_addlog("The product id {$orderProduct["ArticleId"]} is already exist and start updating it");
                            $objWCOrder->update_product($item_id, $objProductLine, array("qty"=>$orderProduct["Quantity"]));
                            woovisma_addlog("The product id {$orderProduct["ArticleId"]} is updaed to order");
                            unset($arrProductNotProcessed[$nd]);
                            unset($arrItems[$item_id]);
                            break;
                        }
                    }
                }
                ///if the line item is new from visma
                if(!$productItemExistInSync)
                {
                    woovisma_addlog("The product id {$orderProduct["ArticleId"]} is new and start adding it");
                    $objWCOrder->add_product($objProductLine,$orderProduct["Quantity"]);
                    woovisma_addlog("The product id {$orderProduct["ArticleId"]} is added to order");
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
                        wc_delete_order_item($item_id);
                    }
                    else
                    {
                        $arrItemMapping[$item_id]=$orderProduct["Id"];
                    }
                }
            }
            else
            {
                woovisma_addlog("There is no change in Items");
            }
            $objWCOrder=new WC_Order($order_id);
        }
        $order_id=$objWCOrder->id;
        if($alreadySynced===false)
        {
            $wpdb->insert($tbl_product_sync, array("order_id" => $order_id, "rorder_id" => $remoteOrderID, "itemmapping"=>  json_encode($arrItemMapping)));
            woovisma_addlog("Last Query:".$wpdb->last_query);
        }
        else
        {
            $wpdb->update($tbl_product_sync, array("rorder_id" => $remoteOrderID, "itemmapping"=>  json_encode($arrItemMapping)),array("order_id"=>$order_id));
            woovisma_addlog("Last Query:".$wpdb->last_query);
        }
    }
    
    

    public function getFirstCustomerId()
    {
        return isset($this->arrCustomerId[0]["Id"])?$this->arrCustomerId[0]["Id"]:false;
    }
    function syncOrder($objDSOrder,$elementID)
    {
        $status=is_license_key_valid();
        if( $status!= "Active")
        {
            woovisma_addlog("exiting because license key validation not passed. the status is ".$status);
            return false;
        }

        if(!$objDSOrder->isValid()) 
        {
            woovisma_addlog("Invalid/Missing Datas in Order datastructure");
            return false;
        }
        global $wpdb;
        $tbl_order_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_order_sync";
        $tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";
        //$order = new WC_Order();
        $sql="SELECT rorder_id FROM {$tbl_order_sync} WHERE order_id={$elementID}";
        woovisma_addlog("syncOrder-SELECT order_id SQL");
        $arrOrder = $wpdb->get_results($sql);
        woovisma_addlog(__FUNCTION__.":orderID:".print_r($elementID,true));
        if($arrOrder)
        {
            $objDSOrder->Id=$arrOrder[0]->rorder_id;
        }
        woovisma_addlog("Order before sending to remote");
        woovisma_addlog($objDSOrder);
        $ret=$this->client->setOrder($objDSOrder);
        if($ret && empty($arrOrder))
        {
            woovisma_addlog(__FUNCTION__.":ret from setOrder:".$ret);
            $wpdb->insert($tbl_order_sync, array("order_id" => $elementID, "rorder_id" => $ret));
	    woovisma_addlog($wpdb->last_query);
            woovisma_addlog("end empty articleID");
        }
        return $ret;
    }
    public function pushOrder($order_id,$objUser=false)
    {
        global $wpdb;
        $options = get_option( 'woovisma_options' );
        $objWCOrder=new WC_Order($order_id);
        woovisma_addlog($objWCOrder); 
        $orderType=isset($options["woovismaoptname"]["initiateordersync"]["selected"]["type"])?$options["woovismaoptname"]["initiateordersync"]["selected"]["type"]:false;
        if($orderType && $orderType=="status")
        {
            if(!isset($options["woovismaoptname"]["initiateordersync"]["type"][$orderType])) return false;
            if(empty($options["woovismaoptname"]["initiateordersync"]["type"]["status"])) return false;
            $arrStatus=array('wc-completed'=>3,'wc-on-hold'=>2,'wc-processing'=>1,'wc-pending'=>0);
            $statusindex=$arrStatus[$objWCOrder->post_status];
            if(!isset($options["woovismaoptname"]["initiateordersync"]["type"]["status"][$statusindex]))
            {
                return false;
            }
        }
        $tbl_product_sync=$wpdb->prefix ."woovisma_order_sync";
        $sql="SELECT * FROM {$tbl_product_sync} WHERE order_id='{$order_id}'";
        woovisma_addlog("PushOrder-SELECT order_id SQL");
        $arrSyncData = $wpdb->get_results($sql);
        $rorder_id=false;
        $alreadySynced=false;
        if(isset($arrSyncData[0]))
        {
            $alreadySynced=true;
            $rorder_id=$arrSyncData[0]->rorder_id;
        }
        woovisma_addlog("PushOrder-Preparing Datastructure for pushing order");
            $DSOrder=new DSVismaOrder("Amount", "CustomerId", "CurrencyCode", "VatAmount", "RoundingsAmount", "InvoiceCity", "InvoiceCountryCode", "InvoiceCustomerName", "InvoicePostalCode", "EuThirdParty", "CustomerIsPrivatePerson", "OrderDate", "Status", "RotReducedInvoicingType", "ReverseChargeOnConstructionServices");
            /*
            * $DSOrder->OurReference value should not exceed 50 characters.
            */
            //$DSOrder->OurReference="Order ID: ".$order_id.", Payment Method: ".get_post_meta( $order_id, '_payment_method', true ).' ('.current_time('Y-m-d').')';
            $DSOrder->OurReference="ID: ".$order_id.", Method: ".get_post_meta( $order_id, '_payment_method', true ).' ('.current_time('Y-m-d').')';
            $DSOrder->OurReference=substr($DSOrder->OurReference,0,50);
            $DSOrder->Amount=$objWCOrder->get_total();
            $orderAddress=$objWCOrder->get_address("billing");
            $shippingAddress=$objWCOrder->get_address("shipping");
            woovisma_addlog("PushOrder-Checking whether order placed by guest user or existing user");
            $customer_number=get_post_meta( $order_id, "billing_ssn", true );
            $rcustid=false;
            if(!isset($objWCOrder->customer_user) || empty($objWCOrder->customer_user)) 
            {
                woovisma_addlog("Customer not set in woocommerce order");
                if(!isset($objWCOrder->billing_email))
                {
                    if(isset($objWCOrder->ID))
                        uniwinMessage("The order ".($objWCOrder->ID)." is not valid");
                    else
                        uniwinMessage("The order is not valid"); 
                }
                else
                {
                    woovisma_addlog("customer not exist");
                    $objCustomer=new Woovisma_Customer();
                    $objCustomer->init();
                    woovisma_addlog("Customer creation started");
                    $arrCustData=array();
                    $arrCustData["billing_email"]=$objWCOrder->billing_email;
                    $arrCustData["billing_first_name"]=$objWCOrder->billing_first_name;
                    $arrCustData["billing_last_name"]=$objWCOrder->billing_last_name;
                    $arrCustData["billing_address_1"]=$objWCOrder->billing_address_1;
                    $arrCustData["billing_address_2"]=$objWCOrder->billing_address_2;
                    $arrCustData["billing_city"]=$objWCOrder->billing_city;
                    $arrCustData["billing_state"]=$objWCOrder->billing_state;
                    $arrCustData["billing_country"]=$objWCOrder->billing_country;
                    $arrCustData["billing_phone"]=$objWCOrder->billing_phone;
                    $arrCustData["billing_postcode"]=$objWCOrder->billing_postcode;
                    $arrCustData["billing_company"]=$objWCOrder->billing_company;
                    ///if ssn field exist in invoice
                    if(isset($customer_number) && !empty($customer_number))
                    {
                        woovisma_addlog("Customer Number is ".$customer_number, true);
                        $arrCustData["customer_number"]=$customer_number;
                    }
                    woovisma_addlog($arrCustData);
                    ///get customer id from visma by not pushing customer from wordpress. Because, the order is created by guest user
                    $rcustid=$objCustomer->createCustomer($arrCustData);
                    woovisma_addlog("Customer created and remote id is {$rcustid}");
                    woovisma_addlog("The remote id is {$rcustid}",true);
                }
            }
            ///if the customer already exist in wordpress, push to visma.
            else
            {
                $objCustomer=new Woovisma_Customer();
                $objCustomer->init();
                woovisma_addlog("Customer pushing started");
                woovisma_addlog($objWCOrder->customer_user);
                ///if ssn field exist in invoice
                if(isset($customer_number) && !empty($customer_number))
                {
                    $rcustid=$objCustomer->pushCustomer($objWCOrder->customer_user,false,$customer_number);
                }
                else
                {
                    $rcustid=$objCustomer->pushCustomer($objWCOrder->customer_user);
                }
                if(empty($rcustid))
                {
                    woovisma_addlog("Customer push failed");
                    woovisma_addlog("The failed customer is {$objWCOrder->customer_user}",true);
                }
                else
                {
                    woovisma_addlog("Customer pushed");
                    woovisma_addlog("The remote id of customer({$objWCOrder->customer_user}) is {$rcustid}",true);
                }
            }
            if($rcustid===false) 
            {
                woovisma_addlog("Customer not exist in remote");
                $this->arrMessage["order"][$order_id][]="Customer not exist in visma";
                return false;
            }
            woovisma_addlog("Customer pushed and remote id is {$rcustid}");
            $DSOrder->CustomerId=$rcustid;
            $DSOrder->CurrencyCode=$objWCOrder->order_currency;
            $woocommerce_tax=getConfigVal("woocommerce_tax");
            $DSOrder->Amount=$objWCOrder->get_total();
            if(!$DSOrder->Amount===0 && empty($DSOrder->Amount))
            {
                woovisma_addlog("Amount is empty");
                $this->arrMessage["order"][$order_id][]="Amount is empty";
                return false;
            }
            $DSOrder->InvoiceAddress1=$orderAddress["address_1"];
            $DSOrder->InvoiceAddress2=$orderAddress["address_2"];
            $DSOrder->InvoiceCity=$orderAddress["city"];
            $DSOrder->InvoiceCountryCode=$orderAddress["country"];
            $DSOrder->InvoiceCustomerName=$orderAddress["first_name"]." ".$orderAddress["last_name"];
            $DSOrder->InvoicePostalCode=$orderAddress["postcode"];
            $DSOrder->DeliveryAddress1=$shippingAddress["address_1"];
            $DSOrder->DeliveryAddress2=$shippingAddress["address_2"];
            $DSOrder->DeliveryCity=$shippingAddress["city"];
            $DSOrder->DeliveryCountryCode=$shippingAddress["country"];
            $DSOrder->DeliveryCustomerName=$shippingAddress["first_name"]." ".$shippingAddress["last_name"];
            $DSOrder->DeliveryPostalCode=$shippingAddress["postcode"];
            $DSOrder->EuThirdParty="true";
            $DSOrder->CustomerIsPrivatePerson="true";
            $DSOrder->OrderDate=$objWCOrder->order_date;
            $DSOrder->Status=1;
            $DSOrder->RotReducedInvoicingType="Normal";
            $DSOrder->ReverseChargeOnConstructionServices="false";
           
           

            $arrDSItem=array();
            if($rorder_id!==false)
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
            woovisma_addlog("Order Object");
            woovisma_addlog($objWCOrder);
            woovisma_addlog("Shipping cost: ".$objWCOrder->order_shipping);
            woovisma_addlog("Order Tax: ".$objWCOrder->order_tax);
            $arrItems=$objWCOrder->get_items();
            woovisma_addlog("Items to be added");
            woovisma_addlog($arrItems); 
            require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaClient.php');
            require_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/includes/class-woovisma-product.php");
            $taxAmount=0;
            $DSOrder->Amount=0;
            if($arrItems)
            {
                ///update with the visma order's line item id. Later keep the line item updated with remote litem. And remove others
                $client=WooVismaClient::getInstance(OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
                $count=0;
                foreach($arrItems as $item_id=>$itemData)
                {
                    $itemData=processItemData($itemData);
                    woovisma_addlog($itemData);
                    $objProd=new Woovisma_Product();
                    $objProd->init($client);
                    woovisma_addlog("Product sync started");
                    woovisma_addlog("Variation".$itemData["variation_id"]);
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
                        $this->arrMessage["order"][$order_id][]="Product not exist in  remote";
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
                    $DSOrder->Rows[]=array(
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
                    woovisma_addlog("Order amount on each product accumulated = precious amount:{$DSOrder->Amount} + product price after discount:{$itemData["line_total"]}");
                    $DSOrder->Amount=$DSOrder->Amount+$itemData["line_total"]; 
                }
            }
            if($objWCOrder->order_shipping)
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
                    $productUnitPrice=$objWCOrder->order_shipping;
                    woovisma_addlog("Product Tax Amount: {$objWCOrder->order_shipping_tax}");
                    $productTaxAmount=$objWCOrder->order_shipping_tax;
                    woovisma_addlog("Product Unit Price: {$productUnitPrice}+{$productTaxAmount}");
                    /*$prodTaxCode=$objArticleCode->getProductTaxRateCodeByPostID($postID);
                    $taxPercentage=$objArticleCode->getTaxPercentageByCode($prodTaxCode);*/
                    $DSOrder->Rows[]=array(
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
                    woovisma_addlog("Invoice amount on each product accumulated = precious amount:{$DSOrder->Amount} + {$productUnitPrice}+{$productTaxAmount}");
                    $DSOrder->Amount=$DSOrder->Amount+$productUnitPrice;
                }
            }
            ///add tax with the amount
            woovisma_addlog("Order amount is amount:{$DSOrder->Amount}+tax:{$taxAmount}");
            $DSOrder->Amount=round($DSOrder->Amount+$taxAmount); 
            $DSOrder->VatAmount=round($taxAmount,2);
			$DSOrder->DeliveredVatAmount=round($taxAmount,2);
            $DSOrder->RoundingsAmount=$DSOrder->Amount;
            if(!$DSOrder->isValid())
            {
                return false;
            }
            woovisma_addlog("Order datastructure is ".print_r($DSOrder,true));
            $this->syncOrder($DSOrder, $order_id);
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
        $arrOrderNotSynced=array();
        ///if $arrSyncid is not false, skip all the preprocess and sync directly.  Since partly executed sync, the synced time should not be updated.
        if($arrSyncID!==false)
        {
            foreach($arrSyncID as $orderID)
            {
                $ret=$this->pushOrder($orderID);
                if($ret===false)
                {
                    $arrOrderNotSynced[]=$orderID;
                }
            }
            return $arrOrderNotSynced;
        }

        woovisma_addlog("woocommerce to order sync started");
        global $wpdb;
        $tbl_order_sync=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_order_sync";

        /*$tbl_settings=$wpdb->prefix .WOOVISMA_PLUGIN_DIRECTORY."_settings";

        $objucSettings=Woovisma_Settings::getInstance();
        $startTime=time();

        $modifiedTime=$objucSettings->getData("woocommerce_order_modified_time","0000-00-00 00:00:00");*/

        $tbl_settings=$wpdb->prefix ."woovisma_settings";

        $now = new DateTime();

        $datesent=$now->format('Y-m-d H:i:s');  

        $sql="SELECT data FROM $tbl_settings where name='woocommerce_order_modified_time' ";

        $arrRow=$wpdb->get_results($sql);

        if(empty($arrRow))

        {woovisma_addlog("Fresh sync");

            $sql = "Insert INTO $tbl_settings(data,name) Values ('$datesent','woocommerce_order_modified_time')";

            $wpdb->query($sql);

        $args = array('post_type' => array('shop_order'),'post_status'=>array('wc-completed','wc-on-hold','wc-processing','wc-pending'), 'nopaging' => true, 'fields' => 'ids');
        
        $order_ids = new WP_Query($args);

            foreach($order_ids->posts as $order_id)

            {

                $success=$this->pushOrder($order_id);

                if($success===false)

                {

                    $arrOrderNotSynced[]=$orderID;

                    continue;

                }

            }

			

			///if order updated partly, the modified time should not be updated. else the failed order never synced unless there is a modification in order

			if(!empty($arrOrderNotSynced)) return $arrOrderNotSynced;

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

           

            $order_ids = new WP_Query($args);

            if(($order_ids->post_count)==0)

            {

                woovisma_addlog("no order exist for sync");

                wp_redirect( add_query_arg(array(  'page' => 'woo-visma-settings',"tab"=>"manual",'product_sync_status' => '97' ),admin_url( 'admin.php' ) ) );

                exit;

            }

            else

            {woovisma_addlog("orders exist for sync");

        foreach($order_ids->posts as $order_id)
        {
            $success=$this->pushOrder($order_id);
            if($success===false)
            {
                $arrOrderNotSynced[]=$orderID;
                continue;
            }
        }

				woovisma_addlog("End not empty order_ids");

        ///if order updated partly, the modified time should not be updated. else the failed order never synced unless there is a modification in order
        if(!empty($arrOrderNotSynced)) return $arrOrderNotSynced;

				

            }

        woovisma_addlog("end not empty arrRow");

        }

        $date=date('Y-m-d h:i:s');

        $newDate = strtotime($date) - 1;

        $date=date('Y-m-d h:i:s',$newDate);



        $sql="Update $tbl_settings SET `data` = '$date' where `name`= 'woocommerce_order_modified_time' ";

        

        $wpdb->query($sql);



        woovisma_addlog("End bulkPushOrder");

        

        return true;
    }
}
?>

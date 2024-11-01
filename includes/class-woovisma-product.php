<?php

class Woovisma_Product
{
    protected $client=null;
    protected $productCodes=array();
    protected $productUnits=array();
    protected $initialized=false;
    public function __construct() 
    {
        
    }
    public function init(WooVismaClient &$client)
    {
        if($this->initialized) return;
        $this->client=$client;
        $objArticleCode=Woovisma_ArticleCode::getInstance();
        woovisma_addlog("Load Product From Visma started");
        $objArticleCode->loadFromVisma($client);
        woovisma_addlog("Load Product From Visma end and save");
        $objArticleCode->save();
        woovisma_addlog("Load Product From Visma saved");
        $this->productCodes=$objArticleCode->getProductCodes();
            woovisma_addlog("Product codes retrieved as : ".print_r($this->productCodes,true));
        $this->productUnits=$this->client->getProductUnits();
        woovisma_addlog("Product Units retrieved as ".print_r($this->productUnits,true));
    }
    public function getInstance()
    {
        static $obj=null;
        if(is_null($obj))
        {            
            $obj=new Woovisma_Product();
        }
        return $obj;
    }
    public function getEmptyProductIfNotExist($sku,$name)
    {
        $objDSVismaProduct=new DSVismaProduct();
        $UnitId=$this->getUnitIdByCode("PCE");
        $objDSVismaProduct->getEmptyProduct($sku, $name, $this->getFirstProductCode(), $UnitId);
        $objDSVismaProduct=$this->client->loadProductIDBySKU($objDSVismaProduct);
        return $objDSVismaProduct;
    }
    public function getFirstProductCode()
    {
        $zeroProductCode=$this->getZeroProductCode();
        if($zeroProductCode) return $zeroProductCode["Id"];
        return isset($this->productCodes[0]["Id"])?$this->productCodes[0]["Id"]:false;
    }
    public function getZeroProductCode()
    {
        foreach($this->productCodes as $prodCode)
        {
            if($prodCode["VatRate"]=="0%") return $prodCode;
        }
        return false;
    }
    public function getProductCode($codeID)
    {
        foreach($this->productCodes as $prodCode)
        {
            if($prodCode["Id"]==$codeID) return $prodCode;
        }
        return false;
    }
    public function getFirstUnit()
    {
        return isset($this->productUnits[0]["Id"])?$this->productUnits[0]["Id"]:false;
    }
    public function getUnitIdByName($name)
    {
        foreach($this->productUnits as $units)
        {
            if($units["Name"]==$name) return $units["Id"];
        }
        return false;
    }
    public function getUnitIdByCode($code)
    {
        foreach($this->productUnits as $units)
        {
            if($units["Code"]==$code) return $units["Id"];
        }
        return false;
    }
    public function bulkPushCustomer()
    {
        woovisma_addlog("Inside bulkPushCustomer");
        $args = array(
			'role' => 'customer',
		);
        $customers = get_users( $args );
        woovisma_addlog("Inside bulkPushCustomer End");
    } 
    public function woo_get_product_sku($id)
    {     
        global $wpdb;
        $sql="SELECT meta_value FROM {$wpdb->prefix}postmeta where meta_key='_sku' AND post_id=$id";
        woovisma_addlog($sql);
        $arrRow=$wpdb->get_results($sql);
        woovisma_addlog($arrRow);
        return $arrRow[0]->meta_value;
    }
    public function get_productid_by_sku($sku)
    {   woovisma_addlog("start get_productid_by_sku");
        global $wpdb;
        $sql="SELECT post_id FROM {$wpdb->prefix}postmeta where meta_value='$sku'";
        $arrRow=$wpdb->get_results($sql);
       //woovisma_addlog($arrRow[0]->post_id);
        woovisma_addlog($arrRow);
        if(empty($arrRow))
        {woovisma_addlog("empty of array");
            return false;
        }
        woovisma_addlog($arrRow[0]->post_id);
        woovisma_addlog("end get_productid_by_sku");
        return $arrRow[0]->post_id;
    }
    public  function popProduct($articleID)
    {
        global $wpdb;
        woovisma_addlog("popProduct:Inside ForEach");
        $value=$this->client->getProduct($articleID);
        $tbl_product_sync=$wpdb->prefix ."woovisma_product_sync";
        $sql="SELECT product_id FROM {$tbl_product_sync} WHERE article_id='{$articleID}'";
        $arrProductID = $wpdb->get_results($sql);
        $product_id=false;
        if(!empty($arrProductID[0]->product_id))
        {
            $product_id=$arrProductID[0]->product_id;
        }
        //$product_id= $this->get_productid_by_sku($value["Number"]);
        woovisma_addlog($product_id);
        ///if product  synced already, it will be existing product from visma and updated in woocommerce
        if($product_id!==false)
        {woovisma_addlog("product update");
            ///check for product type
            $objPost=  get_post($product_id);
            $isProductVariant=false;
            $isProductVariable=false;
            ///if there is no product in woocommerce and the old synced product referenece in product sync table, make the product_id to false to recreate the product
            if(empty($objPost))
            {
                $product_id=false;
            }
            else
            {
                if(strpos($objPost->guid,"product_variation"))
                {
                    $isProductVariant=true;
                }
                else
                {
                    $objTmpProduct=wc_get_product($product_id);
                    if($objTmpProduct->product_type=="variable")
                    {
                        $isProductVariable=true;
                    }
                }
                if($isProductVariant)
                {
                    $objProductVariation=new WC_Product_Variation($product_id);
                    $sale_price=$objProductVariation->get_sale_price();
                    $regular_price=$objProductVariation->get_regular_price();
                    update_post_meta( $product_id, '_price',  $value["NetPrice"] );
                    if($regular_price<$value["NetPrice"])
                    {
                        update_post_meta( $product_id, '_regular_price',  $value["NetPrice"] );
                    }
                    update_post_meta( $product_id, '_sale_price',  $value["NetPrice"] );
                    update_post_meta( $product_id, '_sku', deprefixWord($value["Number"]) );
                    update_post_meta( $product_id, '_stock', $value["StockBalance"] );
                    //$objProductVariation->set_stock($value["StockBalance"]);trace($objProductVariation);
                }
                else if($isProductVariable)
                {
                    $objProduct=wc_get_product($product_id);
                    ///if the return value is empty, the product id is not valid. so skip the sync
                    if(empty($objProduct)) return false;;
                    $post=array('ID'=>$product_id,'post_status'=>'publish','post_type'=>'product','post_title'=> $value["Name"]);
                    $post_id = wp_update_post( $post, true );
                    woovisma_addlog($wpdb->last_query);
                    update_post_meta( $post_id, '_sku', deprefixWord($value["Number"]) );
                    update_post_meta( $post_id, '_stock', $value["StockBalance"] );
                    $obj=Woovisma_ArticleCode::getInstance();
                    //$obj->setProductArticleCode($value["CodingId"], $post_id);
                }
                else
                {
                    $objProduct=wc_get_product($product_id);
                    ///if the return value is empty, the product id is not valid. so skip the sync
                    if(empty($objProduct)) return false;
                    $sale_price=$objProduct->get_sale_price();
                    $regular_price=$objProduct->get_regular_price();
                    $post=array('ID'=>$product_id,'post_status'=>'publish','post_type'=>'product','post_title'=> $value["Name"]);
                    $post_id = wp_update_post( $post, true );
                    woovisma_addlog($wpdb->last_query);
                    update_post_meta( $post_id, '_price',  $value["NetPrice"] );
                    if($regular_price<$value["NetPrice"])
                    {
                        update_post_meta( $post_id, '_regular_price',  $value["NetPrice"] );
                    }
                    update_post_meta( $post_id, '_sale_price',  $value["NetPrice"] );
                    update_post_meta( $post_id, '_sku', deprefixWord($value["Number"]) );
                    update_post_meta( $post_id, '_stock', $value["StockBalance"] );
                    $obj=Woovisma_ArticleCode::getInstance();
                    $obj->setProductArticleCode($value["CodingId"], $post_id);
                }
                woovisma_addlog("product update End");
            }
        }
        ///if product not synced already, it will be new product from visma and createda as simple product in woocommerce
        if($product_id===false)
        {woovisma_addlog("product not synced");
            woovisma_addlog("product details:".print_r($value,true));
            $post = array(
                                        'post_status'  => 'publish',
                                        'post_type'    => 'product',
                                        'post_title'   => $value["Name"]
                                        );
            woovisma_addlog("post details:".print_r($post,true));
            $post_id=wp_insert_post($post,true);
            if (is_wp_error($post_id)) 
            {
                woovisma_addlog("Wordpress Insert Error");
                    $errors = $post_id->get_error_messages();
                    foreach ($errors as $error) 
                    {
                        woovisma_addlog('Product creation error');
                        woovisma_addlog($error);
                    }
                    return false;
            }
            woovisma_addlog("post ID:".print_r($post_id,true)); 
           // woovisma_addlog($wpdb->last_query);
            //update_post_meta( $post_id, '_sale_price',  $value["NetPrice"] );
            update_post_meta( $post_id, '_price',  $value["NetPrice"] );
            update_post_meta( $post_id, '_regular_price',  $value["NetPrice"] );
            update_post_meta( $post_id, '_sku',  deprefixWord($value["Number"]) );
            update_post_meta( $post_id, '_stock',  $value["StockBalance"] );
            woovisma_addlog("product sync End");
            $tbl_product_sync=$wpdb->prefix ."woovisma_product_sync";
            $wpdb->insert($tbl_product_sync, array('product_id' => $post_id, 'article_id' => $value["Id"]));
            $obj=Woovisma_ArticleCode::getInstance();
            $obj->setProductArticleCode($value["CodingId"], $post_id);
        }
        return $product_id;
    }
    function bulkPopProduct()
    {
        global $wpdb;
        woovisma_addlog("started fetching products from visma");

            //$ret=$this->client->getProducts();
            $ret=$this->getVismaProducts();
            if($ret)
            {
                $tbl_product_sync=$wpdb->prefix ."woovisma_product_sync";
                //$wpdb->query("TRUNCATE TABLE  {$tbl_product_sync}");

                woovisma_addlog("products to sync:".print_r($ret,true));
                $count=count($ret);

                    foreach($ret as $key=>$value)
                    { woovisma_addlog("bulkPopProduct:Inside ForEach");
                        $this->popProduct($value["Id"]);
                    }
            }
    }
    function getProductsNotSynced($page=1)
    {
        global $wpdb;
        $tbl_product_sync=$wpdb->prefix ."woovisma_product_sync";
        $sql="Select article_id, product_id from {$tbl_product_sync}";
        $arrObj=$wpdb->get_results($sql);
        $arrSync=array();
        $arrSyncID=array();
        foreach($arrObj as $obj)
        {
            $arrSync[$obj->product_id]=$obj->article_id;
            $arrSyncID[]=$obj->product_id;
        }
        $tbl_settings=$wpdb->prefix ."woovisma_settings";
        $now = new DateTime();
        $datesent=$now->format('Y-m-d H:i:s');  
        //$sql="SELECT data FROM $tbl_settings where `name`='woocommerce_product_sync_time'";
        //$arrRow=$wpdb->get_results($sql); 
        $objucSettings=Woovisma_Settings::getInstance();
        $startTime=time();
        $modifiedTime=$objucSettings->getData("woocommerce_customer_modified_time","0000-00-00 00:00:00");
        if($modifiedTime)
        {woovisma_addlog("sync based on the last synced date");
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
                $args = array('post_type' => array('product','product_variation'),'post_status' => array('publish'), 'nopaging' => false, 'posts_per_page' =>10, 'paged'=>$page,  'fields' => 'ids','post__not_in'=>$arrSyncID);
            }
            else
            {
                $args = array('post_type' => array('product','product_variation'),'post_status' => array('publish'), 'nopaging' => false, 'posts_per_page' =>10, 'paged'=>$page,  'fields' => 'ids','post__in'=>$arrSyncID);
            }
            $product_ids = new WP_Query($args);
            $arrSync=array();
            if(($product_ids->post_count)>0)
            {
                woovisma_addlog("products exist for sync");
                foreach($product_ids->posts as $productID)
                {
                    $arrSync[]=$productID;
                }
                woovisma_addlog("End not empty product_ids");
            }
            woovisma_addlog("end not empty arrRow");
            return array("data"=>$arrSync,"page"=>$page,"total"=>$product_ids->found_posts,"pages"=>$product_ids->max_num_pages);
        }
	woovisma_addlog("End bulkPushProduct");
    }
    function automaticSync($post)
    {   
        //$pluginInfo=getWoocommercePluginInfo();
        woovisma_addlog("inside automaticSync");
        woovisma_addlog("post Id:".$post);
        $objProduct=  wc_get_product($post);
        $objProduct=processProductObject($objProduct);
        $arrProductID=array();
        $objArticleCode=Woovisma_ArticleCode::getInstance();
        $productTaxRateID=$objArticleCode->getProductTaxRateCodeByPostID($objProduct->id);
        $arrProductID[]=array("productID"=>$objProduct->id,"productType"=>$objProduct->product_type,"productTaxRateID"=>$productTaxRateID);
        if($objProduct->product_type=="variable")
        {
            $arrChildProduct=$objProduct->get_children();
            foreach($arrChildProduct as $chidProductID)
            {
                $arrProductID[]=array("productID"=>$chidProductID,"productType"=>$objProduct->product_type,"productTaxRateID"=>$productTaxRateID);
            }
        }
        foreach($arrProductID as $arrProd)
        {
            $productID=$arrProd["productID"];
            $productType=$arrProd["productType"];
            $productTaxRateID=$arrProd["productTaxRateID"];
            $objPost=get_post($productID);
            woovisma_addlog($objPost);
            if($objPost->post_status == 'publish')
            {
                if($objPost->post_type == 'product')
                {
                    woovisma_addlog($objPost->post_status);  
                    $this->syncProduct($post,$productType,$objPost->post_type,$productTaxRateID); 
                }
                else if($objPost->post_type=='product_variation')
                {
                    woovisma_addlog($objPost->post_status);  
                    $this->syncProduct($productID,$productType,$objPost->post_type,$productTaxRateID); 
                }
                else
                {
                    //trace("===============");
                }
            }
        }
  
     woovisma_addlog("End Of automaticSync");   
    }
    function getProductInfo($productID)
    {
        //$product = new WC_Product($productID);
        $product = wc_get_product( $productID );
        $product=processProductObject($product);
        $arrProduct["name"]=$product->get_title();
        $skuID=$this->woo_get_product_sku($product->id);
        $arrProduct["sku"]=$skuID;
        return $arrProduct;
    }
    ///if $obDS provided, the values will be filled in that object. if $obDS is not null, this method will understand that this a update request existing in visma
    function &loadVismaArticle($productID,$updatePrice=false,$updateTaxRateID=false,$objDS=null)
    {
        woovisma_addlog("loading visma article for product id {$productID} started"); 
        try
        {
            woovisma_addlog("before get product"); 
            $product = wc_get_product( $productID );
            woovisma_addlog($product); 
            woovisma_addlog("after get product"); 
        }
        catch(Exception $e)
        {
            woovisma_addlog($e); 
        }
        try
        {
            woovisma_addlog("before processProductObject"); 
            $product=processProductObject($product);
            woovisma_addlog("after processProductObject"); 
        }
        catch(Exception $e)
        {
            woovisma_addlog($e); 
        }
        woovisma_addlog($product);
        if(is_null($objDS))
        {
            $objVismaProduct=new DSVismaProduct();
        }
        else
        {
            $objVismaProduct=$objDS;
        }
        $formatted_number =round($product->stock,4);
        $objVismaProduct->StockBalance=$formatted_number;
        $objVismaProduct->StockBalanceAvailable=$formatted_number;
        $options = get_option( 'woovisma_options' );
        if(!is_null($objDS) && isset($options["woovismaoptname"]["stockupdateonly"]) && $options["woovismaoptname"]["stockupdateonly"])
        {
            logthis("Product stock update only option set in the settings");
            return $objVismaProduct;
        }
        $productTitle=$product->get_title();
        if(strlen($productTitle)>50)
        {
            $objVismaProduct->Name=substr($productTitle,0,50);
            uniwinMessage("Product name trimed to 50 characters");
        }
        else
        {
            $objVismaProduct->Name=$productTitle;
        }
        $skuID=$this->woo_get_product_sku($product->id);
        $objVismaProduct->Number=$skuID; 
        //$objVismaProduct->UnitId=$this->getUnitIdByName("Styck");
        $objVismaProduct->UnitId=$this->getUnitIdByCode("PCE");
        if(empty($skuID))
        {
            $objVismaProduct->Number=$product->id; 
        }
        $productPrice=$product->get_price_excluding_tax();
        if($updatePrice!==false) $productPrice=$updatePrice;
        
        $objVismaProduct->NetPrice=$productPrice;
        $objArticleCode=Woovisma_ArticleCode::getInstance();
        
        $productTaxRateID=$objArticleCode->getProductTaxRateCodeByPostID($productID);
        if($updateTaxRateID!==false) $productTaxRateID=$updateTaxRateID;
        $taxRateID=$productTaxRateID;
        
        if(!empty($taxRateID))
        {
            $objVismaProduct->CodingId=$taxRateID;
            $prodGroup=$this->getProductCode($taxRateID);
            $grossPrice=$objVismaProduct->NetPrice + $prodGroup["VatRatePercent"]*$objVismaProduct->NetPrice;
            $objVismaProduct->GrossPrice=round($grossPrice,2);
            $objVismaProduct->NetPrice=round($objVismaProduct->NetPrice,2);
        }
        else
        {
            $objVismaProduct->NetPrice=round($objVismaProduct->NetPrice,2);
            $objVismaProduct->GrossPrice=$objVismaProduct->NetPrice;
            $objVismaProduct->CodingId=$this->getFirstProductCode();
        }
        woovisma_addlog("loading visma article for product id {$productID} started"); 
        return $objVismaProduct;
    }
    function &getVismaProducts($isRefresh=false)
    {
        global $wpdb;
        static $arrProduct=array();
        if($isRefresh)
        {
            $arrProduct=array();
        }
        if(empty($arrProduct))
        {
            /*$tbl_product_sync=$wpdb->prefix ."woovisma_product_sync";
            $tbl_settings=$wpdb->prefix ."woovisma_settings";
            $sql="SELECT product_id,article_id FROM {$tbl_product_sync}";
            $arrRow=$wpdb->get_results($sql);
            $arrWooProduct=array();
            foreach($arrRow as $row)
            {
                $arrWooProduct[$row->article_id]=$row->product_id;
            }*/
            $arrTmpProduct=$this->client->getProducts();
            foreach($arrTmpProduct as $tmpProduct)
            {
                if(!isPrefixed($tmpProduct["Number"])) continue;
                $tmpProduct["Number"]=deprefixWord($tmpProduct["Number"]);
                $arrProduct[$tmpProduct["Number"]]=$tmpProduct;
            }
        }
        woovisma_addlog($arrProduct);
        return $arrProduct;
    }
    function onProductPrefixChange($oldPrefix)
    {
        global $wpdb;
        $arrProduct=array();
        $tbl_product_sync=$wpdb->prefix ."woovisma_product_sync";
        $tbl_settings=$wpdb->prefix ."woovisma_settings";
        $sql="SELECT product_id,article_id FROM {$tbl_product_sync}";
        $arrRow=$wpdb->get_results($sql);
        ///retrieve woocommerce product
        $arrWooProduct=array();
        foreach($arrRow as $row)
        {
            $arrWooProduct[$row->article_id]=$row->product_id;
        }
        ///retrieve visma product
        $arrTmpProduct=$this->client->getProducts();
        foreach($arrTmpProduct as $tmpProduct)
        {
            if(!isset($arrWooProduct[$tmpProduct["Id"]])) continue;
            $skuID=$this->woo_get_product_sku($arrWooProduct[$tmpProduct["Id"]]);
            if(empty($skuID))
            {
                $skuID=$tmpProduct["Id"];
            }
            $objDSProduct=$this->client->getProductDS($tmpProduct["Id"]);
            $objDSProduct->Number = prefixWord($skuID);
            woovisma_addlog($objDSProduct);
            $ret=$this->client->setProduct($objDSProduct);
            woovisma_addlog("end prefix change");
        }
    }
    function syncProduct($productID,$productType=false,$postType=false,$productTaxRateID=false)
    {
        global $wpdb;
        static $arrProduct=array();
        if(empty($arrProduct))
        {
            $arrProduct=$this->getVismaProducts();
            /*$arrTmpProduct=$this->client->getProducts();
            foreach($arrTmpProduct as $tmpProduct)
            {
                $arrProduct[$tmpProduct["Number"]]=$tmpProduct;
            }*/
        }
        $tbl_product_sync=$wpdb->prefix ."woovisma_product_sync";
        $tbl_settings=$wpdb->prefix ."woovisma_settings";
        $sql="SELECT article_id FROM {$tbl_product_sync} WHERE product_id={$productID}";
        woovisma_addlog("SELECT article_id SQL:".$sql);
        $artilcleID = $wpdb->get_results($sql);
        $updatePrice=false;
        $updateTaxRateID=false;
        $options = get_option( 'woovisma_options' );
        woovisma_addlog(__FUNCTION__.":articleID:".print_r($artilcleID,true));
        $isAlreadySynced=true;
        if(!isset($artilcleID[0]) || empty($artilcleID[0]->article_id))
        {
            $skuID=$this->woo_get_product_sku($productID);
            ///to avold duplicate sku in visma. If the sku alread exist, update the sync table
            if($skuID && isset($arrProduct[$skuID]))
            {
                $wpdb->insert($tbl_product_sync, array('product_id' => $productID, 'article_id' => $arrProduct[$skuID]["Id"]));
                $currentArticleID=$arrProduct[$skuID]["Id"];
            }
            else
            {
                $isAlreadySynced=false;
                woovisma_addlog(__FUNCTION__.":product not yet synced");
                if($productType=="variable" && $postType!="product_variation") $updatePrice=0;
                if($postType=="product_variation") $updateTaxRateID=$updateTaxRateID;
                $objVismaProduct=$this->loadVismaArticle($productID,$updatePrice,$updateTaxRateID); 
                $objVismaProduct->Number=  prefixWord($objVismaProduct->Number);
                ///woovisma may have tax id set in woovisma's setting which is not exist in the Visma. 
                ///This is due to not choosing the site id in woovisma's setting after resetting the Visma(after resetting the Visma all the tax ids will change).
                ///Todo: need to address this issue in efficient way
                $objVismaProduct->CodingId=Woovisma_ArticleCode::getInstance()->getProductTaxRateCodeByPostID($productID);
                woovisma_addlog($objVismaProduct); 
                $ret=$this->client->setProduct($objVismaProduct);
                if($ret)
                {
                    woovisma_addlog(__FUNCTION__.":ret from setProduct:".$ret); 
                    $wpdb->insert($tbl_product_sync, array('product_id' => $productID, 'article_id' => $ret));
                    $currentArticleID=$ret;
                    woovisma_addlog($wpdb->last_query);
                    woovisma_addlog("end product sync");
                }
                else
                {
                    woovisma_addlog($arrProductTmp);
                    woovisma_addlog(__FUNCTION__.":ret from setProduct:".$this->client->errMsg);
                    woovisma_addlog($wpdb->last_query);
                    woovisma_addlog("product sync failed");
                    $arrProductTmp=$this->client->getProducts();
                    if($arrProductTmp)
                    {
                        foreach($arrProductTmp as $productTmp)
                        {
                            if($productTmp["Number"]==$productID)
                            {
                                woovisma_addlog("The sync table does not have product info but the Visma is having");
                                $wpdb->insert($tbl_product_sync, array('product_id' => $productID, 'article_id' => $productTmp["Id"]));
                                $currentArticleID=$productTmp["Id"];
                                woovisma_addlog($wpdb->last_query);
                                $isAlreadySynced=true;
                            }
                        }
                    }
                }
            }
        }
        else
        {
            $currentArticleID=$artilcleID[0]->article_id;
        }
        woovisma_addlog("The visma article id is {$currentArticleID}");
        ///if syncing the already synced product
        if($isAlreadySynced)
        {
            woovisma_addlog("product already synced. udpate start");
            if($productType=="variable" && $postType!="product_variation") $updatePrice=0;
            if($postType=="product_variation") $updateTaxRateID=$updateTaxRateID;
            $objDSProduct=$this->client->getProductDS($currentArticleID);
            ///if product not exist in visma and the sync table is having the sync record(unstable situation)
            if($objDSProduct===false)
            {
                ///delete the sync record
                $sql="DELETE FROM {$tbl_product_sync} WHERE product_id={$productID}";
                $wpdb->query($sql);
                return $this->syncProduct($productID, $productType, $postType, $productTaxRateID);
            }
            else
            {
                $objVismaProduct=$this->loadVismaArticle($productID,$updatePrice,$updateTaxRateID,$objDSProduct);
                $objVismaProduct->Id=$currentArticleID; 
                //$objVismaRetProduct=$this->client->getProduct($currentArticleID);
                $objVismaProduct->Number=  prefixWord($objVismaProduct->Number);
                woovisma_addlog($objVismaProduct);
                $ret=$this->client->setProduct($objVismaProduct);
                woovisma_addlog("end not empty articleID");
            }
        }
        return $ret;
    }
    function bulkPushProduct($arrSyncID=false)
    {
        ///if $arrSyncid is not false, skip all the preprocess and sync directly. Since partly executed sync, the synced time should not be updated.
        if($arrSyncID!==false)
        {
            foreach($arrSyncID as $productID)
            {
                $this->syncProduct($productID);
            }
            return;
        }
        woovisma_addlog("woocommerce to visma sync started");
        global $wpdb;
        $tbl_product_sync=$wpdb->prefix ."woovisma_product_sync";
        $tbl_settings=$wpdb->prefix ."woovisma_settings";
        $now = new DateTime();
        $datesent=$now->format('Y-m-d H:i:s');  
        $sql="SELECT data FROM $tbl_settings where name='woocommerce_product_modified_time' ";
        $arrRow=$wpdb->get_results($sql);
        if(empty($arrRow))
        {woovisma_addlog("Fresh sync");
            $sql = "Insert INTO $tbl_settings(data,name) Values ('$datesent','woocommerce_product_modified_time')";
            $wpdb->query($sql);
            $args = array('post_type' => array('product','product_variation'),'post_status' => array('publish'), 'nopaging' => true, 'fields' => 'ids');
            $product_ids = new WP_Query($args);
            foreach($product_ids->posts as $productID)
            {
                    
                $this->syncProduct($productID);
            }
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
            $args = array('post_type' => array('product','product_variation'),'post_status' => array('publish'),'date_query'    => array(
        'column'  => 'post_modified',
        'after' => array('year' => $year,'month' => $month,'day'=>$date,'hour'=>$hour,'minute'=>$min,'second'=>$sec)
    ), 'nopaging' => true, 'fields' => 'ids');
            $product_ids = new WP_Query($args);
            if(($product_ids->post_count)==0)
            {
                woovisma_addlog("no product exist for sync");
                wp_redirect( add_query_arg(array(  'page' => 'woo-visma-settings',"tab"=>"manual",'product_sync_status' => '99' ),admin_url( 'admin.php' ) ) );
                exit;
            }
            else
            {woovisma_addlog("products exist for sync");
                foreach($product_ids->posts as $productID)
                {woovisma_addlog("start not empty product_ids==inside foreach:".$productID);
                        
                        $this->syncProduct($productID);
                }
            woovisma_addlog("End not empty product_ids");
            }
        woovisma_addlog("end not empty arrRow");
        }
        $date=date('Y-m-d h:i:s');
        $newDate = strtotime($date) - 1;
        $date=date('Y-m-d h:i:s',$newDate);

        $sql="Update $tbl_settings SET `data` = '$date' where `name`= 'woocommerce_product_modified_time' ";
        
        $wpdb->query($sql);

	woovisma_addlog("End bulkPushProduct");
    }  
 
}

?>

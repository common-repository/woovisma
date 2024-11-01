<?php
class Woovisma_Product_Variable extends Woovisma_Product
{
    protected $client=null;
    protected $productCodes=array();
    protected $productUnits=array();
    public function __construct() 
    {
    }
    public function init(WooVismaClient $client)
    {
        $this->client=$client;
        $objArticleCode=Woovisma_ArticleCode::getInstance();
        $objArticleCode->loadFromVisma($client);
        $objArticleCode->save();
        $this->productCodes=$objArticleCode->getProductCodes();
        $this->productUnits=$this->client->getProductUnits();
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
    function bulkPopProduct()
    {
        global $wpdb;
        woovisma_addlog("started fetching products from visma");
        $ret=$this->client->getProducts();//trace($ret);
        woovisma_addlog("products to sync:".print_r($ret,true));
        $count=count($ret);
        
            foreach($ret as $key=>$value)
            { woovisma_addlog("bulkPopProduct:Inside ForEach");
                
                $product_id= $this->get_productid_by_sku(deprefixWord($value["Number"]));
                woovisma_addlog($product_id);
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
                            continue;
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
                else 
                {woovisma_addlog("product update");
                    ///check for product type
                    $objProduct=wc_get_product($product_id);
                    ///if the return value is empty, the product id is not valid. so skip the sync
                    if(empty($objProduct)) continue;
                    if($objProduct->product_type=="product_variation")
                    {
                        $post=array('ID'=>$product_id,'post_status'=>'publish','post_type'=>'product','post_title'=> $value["Name"]);
                        $post_id = wp_update_post( $post, true );
                        //woovisma_addlog($wpdb->last_query);
                       // update_post_meta( $post_id, '_sale_price',  $value["NetPrice"] );
                        update_post_meta( $post_id, '_price',  $value["NetPrice"] );
                        update_post_meta( $post_id, '_regular_price',  $value["NetPrice"] );
                        update_post_meta( $post_id, '_sku', deprefixWord($value["Number"]) );
                        update_post_meta( $post_id, '_stock', $value["StockBalance"] );
                        $obj=Woovisma_ArticleCode::getInstance();
                        $obj->setProductArticleCode($value["CodingId"], $post_id);
                    }
                    else
                    {
                        $post=array('ID'=>$product_id,'post_status'=>'publish','post_type'=>'product','post_title'=> $value["Name"]);
                        $post_id = wp_update_post( $post, true );
                        //woovisma_addlog($wpdb->last_query);
                       // update_post_meta( $post_id, '_sale_price',  $value["NetPrice"] );
                        update_post_meta( $post_id, '_price',  $value["NetPrice"] );
                        update_post_meta( $post_id, '_regular_price',  $value["NetPrice"] );
                        update_post_meta( $post_id, '_sku', deprefixWord($value["Number"]) );
                        update_post_meta( $post_id, '_stock', $value["StockBalance"] );
                        $obj=Woovisma_ArticleCode::getInstance();
                        $obj->setProductArticleCode($value["CodingId"], $post_id);
                    }
                    woovisma_addlog("product update End");
                }



            }
    }
    function automaticSync($post)
    {   woovisma_addlog("inside automaticSync");
        woovisma_addlog("post Id:".$post);
        $objProduct=  wc_get_product($post);
        $arrProductID=array();
        if($objProduct->product_type=="simple")
        {
            $arrProductID[]=$objProduct->id;
        }
        else
        {
            $arrProductID=$objProduct->get_children();
        }
        foreach($arrProductID as $productID)
        {
            $objPost=get_post($productID);
            woovisma_addlog($objPost);
            if($objPost->post_status == 'publish')
            {
                if($objPost->post_type == 'product')
                {
                    woovisma_addlog($objPost->post_status);  
                    $this->syncProduct($post); 
                }
                else if($objPost->post_type=='product_variation')
                {
                    woovisma_addlog($objPost->post_status);  
                    $this->syncProduct($productID); 
                }
                else
                {
                    trace("===============");
                }
            }
        }
  
     woovisma_addlog("End Of automaticSync");   
    }
    function syncProduct($productID)
    {
        
        global $wpdb;
        $tbl_product_sync=$wpdb->prefix ."woovisma_product_sync";
        $tbl_settings=$wpdb->prefix ."woovisma_settings";
        $product = new WC_Product($productID);
        $sql="SELECT article_id FROM {$tbl_product_sync} WHERE product_id={$product->id}";
        woovisma_addlog("SELECT article_id SQL:".$sql);
        $artilcleID = $wpdb->get_results($sql);
        $objArticleCode=Woovisma_ArticleCode::getInstance();
        $taxRateID=$objArticleCode->getProductTaxRateCodeByPostID($productID);
        $options = get_option( 'woovisma_options' );
        woovisma_addlog(__FUNCTION__.":articleID:".print_r($artilcleID,true));
        if(empty($artilcleID[0]->article_id))
        {
            woovisma_addlog(__FUNCTION__.":product not yet synced");
            woovisma_addlog($product);
            $objVismaProduct=new DSVismaProduct();
            $objVismaProduct->Name=$product->get_title();
            $skuID=$this->woo_get_product_sku($product->id);
            $objVismaProduct->Number=$skuID;
            $objVismaProduct->UnitId=$this->getUnitIdByName("Styck");
            if(empty($skuID))
            {
                $objVismaProduct->Number=$product->id; 
            }
            /*if($product->sale_price>0)
            {
                $objVismaProduct->NetPrice=$product->sale_price;
            }
            else
            {
                $objVismaProduct->NetPrice=$product->regular_price;
            }*/
            
            $objVismaProduct->NetPrice=$product->get_price_excluding_tax();
            if(!empty($taxRateID))
            {
                $objVismaProduct->CodingId=$taxRateID;
                $prodGroup=$this->getProductCode($taxRateID);
                $grossPrice=$objVismaProduct->NetPrice + $prodGroup["VatRatePercent"]*$objVismaProduct->NetPrice;
                $objVismaProduct->GrossPrice=$grossPrice;
            }
            else
            {
                $objVismaProduct->GrossPrice=$objVismaProduct->NetPrice;
                $objVismaProduct->CodingId=$this->getFirstProductCode();
            }
            //$objVismaProduct->GrossPrice=$objVismaProduct->NetPrice;
            $formatted_number =round($product->stock,4);
            $objVismaProduct->StockBalance=$formatted_number;
            $objVismaProduct->StockBalanceAvailable=$formatted_number;
            woovisma_addlog($objVismaProduct); 
            $ret=$this->client->setProduct($objVismaProduct);
            woovisma_addlog(__FUNCTION__.":ret from setProduct:".$ret);
            $wpdb->insert($tbl_product_sync, array('product_id' => $product->id, 'article_id' => $ret));
	    woovisma_addlog($wpdb->last_query);
            woovisma_addlog("end empty articleID");
        }
        else
        {
            woovisma_addlog("product already synced. udpate start");
            woovisma_addlog($product);
            $objVismaProduct=new DSVismaProduct();
            $objVismaProduct->Name=$product->get_title();
            $objVismaProduct->Id=$artilcleID[0]->article_id; 
            $skuID=$this->woo_get_product_sku($product->id);
            $objVismaProduct->Number=$skuID; 
            $objVismaRetProduct=$this->client->getProduct($artilcleID[0]->article_id);
            //$objVismaProduct->CodingId=$this->getFirstProductCode();
            $objVismaProduct->UnitId=$this->getUnitIdByName("Styck");
            if(empty($skuID))
            {
                $objVismaProduct->Number=$product->id; 
            }
            /*if($product->sale_price>0)
            {
                $objVismaProduct->NetPrice=$product->sale_price;
            }
            else
            {
                $objVismaProduct->NetPrice=$product->regular_price;
            }*/
            $objVismaProduct->NetPrice=$product->get_price_excluding_tax();
            if(!empty($taxRateID))
            {
                $objVismaProduct->CodingId=$taxRateID;
                $prodGroup=$this->getProductCode($taxRateID);
                $grossPrice=$objVismaProduct->NetPrice + $prodGroup["VatRatePercent"]*$objVismaProduct->NetPrice;
                $objVismaProduct->GrossPrice=$grossPrice;
            }
            else
            {
                $objVismaProduct->GrossPrice=$objVismaProduct->NetPrice;
                $objVismaProduct->CodingId=$this->getFirstProductCode();
            }
            //$objVismaProduct->GrossPrice=$product->get_price_including_tax();
            //$objVismaProduct->GrossPrice=$product->SalesPrice;
	    $formatted_number =round($product->stock,4);
            $objVismaProduct->StockBalance=$formatted_number;
            woovisma_addlog($objVismaProduct);
            $ret=$this->client->setProduct($objVismaProduct);

            //$wpdb->insert($tbl_product_sync, array('product_id' => $product->id, 'article_id' => $ret), array('%d', '%s'));
            woovisma_addlog("end not empty articleID");
        }
    }
    function bulkPushProduct()
    {
        woovisma_addlog("woocommerce to visma sync started");
        global $wpdb;
        $tbl_product_sync=$wpdb->prefix ."woovisma_product_sync";
        $tbl_settings=$wpdb->prefix ."woovisma_settings";
        $now = new DateTime();
        $datesent=$now->format('Y-m-d H:i:s');  
        $sql="SELECT data FROM $tbl_settings";
        $arrRow=$wpdb->get_results($sql);
        if(empty($arrRow))
        {woovisma_addlog("Fresh sync");
            $sql = "INSERT INTO $tbl_settings(`data`) values ('$datesent')";
            $wpdb->query($sql);
            $args = array('post_type' => array('product'),'post_status' => array('publish'), 'nopaging' => true, 'fields' => 'ids');
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
            $month=$expDate[1];
            $date=$expDate[2];
            $expTime=explode(":",$lastdate[1]);
            $hour=$expTime[0];
            $min=$expTime[1];
            $sec=$expTime[2];
            $args = array('post_type' => array('product'),'post_status' => array('publish'),'date_query'    => array(
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

        $sql="Update $tbl_settings SET `data` = '$date'";
        $wpdb->query($sql);

	woovisma_addlog("End bulkPushProduct");
    }  
 
}

?>

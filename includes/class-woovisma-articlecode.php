<?php
class Woovisma_ArticleCode
{
    protected $productCodes=array();
    public function __construct() 
    {
        
    }
    public static function &getInstance()
    {
        static $objArticleCode=null;
        if(is_null($objArticleCode))
        {
            $objArticleCode=new Woovisma_ArticleCode();
        }
        return $objArticleCode;
    }
    public function product_taxrate_save($post_id)
    {
        if(isset($_REQUEST["action"]) && $_REQUEST["action"]=="save_woovisma_sync") return;
        if(isset($_REQUEST["action"]) && $_REQUEST["action"]=="save_woovisma_sync_status") return;
        $objPost=get_post($post_id);
        if ($objPost->post_type != 'product' || $objPost->post_status != 'publish') 
        {
            woovisma_addlog($objPost->post_status);  
            return;
        }
        //$obj= Woovisma_ArticleCode::getInstance();
        if(isset($_REQUEST["woovismaProductTaxRate"]))
        {
            $this->setProductArticleCode($_REQUEST["woovismaProductTaxRate"],$post_id);
        }
    }
    public function load()
    {
        global $wpdb;
        if(!empty($this->productCodes)) return true;
        $tbl_articlecode=$wpdb->prefix ."woovisma_articlecode";
        $sql = "SELECT `id`,`name`,`type`,`vatrate`,`isactive`,`vatratepercent` FROM  `$tbl_articlecode`";
        $arrRec=$wpdb->get_results($sql,"ARRAY_A");
        foreach($arrRec as $ind=>$rec)
        {
            $this->productCodes[$ind]["Id"]=$rec["id"];
            $this->productCodes[$ind]["Name"]=$rec["name"];
            $this->productCodes[$ind]["Type"]=$rec["type"];
            $this->productCodes[$ind]["VatRate"]=$rec["vatrate"];
            $this->productCodes[$ind]["IsActive"]=$rec["isactive"];
            $this->productCodes[$ind]["VatRatePercent"]=$rec["vatratepercent"];
        }
    }
    public function loadFromVisma(WooVismaClient &$client)
    {
        woovisma_addlog("retrieve product codes from remote");
        $this->productCodes=$client->getProductCodes();
        woovisma_addlog("product code retrieved");
        woovisma_addlog($this->productCodes,true);
    }
    public function getProductCodes()
    {
        $this->load();
        return $this->productCodes;
    }
    public function setSiteWideArticleCode($articleID)
    {
        $options = get_option( 'woovisma_options' );
        $options["site_wide_article_id"]=$articleID;
        update_option( 'woovisma_options', $options );
        woovisma_addlog("sitewide article ID updated");
    }
    public function getProductArticleCode($postID)
    {
        global $wpdb;
        $tbl_product_taxrate=$wpdb->prefix ."woovisma_product_taxrate";
        $sql=$wpdb->prepare("SELECT * FROM $tbl_product_taxrate WHERE `product_id`=%s",array($postID));
        $arrRow=$wpdb->get_results($sql);
        woovisma_addlog($arrRow);
        return isset($arrRow[0]->tax_id)?$arrRow[0]->tax_id:false;
    }
    public function setProductArticleCode($taxID,$postID)
    {
        global $wpdb;
        $tbl_product_taxrate=$wpdb->prefix ."woovisma_product_taxrate";
        $sql=$wpdb->prepare("SELECT * FROM $tbl_product_taxrate WHERE `product_id`=%s",array($postID));
        $arrRow=$wpdb->get_results($sql);
        if(empty($arrRow))
        {woovisma_addlog("Article code new insert");
            $sql = $wpdb->prepare("INSERT INTO $tbl_product_taxrate (`product_id`,`tax_id`) values (%s,%s)",array($postID,$taxID));
            $wpdb->query($sql);
        }
        else
        {
            $sql = $wpdb->prepare("UPDATE $tbl_product_taxrate SET `tax_id`=%s WHERE `product_id`=%s",array($taxID,$postID));
            $wpdb->query($sql);
        }
        woovisma_addlog("article ID specific to product updated");
    }
    public function getSiteWideArticleCode()
    {
        $options = get_option( 'woovisma_options' );
        if(isset($options["product-group"])) return $options["product-group"];
        return false;
    }
    function getDefaultTaxCode()
    {   woovisma_addlog("Start getDefaultTaxCode");
        $this->load();
        foreach($this->productCodes as $productCode)
        {
            if($productCode["VatRatePercent"]=="0" && $productCode["Type"]=="Goods")
            {   
                woovisma_addlog($productCode["Name"]);
                return $productCode["Id"];
            }
         }
    }
    public function getZeroTaxCode()
    {
        $this->load();
        foreach($this->productCodes as $productCode)
        {
            if($productCode["VatRatePercent"]==0) return $productCode["Id"];
        }
        return false;
    }
    public function getTaxNameByCode($code)
    {
        $this->load();
        foreach($this->productCodes as $productCode)
        {
            if($productCode["Id"]==$code) return $productCode["Name"];
        }
        return false;
    }
    public function getTaxPercentageByCode($code)
    {
        $this->load();
        foreach($this->productCodes as $productCode)
        {
            if($productCode["Id"]==$code) return $productCode["VatRatePercent"];
        }
        return false;
    }
    public function getProductTaxRateCodeByPostID($postID)
    {
        $productTaxCode=$this->getProductArticleCode($postID);
        if(empty($productTaxCode))
        {
            $productTaxCode=$this->getDefaultTaxCode();
            //woovisma_addlog("sitewide article code is {$productTaxCode}");
			woovisma_addlog("another empty of productTaxCode");
          
            
        }
        /**
         * if site wide productTaxCode also emtpy
         */
        if(empty($productTaxCode))
        {
            return false;
        }
        else
        {
            ///check the code is valid
            if($this->getTaxNameByCode($productTaxCode))
            {
                return $productTaxCode;
            }
            else
            {
                woovisma_addlog("article code is invalid and returning zero tax code");
                return $this->getZeroTaxCode();
            }
        }
    }
    function getTaxRateDropdownForProduct($defaultValue="")
    {
        $this->load();
        $groups=$this->productCodes;
        ob_start();
        echo '<select name="woovismaProductTaxRate">';
        if(empty($defaultValue))
            echo '<option selected value="">- None -</option>';
        else
            echo '<option value="">- None -</option>';
        if(is_array($groups)){
                foreach($groups as $group){
                        $groupnames[$group["Id"]] = $group["Name"];

                        if($defaultValue == $group["Id"]){
                                echo '<option selected value='.$defaultValue.'>'.$group["Name"].'</option>';
                        }else{
                                echo '<option value='.$group["Id"].'>'.$group["Name"].'</option>';
                        }
                }
        }else{
                //$groupnames[$group["Id"]] = $group["Name"];
                echo '<option selected value='.$defaultValue.'>'.$group["Name"].'</option>';
        }
        echo '</select>';
        return ob_get_clean();
    }
    function getProductGroupDropdown($defaultValue="")
    {
        $this->load();
        $groups=$this->productCodes;
        ob_start();
        echo '<select name="productGroup">';
        if(empty($defaultValue))
            echo '<option selected value="">- None -</option>';
        else
            echo '<option value="">- None -</option>';
        if(is_array($groups)){
                foreach($groups as $group){
                        $groupnames[$group["Id"]] = $group["Name"];

                        if($defaultValue == $group["Id"]){
                                echo '<option selected value='.$defaultValue.'>'.$group["Name"].'</option>';
                        }else{
                                echo '<option value='.$group["Id"].'>'.$group["Name"].'</option>';
                        }
                }
        }else{
                //$groupnames[$group["Id"]] = $group["Name"];
                echo '<option selected value='.$defaultValue.'>'.$group["Name"].'</option>';
        }
        echo '</select>';
        return ob_get_clean();
    }
    function visma_product_group($post) 
    {
        //include_once("class-economic-api.php");
        // Add a nonce field so we can check for it later.
        //wp_nonce_field( 'economic_productGroup_save_meta_box_data', 'economic_productGroup_meta_box_nonce' );
        //wp_nonce_field( 'woovisma' );
        $options = get_option('woovisma_options');
        $siteWideArticleCode =  $this->getSiteWideArticleCode();
        //echo  'woocommerce-visma-integration: ';
        $productCode=$this->getProductArticleCode($post->ID);
        if($productCode===false)
        {
            if($siteWideArticleCode)
            {
                echo $this->getTaxRateDropdownForProduct($siteWideArticleCode);
            }
            else
            {
                echo $this->getTaxRateDropdownForProduct();
            }
        }
        else
        {
            echo $this->getTaxRateDropdownForProduct($productCode);
        }
        echo "<br />";
        /*if(!$siteWideArticleCode)
        {
            echo "<b>Sitewide Tax Rate</b> is not set";
        }
        else
        {
            echo "<b>SitewideTax Rate</b> is ". $this->getTaxNameByCode($siteWideArticleCode);
        }
        echo "<hr /> (If Product tax rate not set, the sitewide tax rate will be used to sync with Visma)";*/
    }
    public function save()
    {
        global $wpdb;
        $tbl_articlecode=$wpdb->prefix ."woovisma_articlecode";
        //$wpdb->query("TRUNCATE TABLE `{$tbl_articlecode}`");
        if(empty($this->productCodes)) return true;
        woovisma_addlog("saving product codes started");
        foreach($this->productCodes as $prodCode)
        {
            if(!isset($prodCode["Id"])) continue;
            $sql = "SELECT * FROM  $tbl_articlecode WHERE `id`='{$prodCode["Id"]}'";
            woovisma_addlog($sql,true);
            $arrRec=$wpdb->get_results($sql,"ARRAY_A");
            if($arrRec)
            {
                $sql="UPDATE `$tbl_articlecode` SET `name`='{$prodCode["Name"]}',`type`='{$prodCode["Type"]}',`vatrate`='{$prodCode["VatRate"]}',`isactive`='{$prodCode["IsActive"]}',`vatratepercent`='{$prodCode["VatRatePercent"]}' WHERE `id`='{$prodCode["Id"]}'";
            }
            else
            {
                $sql="INSERT INTO `$tbl_articlecode` (`id`,`name`,`type`,`vatrate`,`isactive`,`vatratepercent`) VALUES('{$prodCode["Id"]}','{$prodCode["Name"]}','{$prodCode["Type"]}','{$prodCode["VatRate"]}','{$prodCode["IsActive"]}','{$prodCode["VatRatePercent"]}')";
            }
            woovisma_addlog($sql,true);
            $wpdb->query($sql);
        }
        woovisma_addlog("saving product codes end");
    }
}
?>
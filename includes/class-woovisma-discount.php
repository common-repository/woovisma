<?php
class Woovisma_Discount
{
    public $arrProduct=array();
    public  function __cosntruct()
    {
        
    }
    public function addProductInfo($productid,$subtotal,$total,$qty)
    {
        $this->arrProduct[$productid]=array("subtotal"=>$subtotal,"total"=>$total,"qty"=>$qty);
        /*$discountPercentage=0;
        woovisma_addlog("Product unit price before discount calculation: {$itemData["line_subtotal"]}/{$itemData["qty"]}");
        $productUnitPriceBeforeDiscount=$itemData["line_subtotal"]/$itemData["qty"];
        
        $discountPercentage=round($discountPercentage,2);
        $productUnitPrice=$objP->get_price_excluding_tax();
        $prodTaxCode=$objArticleCode->getProductTaxRateCodeByPostID($itemData["product_id"]);
        $taxPercentage=$objArticleCode->getTaxPercentageByCode($prodTaxCode);
        woovisma_addlog("Product Tax Amount: {$taxPercentage}*".$objP->get_price_excluding_tax());
        $productTaxAmount=$taxPercentage*$objP->get_price_excluding_tax();*/
    }
    
    public function addCartTotal()
    {
        
    }
    
    public function getProductDiscountPercentage($productid,$isOrder=true)
    {
        $productData=$this->arrProduct[$productid];
        $productUnitPriceBeforeDiscount=$productData["subtotal"]/$productData["qty"];
        woovisma_addlog("Discount calculation for product  {$productid}: (({$productData["subtotal"]}-{$productData["total"]})/{$productData["qty"]})/{$productUnitPriceBeforeDiscount}");
        $discountPercentage=(($productData["subtotal"]-$productData["total"])/$productData["qty"])/$productUnitPriceBeforeDiscount;
        if($isOrder)
        {
            $discountPercentage=round($discountPercentage,2);
        }
        else
        {
            $discountPercentage=round($discountPercentage,4);
        }
        return $discountPercentage;
    }
}
?>
<?php
class Woovisma_Termsofpayment
{
    protected $termsofpayment=array();
    public function __construct() 
    {
        
    }
    public static function &getInstance()
    {
        static $objTermsofpayment=null;
        if(is_null($objTermsofpayment))
        {
            $objTermsofpayment=new Woovisma_Termsofpayment();
        }
        return $objTermsofpayment;
    }
    public function loadFromVisma(WooVismaClient &$client)
    {
        woovisma_addlog("Terms of payment".print_r($this->termsofpayment,true));
        $this->termsofpayment=$client->getTermsofpayment();
    }
    public function getTermsofpayment()
    {
        return $this->termsofpayment;
    }
}
?>
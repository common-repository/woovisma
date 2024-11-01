<?php
class DSVisma
{
    protected $arrRequiredField=array();
    public $arrField=array();
    public $arrMissingFields=array();
    public function __construct($arrField=array())
    {$this->arrField=$arrField;}
    public function render()
    {
        if(!empty($this->arrField)) return $this->arrField;
        $arrVar=get_object_vars($this);
        $arrVarNew=array();
        foreach($arrVar as $key=>$var)
        {
            if(!is_null($var)) 
            {
                if($key=="arrRequiredField") continue;
                ///if arrray
                /*if(is_array($var))
                {
                    $isArray=true;
                    foreach($var as $k=>$v)
                    {
                        ///if arrray of object
                        if(is_object($v))
                        {
                            $arrVarNew[$key][]=$v->render();
                        }
                        else
                        {
                            $arrVarNew[$key][]=$v;
                        }
                    }
                }
                else
                {*/
                if(is_array($var))
                {
                    $isArray=true;
                }
                    $arrVarNew[$key]=$var;
                //}
            }
        }
        if(isset($isArray))
        {
            //trace($arrVarNew);
        }
        return $arrVarNew;
    }
    public function __get($var)
    {
        if(isset($this->$var)) return $this->$var;
        if(isset($this->arrField[$var])) return $this->arrField[$var];
        return null;
    }
    public function load($productInfo)
    {
        $arrVar=get_object_vars($this);
        foreach($arrVar as $var=>$data)
        {
            if(isset($productInfo[$var]))
            {
                $this->$var=$productInfo[$var];
            }
        }
    }
    public function setRequiredField($field)
    {
        /*$arrObjVar=get_object_vars($this);
        if(isset($arrObjVar[$field]))
        {
            if(!in_array($field, $arrObjVar))
            {*/
                $this->arrRequiredField[]=$field;
           // }
        //}
    }
    public function isValid()
    {
        $this->arrMissingFields=array();
        if($this->arrRequiredField)
        foreach($this->arrRequiredField as $requiredField)
        {
            if(!is_numeric($this->$requiredField) && empty($this->$requiredField))  
            {
                $this->arrMissingFields[]=$requiredField;
                uniwinMessage("required field {$requiredField} missing");
                //woovisma_addlog ("required field missing: {$requiredField}");
                //woovisma_addlog($this->arrRequiredField);
                //woovisma_addlog($this); 
                //return false;
            }
        }
        if(empty($this->arrMissingFields)) return true;
        else
        {
                return false;
            }
        }
    public function getMissingFields()
    {
        return $this->arrMissingFields;
    }
}
class DSVismaProduct extends DSVisma
{
    public $Id;// (Guid, optional): Read-only: Unique Id provided by eAccounting,
    public $IsActive="true";// (boolean),
    public $Number;// (string): Max length: 16 characters,
    public $Name;// (string): Max length: 16 characters,
    public $NameEnglish;// (string, optional): Max length: 16 characters,
    public $NetPrice;// (number, optional): Format: Max 2 decimals,
    public $GrossPrice;// (number, optional): Format: Max 2 decimals,
    public $CodingId="f115d6df-d841-4121-a10c-73d45a84abfd";// (Guid): Source: Get from /v1/articleaccountcodings,
    public $UnitId="9c801400-bd31-4ce5-bac4-4f121b8b74b3";// (Guid): Source: Get from /v1/units,
    public $UnitName;// (string, optional): Purpose: Returns the unit name entered from UnitId,
    public $StockBalance;// (number, optional): Default: 0. Purpose: Sets the stock balance for this article,
    public $StockBalanceReserved;// (number, optional): Purpose: Returns the reserved stock balance for this article,
    public $StockBalanceAvailable;// (number, optional): Purpose: Returns the available stock balance for this article,
    public $ChangedUtc;// (string, optional): Purpose: Returns the last date and time from when a change was made on the article,
    public $HouseWorkType;// (integer, optional)
    public function __construct()
    {}
    public function getEmptyProduct($sku,$name,$productCode,$unitID)
    { 
        $this->Name=$name;
        $this->Number=$sku;
        $this->NetPrice=0.00;
        $this->Amount=0.00;
        $this->GrossPrice=0.00;
        $this->CodingId=$productCode;
        $this->UnitId=$unitID;
        $formatted_number =round(0,4);
        $objVismaProduct->StockBalance=$formatted_number;
        $objVismaProduct->StockBalanceAvailable=$formatted_number;
        return $this;
    }
}
/*class DSVismaCustomer extends DSVisma
{
    public $Id;// (Guid, optional): Read-only: Unique Id provided by eAccounting,
    public $CustomerNumber;// (string, optional): Purpose: Unique identifier. If not provided, eAccounting will provide one,
    public $CorporateIdentityNumber;// (string, optional): Max length: 20 characters,
    public $ContactPersonEmail;// (string, optional): Max length: 255 characters,
    public $ContactPersonMobile;// (string, optional): Max length: 50 characters,
    public $ContactPersonName;// (string, optional): Max length: 100 characters,
    public $ContactPersonPhone;// (string, optional): Max length: 50 characters,
    public $CurrencyCode;// (string, optional): Max length: 50 characters. Default value: Currency of the user company,
    public $GLN;// (string, optional): Max length: 255 characters,
    public $EmailAddress;// (string, optional): Max length: 255 characters,
    public $InvoiceAddress1;// (string, optional): Max length: 50 characters,
    public $InvoiceAddress2;// (string, optional): Max length: 50 characters,
    public $InvoiceCity;// (string): Max length: 50 characters,
    public $InvoiceCountryCode;// (string, optional): Max length: 2 characters,
    public $InvoicePostalCode;// (string): Max length: 10 characters,
    public $DeliveryCustomerName;// (string, optional): Max length: 100 characters,
    public $DeliveryAddress1;// (string, optional): Max length: 50 characters. Purpose: Only used if invoice address differs from delivery address,
    public $DeliveryAddress2;// (string, optional): Max length: 50 characters. Purpose: Only used if invoice address differs from delivery address,
    public $DeliveryCity;// (string, optional): Max length: 50 characters. Purpose: Only used if invoice city differs from delivery city,
    public $DeliveryCountryCode;// (string, optional): Max length: 2 characters. Purpose: Only used if invoice country code differs from delivery country code,
    public $DeliveryPostalCode;// (string, optional): Max length: 10 characters. Purpose: Only used if invoice postal code differs from delivery postal code,
    public $DeliveryMethodId;// (Guid, optional): Source: Get from /v1/deliverymethods,
    public $DeliveryTermId;// (Guid, optional): Source: Get from /v1/deliveryterms,
    public $Name;// (string): Max length: 50 characters,
    public $Note;// (string, optional): Max length: 4000 characters,
    public $ReverseChargeOnConstructionServices;// (boolean, optional): Default: false. Purpose: If true, VatNumber must be set aswell,
    public $WebshopCustomerNumber;// (integer, optional),
    public $MobilePhone;// (string, optional): Max length: 50 characters,
    public $Telephone;// (string, optional): Max length: 50 characters,
    public $TermsOfPaymentId;// (Guid): Source: Get from /v1/termsofpayment,
    public $TermsOfPayment;// (TermsOfPayment, optional): Purpose: Returns the terms of payment model entered from TermsOfPaymentId,
    public $VatNumber;// (string, optional): Max length: 20 characters. Format: 2 character country code followed by 8-12 numbers.,
    public $WwwAddress;// (string, optional): Max length: 255 characters,
    public $LastInvoiceDate;// (string, optional): Purpose: Returns the last invoice date,
    public $IsPrivatePerson;// (boolean),
    public $ChangedUtc;// (string, optional): Purpose: Returns the last date and time from when a change was made on the customer
    public function __construct()//$Name,$InvoiceCity,$InvoicePostalCode,$TermsOfPaymentId,$IsPrivatePerson)
    {
        parent::__construct();
        $this->setRequiredField("InvoiceCity");
        $this->setRequiredField("InvoicePostalCode");
        $this->setRequiredField("Name");
        $this->setRequiredField("TermsOfPaymentId");
        $this->setRequiredField("IsPrivatePerson");
    }
    public function isValid()
    {
        if($this->arrRequiredField)
        foreach($this->arrRequiredField as $requiredField)
        {
            if(empty($requiredField))  return false;
        }
        return true;
    }
}*/
?>
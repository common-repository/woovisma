<?php
class DSVismaInvoice extends DSVisma
{
    public $Id;// (Guid, optional): Read-only: Unique Id provided by eAccounting
    public $Amount;// (number): Format: 2 decimals
    public $CustomerId;// (Guid): Get from /v1/customerlistitems
    public $CurrencyCode;//  (string): Max length: 3 characters
    public $VatAmount;//  (number): Format: 2 decimals
    public $RoundingsAmount;//  (number): Format: 2 decimals
    public $DeliveredAmount;//  (number, optional): Format: 2 decimals
    public $DeliveredVatAmount;//  (number, optional): Format: 2 decimals
    public $DeliveredRoundingsAmount;//  (number, optional): Format: 2 decimals
    public $DeliveryCustomerName;//  (string, optional): Max length: 50 characters
    public $DeliveryAddress1;//  (string, optional): Max length: 50 characters
    public $DeliveryAddress2;//  (string, optional): Max length: 50 characters
    public $DeliveryPostalCode;//  (string, optional): Max length: 10 characters
    public $DeliveryCity;//  (string, optional): Max length: 50 characters
    public $DeliveryCountryCode;//  (string, optional): Max length: 2 characters
    public $YourReference;//  (string, optional): Max length: 50 characters
    public $OurReference;//  (string, optional): Max length: 50 characters
    public $InvoiceAddress1;//  (string, optional): Max length: 50 characters
    public $InvoiceAddress2;//  (string, optional): Max length: 50 characters
    public $InvoiceCity;//  (string)
    public $InvoiceCountryCode;//  (string): Max length: 2 characters
    public $InvoiceCustomerName;//  (string): Max length: 50 characters
    public $InvoicePostalCode;//  (string): Max length: 10 characters
    public $DeliveryMethodName;//  (string, optional): Max length: 50 characters
    public $DeliveryMethodCode;//  (string, optional): Max length: 50 characters
    public $DeliveryTermName;//  (string, optional): Max length: 50 characters
    public $DeliveryTermCode;//  (string, optional): Max length: 50 characters
    public $EuThirdParty;//  (boolean)
    public $CustomerIsPrivatePerson;//  (boolean)
    public $InvoiceDate;//  (string): Format: YYYY-MM-DD
    public $Status;//  (string) = ['Draft' or 'Ongoing' or 'Shipped']
    public $DeliveryDate;//  (string, optional): Format: YYYY-MM-DD. Default: null
    public $HouseWorkAmount;//  (number, optional)
    public $HouseWorkAutomaticDistribution;//  (boolean, optional)
    public $HouseWorkCorporateIdentityNumber;//  (string, optional)
    public $HouseWorkPropertyName;//  (string, optional)
    public $Rows;//  (array[InvoiceRow], optional)
    public $ShippedDateTime;//  (string, optional): Format: YYYY-MM-DD. Default: null
    public $RotReducedInvoicingType;//  (string) = ['Normal' or 'Rot' or 'Rut']
    public $RotPropertyType;//  (integer, optional)
    public $Persons;//  (array[SalesDocumentRotRutReductionPerson], optional)
    public $ReverseChargeOnConstructionServices;//  (boolean)
    
    public function __construct($Amount, $CustomerId, $CurrencyCode, $VatAmount, $RoundingsAmount, $InvoiceCity, $InvoiceCountryCode, $InvoiceCustomerName, $InvoicePostalCode, $EuThirdParty, $CustomerIsPrivatePerson, $InvoiceDate, $Status, $RotReducedInvoicingType, $ReverseChargeOnConstructionServices)
    {
        parent::__construct();
            
        $this->setRequiredField("Amount");
        $this->setRequiredField("CustomerId");
        $this->setRequiredField("CurrencyCode");
        $this->setRequiredField("VatAmount");
        $this->setRequiredField("RoundingsAmount");
        $this->setRequiredField("InvoiceCity");
        $this->setRequiredField("InvoiceCountryCode");
        $this->setRequiredField("InvoiceCustomerName");
        $this->setRequiredField("InvoicePostalCode");
        $this->setRequiredField("EuThirdParty");
        $this->setRequiredField("CustomerIsPrivatePerson");
        $this->setRequiredField("InvoiceDate");
        $this->setRequiredField("Status");
        $this->setRequiredField("RotReducedInvoicingType");
        $this->setRequiredField("ReverseChargeOnConstructionServices");
    }

}
?>
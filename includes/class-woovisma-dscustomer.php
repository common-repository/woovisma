<?php
class DSVismaCustomer extends DSVisma
{
    public $Id;// (Guid, optional): Read-only: Unique Id provided by eAccounting
    public $CustomerNumber;// (string, optional): Purpose: Unique identifier. If not provided, eAccounting will provide one
    public $CorporateIdentityNumber;// (string, optional): Max length: 20 characters
    public $ContactPersonEmail;// (string, optional): Max length: 255 characters
    public $ContactPersonMobile;// (string, optional): Max length: 50 characters
    public $ContactPersonName;// (string, optional): Max length: 100 characters
    public $ContactPersonPhone;// (string, optional): Max length: 50 characters
    public $CurrencyCode;// (string, optional): Max length: 50 characters. Default value: Currency of the user company
    public $GLN;// (string, optional): Max length: 255 characters
    public $EmailAddress;// (string, optional): Max length: 255 characters
    public $InvoiceAddress1;// (string, optional): Max length: 50 characters
    public $InvoiceAddress2;// (string, optional): Max length: 50 characters
    public $InvoiceCity;// (string): Max length: 50 characters
    public $InvoiceCountryCode;// (string, optional): Max length: 2 characters
    public $InvoicePostalCode;// (string): Max length: 10 characters
    public $DeliveryCustomerName;// (string, optional): Max length: 100 characters
    public $DeliveryAddress1;// (string, optional): Max length: 50 characters. Purpose: Only used if invoice address differs from delivery address
    public $DeliveryAddress2;// (string, optional): Max length: 50 characters. Purpose: Only used if invoice address differs from delivery address
    public $DeliveryCity;// (string, optional): Max length: 50 characters. Purpose: Only used if invoice city differs from delivery city
    public $DeliveryCountryCode;// (string, optional): Max length: 2 characters. Purpose: Only used if invoice country code differs from delivery country code
    public $DeliveryPostalCode;// (string, optional): Max length: 10 characters. Purpose: Only used if invoice postal code differs from delivery postal code
    public $DeliveryMethodId;// (Guid, optional): Source: Get from /v1/deliverymethods
    public $DeliveryTermId;// (Guid, optional): Source: Get from /v1/deliveryterms
    public $Name;// (string): Max length: 50 characters
    public $Note;// (string, optional): Max length: 4000 characters
    public $ReverseChargeOnConstructionServices;// (boolean, optional): Default: false. Purpose: If false, VatNumber must be set aswell
    public $WebshopCustomerNumber;// (integer, optional)
    public $MobilePhone;// (string, optional): Max length: 50 characters
    public $Telephone;// (string, optional): Max length: 50 characters
    public $TermsOfPaymentId;// (Guid): Source: Get from /v1/termsofpayment
    public $TermsOfPayment;// (TermsOfPayment, optional): Purpose: Returns the terms of payment model entered from TermsOfPaymentId
    public $VatNumber;// (string, optional): Max length: 20 characters. Format: 2 character country code followed by 8-12 numbers.
    public $WwwAddress;// (string, optional): Max length: 255 characters
    public $LastInvoiceDate;// (string, optional): Purpose: Returns the last invoice date
    public $IsPrivatePerson;// (boolean)
    public $ChangedUtc;// (string, optional): Purpose: Returns the last date and time from when a change was made on the customer
    
    public function __construct()
    {
        parent::__construct();
            
        $this->setRequiredField("InvoiceCity");
        $this->setRequiredField("InvoicePostalCode");
        $this->setRequiredField("Name");
        $this->setRequiredField("TermsOfPaymentId");
        $this->setRequiredField("IsPrivatePerson");
    }

}
?>
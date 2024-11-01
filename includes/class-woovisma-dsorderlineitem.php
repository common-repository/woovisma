<?php
class DSVismaOrderlineitem extends DSVisma
{
    public $Id;// (Guid, optional): Read-only: Unique Id provided by eAccounting
    public $LineNumber;// (number): Format: 2 decimals
    public $DeliveredQuantity;// (number): Format: 2 decimals
    public $ArticleId;// (Guid): Get from /v1/customerlistitems
    public $ArticleNumber;//  (string)
    public $IsTextRow;//  (boolean)
    public $Text;//  (string, optional)
    public $UnitPrice;//   (number, optional): Format: 2 decimals
    public $DiscountPercentage;//  (number, optional): Format: 2 decimals,
    public $Quantity;//  (number, optional): Format: 4 decimals,
    public $WorkCostType;//  (integer, optional)
    public $IsWorkCost;//  (boolean)
    public $IsVatFree;//  (boolean)
    public $CostCenterItemId1;//  (Guid)
    public $CostCenterItemId2;//  (Guid)
    public $CostCenterItemId3;//  (Guid)
    
    public function __construct($LineNumber, $ArticleNumber, $IsTextRow, $Text, $IsWorkCost, $IsVatFree)
    {
        parent::__construct();
            
        $this->setRequiredField("LineNumber");
        $this->setRequiredField("ArticleNumber");
        $this->setRequiredField("IsTextRow");
        $this->setRequiredField("Text");
        $this->setRequiredField("IsWorkCost");
        $this->setRequiredField("IsVatFree");
    }

}
?>
<?php
require_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/visma/OAuth2/Client.php");
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/IGrantType.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/AuthorizationCode.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/RefreshToken.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/DSVisma.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaDBLayer.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaClient.php');
class Woovisma_InvoiceR extends WooVismaClient
{
    public function __construct($client_auth) 
    {
        parent::__construct($client_auth);
    }
    public static function &getInstance($client_auth = self::AUTH_TYPE_URI, $certificate_file = null,$refresh=false)
    {
        static $objInstance=array();
        if($refresh) return new Woovisma_InvoiceR($client_auth, $certificate_file);
        if(!isset($objInstance[$client_auth]))
        {
            $objInstance[$client_auth]=new Woovisma_InvoiceR($client_auth);
        }
        return $objInstance[$client_auth];
    }
       public function getInvoice($id)
    {
        $response = $this->fetch($this->objConfig->apiEndpoint()."/v1/customerinvoicedrafts/".$id, $this->arrParam);
        return $response["result"];
    }
    public function getInvoices($modifiedTime=false)
    {
        static $response=null;
        if(is_null($response))
        {
            $response = $this->fetch($this->objConfig->apiEndpoint()."/v1/customerinvoicelistitems", $this->arrParam);
        }
        return $response["result"];
    }
    public function setInvoice(DSVismaInvoice $invoice)
    {
        try
        {
            if(empty($invoice->Id))
                $response = $this->fetch($this->objConfig->apiEndpoint()."/v1/customerinvoicedrafts",$invoice->render(),OAuth2\Client::HTTP_METHOD_POST,array(), OAuth2\Client::HTTP_FORM_CONTENT_TYPE_APPLICATION);
            else
            {
                $response = $this->fetch($this->objConfig->apiEndpoint()."/v1/customerinvoicedrafts/".$invoice->Id,$invoice->render(),OAuth2\Client::HTTP_METHOD_PUT,array(), OAuth2\Client::HTTP_FORM_CONTENT_TYPE_APPLICATION);
            }
            woovisma_addlog("Response received for invoice insert/update to visma"); 
            woovisma_addlog($response);
            if(isset($response["result"]["Id"]))
            {
                return $response["result"]["Id"];
            }
            else
            {
                //$this->errMsg=$response["result"]["Message"];
                return false;
            }
         }
        catch(Exception $e)
        {
            woovisma_addlog($e);
            return false;
        }
    }
}
?>
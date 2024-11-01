<?php
require_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/visma/OAuth2/Client.php");
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/IGrantType.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/AuthorizationCode.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/RefreshToken.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/DSVisma.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaDBLayer.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaClient.php');
class Woovisma_InvoicelineitemR extends WooVismaClient
{
    public function __construct($client_auth) 
    {
        parent::__construct($client_auth);
    }
    public static function &getInstance($client_auth = self::AUTH_TYPE_URI, $certificate_file = null,$refresh=false)
    {
        static $objInstance=array();
        if($refresh) return new Woovisma_InvoicelineitemR($client_auth, $certificate_file);
        if(!isset($objInstance[$client_auth]))
        {
            $objInstance[$client_auth]=new Woovisma_InvoicelineitemR($client_auth);
        }
        return $objInstance[$client_auth];
    }
       public function getInvoicelineitem($id)
    {
        $response = $this->fetch($this->objConfig->apiEndpoint()."/v1/invoices/".$id, $this->arrParam);
        return $response["result"];
    }
    public function getInvoicelineitems($modifiedTime=false)
    {
        static $response=null;
        if(is_null($response))
        {
            $response = $this->fetch($this->objConfig->apiEndpoint()."/v1/invoicelistitems", $this->arrParam);
        }
        return $response["result"];
    }
    public function setInvoicelineitem(DSVismaInvoicelineitem $invoicelineitem)
    {
        try
        {
            if(empty($invoicelineitem->Id))
                $response = $this->fetch($this->objConfig->apiEndpoint()."/v1/invoices",$invoicelineitem->render(),OAuth2\Client::HTTP_METHOD_POST,array(), OAuth2\Client::HTTP_FORM_CONTENT_TYPE_APPLICATION);
            else
            {
                $response = $this->fetch($this->objConfig->apiEndpoint()."/v1/invoices/".$invoicelineitem->Id,$invoicelineitem->render(),OAuth2\Client::HTTP_METHOD_PUT,array(), OAuth2\Client::HTTP_FORM_CONTENT_TYPE_APPLICATION);
            }
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
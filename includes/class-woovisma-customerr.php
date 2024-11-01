<?php
require_once(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/visma/OAuth2/Client.php");
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/IGrantType.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/AuthorizationCode.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/OAuth2/GrantType/RefreshToken.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/DSVisma.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaDBLayer.php');
                        require_once(WP_PLUGIN_DIR.'/'.WOOVISMA_PLUGIN_DIRECTORY.'/visma/WooVismaClient.php');
class Woovisma_CustomerR extends WooVismaClient
{
    public function __construct($client_auth) 
    {
        parent::__construct($client_auth);
    }
    public static function &getInstance($client_auth = self::AUTH_TYPE_URI, $certificate_file = null,$refresh=false)
    {
        static $objInstance=array();
        if($refresh) return new Woovisma_CustomerR($client_auth, $certificate_file);
        if(!isset($objInstance[$client_auth]))
        {
            $objInstance[$client_auth]=new Woovisma_CustomerR($client_auth);
        }
        return $objInstance[$client_auth];
    }
       public function getCustomer($id)
    {
        $response = $this->fetch($this->objConfig->apiEndpoint()."/v1/customers/".$id, $this->arrParam);
        if($response["code"]!=200) return false;
        return $response["result"];
    }
    public function getCustomers($modifiedTime=false)
    {
        static $response=null;
        if(is_null($response))
        {
            $response = $this->fetch($this->objConfig->apiEndpoint()."/v1/customerlistitems", $this->arrParam);
        }
        return $response["result"];
    }
    public function setCustomer(DSVismaCustomer $customer)
    {
        try
        {
            if(empty($customer->Id))
                $response = $this->fetch($this->objConfig->apiEndpoint()."/v1/customers",$customer->render(),OAuth2\Client::HTTP_METHOD_POST,array(), OAuth2\Client::HTTP_FORM_CONTENT_TYPE_APPLICATION);
            else
            {
                $response = $this->fetch($this->objConfig->apiEndpoint()."/v1/customers/".$customer->Id,$customer->render(),OAuth2\Client::HTTP_METHOD_PUT,array(), OAuth2\Client::HTTP_FORM_CONTENT_TYPE_APPLICATION);
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
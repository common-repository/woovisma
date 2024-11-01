<?php
include_once(__DIR__."/WooVismaDBLayer.php");
include_once(__DIR__."/Config/WooVismaConfig.php");
class WooVismaClient extends OAuth2\Client
{
    protected $arrParam=array();
    protected $wooAccessToken=false;
    protected $wooTokenType=false;
    public $errMsg="";
    protected $objConfig=null;
    public $status="";
    public function __construct( $client_auth = self::AUTH_TYPE_URI, $certificate_file = null)
    {
        $this->objConfig=  WooVismaConfig::getConfigObject();
        $client_id=WooVismaDBLayer::getClientID();
        $client_secret=WooVismaDBLayer::getClientSecret();
        parent::__construct($client_id, $client_secret, $client_auth, $certificate_file);
        $accessToken=false;
        ///if user wants to test the connection, proceed to token request
        if((isset($_POST["submit"]) && $_POST["submit"]=="Authenticate")||(isset($_REQUEST["code"]))){}
        else $accessToken=WooVismaDBLayer::getToken(); 
        if($accessToken!==false)
        {
            woovisma_addlog("access token exist");
            $expire=WooVismaDBLayer::getTokenExpire();
            if($expire<time())
            {
                woovisma_addlog("access token expired");
                $accessToken=false;
                $refreshToken=WooVismaDBLayer::getRefreshToken();
                woovisma_addlog("refresh token: ".$refreshToken);
                $suceed=$this->refreshAuthToken($refreshToken);
                if($suceed===false)
                {
                    $accessToken=false;
                }
                else
                    $accessToken=true;
                $this->status="token_refresh";
            }
        }
        if($accessToken===false)
        {
            woovisma_addlog("access token not set");
            $option=get_option("woovisma_options");
            if (isset($_REQUEST["serialized_token"]) && isset($_REQUEST["redirect"]) && $_REQUEST["redirect"]==1)
            {
                woovisma_addlog("parsing serialized token");
                $this->wooAccessToken=$_REQUEST["serialized_token"];
                $this->wooTokenType=$_REQUEST['token_type'];
                WooVismaDBLayer::setToken($this->wooAccessToken);
                WooVismaDBLayer::setTokenType($this->wooTokenType);
                WooVismaDBLayer::setRefreshToken($_REQUEST['refresh_token']);
                ///reduce 100 from expire time to accomodate script processing time fror fetching the loken
                WooVismaDBLayer::setTokenExpire(time()+3600-100);
                //WooVismaDBLayer::setCode($_GET['code']);
                $this->setAccessToken($this->wooAccessToken);
                if($this->wooTokenType=="Bearer")
                {
                    $this->setAccessTokenType(OAuth2\Client::ACCESS_TOKEN_BEARER);
                }
                $this->arrParam = array( "grant_type"=>"authorization_code", 'redirect_uri' => WooVismaDBLayer::getRedirectUri());
                $this->status="token_new";
                woovisma_addlog("token status set as token_new and the options are :".print_r($option,true));
            }
            else if (!isset($_GET['code']))
            {
                woovisma_addlog("code not set in GET");
                $auth_url = $this->getAuthenticationUrl($this->objConfig->authEndpoint(), WooVismaDBLayer::getRedirectUri());
                woovisma_addlog("Visma Auth URL is : {$auth_url}");
                if(!isset($option["nossl"]) || $option["nossl"]<1)
                {
                    $state="abcduniwin";
                }
                else
                {
                    $state=urlencode(get_home_url());
                }
                woovisma_addlog("Redirected to : ".$auth_url.'&scope='.$this->objConfig->getScope().'&state='.$state);
                header('Location: ' . $auth_url.'&scope='.$this->objConfig->getScope().'&state='.$state);
                exit;
            }
            else
            {
                woovisma_addlog("code set in GET");
                $this->arrParam = array('code' => $_GET['code'], 'redirect_uri' => WooVismaDBLayer::getRedirectUri());
                $response = $this->getAccessToken($this->objConfig->tokenEndpoint(), 'authorization_code', $this->arrParam);
                woovisma_addlog($response);
                $this->wooAccessToken=$response["result"]['access_token'];
                $this->wooTokenType=$response["result"]['token_type'];
                WooVismaDBLayer::setToken($this->wooAccessToken);
                WooVismaDBLayer::setTokenType($this->wooTokenType);
                WooVismaDBLayer::setRefreshToken($response['result']['refresh_token']);
                ///reduce 100 from expire time to accomodate script processing time fror fetching the loken
                WooVismaDBLayer::setTokenExpire(time()+$response['result']['expires_in']-100);
                WooVismaDBLayer::setCode($_GET['code']);
                $this->setAccessToken($this->wooAccessToken);
                if($this->wooTokenType=="Bearer")
                {
                    $this->setAccessTokenType(OAuth2\Client::ACCESS_TOKEN_BEARER);
                }
                $this->arrParam = array('code' => $_GET['code'], "grant_type"=>"authorization_code", 'redirect_uri' => WooVismaDBLayer::getRedirectUri());
                $this->status="token_new";
            }
        }
        else
        {
            $this->wooAccessToken=  WooVismaDBLayer::getToken();
            $this->wooTokenType=  WooVismaDBLayer::getTokenType();
            $this->setAccessToken($this->wooAccessToken);
            if($this->wooTokenType=="Bearer")
            {
                $this->setAccessTokenType(OAuth2\Client::ACCESS_TOKEN_BEARER);
            }
            $this->arrParam = array('code' =>  WooVismaDBLayer::getCode(), "grant_type"=>"authorization_code", 'redirect_uri' => WooVismaDBLayer::getRedirectUri());
            $this->status="token_ready";
        }
    }
    public static function &getInstance($client_auth = self::AUTH_TYPE_URI, $certificate_file = null,$refresh=false)
    {
        static $objInstance=array();
        if($refresh) return new WooVismaClient($client_auth, $certificate_file);
        if(!isset($objInstance[$client_auth]))
        {
            $objInstance[$client_auth]=new WooVismaClient($client_auth, $certificate_file);
        }
        return $objInstance[$client_auth];
    }
    public function refreshAuthToken($refreshToken)
    {
        $options = get_option( 'woovisma_options' );
        if(isset($options["nossl"]) && $options["nossl"]>0)
        {
            $state=urlencode(get_home_url());
            $params=array();
            $params["refreshtoken"]=urlencode($refreshToken);
            $params["state"]=$state;
            $query_string="refreshtoken=".urlencode($refreshToken)."&state=".$state;
            $resp=httpPost("https://uniwin.in", $params);
            $response=json_decode($resp,true);
        }
        else
        {
            $this->arrParam = array('redirect_uri' => WooVismaDBLayer::getRedirectUri(),'refresh_token' => $refreshToken);
            $this->objConfig=  WooVismaConfig::getConfigObject();
            woovisma_addlog("refresh token end point: ".$this->objConfig->tokenEndpoint()."parameters: ".print_r($this->arrParam,true));
            $response = $this->getAccessToken($this->objConfig->tokenEndpoint(), 'refresh_token', $this->arrParam); 
        }
        woovisma_addlog($response);
        woovisma_addlog("-------------------------------");
        if(isset($response["result"]["error"]) && $response["result"]["error"]=="invalid refresh token") return false;
        WooVismaDBLayer::setToken($response['result']['access_token']);
        WooVismaDBLayer::setRefreshToken($response['result']['refresh_token']);
        ///reduce 100 from expire time to accomodate script processing time fror fetching the loken
        WooVismaDBLayer::setTokenExpire(time()+$response['result']['expires_in']-100);
        return true;
    }
    public function getProductCodes() 
    {
        static $response=null;
        if(is_null($response))
        {
            $response = $this->fetch($this->objConfig->apiEndpoint().'/v1/articleaccountcodings', $this->arrParam);
        }
        return $response['result'];
    }
    public function getProductUnits()
    {
        static $response=null;
        if(is_null($response))
        {
            $response = $this->fetch($this->objConfig->apiEndpoint().'/v1/units', $this->arrParam);
        }
        return $response['result'];
    }
    public function getProduct($id)
    {
        $response = $this->fetch($this->objConfig->apiEndpoint().'/v1/articles/'.$id, $this->arrParam);
        return $response['result'];
    }
    public function &getProductDS($id)
    {
        $response = $this->fetch($this->objConfig->apiEndpoint().'/v1/articles/'.$id, $this->arrParam);
        if($response["code"]==200)
        {
            $objDSProduct=new DSVismaProduct();
            foreach($response["result"] as $k=>$v)
            {
                if($k=="IsActive")
                {
                    $objDSProduct->$k=$v?"true":"false";
                }
                else
                {
                    $objDSProduct->$k=$v;
                }
            }
            return $objDSProduct;
        }
        return false;
    }
    public function getProducts()
    {
        static $response=null;
        if(is_null($response))
        {
            $response = $this->fetch($this->objConfig->apiEndpoint().'/v1/articles', $this->arrParam);
        }
        return $response['result'];
    }
    public function getTermsofpayment()
    {
        static $response=null;
        if(is_null($response))
        {
            $response = $this->fetch($this->objConfig->apiEndpoint().'/v1/termsofpayment', $this->arrParam);
        }
        return $response['result'];
    }
    public function getProductBySKU($sku)
    {
        $arrProductTmp=$this->getProducts();
        if($arrProductTmp)
        {
            foreach($arrProductTmp as $productTmp)
            {
                if(isset($productTmp["Number"]) && $productTmp["Number"]==$sku)
                {
                    return $productTmp;
                }
            }
        }
        return false;
    }
    /**
     * @param DSVismaProduct $product
     */
    public function loadProductIDBySKU(DSVismaProduct $product)
    {
        if(!empty($product->Id)) return $product;
        $arrProduct=$this->getProductBySKU($product->Number);
        $productID=false;
        if($arrProduct===false)
        {
            $productID=$this->setProduct($product);
        }
        else
        {
            $productID=$arrProduct["Id"];
        }
        if($productID===false) return false;
        $product->Id=$productID;
        return $product;
    }
    public function setProduct(DSVismaProduct $product)
    {
        if(empty($product->Id))
            $response = $this->fetch($this->objConfig->apiEndpoint().'/v1/articles',$product->render(),OAuth2\Client::HTTP_METHOD_POST,array(), OAuth2\Client::HTTP_FORM_CONTENT_TYPE_APPLICATION);
        else
        {
            $response = $this->fetch($this->objConfig->apiEndpoint().'/v1/articles/'.$product->Id,$product->render(),OAuth2\Client::HTTP_METHOD_PUT,array(), OAuth2\Client::HTTP_FORM_CONTENT_TYPE_APPLICATION);
        }
        woovisma_addlog($response);
        if(isset($response['result']["Id"]))
        {
            return $response['result']["Id"];
        }
        else
        {
            $this->errMsg=isset($response["result"]["Message"])?$response["result"]["Message"]:"Accessing endpoint failed. No response";
            return false;
        }
    }
}
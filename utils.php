<?php
function getModuleInstaller()
{
    $path = __DIR__.'/modules';
    $arrInstall = array();
    // directory handle
    $dir = dir($path);
    while (false !== ($entry = $dir->read())) 
    {
        if ($entry != '.' && $entry != '..') {
           if (is_dir($path . '/' .$entry) && file_exists($path . '/' .$entry."/install.php")) {
                $arrInstall[] = $path . '/' .$entry."/install.php"; 
           }
        }
    }
    return $arrInstall;
}
if(!function_exists("trace"))
{
    function trace($message,$param1=false,$param2=false)
    {
        echo "";
    }
}
function woovisma_addlog($message, $isDeveloperMode=false)
{$options = get_option( 'woovisma_options' );
    $log=getConfigVal("log");//print_r($options["woovismaoptname"]["log"]);exit;
    if(isset($log) && $options["woovismaoptname"]["log"]=="Enabled")
    {
    /*$log=getConfigVal("log");
    if(isset($log) && $log===true)
    {*/
        $developer_mode=false;
        if(isset($options["woovismaoptname"]["developermode"]) && $options["woovismaoptname"]["developermode"]=="Enabled")
            $developer_mode = true;
        if($isDeveloperMode && $developer_mode)
        {
            
        }
        else if(is_object($message))
        {
            if(!$developer_mode) $message="object";
        }
        else if(is_array($message))
        {
            if(!$developer_mode) $message="array";
        }
        else if(strpos($message, "Array")===0)
        {
            if(!$developer_mode) $message="array";
        }
        else if(strpos($message, ":Array") || strpos($message, ": Array"))
        {
            if(!$developer_mode) $message="array";
        }
        else if(strlen($message)>1000)
        {
            if(!$developer_mode) $message = substr($message, 0, 200)."....";
        }
        $message=date("m-d-y h:i:s")." ".print_r($message,true);
        if($developer_mode)
        {
            $message="<b>".get_caller_info()."</b> <br />".$message."<br />";
        }
        file_put_contents(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/woovisma.html", "<br />{$message}<br />",FILE_APPEND); 
    }
}
function get_caller_info() {
    $c = '';
    $file = '';
    $func = '';
    $line="";
    $arg="";
    $class = '';
    $trace = debug_backtrace();
    $stachNo=1;
    if (isset($trace[$stachNo])) {
        $file = $trace[$stachNo-1]['file'];
        $func = $trace[$stachNo]['function'];
        $line = isset($trace[$stachNo]['line'])?$trace[$stachNo]['line']:"";
        $arg = isset($value["args"]) ? $value["args"] : "";
        if ((substr($func, 0, 7) == 'include') || (substr($func, 0, 7) == 'require')) {
            $func = '';
        }
    } else if (isset($trace[$stachNo-1])) {
        $file = $trace[$stachNo-1]['file'];
        $func = '';
    }
    if (isset($trace[$stachNo+1]['class'])) {
        $class = $trace[$stachNo+1]['class'];
        $func = $trace[$stachNo+1]['function'];
        $file = $trace[$stachNo]['file'];
        $line = isset($trace[$stachNo]['line'])?$trace[$stachNo]['line']:"";
        $arg = isset($value["args"]) ? $value["args"] : "";
    } else if (isset($trace[$stachNo]['class'])) {
        $class = $trace[$stachNo]['class'];
        $func = $trace[$stachNo]['function'];
        $file = $trace[$stachNo-1]['file'];
        $line = isset($trace[$stachNo]['line'])?$trace[$stachNo]['line']:"";
        $arg = isset($value["args"]) ? $value["args"] : "";
    }
    $strarg="";
    if(is_array($arg))
    {
        foreach($arg as $tmparg)
        {
            if(is_string($tmparg) || is_numeric($tmparg))
            {
                $strarg=empty($strarg)?$tmparg:$strarg.", ".$tmparg;
            }
            else
            {
                $strarg=empty($strarg)?"Not a string or number":$strarg.", Not a string or number";
            }
        }
    }
    if ($file != '') $file = basename($file);
    $c = $file . " {$line}: ";
    $c .= ($class != '') ? ":" . $class . "->" : "";
    $c .= ($func != '') ? $func . "({$strarg}): " : "";
    return($c);
}
function getConfigVal($WOOVISMA_CONFIG_KEY)
{
    static $ARR_WOOVISMA_CONFIG=array();
    if(empty($ARR_WOOVISMA_CONFIG))
    {
        include WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/config.php";
        $arrConfig=get_defined_vars();
        unset($arrConfig["ARR_WOOVISMA_CONFIG"]);
        unset($arrConfig["WOOVISMA_CONFIG_KEY"]);
        $ARR_WOOVISMA_CONFIG=$arrConfig;
    }
    if(isset($ARR_WOOVISMA_CONFIG[$WOOVISMA_CONFIG_KEY])) return $ARR_WOOVISMA_CONFIG[$WOOVISMA_CONFIG_KEY];
    return null;
}
function httpPost($url,$params)
{
  $postData = '';
   //create name value pairs seperated by &
   foreach($params as $k => $v) 
   { 
      $postData .= "{$k}={$v}&"; 
   }
   $postData = rtrim($postData, '&');
 
    $ch = curl_init();  
 
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HEADER, false); 
    curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);    
 
    $output=curl_exec($ch);
 
    curl_close($ch);
    return $output;
 
}
function woovisma_is_user_role_customer($user) 
{
    //$is_customer = false; changed for accepting all customers.
    $is_customer = true;
    return $is_customer;
    foreach ($user->roles as $role) 
    {
        woovisma_addlog("user role: " . $role);
        if ($role == "customer") 
        {
            $is_customer = true;
            break;
        }
    }
    return $is_customer;
}
function isPrefixed($word)
{
    $options = get_option( 'woovisma_options' );
    $product_prefix=isset($options["woovismaoptname"]["product_prefix"])?$options["woovismaoptname"]["product_prefix"]:"";
    ///if the product prefix from setting is empty consider it as prefixed
    if($product_prefix=="") return true;
    ///if the product is prefixed already return
    if(strpos($word, $product_prefix."_")===0) return true;
    return false;
}
function prefixWord($word)
{
    $options = get_option( 'woovisma_options' );
    $product_prefix=isset($options["woovismaoptname"]["product_prefix"])?$options["woovismaoptname"]["product_prefix"]:"";
    ///if the product prefix from setting is empty return
    if($product_prefix=="") return $word;
    ///if the product is prefixed already return
    if(strpos($word, $product_prefix."_")===0) return $word;
    return $product_prefix."_".$word;
}
function deprefixWord($word)
{
    $options = get_option( 'woovisma_options' );
    $product_prefix=isset($options["woovismaoptname"]["product_prefix"])?$options["woovismaoptname"]["product_prefix"]:"";
    if($product_prefix=="") return $word;
    if(strpos($word, $product_prefix."_")!==0) return $word;
    $tmp=substr($word, strlen($product_prefix."_"));
    return $tmp;
}
 /**
     * Creates a HttpRequest and appends the given XML to the request and sends it For license key
     *
     * @access public
     * @return bool
     */
    function create_license_validation_request($localkey=''){
        
        $arrOption=get_option("woovisma_options");

        if(!isset($arrOption["license_key"])){
            return false;
        }
        $licensekey = $arrOption["license_key"];
        // -----------------------------------
        //  -- Configuration Values --
        // -----------------------------------
        // Enter the url to your WHMCS installation here
        
        $whmcsurl = 'http://whmcs.onlineforce.net/'; $whmcsurlsock = 'whmcs.onlineforce.net';
        // Must match what is specified in the MD5 Hash Verification field
        // of the licensing product that will be used with this check.
       
		$licensing_secret_key = 'ak4762';
        // The number of days to wait between performing remote license checks
        $localkeydays = 15;
        // The number of days to allow failover for after local key expiry
        $allowcheckfaildays = 5;

        // -----------------------------------
        //  -- Do not edit below this line --
        // -----------------------------------

        $check_token = time() . md5(mt_rand(1000000000, mt_getrandmax()) . $licensekey);
        $checkdate = date("Ymd");
        $domain = $_SERVER['SERVER_NAME'];
		$host= gethostname();
		//$usersip = gethostbyname($host);
        $usersip = gethostbyname($host) ? gethostbyname($host) : $_SERVER['SERVER_ADDR'];
        //$usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
        $dirpath = dirname(__FILE__);
        $verifyfilepath = 'modules/servers/licensing/verify.php';
        $localkeyvalid = false;
        if ($localkey) {
            $localkey = str_replace("\n", '', $localkey); # Remove the line breaks
            $localdata = substr($localkey, 0, strlen($localkey) - 32); # Extract License Data
            $md5hash = substr($localkey, strlen($localkey) - 32); # Extract MD5 Hash
            if ($md5hash == md5($localdata . $licensing_secret_key)) {
                $localdata = strrev($localdata); # Reverse the string
                $md5hash = substr($localdata, 0, 32); # Extract MD5 Hash
                $localdata = substr($localdata, 32); # Extract License Data
                $localdata = base64_decode($localdata);
                $localkeyresults = unserialize($localdata);
                $originalcheckdate = $localkeyresults['checkdate'];
                if ($md5hash == md5($originalcheckdate . $licensing_secret_key)) {
                    $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $localkeydays, date("Y")));
                    if ($originalcheckdate > $localexpiry) {
                        $localkeyvalid = true;
                        $results = $localkeyresults;
                        $validdomains = explode(',', $results['validdomain']);
                        if (!in_array($_SERVER['SERVER_NAME'], $validdomains)) {
                            $localkeyvalid = false;
                            $localkeyresults['status'] = "Invalid";
                            $results = array();
                        }
                        $validips = explode(',', $results['validip']);
                        if (!in_array($usersip, $validips)) {
                            $localkeyvalid = false;
                            $localkeyresults['status'] = "Invalid";
                            $results = array();
                        }
                        $validdirs = explode(',', $results['validdirectory']);
                        if (!in_array($dirpath, $validdirs)) {
                            $localkeyvalid = false;
                            $localkeyresults['status'] = "Invalid";
                            $results = array();
                        }
                    }
                }
            }
        }
        if (!$localkeyvalid) {
            $postfields = array(
                'licensekey' => $licensekey,
                'domain' => $domain,
                'ip' => $usersip,
                'dir' => $dirpath,
            );
            if ($check_token) $postfields['check_token'] = $check_token;
            $query_string = '';
            foreach ($postfields AS $k=>$v) {
                $query_string .= $k.'='.urlencode($v).'&';
            }
            if (function_exists('curl_exec')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $whmcsurl . $verifyfilepath);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $data = curl_exec($ch);
                curl_close($ch);
            } else {
                $fp = fsockopen($whmcsurlsock, 80, $errno, $errstr, 5);
				//woovisma_addlog($errstr.':'.$errno);
                if ($fp) {
                    $newlinefeed = "\r\n";
                    $header = "POST ".$whmcsurl . $verifyfilepath . " HTTP/1.0" . $newlinefeed;
                    $header .= "Host: ".$whmcsurl . $newlinefeed;
                    $header .= "Content-type: application/x-www-form-urlencoded" . $newlinefeed;
                    $header .= "Content-length: ".@strlen($query_string) . $newlinefeed;
                    $header .= "Connection: close" . $newlinefeed . $newlinefeed;
                    $header .= $query_string;
                    $data = '';
                    @stream_set_timeout($fp, 20);
                    @fputs($fp, $header);
                    $status = @socket_get_status($fp);
                    while (!@feof($fp)&&$status) {
                        $data .= @fgets($fp, 1024);
                        $status = @socket_get_status($fp);
                    }
                    @fclose ($fp);
                }
            }
            if (!$data) {
                $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - ($localkeydays + $allowcheckfaildays), date("Y")));
                if ($originalcheckdate > $localexpiry) {
                    $results = $localkeyresults;
                } else {
                    $results = array();
                    $results['status'] = "Invalid";
                    $results['description'] = "Remote Check Failed";
                    return $results;
                }
            } else {
                preg_match_all('/<(.*?)>([^<]+)<\/\1>/i', $data, $matches);
                $results = array();
                foreach ($matches[1] AS $k=>$v) {
                    $results[$v] = $matches[2][$k];
                }
            }
            if (!is_array($results)) {
                die("Invalid License Server Response");
            }
            if (isset($results['md5hash'])) {
                if ($results['md5hash'] != md5($licensing_secret_key . $check_token)) {
                    $results['status'] = "Invalid";
                    $results['description'] = "MD5 Checksum Verification Failed";
                    return $results;
                }
            }
            if ($results['status'] == "Active") {
                $results['checkdate'] = $checkdate;
                $data_encoded = serialize($results);
                $data_encoded = base64_encode($data_encoded);
                $data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
                $data_encoded = strrev($data_encoded);
                $data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
                $data_encoded = wordwrap($data_encoded, 80, "\n", true);
                $results['localkey'] = $data_encoded;
            }
            $results['remotecheck'] = true;
        }
        unset($postfields,$data,$matches,$whmcsurl,$licensing_secret_key,$checkdate,$usersip,$localkeydays,$allowcheckfaildays,$md5hash);
        return $results;
        //return true;
    }
/**
    * Checks if license-key is valid
    *
    * @access public
    * @return void
    */
function is_license_key_valid() {
    woovisma_addlog("LICENSE VALIDATION Start");
    $result = create_license_validation_request();
    if($result===false) $result['status']=false;
    switch ($result['status']) {
        case "Active":
            // get new local key and save it somewhere
            $localkeydata = $result['localkey'];
            update_option( 'local_key_woovisma_plugin', $localkeydata );
            return $result['status'];
            break;
        case "Invalid":
            woovisma_addlog("License key is Invalid");
            return $result['status'];
            break;
        case "Expired":
            woovisma_addlog("License key is Expired");
    return $result['status'];
            break;
        case "Suspended":
            woovisma_addlog("License key is Suspended");
            return $result['status'];
            break;
        default:
    woovisma_addlog("Invalid Response");
            break;
    }
    woovisma_addlog("LICENSE VALIDATION End");
}
function processItemData($itemData)
{
    $arrItem=array();
    if(is_a( $itemData, 'WC_Order_Item_Product' ))
    {
        $arrItem["variation_id"]=$itemData->get_variation_id();
        $arrItem["product_id"]=$itemData->get_product_id();
        $arrItem["line_subtotal"]=$itemData->get_subtotal();
        $arrItem["qty"]=$itemData->get_quantity();
        $arrItem["line_total"]=$itemData->get_total();
        $objProduct=$itemData->get_product();
        $arrItem["name"]="";
        if(isset($objProduct) && $objProduct)
        {
            $arrItem["name"]=$objProduct->get_name();
        }
        $arrItem["remote_line_item_id"]=$itemData->get_total();
    }
    else
    {
        $arrItem=$itemData;
    }
    return $arrItem;
}
function processProductObject($product)
{
    $objProduct=new UniwinProduct();
    $pluginInfo=getWoocommercePluginInfo();
    if(empty($pluginInfo)) return null;
    $ret=version_compare($pluginInfo["Version"], "2.7.0");
    //if($ret<1 || isset($product->stock)) 
    if($ret<1) 
    {
        woovisma_addlog("Seems like woocommerce not updated");
        return $product;
    }
    //$product=new WC_Product_Variation();
    $objProduct->stock=$product->get_stock_quantity();
    $objProduct->id=$product->get_id();
    $objProduct->title=$product->get_name();
    $objProduct->product_type=$product->get_type();
    $priceExcludingTax=0;
    if($objProduct->product_type!="variable")
    {
        $priceExcludingTax=$product->get_price_excluding_tax();
    }
    $objProduct->priceExcludingTax=$priceExcludingTax;
    $objProduct->children=$product->get_children();
    return $objProduct;
}
function getWoovismaPluginInfo() {
    static $pluginInfo=array();
    if(is_null($pluginInfo)) return array();
    if(empty($pluginInfo))
    {
        // If get_plugins() isn't available, require it
	if ( ! function_exists( 'get_plugins' ) )
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	$plugins = get_option( 'active_plugins' );
        $woocommerceDirectory="";
        foreach($plugins as $pluginpath)
        {
            if($pos=strpos($pluginpath,"/woovisma.php"))
            {
                $woocommerceDirectory = substr($pluginpath, 0, $pos);
            }
        }
        if($woocommerceDirectory=="") $pluginInfo=null;
        else
        {
            // Create the plugins folder and file variables
            $plugin_folder = get_plugins( '/' . $woocommerceDirectory );
            $plugin_file = 'woovisma.php';

            // If the plugin version number are set, return it 
            if ( isset( $plugin_folder[$plugin_file] ) ) {
                    $pluginInfo = $plugin_folder[$plugin_file];
            }
        }
    }
    return $pluginInfo;
}
function getWoocommercePluginInfo() {
    static $pluginInfo=array();
    if(is_null($pluginInfo)) return array();
    if(empty($pluginInfo))
    {
        // If get_plugins() isn't available, require it
	if ( ! function_exists( 'get_plugins' ) )
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	$plugins = get_option( 'active_plugins' );
        $woocommerceDirectory="";
        foreach($plugins as $pluginpath)
        {
            if($pos=strpos($pluginpath,"/woocommerce.php"))
            {
                $woocommerceDirectory = substr($pluginpath, 0, $pos);
            }
        }
        if($woocommerceDirectory=="") $pluginInfo=null;
        else
        {
            // Create the plugins folder and file variables
            $plugin_folder = get_plugins( '/' . $woocommerceDirectory );
            $plugin_file = 'woocommerce.php';

            // If the plugin version number are set, return it 
            if ( isset( $plugin_folder[$plugin_file] ) ) {
                    $pluginInfo = $plugin_folder[$plugin_file];
            }
        }
    }
    return $pluginInfo;
}
///if the input is false, the value will be returned. If other than false, it will be logged
function uniwinMessage($message)
{
    $options = get_option( 'woovisma_options' );
    if($message===false)
    {
        $data="";
        if(!isset($options["uniwinsession"]) || empty($options["uniwinsession"])) 
        {
            $data = "";
        }
        else
        {
            $data = implode("<br /> ",$options["uniwinsession"]);
        }
        unset($options["uniwinsession"]);
        update_option( 'woovisma_options', $options );
        return $data;
    }
    if(!isset($options["uniwinsession"]))
    {
        $options["uniwinsession"]=array();
    }
    $options["uniwinsession"][]=$message;
    update_option( 'woovisma_options', $options );
}
function showErrorMessage($uniwinMessage)
{
    $uniwinErrorMessage=uniwinMessage(false);
    if(empty($uniwinErrorMessage))
    { 
        $uniwinErrorMessage=$uniwinMessage;
    }
    else
    {
        $uniwinErrorMessage=$uniwinMessage."<br />".$uniwinErrorMessage;
    }
    echo "<div id='message' class='updated fade'><p><strong>{$uniwinErrorMessage}</strong></p></div>";
}
class UniwinProduct
{
    public $stock;
    public $id;
    public $priceExcludingTax;
    public $title;
    public $product_type;
    public $children=array();
    public function get_price_excluding_tax()
    {
        return $this->priceExcludingTax;
    }
    public function get_title()
    {
        return $this->title;
    }
    public function get_children()
    {
        return $this->children;
    }
}
?>
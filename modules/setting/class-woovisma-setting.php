<?php
class Woovisma_Setting
{
    protected $arrMessage=array();
    protected $initialized=false;
    public function __construct()
    {
    }
    public function init()
    {
        //if($this->initialized) return;
        //$this->initialized=true; 
    }
    public static function &getInstance()
    { 
        static $objSettings=null;
        if(is_null($objInvoice))
        {
            $objSettings=new Woovisma_Setting();
        }
        return $objSettings;
    }
    public function clear_log()
    { 
        $this->arrMessage[]="Log Cleared";
        file_put_contents(WP_PLUGIN_DIR."/".WOOVISMA_PLUGIN_DIRECTORY."/woovisma.html", "");
        
    }
    public function getMessage()
    {
        if(empty($this->arrMessage)) return "";
        return implode(", ",$this->arrMessage);
    }
    public function render()
    {
        //trace("on render");
    }
}
?>
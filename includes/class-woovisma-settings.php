<?php
class Woovisma_Settings
{
    public function __construct()
    {
    }
    public static function &getInstance()
    {
        static $objInstance=null;
        if(is_null($objInstance))
        {
            $objInstance=new Woovisma_Settings();
        }
        return $objInstance;
    }
    /**
     * 
     * @param type $name - setting time field name
     * @param type $data = false, date, integer(positive or negative) - if positive, seconds added with current time if negative seconds subtracted with current time. If 0, current time
     */
    public function setTimeData($name,$data=false)
    {
        if($data===false)
        {
            $data="0000-00-00 00:00:00";
        }
        else if(ctype_digit ($data))
        {
            $data=date('Y-m-d h:i:s');
            $newDate = strtotime($date) + $data;
            $date=date('Y-m-d h:i:s',$newDate);
        }
        return $this->setData($name,$data);
    }
    /**
     * 
     * @global type $wpdb - database object
     * @param type $name - setting name
     * @param type $data - setting data
     * @return type  - integer, FALSE
     */
    public function setData($name,$data=false)
    {
        global $wpdb;
        $tbl_name=$wpdb->prefix.WOOVISMA_PLUGIN_DIRECTORY."_settings";
        if($data===false)
        {
            $data="";
        }
        $dbData=$this->getData($name);
        if($dbData!==false)
        {
            $sql=$wpdb->prepare("UPDATE `{$tbl_name}` SET `data`=%s WHERE `name`=%s",array($data,$name));
        }
        else
        {
            $sql=$wpdb->prepare("INSERT INTO `{$tbl_name}` (`name`,`data`) VALUES(%s,%s)",array($name,$data));
        }
        return $wpdb->query($sql);
    }
    public function getData($name,$defaultValue=false)
    {
        global $wpdb;
        $tbl_name=$wpdb->prefix.WOOVISMA_PLUGIN_DIRECTORY."_settings";
        $sql=$wpdb->prepare("SELECT * FROM `{$tbl_name}` WHERE `name`=%s",array($name));
        $arr=$wpdb->get_results($sql);
        if($arr)
        {
            return $arr[0]->data;
        }
        return $defaultValue;
    }
}
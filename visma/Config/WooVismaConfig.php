<?php
include_once(__DIR__."/WooVismaConfigTest.php");
include_once(__DIR__."/WooVismaConfigLive.php");
class WooVismaConfig
{
    private static $isTestConfig=false;
    public function __construct() {
    }
    public static function getConfigObject()
    {
        if(self::$isTestConfig) return new WooVismaConfigTest();
        else return new WooVismaConfigLive();
    }
}
?>

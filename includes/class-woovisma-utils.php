<?php
class Woovisma_Utils
{
    public function getTabPage()
    {
        $tabName=false;
        $tabPath=dirname(__DIR__)."/templates/tabs/";
        if(isset($_REQUEST["tab"]) && !empty($_REQUEST["tab"]) && file_exists($tabPath.$_REQUEST["tab"].".php"))
        {
            $tabName=$_REQUEST["tab"];
                       
        }
        else if(file_exists("{$tabPath}visma.php"))
        {
            $tabName="visma";
        }
        $active_tab=false;
        $settingsPage=false;
        if($tabName!==false)
        {
            ob_start();
            $tabFile="{$tabPath}{$tabName}.php";
            include($tabFile);
            $tplContent=ob_get_clean();
            ob_start();
            $active_tab=$tabName;
            include(dirname(__DIR__)."/templates/tabs.php");
            $settingsPage=ob_get_clean();
        }
        return $settingsPage;
    }
}
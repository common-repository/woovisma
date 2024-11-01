<?php
class Woovisma_Module
{
    private function __construct() {
        ;
    }
    public static function getModule($module)
    {
        $class=ucfirst(WOOVISMA_PLUGIN_DIRECTORY)."_".ucfirst($module);
        if(!class_exists($class)) 
        {
            include_once(WOOVISMA_PLUGIN_ABS_DIRECTORY. "/modules/{$module}/class-".WOOVISMA_PLUGIN_DIRECTORY."-{$module}.php");
            if(file_exists(WOOVISMA_PLUGIN_ABS_DIRECTORY. "/modules/{$module}/class-".WOOVISMA_PLUGIN_DIRECTORY."-{$module}r.php"))
            {
                include_once(WOOVISMA_PLUGIN_ABS_DIRECTORY. "/modules/{$module}/class-".WOOVISMA_PLUGIN_DIRECTORY."-{$module}r.php");
            }
            if(file_exists(WOOVISMA_PLUGIN_ABS_DIRECTORY. "/modules/{$module}/class-".WOOVISMA_PLUGIN_DIRECTORY."-ds{$module}.php"))
            {
                include_once(WOOVISMA_PLUGIN_ABS_DIRECTORY. "/modules/{$module}/class-".WOOVISMA_PLUGIN_DIRECTORY."-ds{$module}.php");
            }
            if(file_exists(WOOVISMA_PLUGIN_ABS_DIRECTORY. "/modules/{$module}/class-".WOOVISMA_PLUGIN_DIRECTORY."-ds{$module}lineitem.php"))
            {
                include_once(WOOVISMA_PLUGIN_ABS_DIRECTORY. "/modules/{$module}/class-".WOOVISMA_PLUGIN_DIRECTORY."-ds{$module}lineitem.php");
                include_once(WOOVISMA_PLUGIN_ABS_DIRECTORY. "/modules/{$module}/class-".WOOVISMA_PLUGIN_DIRECTORY."-{$module}lineitem.php");
                include_once(WOOVISMA_PLUGIN_ABS_DIRECTORY. "/modules/{$module}/class-".WOOVISMA_PLUGIN_DIRECTORY."-{$module}lineitemr.php");
            }
        }
        return new $class();
    }
}
?>
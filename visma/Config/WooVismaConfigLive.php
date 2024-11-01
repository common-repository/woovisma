<?php
 include_once(__DIR__."/WooVismaConfigBase.php");
class WooVismaConfigLive extends WooVismaConfigBase
{
    public function authEndpoint()
    {
        return 'https://auth.vismaonline.com/eaccountingapi/oauth/authorize';
    }
    public function tokenEndpoint()
    {
        return 'https://auth.vismaonline.com/eaccountingapi/oauth/token';
    }
    public function apiEndpoint()
    {
        return 'https://eaccountingapi.vismaonline.com';
    }
    public function getScope()
    {
        return 'sales accounting purchase';
    }
}

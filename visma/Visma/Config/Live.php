<?php
 include_once(__DIR__."/Base.php");
class Live extends Base
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
        return 'https://auth.vismaonline.com';
    }
    public function getScope()
    {
        return 'sales accounting purchase';
    }
}

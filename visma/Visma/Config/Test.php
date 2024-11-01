<?php
include_once(__DIR__."/Base.php");
class Test extends Base
{
    public function authEndpoint()
    {
        return 'https://auth-sandbox.test.vismaonline.com/eaccountingapi/oauth/authorize';
    }
    public function tokenEndpoint()
    {
        return 'https://auth-sandbox.test.vismaonline.com/eaccountingapi/oauth/token';
    }
    public function apiEndpoint()
    {
        return 'https://auth-sandbox.test.vismaonline.com';
    }
    public function getScope()
    {
        return 'sales accounting purchase';
    }
}

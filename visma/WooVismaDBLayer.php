<?php
class WooVismaDBLayer
{
    public function __construct()
    {
    }
    public static function setToken($token)
    {
        $options = get_option( 'woovisma_options' );
        $options["visma_access_token"]=$token;
        update_option( 'woovisma_options', $options );
        woovisma_addlog("token updated");
        //$_SESSION["WooVisma"]["token"]=$token;
    }
    public static function getToken()
    {
        $options = get_option( 'woovisma_options' );
        if(isset($options["visma_access_token"])) 
        {
            woovisma_addlog("token returned");
            return $options["visma_access_token"];
        }
        else 
        {
            woovisma_addlog("token not available");
            return false;
        }
        //return (isset($_SESSION["WooVisma"]["token"]))?$_SESSION["WooVisma"]["token"]:false;
    }
    public static function setRefreshToken($token)
    {
        $options = get_option( 'woovisma_options' );
        $options["visma_refresh_token"]=$token;
        update_option( 'woovisma_options', $options );
        woovisma_addlog("refreshed token set");
        //$_SESSION["WooVisma"]["token"]=$token;
    }
    public static function getRefreshToken()
    {
        $options = get_option( 'woovisma_options' );
        if(isset($options["visma_refresh_token"])) return $options["visma_refresh_token"];
        else return false;
        //return (isset($_SESSION["WooVisma"]["token"]))?$_SESSION["WooVisma"]["token"]:false;
    }
    public static function setTokenExpire($expire)
    {
        $options = get_option( 'woovisma_options' );
        $options["visma_token_expire"]=$expire;
        update_option( 'woovisma_options', $options );
        woovisma_addlog("token expiration time set");
        //$_SESSION["WooVisma"]["token"]=$token;
    }
    public static function getTokenExpire()
    {
        $options = get_option( 'woovisma_options' );
        if(isset($options["visma_token_expire"])) return $options["visma_token_expire"];
        else return false;
        //return (isset($_SESSION["WooVisma"]["token"]))?$_SESSION["WooVisma"]["token"]:false;
    }
    public static function setCode($code)
    {
        $options = get_option( 'woovisma_options' );
        $options["visma_access_code"]=$code;
        update_option( 'woovisma_options', $options );
        woovisma_addlog("code set");
    }
    public static function getCode()
    {
        $options = get_option( 'woovisma_options' );
        if(isset($options["visma_access_code"])) return $options["visma_access_code"];
        else return false;
        //return (isset($_SESSION["WooVisma"]["token"]))?$_SESSION["WooVisma"]["token"]:false;
    }
    public static function setTokenType($tokenType)
    {
        $options = get_option( 'woovisma_options' );
        $options["visma_access_token_type"]=$tokenType;
        update_option( 'woovisma_options', $options );
        woovisma_addlog("Token Type set");
    }
    public static function getTokenType()
    {
        $options = get_option( 'woovisma_options' );
        if(isset($options["visma_access_token_type"])) return $options["visma_access_token_type"];
        else return false;
        //return (isset($_SESSION["WooVisma"]["token_type"]))?$_SESSION["WooVisma"]["token_type"]:false;
    }
    public static function getRedirectUri()
    {
        $options = get_option( 'woovisma_options' );
        if(isset($options["nossl"]) && $options["nossl"]>0) return "https://uniwin.in/";
        if(isset($options["redirect_uri"])) return $options["redirect_uri"];
        return false;
    }
    public static function setRedirectUri($uri)
    {
        $options = get_option( 'woovisma_options' );
        $options["redirect_uri"]=$uri;
        update_option( 'woovisma_options', $options );
        woovisma_addlog("Redirection URI set");
    }
    public static function getClientID()
    {
        $options = get_option( 'woovisma_options' );
        if(isset($options["nossl"]) && $options["nossl"]>0) return "uniwin";
        if(isset($options["client_id"])) return $options["client_id"];
        return false;
        //return  'uniwin';
    }
    public static function setClientID($client_id)
    {
        $options = get_option( 'woovisma_options' );
        $options["client_id"]=$client_id;
        update_option( 'woovisma_options', $options );
        woovisma_addlog("Client ID set");
    }
    public static function getClientSecret()
    {
        $options = get_option( 'woovisma_options' );
        if(isset($options["client_secret"])) return $options["client_secret"];
        return false;
    }
    public static function setClientSecret($client_secret)
    {
        $options = get_option( 'woovisma_options' );
        $options["client_secret"]=$client_secret;
        update_option( 'woovisma_options', $options );
        woovisma_addlog("Client Secret set");
    }
}
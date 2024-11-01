<?php
interface IConfig
{
    public function authEndpoint();
    public function tokenEndpoint();
    public function redirectUri();
    public function getScope();
}

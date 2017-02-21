<?php

if (isset($_SERVER['HTTP_AUTHORIZATION']))
{
    $ha = base64_decode( substr($_SERVER['HTTP_AUTHORIZATION'],6) );
    list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', $ha);
    unset $ha;
}
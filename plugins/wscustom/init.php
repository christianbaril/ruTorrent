<?php

$thePluginPath = dirname(__FILE__);
require_once($thePluginPath . '/wsCustomization.php');

$wsCustomization = wsCustomization::init();
$theSettings->registerPlugin($plugin["name"],$pInfo["perms"]);
$jResult .="noty('wsCustomisation : loaded');";
$jResult .="noty('wsCustomisation : Profile loaded for user ' + userinfo.username );";

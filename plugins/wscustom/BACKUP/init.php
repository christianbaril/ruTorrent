<?php
$thePluginPath = dirname(__FILE__);
require_once($thePluginPath . '/wsCustomization.php');
$wsCustomization = wsCustomization::init();
$theSettings->registerPlugin($plugin["name"],$pInfo["perms"]);
?>

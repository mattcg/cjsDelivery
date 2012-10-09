<?php

$lib = dirname(__FILE__) . '/lib';
$ext = $lib . '/external';
$plg = $lib . '/plugins';

require $ext . '/processHooks.php';
require $lib . '/cjsDelivery.php';
require $plg . '/pragmaManager.php';
require $plg . '/cacheManager.php';
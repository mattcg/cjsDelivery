#!/usr/bin/php
<?php

require_once __DIR__.'/../../cjsDelivery.php';

$mainmodule = __DIR__.'/modules/main';

$delivery = cjsDelivery\create();
$delivery->addModule($mainmodule);
$delivery->setMainModule($mainmodule);

file_put_contents(__DIR__.'/../output/compiled.js', $delivery->getOutput(), LOCK_EX);
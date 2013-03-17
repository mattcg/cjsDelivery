<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

function create($minifyidentifiers = false, array $includes = null, array $globals = null) {
	$signal = require __DIR__ . '/../vendor/aura/signal/scripts/instance.php';

	if ($minifyidentifiers) {
		$identifiergenerator = new MinIdentifierGenerator();
	} else {
		$identifiergenerator = new FlatIdentifierGenerator();
	}
	$identifiermanager = new FileIdentifierManager($identifiergenerator);
	if ($includes) {
		$identifiermanager->setIncludes($includes);
	}

	$dependencyresolver = new FileDependencyResolver($identifiermanager);
	$dependencyresolver->setSignalManager($signal);

	$outputgenerator = new OutputGenerator(new TemplateOutputRenderer());
	$outputgenerator->setSignalManager($signal);

	$delivery = new Delivery();
	$delivery->setSignalManager($signal);
	$delivery->setOutputGenerator($outputgenerator);
	$delivery->setDependencyResolver($dependencyresolver);

	if ($globals) {
		$delivery->setGlobals($globals);
	}

	return $delivery;
}
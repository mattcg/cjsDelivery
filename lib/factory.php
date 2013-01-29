<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require 'Delivery.php';

function create($minifyidentifiers = false, array $includes = null, $globals = '') {
	$hookmanager = \hookManager\create();

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
	$dependencyresolver->setHookManager($hookmanager);

	$outputgenerator = new OutputGenerator(new TemplateOutputRenderer());
	$outputgenerator->setHookManager($hookmanager);

	$delivery = new Delivery();
	$delivery->setHookManager($hookmanager);
	$delivery->setOutputGenerator($outputgenerator);
	$delivery->setDependencyResolver($dependencyresolver);

	if ($globals) {
		$delivery->setGlobals($globals);
	}

	return $delivery;
}
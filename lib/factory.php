<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require_once 'external/hookManager/hookManager.php';

require_once 'Delivery.php';
require_once 'MinIdentifierGenerator.php';
require_once 'FlatIdentifierGenerator.php';
require_once 'FileIdentifierManager.php';
require_once 'FileDependencyResolver.php';
require_once 'OutputGenerator.php';
require_once 'TemplateOutputRenderer.php';

function create($minifyidentifiers = false, array $includes = null, array $globals = null) {
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
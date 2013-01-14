<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require 'Delivery.php';

function create($minifyidentifiers = false) {
	$hookmanager = \hookManager\create();

	if ($minifyidentifiers) {
		$identifiergenerator = new minIdentifierGenerator();
	} else {
		$identifiergenerator = new flatIdentifierGenerator();
	}
	$identifiermanager = new FileIdentifierManager($identifiergenerator);

	$dependencyresolver = new FileDependencyResolver($identifiermanager);
	$dependencyresolver->setHookManager($hookmanager);

	$outputgenerator = new outputGenerator(new templateOutputRenderer());
	$outputgenerator->setHookManager($hookmanager);

	$delivery = new Delivery();
	$delivery->setHookManager($hookmanager);
	$delivery->setOutputGenerator($outputgenerator);
	$delivery->setDependencyResolver($dependencyresolver);

	return $delivery;
}
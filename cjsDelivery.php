<?php
/**
 * cjsDelivery
 *
 * Write CommonJS-syntax JavaScript modules and deliver them to clients as a single file.
 *
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require_once __DIR__.'/lib/external/hookManager/hookManager.php';

require_once __DIR__.'/lib/delivery.php';

require_once __DIR__.'/lib/fileDependencyResolver.php';
require_once __DIR__.'/lib/fileIdentifierManager.php';
require_once __DIR__.'/lib/outputGenerator.php';
require_once __DIR__.'/lib/templateOutputRenderer.php';

function create() {
	$hookmanager = \hookManager\create();
	$identifiermanager = new fileIdentifierManager(new flatIdentifierGenerator());

	$resolver = new fileDependencyResolver($identifiermanager);
	$resolver->setHookManager($hookmanager);

	$generator = new outputGenerator(new templateOutputRenderer());
	$generator->setHookManager($hookmanager);

	$delivery = new delivery();
	$delivery->setHookManager($hookmanager);
	$delivery->setGenerator($generator);
	$delivery->setResolver($resolver);

	return $delivery;
}
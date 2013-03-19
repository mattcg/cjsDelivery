<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

class DeliveryFactory {

	const OPT_MINIFY = 'minifyIdentifiers';
	const OPT_SIGNALS = 'sendSignals';
	const OPT_GLOBALS = 'globals';
	const OPT_INCLUDES = 'includes';

	public static function getDefaultOptions() {
		return array(
			self::OPT_MINIFY => false,
			self::OPT_SIGNALS => false,
			self::OPT_GLOBALS => null,
			self::OPT_INCLUDES => null
		);
	}

	public static function getSignalManagerInstance() {
		return require __DIR__ . '/../vendor/aura/signal/scripts/instance.php';
	}

	public static function create(array $options = array()) {
		$options = array_merge(self::getDefaultOptions(), $options);

		$delivery = new Delivery();

		// Add a signal manager?
		if ($options[self::OPT_SIGNALS]) {
			$signalmanager = self::getSignalManagerInstance();
			$delivery->setSignalManager($signalmanager);
		} else {
			$signalmanager = null;
		}

		// Minify identifiers?
		if ($options[self::OPT_MINIFY]) {
			$identifiergenerator = new MinIdentifierGenerator();
		} else {
			$identifiergenerator = new FlatIdentifierGenerator();
		}

		// Search include directories?
		if ($options[self::OPT_INCLUDES]) {
			$delivery->setIncludes($options[self::OPT_INCLUDES]);
		}

		// Add global JavaScript?
		if ($options[self::OPT_GLOBALS]) {
			$delivery->setGlobals($options[self::OPT_GLOBALS]);
		}

		$identifiermanager = new FileIdentifierManager($identifiergenerator);
		$dependencyresolver = new FileDependencyResolver($identifiermanager);
		if ($signalmanager) {
			$dependencyresolver->setSignalManager($signalmanager);
		}

		$outputgenerator = new OutputGenerator(new TemplateOutputRenderer());
		if ($signalmanager) {
			$outputgenerator->setSignalManager($signalmanager);
		}

		$delivery->setOutputGenerator($outputgenerator);
		$delivery->setDependencyResolver($dependencyresolver);

		return $delivery;
	}
}

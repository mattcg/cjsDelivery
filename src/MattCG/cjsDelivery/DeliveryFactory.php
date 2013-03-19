<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

class DeliveryFactory {

	const OPT_MINI = 'minifyIdentifiers';
	const OPT_SIGN = 'sendSignals';
	const OPT_GLOB = 'globals';
	const OPT_INCL = 'includes';

	public static function getDefaultOptions() {
		return array(
			self::OPT_MINI => false,
			self::OPT_SIGN => false,
			self::OPT_GLOB => null,
			self::OPT_INCL => null
		);
	}

	public static function getSignalManagerInstance() {
		return require __DIR__ . '/../vendor/aura/signal/scripts/instance.php';
	}

	public static function create(array $options = array()) {
		$options = array_merge(self::getDefaultOptions(), $options);

		$delivery = new Delivery();

		// Add a signal manager?
		if ($options[self::OPT_SIGN]) {
			$signalmanager = self::getSignalManagerInstance();
			$delivery->setSignalManager($signalmanager);
		} else {
			$signalmanager = null;
		}

		// Minify identifiers?
		if ($options[self::OPT_MINI]) {
			$identifiergenerator = new MinIdentifierGenerator();
		} else {
			$identifiergenerator = new FlatIdentifierGenerator();
		}

		// Search include directories?
		if ($options[self::OPT_INCL]) {
			$delivery->setIncludes($options[self::OPT_INCL]);
		}

		// Add global JavaScript?
		if ($options[self::OPT_GLOB]) {
			$delivery->setGlobals($options[self::OPT_GLOB]);
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

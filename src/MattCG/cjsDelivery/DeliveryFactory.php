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
	const OPT_PPRG = 'parsePragmas';
	const OPT_PFMT = 'pragmaFormat';
	const OPT_PRGS = 'pragmas';

	public static function getDefaultOptions() {
		return array(
			self::OPT_MINI => false,
			self::OPT_SIGN => false,
			self::OPT_GLOB => null,
			self::OPT_INCL => null,
			self::OPT_PPRG => false,
			self::OPT_PFMT => null,
			self::OPT_PRGS => null
		);
	}

	public static function getSignalManagerInstance() {
		return require __DIR__ . '/../../../vendor/aura/signal/scripts/instance.php';
	}

	private static function attachPragmaManager($options, $delivery) {

		// Override $options[self::OPT_SIGN] if no sigal manager is attached
		$signalmanager = $delivery->getSignalManager();
		if (!$signalmanager) {
			self::attachSignalManager($delivery);
			$signalmanager = $delivery->getSignalManager();
		}

		$pragmamanager = new PragmaManager($signalmanager, $delivery->getDependencyResolver());
		if ($options[self::OPT_PFMT]) {
			$pragmamanager->setPragmaFormat($options[self::OPT_PFMT]);
		}

		if ($options[self::OPT_PRGS]) {
			$pragmamanager->setPragmas($options[self::OPT_PRGS]);
		}
	}

	private static function attachSignalManager($delivery) {
		$signalmanager = self::getSignalManagerInstance();
		$delivery->setSignalManager($signalmanager);

		// Also add the manager to dependencies.
		$delivery->getDependencyResolver()->setSignalManager($signalmanager);
		$delivery->getOutputGenerator()->setSignalManager($signalmanager);
	}

	private static function attachDependencyResolver($options, $delivery) {

		// Minify identifiers?
		if ($options[self::OPT_MINI]) {
			$identifiergenerator = new MinIdentifierGenerator();
		} else {
			$identifiergenerator = new FlatIdentifierGenerator();
		}

		$identifiermanager = new FileIdentifierManager($identifiergenerator);
		$dependencyresolver = new FileDependencyResolver($identifiermanager);
		$delivery->setDependencyResolver($dependencyresolver);
	}

	private static function attachOutputGenerator($delivery) {
		$outputgenerator = new OutputGenerator(new TemplateOutputRenderer());
		$delivery->setOutputGenerator($outputgenerator);
	}

	public static function create(array $options = array()) {
		$options = array_merge(self::getDefaultOptions(), $options);

		$delivery = new Delivery();

		self::attachDependencyResolver($options, $delivery);
		self::attachOutputGenerator($delivery);

		// Forcibly add a signal manager? (Will add one anyway if pragmas are enabled.)
		if ($options[self::OPT_SIGN]) {
			self::attachSignalManager($delivery);
		}

		// Search include directories?
		if ($options[self::OPT_INCL]) {
			$delivery->setIncludes($options[self::OPT_INCL]);
		}

		// Add global JavaScript?
		if ($options[self::OPT_GLOB]) {
			$delivery->setGlobals($options[self::OPT_GLOB]);
		}

		if ($options[self::OPT_PPRG]) {
			self::attachPragmaManager($options, $delivery);
		}

		return $delivery;
	}
}

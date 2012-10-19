<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require 'factory.php';

class commandLineRunner {
	const LONGOPT_MINIFY = 'minify_identifiers';
	const LONGOPT_MAIN = 'main_module';
	const OPT_MODULE = 'm';

	public function getOptions() {
		return self::OPT_MODULE.':';
	}

	public function getLongOptions() {
		return array(self::LONGOPT_MINIFY, self::LONGOPT_MAIN.'::');
	}

	public function run(array $options) {
		if (empty($options[self::OPT_MODULE])) {
			throw new cjsDeliveryException('No module specified', cjsDeliveryException::NOTHING_TO_BUILD);
		}

		$minifyidentifiers = isset($options[self::LONGOPT_MINIFY]);
		$delivery = create($minifyidentifiers);
		$moptions =& $options[self::OPT_MODULE];
		if (is_array($moptions)) {
			foreach($moptions as &$module) {
				$delivery->addModule($module);
			}
		} else {
			$delivery->addModule($moptions);
		}

		if (!empty($options[self::LONGOPT_MAIN])) {
			$delivery->setMainModule($options[self::LONGOPT_MAIN]);
		}

		return $delivery->getOutput();
	}
}
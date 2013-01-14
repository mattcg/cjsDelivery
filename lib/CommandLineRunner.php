<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require 'factory.php';
require 'plugins/pragmaManager/pragmaManager.php';

class CommandLineRunner {
	const LONGOPT_MINIFY = 'minify_identifiers';
	const LONGOPT_MAIN   = 'main_module';
	const LONGOPT_PFMT   = 'pragma_format';

	const OPT_MODULE = 'm';
	const OPT_PRAGMA = 'p';
	const OPT_DEBUG  = 'd';

	private $debugmode = false;
	private $debugfunc = null;

	public function getOptions() {
		return self::OPT_MODULE.':'.self::OPT_PRAGMA.'::'.self::OPT_DEBUG;
	}

	public function getLongOptions() {
		return array(self::LONGOPT_MINIFY, self::LONGOPT_MAIN.'::', self::LONGOPT_PFMT.'::');
	}

	public function inDebugMode() {
		return $this->debugmode;
	}

	private function maybeDebugOut($message) {
		if ($this->debugmode) {
			call_user_func($this->debugfunc, $message);
		}
	}

	public function run(array $options, \Closure $debugfunc) {
		if (empty($options[self::OPT_MODULE])) {
			throw new Exception('No module specified', Exception::NOTHING_TO_BUILD);
		}

		if (isset($options[self::OPT_DEBUG])) {
			$this->debugmode = true;
			$this->debugfunc = $debugfunc;
		}

		$minifyidentifiers = isset($options[self::LONGOPT_MINIFY]);
		$this->maybeDebugOut('Setting identifier minification: '.($minifyidentifiers ? 'true' : 'false'));
		$delivery = create($minifyidentifiers);

		if (isset($options[self::OPT_PRAGMA])) {
			$pragmamanager = new pragmaManager();
			if (isset($options[self::LONGOPT_PFMT])) {
				$pfmt = $options[self::LONGOPT_PFMT];
				$this->maybeDebugOut('Setting pragma format "'.$pfmt.'"...');
				$pragmamanager->setPragmaFormat($pfmt);
			}

			$this->maybeDebugOut('Registering the pragmaManager plugin');
			$delivery->plugin($pragmamanager);

			$poptions =& $options[self::OPT_PRAGMA];
			if (is_array($poptions)) {
				$this->maybeDebugOut('Setting pragmas: '.implode(',', $poptions));
				$pragmamanager->setPragmas($poptions);
			} else if (is_string($poptions)) {
				$this->maybeDebugOut('Setting pragma '.$poptions);
				$pragmamanager->setPragma($poptions);
			}
		}

		$moptions =& $options[self::OPT_MODULE];
		if (is_array($moptions)) {
			foreach($moptions as &$module) {
				$this->maybeDebugOut('Adding module "'.$module.'"');
				$delivery->addModule($module);
			}
		} else {
			$this->maybeDebugOut('Adding module "'.$moptions.'"');
			$delivery->addModule($moptions);
		}

		if (!empty($options[self::LONGOPT_MAIN])) {
			$mainmodule = $options[self::LONGOPT_MAIN];
			$this->maybeDebugOut('Setting main module "'.$mainmodule.'"');
			$delivery->setMainModule($mainmodule);
		}

		return $delivery->getOutput();
	}
}
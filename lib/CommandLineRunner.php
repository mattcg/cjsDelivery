<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require 'factory.php';
require 'plugins/pragmaManager/PragmaManager.php';

class CommandLineRunner {
	const LONGOPT_MINIFY = 'minify_identifiers';
	const LONGOPT_MAIN   = 'main_module';
	const LONGOPT_PFMT   = 'pragma_format';
	const LONGOPT_INCLD  = 'include';

	const OPT_MODULE = 'm';
	const OPT_PRAGMA = 'p';
	const OPT_DEBUG  = 'd';
	const OPT_GLOBAL = 'g';
	const OPT_HELP   = 'h';

	private $debugmode = false;
	private $debugfunc = null;

	public function getOptions() {
		return self::OPT_MODULE.':'.self::OPT_GLOBAL.':'.self::OPT_PRAGMA.'::'.self::OPT_DEBUG.self::OPT_HELP;
	}

	public function getLongOptions() {
		return array(self::LONGOPT_MINIFY, self::LONGOPT_MAIN.'::', self::LONGOPT_INCLD.'::', self::LONGOPT_PFMT.'::');
	}

	public function inDebugMode() {
		return $this->debugmode;
	}

	private function maybeDebugOut(&$message) {
		if ($this->debugmode) {
			call_user_func($this->debugfunc, $message);
		}
	}

	private function outputHelp() {
		echo PHP_EOL, ' ', str_repeat('#', 44), PHP_EOL;
		echo ' # cjsDelivery', PHP_EOL, ' # Copyright (c) 2012 Matthew Caruana Galizia', PHP_EOL, ' # @mcaruanagalizia', PHP_EOL, PHP_EOL;

		$out = function($opts, $long = false) {
			$hyphens = $long ? '--' : '-';
			$indent = str_repeat(' ', 5);
			foreach ($opts as $opt => &$help) {
				echo "\033[1m", $indent, $hyphens, $opt, "\033[0m", PHP_EOL, $indent, chunk_split($help, 76, PHP_EOL . $indent), PHP_EOL;
			}
		};

		$out(array(
			self::OPT_MODULE => 'Specify a module by path.',
			self::OPT_GLOBAL => 'Specify a JavaScript file with contents to be included "globally" so that its symbols are available within the scope of all other other modules. The require function is available within the "global" JavaScript scope.',
			self::OPT_PRAGMA => 'Turn on a pragma by name.',
			self::OPT_DEBUG  => 'Show debug messages while processing commands.',
			self::OPT_HELP   => 'Display this message.'
		));

		$out(array(
			self::LONGOPT_MAIN   => 'Specify the main "bootstrap" module that will be automatically required at the end of the output. A module specified using this option will be added automatically so it doesn\'t need to be specified using -' . self::OPT_MODULE . '.',
			self::LONGOPT_INCLD  => 'Specify the include path as a colon-separated list.',
			self::LONGOPT_PFMT   => 'Specify the pragma format. Defaults to ' . PragmaManager::DEFAULT_PFMT . '.',
			self::LONGOPT_MINIFY => 'Use tiny identifiers in output.'
		), true);
	}

	public function run(array $options, \Closure $debugfunc) {
		if (isset($options[self::OPT_HELP])) {
			$this->outputHelp();
			return;
		}

		if (empty($options[self::OPT_MODULE]) and empty($options[self::LONGOPT_MAIN])) {
			throw new Exception('No module specified. Use -' . self::OPT_HELP . ' for help.', Exception::NOTHING_TO_BUILD);
		}

		if (isset($options[self::OPT_DEBUG])) {
			$this->debugmode = true;
			$this->debugfunc = $debugfunc;
		}

		$includes = null;
		if (isset($options[self::LONGOPT_INCLD])) {
			$includes = explode(':', $options[self::LONGOPT_INCLD]);
		}

		$globals = null;
		if (isset($options[self::OPT_GLOBAL])) {
			$globals = (array) $options[self::OPT_GLOBAL];
			$this->maybeDebugOut('Adding globals "'.implode(', ', $globals).'"');
		}

		$minifyidentifiers = isset($options[self::LONGOPT_MINIFY]);
		$this->maybeDebugOut('Setting identifier minification: '.($minifyidentifiers ? 'true' : 'false'));
		$delivery = create($minifyidentifiers, $includes, $globals);

		if (isset($options[self::OPT_PRAGMA])) {
			$pragmamanager = new PragmaManager();
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

		if (!empty($options[self::LONGOPT_MAIN])) {
			$mainmodule = $options[self::LONGOPT_MAIN];
			$this->maybeDebugOut('Setting main module "'.$mainmodule.'"');
			$delivery->addModule($mainmodule);
			$delivery->setMainModule($mainmodule);
		}

		$moptions =& $options[self::OPT_MODULE];
		if (is_array($moptions)) {
			foreach($moptions as &$module) {
				$this->maybeDebugOut('Adding module "'.$module.'"');
				$delivery->addModule($module);
			}
		} else if ($moptions) {
			$this->maybeDebugOut('Adding module "'.$moptions.'"');
			$delivery->addModule($moptions);
		}

		return $delivery->getOutput();
	}
}
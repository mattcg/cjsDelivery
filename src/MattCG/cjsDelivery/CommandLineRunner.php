<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

class CommandLineRunner {
	const LONGOPT_MINI = 'minify_identifiers';
	const LONGOPT_MAIN = 'main_module';
	const LONGOPT_PFMT = 'pragma_format';
	const LONGOPT_INCL = 'include';
	const LONGOUT_OUTP = 'output';

	const OPT_MODULE = 'm';
	const OPT_PRAGMA = 'p';
	const OPT_DEBUG  = 'd';
	const OPT_GLOBAL = 'g';
	const OPT_HELP   = 'h';

	private $debugfunc = null;

	private $optdebug = false;
	private $opthelp = false;
	private $optmodules = null;
	private $optmainmodule = null;
	private $optincludes = null;
	private $optglobals = null;
	private $optminifyidentifiers = false;
	private $optoutput = null;
	private $optpragmafmt = null;
	private $optparsepragmas = false;
	private $optpragmas = null;

	public function getOptions() {
		return self::OPT_MODULE.':'.self::OPT_GLOBAL.':'.self::OPT_PRAGMA.'::'.self::OPT_DEBUG.self::OPT_HELP;
	}

	public function getLongOptions() {
		return array(self::LONGOPT_MINI, self::LONGOPT_MAIN.'::', self::LONGOPT_INCL.'::', self::LONGOPT_PFMT.'::', self::LONGOUT_OUTP.'::');
	}

	public function getDebugMode() {
		return $this->optdebug;
	}

	public function setDebugFunction(\Closure $debugfunc = null) {
		$this->debugfunc = $debugfunc;
	}

	private function debugOut($message) {
		if ($this->optdebug and $this->debugfunc) {
			call_user_func($this->debugfunc, $message);
		}
	}

	public function setOptions(array $options) {
		if (isset($options[self::OPT_HELP])) {
			$this->opthelp = true;
		}

		if (isset($options[self::OPT_DEBUG])) {
			$this->optdebug = true;
		}

		if (!empty($options[self::OPT_MODULE])) {
			$this->optmodules = (array) $options[self::OPT_MODULE];
		}

		if (!empty($options[self::LONGOPT_MAIN])) {
			$this->optmainmodule = $options[self::LONGOPT_MAIN];
		}

		if (!empty($options[self::LONGOPT_INCL])) {
			$this->optincludes = explode(':', $options[self::LONGOPT_INCL]);
		}

		if (!empty($options[self::OPT_GLOBAL])) {
			$this->optglobals = (array) $options[self::OPT_GLOBAL];
		}

		if (isset($options[self::LONGOPT_MINI])) {
			$this->optminifyidentifiers = true;
		}

		if (isset($options[self::LONGOUT_OUTP])) {
			$this->optoutput = $options[self::LONGOUT_OUTP];
		}

		if (isset($options[self::OPT_PRAGMA])) {
			$this->optparsepragmas = true;

			if (!empty($options[self::LONGOPT_PFMT])) {
				$this->optpragmafmt = $options[self::LONGOPT_PFMT];
			}

			if (!empty($options[self::OPT_PRAGMA])) {
				$this->optpragmas = (array) $options[self::OPT_PRAGMA];
			}
		}
	}

	private function getDeliveryInstance() {
		return DeliveryFactory::create(array(
			DeliveryFactory::OPT_MINIFY => $this->optminifyidentifiers,
			DeliveryFactory::OPT_SIGNALS => $this->optparsepragmas,
			DeliveryFactory::OPT_GLOBALS => $this->optglobals,
			DeliveryFactory::OPT_INCLUDES => $this->optincludes
		));
	}

	public function run() {
		if ($this->opthelp) {
			$this->outputHelp();
			return;
		}

		if (empty($this->optmodules) and !$this->optmainmodule) {
			throw new Exception('No module specified. Use -' . self::OPT_HELP . ' for help.', Exception::NOTHING_TO_BUILD);
		}

		$delivery = $this->getDeliveryInstance();

		if ($this->optparsepragmas) {
			$pragmamanager = new PragmaManager($delivery->getSignalManager(), $delivery->getDependencyResolver());
			if ($this->optpragmafmt) {
				$this->debugOut('Setting pragma format "' . $this->optpragmafmt . '"...');
				$pragmamanager->setPragmaFormat($this->optpragmafmt);
			}

			if ($this->optpragmas) {
				$this->debugOut('Setting pragmas: '.implode(',', $this->optpragmas));
				$pragmamanager->setPragmas($this->optpragmas);
			}
		}

		if ($this->optmodules) {
			foreach($this->optmodules as &$optmodule) {
				$this->debugOut('Adding module "' . $optmodule . '"');
				$delivery->addModule($optmodule);
			}
		}

		if ($this->optmainmodule) {
			$this->debugOut('Setting main module "' . $this->optmainmodule . '"');
			$delivery->addModule($this->optmainmodule);
			$delivery->setMainModule($this->optmainmodule);
		}

		if ($this->optoutput) {
			file_put_contents($this->optoutput, $delivery->getOutput());
			return;
		}

		return $delivery->getOutput();
	}

	private function outputHelp() {
		echo PHP_EOL, ' ', str_repeat('#', 44), PHP_EOL;
		echo ' # cjsDelivery', PHP_EOL, ' # Copyright (c) 2012 Matthew Caruana Galizia', PHP_EOL, ' # @mcaruanagalizia', PHP_EOL, PHP_EOL;

		$out = function($opts, $long = false) {
			$hyphens = $long ? '--' : '-';
			$indent = str_repeat(' ', 5);
			foreach ($opts as $opt => &$help) {
				echo "\033[1m", $indent, $hyphens, $opt, "\033[0m", PHP_EOL, $indent, wordwrap($help, 76, PHP_EOL . $indent), PHP_EOL, PHP_EOL;
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
			self::LONGOPT_MAIN => 'Specify the main "bootstrap" module that will be automatically required at the end of the output. A module specified using this option will be added automatically so it doesn\'t need to be specified using -' . self::OPT_MODULE . '.',
			self::LONGOPT_INCL => 'Specify the include path as a colon-separated list.',
			self::LONGOPT_PFMT => 'Specify the pragma format. Defaults to "' . PragmaManager::DEFAULT_PFMT . '".',
			self::LONGOPT_MINI => 'Use tiny identifiers in output.',
			self::LONGOUT_OUTP => 'Output to file.'
		), true);
	}
}

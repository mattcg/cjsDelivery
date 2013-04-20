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
	const LONGOPT_OUTP = 'output';
	const LONGOPT_VERS = 'version';

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
	private $optversion = false;

	public function getOptions() {
		return self::OPT_MODULE.':'.self::OPT_GLOBAL.':'.self::OPT_PRAGMA.'::'.self::OPT_DEBUG.self::OPT_HELP;
	}

	public function getLongOptions() {
		return array(self::LONGOPT_MINI, self::LONGOPT_MAIN.'::', self::LONGOPT_INCL.'::', self::LONGOPT_PFMT.'::', self::LONGOPT_OUTP.'::', self::LONGOPT_VERS);
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

		if (isset($options[self::LONGOPT_VERS])) {
			$this->optversion = true;
		}

		if (isset($options[self::LONGOPT_MINI])) {
			$this->optminifyidentifiers = true;
		}

		if (isset($options[self::LONGOPT_OUTP])) {
			$this->optoutput = $options[self::LONGOPT_OUTP];
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
			DeliveryFactory::OPT_MINI => $this->optminifyidentifiers,
			DeliveryFactory::OPT_GLOB => $this->optglobals,
			DeliveryFactory::OPT_INCL => $this->optincludes,
			DeliveryFactory::OPT_PFMT => $this->optpragmafmt,
			DeliveryFactory::OPT_PRGS => $this->optpragmas,
			DeliveryFactory::OPT_PPRG => $this->optparsepragmas
		));
	}

	public function run() {
		if ($this->opthelp) {
			$this->outputHelp();
			return;
		}

		if ($this->optversion) {
			$this->outputVersion();
			return;
		}

		if (empty($this->optmodules) and !$this->optmainmodule) {
			throw new Exception('No module specified. Use -' . self::OPT_HELP . ' for help.', Exception::NOTHING_TO_BUILD);
		}

		$delivery = $this->getDeliveryInstance();

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
			self::LONGOPT_OUTP => 'Output to file.',
			self::LONGOPT_VERS => 'Show version.'
		), true);
	}

	private function outputVersion() {
		$composer = json_decode(file_get_contents(__DIR__ . '/../../../composer.json'));
		echo 'v', $composer->version, PHP_EOL;
	}
}

<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

class OutputGenerator extends SignalSender {

	private $renderer = null;
	private $mainmodule = null;
	private $modules = null;
	private $exportrequire = null;
	private $globalscode = null;

	public function __construct(OutputRendererInterface $renderer) {
		$this->renderer = $renderer;
	}


	/**
	 * @param Module $mainmodule Main module that will be require()'d automatically
	 */
	public function setMainModule(Module $mainmodule = null) {
		$this->mainmodule = $mainmodule;
	}


	/**
	 * @param Module[] $modules List of modules from which to build output
	 */
	public function setModules(array $modules = null) {
		$this->modules = $modules;
	}


	/**
	 * @param string $exportrequire Name of variable to export the require function as
	 */
	public function setExportRequire($exportrequire = null) {
		$this->exportrequire = $exportrequire;
	}


	/**
	 * @param string $globals Raw JavaScript included just outside module scope
	 */
	public function setGlobalsCode($globalscode = null) {
		$this->globalscode = $globalscode;
	}

	/**
	 * @param string $globals Raw JavaScript included just outside module scope
	 */
	public function addGlobalsCode($globalscode) {
		if ($this->globalscode === null) {
			$this->globalscode = $globalscode;
		} else {
			$this->globalscode .= $globalscode;
		}
	}


	/**
	 * Build complete module output, including all added modules and dependencies
	 *
	 * @throws Exception If the module is not found
	 *
	 * @return string Complete output
	 */
	public function buildOutput() {
		if (empty($this->modules)) {
			throw new Exception('Nothing to build', Exception::NOTHING_TO_BUILD);
		}

		// If output is created by the hook callbacks, return it
		if ($this->signal) {
			$result = $this->signal->send($this, SignalSender::BUILD_OUTPUT)->getLast();
			if ($result and $result->value) {
				return $result->value;
			}
		}

		// Loop through the modules, render and look for the main module
		$concat = '';
		foreach ($this->modules as $realpath => &$module) {
			$concat .= $this->renderer->renderModule($module);
		}

		$output = $this->renderer->renderOutput($concat, $this->mainmodule, $this->globalscode, $this->exportrequire);

		// Run hooks with the fully built output
		if ($this->signal) {
			$this->signal->send($this, SignalSender::OUTPUT_READY, $output);
		}

		return $output;
	}
}

<?php
/**
 * hookManager
 *
 * Create a simple plugin architecture for data processing classes.
 *
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package hookManager
 */

namespace hookManager;

require_once __DIR__.'/managerInterface.php';
require_once __DIR__.'/clientInterface.php';

function create() {
	return new hookManager();
}

class hookManager implements manager {

	private $hooks;

	function __construct() {
		$this->hooks = array();
	}


	/**
	 * Hook a callback
	 *
	 * @param string $name The name of the hook to hook on
	 * @param Closure $callback Callback to invoke when the hook is run
	 */
	public function hook($name, Closure $callback) {
		$hooks =& $this->hooks;
		if (!isset($hooks[$name])) {
			$hooks[$name] = array();
		}

		$hooks[$name][] = $callback;
	}


	/**
	 * Unhook a callback
	 *
	 * @param string $name The name of the hook to unhook
	 * @param Closure $callback If specified, only this callback will be unhooked
	 */
	public function unhook($name, Closure $callback = null) {
		$hooks =& $this->hooks;
		if (isset($hooks[$name])) {
			return;
		}

		if (is_null($callback)) {
			unset($hooks[$name]);
			return;
		}

		$i = array_search($callback, $hooks[$name], true);
		if ($i !== false) {
			array_splice($hooks[$name], $i, 1);
		}
	}


	/**
	 * Run a hook
	 *
	 * @param string $name The name of the hook to run
	 * @param mixed $arg Argument to pass to the hooked callback
	 */
	public function run($name, &$arg) {
		if (!isset($this->hooks[$name])) {
			return;
		}

		$hooks =& $this->hooks[$name];
		foreach ($hooks as &$hook) {
			$hook($arg);
		}
	}
}
<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package hookManager
 */

namespace hookManager;

require_once 'Client.php';
require_once 'Manager.php';
require_once 'Plugin.php';

abstract class Pluggable implements Client {

	private $hookmanager = null;

	public function setHookManager(Manager $hookmanager) {
		$this->hookmanager = $hookmanager;
	}

	public function getHookManager() {
		return $this->hookmanager;
	}

	public function plugin(Plugin $plugin) {
		if ($this->hookmanager) {
			return $plugin->register($this);
		}

		return null;
	}
}
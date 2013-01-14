<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package hookManager
 */

namespace hookManager;

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
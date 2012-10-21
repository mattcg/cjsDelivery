<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package hookManager
 */

namespace hookManager;

abstract class pluggable implements client {

	private $hookmanager = null;

	public function setHookManager(manager $hookmanager) {
		$this->hookmanager = $hookmanager;
	}

	public function getHookManager() {
		return $this->hookmanager;
	}

	public function plugin(plugin $plugin) {
		if ($this->hookmanager) {
			return $plugin->register($this);
		}

		return null;
	}
}
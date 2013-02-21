<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package hookManager
 */

namespace hookManager;

require_once 'Manager.php';

interface Client {

	public function setHookManager(Manager $manager);
	public function getHookManager();
}
<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package hookManager
 */

namespace hookManager;

interface client {

	public function setHookManager(manager $manager);
	public function getHookManager();
}
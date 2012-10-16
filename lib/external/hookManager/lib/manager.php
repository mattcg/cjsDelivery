<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package hookManager
 */

namespace hookManager;

interface manager {

	public function hook($name, Closure $callback);
	public function unhook($name, Closure $callback = null);
	public function run($name, &$arg);
}
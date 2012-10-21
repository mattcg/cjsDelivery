<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package hookManager
 */

namespace hookManager;

interface plugin {

	public function register(client $hookclient);
}
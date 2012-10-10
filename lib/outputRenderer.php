<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

interface outputRenderer {


	/**
	 * Render output-ready code for a given module, calling the callback if specified
	 *
	 * @param string $name
	 * @param array $code
	 * @return string
	 */
	public function renderModule($name, &$code);


	/**
	 * Render all of the output-ready code together
	 *
	 * @param string $output Concatenated module code
	 * @param string $main Name of the main module
	 * @return string
	 */
	public function renderOutput($output, $main);
}
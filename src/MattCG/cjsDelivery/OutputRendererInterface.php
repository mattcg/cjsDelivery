<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

interface OutputRendererInterface {


	/**
	 * Render output-ready code for a given module
	 *
	 * @param Module $module
	 * @return string
	 */
	public function renderModule(Module &$module);


	/**
	 * Render all of the output-ready code together
	 *
	 * @param string $output Concatenated module code
	 * @param Module $mainmodule Main module that will be require()'d automatically
	 * @param string $globals Raw JavaScript included just outside module scope
	 * @param string $exportrequire Name of variable to export the require function as
	 * @return string
	 */
	public function renderOutput(&$output, Module &$mainmodule = null, &$globalscode = null, $exportrequire = null);
}

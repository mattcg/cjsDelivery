<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

class TemplateOutputRenderer implements OutputRenderer {


	/**
	 * @see outputRender::renderModule
	 */
	public function renderModule(&$module) {
		$identifier = $module->getUniqueIdentifier();
		$code = $module->getCode();
		return <<<MODULE
modules['$identifier'] = function(require, exports, module, modules) {
$code
};

MODULE;

	}


	/**
	 * @see OutputRenderer::renderOutput
	 */
	public function renderOutput(&$output, $main = '', &$globals = '', $exportrequire = '') {
		if ($main) {
			$main = "require('$main');";
		}

		if ($exportrequire and preg_match('/^[a-z]+$/i', $exportrequire)) {
			$exportrequire = "var $exportrequire = ";
			$returnrequire = 'return require;';
		} else {
			$returnrequire = '';
		}

		return <<<OUTPUT
$exportrequire(function(modules) {
	var require = function(identifier) {
		var module, exports, closure;
		if (!modules[identifier].hasOwnProperty('exports')) {
			exports = {};
			module = {id: identifier, exports: exports};
			closure = modules[identifier];
			modules[identifier] = module;
			closure.call(module, require, exports, module);
		}
		return modules[identifier].exports;
	};

$output
$globals
$main
$returnrequire
}({}));

OUTPUT;

	}
}

<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

class templateOutputRenderer implements OutputRenderer {

	const DIR_TEMPLATES = '/templates';
	const DIR_LIB = __DIR__;

	const TEMPLATE_FULL = 'full';
	const TEMPLATE_MODULE = 'module';
	const TEMPLATE_MAIN = 'main';

	const EXT_MS = '.ms';

	private $templates = array();


	/**
	 * Simple template renderer used internally
	 *
	 * @param string $templatename
	 * @param array $keys
	 * @param array $values
	 * @return string
	 */
	private function renderTemplate($name, $keys, $values) {
		if (!isset($this->templates[$name])) {
			$filepath = self::DIR_LIB . self::DIR_TEMPLATES . '/' . $name . self::EXT_MS;
			$this->templates[$name] = file_get_contents($filepath, false);
		}

		return str_replace($keys, $values, $this->templates[$name]);
	}


	/**
	 * @see outputRender::renderModule
	 */
	public function renderModule(&$module) {
		return $this->renderTemplate(self::TEMPLATE_MODULE,
			array('{{identifier}}', '{{code}}'),
			array($module->getUniqueIdentifier(), $module->getCode())
		);
	}


	/**
	 * @see OutputRenderer::renderOutput
	 */
	public function renderOutput(&$output, $main = '') {
		if ($main) {
			$main = $this->renderTemplate(self::TEMPLATE_MAIN, '{{main}}', $main);
		}

		return $output = $this->renderTemplate(self::TEMPLATE_FULL,
			array('{{output}}', '{{main}}'),
			array($output, $main)
		);
	}
}
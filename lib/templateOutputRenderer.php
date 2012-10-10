<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

class templateOutputRenderer implements outputRenderer {

	const DIR_TEMPLATES = '/templates';
	const DIR_LIB = __DIR__;

	const TEMPLATE_FULL = 'full';
	const TEMPLATE_MODULE = 'module';

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
	private function renderTemplate($name, $keys = null, $values = null) {
		$filepath = self::DIR_LIB . self::DIR_TEMPLATES . '/' . $name . self::EXT_MS;

		if (!isset($this->templates[$name])) {
			$this->templates[$name] = file_get_contents($filepath, false);
		}

		if (!$keys) {
			return $this->templates[$name];
		}

		return str_replace($keys, $values, $this->templates[$name]);
	}


	/**
	 * @see outputRender::renderModule
	 */
	public function renderModule($name, &$code) {
		return $this->renderTemplate(self::TEMPLATE_MODULE,
			array('{{name}}', '{{code}}'),
			array($name, $code)
		);
	}


	/**
	 * @see outputRenderer::renderOutput
	 */
	public function renderOutput($output, $main) {
		return $this->renderTemplate(self::TEMPLATE_FULL,
			array('{{main}}', '{{output}}'),
			array($main, $output)
		);
	}
}
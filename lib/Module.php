<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

class Module {
	private $code, $uniqueidentifier, $modificationtime;

	public function __construct(&$code) {
		$this->code = $code;
	}

	public function getCode() {
		return $this->code;
	}

	public function setUniqueIdentifier($uniqueidentifier) {
		$this->uniqueidentifier = $uniqueidentifier;
	}

	public function getUniqueIdentifier() {
		return $this->uniqueidentifier;
	}

	public function setModificationTime($modificationtime) {
		$this->modificationtime = $modificationtime;
	}

	public function getModificationTime() {
		return $this->modificationtime;
	}
}
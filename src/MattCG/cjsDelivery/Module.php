<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

class Module {
	private $code, $uniqueidentifier = null, $modificationtime = null;

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
		if (!is_int($modificationtime)) {
			throw new Exception("Bad module modification time '$modificationtime'", Exception::BAD_MODULE_MTIME);
		}

		$this->modificationtime = $modificationtime;
	}

	public function getModificationTime() {
		return $this->modificationtime;
	}
}
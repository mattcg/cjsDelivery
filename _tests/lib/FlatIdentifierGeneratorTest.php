<?php
/**
 * PHPUnit tests for identifier generators
 *
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2013, Matthew Caruana Galizia
 */

// Class under test
require_once CJSD_LIB_DIR . '/FlatIdentifierGenerator.php';

class FlatIdentifierGeneratorTest extends PHPUnit_Framework_TestCase {

	public function testPathNameIsFlattened() {
		$generator = new cjsDelivery\FlatIdentifierGenerator();
		$flatidentifier = $generator->generateFlattenedIdentifier('/path/to/mymodule');
		$this->assertEquals('mymodule', $flatidentifier);
	}

	public function testPathNameFlatteningIsIdempotent() {
		$generator = new cjsDelivery\FlatIdentifierGenerator();
		$flatidentifier = $generator->generateFlattenedIdentifier('/path/to/mymodule');
		$this->assertEquals('mymodule', $flatidentifier);

		// Supplying the exact same path name should yield the same result
		$flatidentifier = $generator->generateFlattenedIdentifier('/path/to/mymodule');
		$this->assertEquals('mymodule', $flatidentifier);
	}

	public function testFlattenedIdentifierDoesNotIncludeExtension() {
		$generator = new cjsDelivery\FlatIdentifierGenerator();
		$flatidentifier = $generator->generateFlattenedIdentifier('/path/to/mymodule.js');
		$this->assertEquals('mymodule', $flatidentifier);
	}

	public function testFlattenedIdentifierCollisionsAreHandled() {
		$generator = new cjsDelivery\FlatIdentifierGenerator();
		$flatidentifier = $generator->generateFlattenedIdentifier('/path/to/mymodule');
		$this->assertEquals('mymodule', $flatidentifier);
		$flatidentifier = $generator->generateFlattenedIdentifier('/other/path/to/mymodule');
		$this->assertEquals('mymodule1', $flatidentifier);
	}
}

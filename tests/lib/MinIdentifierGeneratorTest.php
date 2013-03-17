<?php
/**
 * PHPUnit tests for identifier generators
 *
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2013, Matthew Caruana Galizia
 */

class MinIdentifierGeneratorTest extends PHPUnit_Framework_TestCase {

	public function testIdentifierIsMinified() {
		$generator = new MattCG\cjsDelivery\MinIdentifierGenerator();
		$minidentifier = $generator->generateFlattenedIdentifier('/path/to/mymodule');
		$this->assertEquals('A', $minidentifier);
	}

	public function testPathNameFlatteningIsIdempotent() {
		$generator = new MattCG\cjsDelivery\MinIdentifierGenerator();
		$minidentifier = $generator->generateFlattenedIdentifier('/path/to/mymodule');
		$this->assertEquals('A', $minidentifier);

		// Supplying the exact same path name should yield the same result
		$minidentifier = $generator->generateFlattenedIdentifier('/path/to/mymodule');
		$this->assertEquals('A', $minidentifier);
	}

	public function testIncrementsThroughAlphabet() {
		$generator = new MattCG\cjsDelivery\MinIdentifierGenerator();
		$minidentifiers = explode(',', 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z');

		foreach ($minidentifiers as $minidentifier) {
			$this->assertEquals($minidentifier, $generator->generateFlattenedIdentifier('/path/to/mymodule'. $minidentifier));
		}

		// And so on...
		foreach ($minidentifiers as $minidentifier) {
			$this->assertEquals($minidentifier . $minidentifier, $generator->generateFlattenedIdentifier('/path/to/mymodule'. $minidentifier . $minidentifier));
		}

		foreach ($minidentifiers as $minidentifier) {
			$this->assertEquals($minidentifier . $minidentifier . $minidentifier, $generator->generateFlattenedIdentifier('/path/to/mymodule'. $minidentifier . $minidentifier . $minidentifier));
		}
	}
}

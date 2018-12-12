<?php
/**
 * test for px2-asazuke2
 */
class mainTest extends PHPUnit_Framework_TestCase{

	public function setup(){
		mb_internal_encoding('UTF-8');
	}


	/**
	 * Test
	 */
	public function testStandard(){
		$this->assertEquals( 1, 1 );
	}

}

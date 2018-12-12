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
		$az = new pxplugin_asazuke_config();
		$this->assertTrue( is_object($az) );
		$crawlctrl = $az->factory_crawlctrl(array('run'));
		$this->assertTrue( is_object($crawlctrl) );
		$crawlctrl->start();
	}

}

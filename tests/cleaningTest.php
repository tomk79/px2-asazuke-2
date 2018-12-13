<?php
/**
 * test for px2-asazuke2
 */
class cleaningTest extends PHPUnit_Framework_TestCase{

	private $fs;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new \tomk79\filesystem();
	}


	/**
	 * Cleaning
	 */
	public function testCleaning(){
		$path_output = __DIR__."/output/";
		$this->fs->rm($path_output);
		clearstatcache();
		$this->assertFalse( is_dir($path_output) );
	}

}

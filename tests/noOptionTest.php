<?php
/**
 * test for px2-asazuke2
 */
class noOptionTest extends PHPUnit_Framework_TestCase{

	public function setup(){
		mb_internal_encoding('UTF-8');
	}


	/**
	 * No Options
	 */
	public function testNoOption(){
		$path_output = __DIR__."/output/";
		if(!is_dir($path_output)){
			mkdir( $path_output );
		}
		$az = new tomk79\pickles2\asazuke2\az(
			__DIR__."/testdata/htdocs_001/",
			$path_output
		);
		$this->assertTrue( is_object($az) );

		ob_start();
		$az->start();
		$stdout = ob_get_clean();
		// var_dump($stdout);

		$this->assertTrue( is_file(__DIR__.'/output/_logs/execute_list.csv') );
		$this->assertTrue( is_file(__DIR__.'/output/contents/index.html') );
		$this->assertTrue( is_file(__DIR__.'/output/sitemaps/sitemap.csv') );

	}

}

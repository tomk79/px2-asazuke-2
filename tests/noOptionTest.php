<?php
/**
 * test for px2-asazuke2
 */
class noOptionTest extends PHPUnit_Framework_TestCase{

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new \tomk79\filesystem();
	}


	/**
	 * No Options
	 */
	public function testNoOption(){
		$path_output = __DIR__."/output/";
		$this->fs->rm($path_output.'_logs/');
		$this->fs->rm($path_output.'contents/');
		$this->fs->rm($path_output.'sitemaps/');

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

		$sitemapCsv = $this->fs->read_csv(__DIR__.'/output/sitemaps/sitemap.csv');
		// var_dump($sitemapCsv);
		$this->assertEquals( $sitemapCsv[4][0], '/test3.html' );
		$this->assertEquals( $sitemapCsv[4][3], 'test3 - test site 001' );
		$this->assertEquals( $sitemapCsv[4][8], '/test1.html>/test2.html' );

		$contents = $this->fs->read_file(__DIR__.'/output/contents/index.html');
		// var_dump($contents);
		$this->assertSame( 1, preg_match('/'.preg_quote('href="/common/css/style.css"', '/').'/', $contents) );
		$this->assertSame( 1, preg_match('/'.preg_quote('src="/js/test.js"', '/').'/', $contents) );
		$this->assertSame( 1, preg_match('/'.preg_quote('src="/js/test2.js"', '/').'/', $contents) );
		$this->assertSame( 1, preg_match('/<p>これはコンテンツエリア。<\/p>/', $contents) );
		$this->assertSame( 1, preg_match('/'.preg_quote('<p class="replace-classname-from">DOM置き換えテスト</p>', '/').'/', $contents) );
		$this->assertSame( 1, preg_match('/'.preg_quote('<p class="replace-classname-from"><?= \'DOM置き換えテスト\' ?></p>', '/').'/', $contents) );
		$this->assertSame( 1, preg_match('/'.preg_quote('<p class="replace-classname-from"><?php print(\'DOM置き換えテスト\'); ?></p>', '/').'/', $contents) );
		$this->assertSame( 1, preg_match('/サブコンテンツエリア/', $contents) );
	}

}

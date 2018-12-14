<?php
/**
 * test for px2-asazuke2
 */
class pxcommandTest extends PHPUnit_Framework_TestCase{

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new \tomk79\filesystem();
	}


	/**
	 * Full Options as PX Command
	 */
	public function testPxCommand(){
		$path_output = __DIR__."/output/";
		$this->fs->rm($path_output.'_logs/');
		$this->fs->rm($path_output.'contents/');
		$this->fs->rm($path_output.'sitemaps/');

		$stdout = shell_exec('php '.__DIR__.'/testdata/px2doc001/.px_execute.php /?PX=asazuke2.run');
		// var_dump($stdout);

		$this->assertTrue( is_file(__DIR__.'/output/_logs/execute_list.csv') );
		$this->assertTrue( is_file(__DIR__.'/output/contents/index.html') );
		$this->assertTrue( is_file(__DIR__.'/output/sitemaps/sitemap.csv') );

		$sitemapCsv = $this->fs->read_csv(__DIR__.'/output/sitemaps/sitemap.csv');
		// var_dump($sitemapCsv);
		$this->assertEquals( $sitemapCsv[4][0], '/test3.html' );
		$this->assertEquals( $sitemapCsv[4][3], 'test3' );
		$this->assertEquals( $sitemapCsv[4][8], '/test1.html>/test2.html' );

		$contents = $this->fs->read_file(__DIR__.'/output/contents/index.html');
		// var_dump($contents);
		$this->assertSame( 0, preg_match('/'.preg_quote('href="/common/css/style.css"', '/').'/', $contents) );
		$this->assertSame( 0, preg_match('/'.preg_quote('src="/js/test.js"', '/').'/', $contents) );
		$this->assertSame( 1, preg_match('/'.preg_quote('src="/js/test2.js"', '/').'/', $contents) );
		$this->assertSame( 1, preg_match('/<p>これはこんてーんつえりあぁ。<\/p>/', $contents) );
		$this->assertSame( 1, preg_match('/'.preg_quote('<div><p>DOM置き換えテスト</p></div>', '/').'/', $contents) );
		$this->assertSame( 1, preg_match('/'.preg_quote('<div><p><?= \'DOM置き換えテスト\' ?></p></div>', '/').'/', $contents) );
		$this->assertSame( 1, preg_match('/'.preg_quote('<div><p><?php print(\'DOM置き換えテスト\'); ?></p></div>', '/').'/', $contents) );
		$this->assertSame( 1, preg_match('/サブこんてーんつえりあぁ/', $contents) );

	}

}

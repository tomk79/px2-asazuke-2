<?php
/**
 * test for px2-asazuke2
 */
class mainTest extends PHPUnit_Framework_TestCase{

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new \tomk79\filesystem();
	}


	/**
	 * Full Options
	 */
	public function testStandard(){
		$path_output = __DIR__."/output/";
		$this->fs->rm($path_output.'_logs/');
		$this->fs->rm($path_output.'contents/');
		$this->fs->rm($path_output.'sitemaps/');

		$az = new tomk79\pickles2\asazuke2\az(
			__DIR__."/testdata/htdocs_001/",
			$path_output,
			array(
				"path_startpage" => "/",
				"accept_html_file_max_size" => 10000000,
				"crawl_max_url_number" => 10000000, // 1回のクロールで処理できる最大URL数, URLなので、画像などのリソースファイルも含まれる。
				"download_list_csv_charset" => "UTF-8", // ダウンロードリストCSVの文字コード: `null` が指定される場合、 `mb_internal_encoding()` を参照する。
				"select_cont_main" => array(
					array(
						"name" => "Primary Contents 1",
						"selector" => ".contents",
						"index" => 0,
					),
				),
				"select_cont_subs" => array(
					array(
						"name" => "Secondary Contents",
						"selector" => ".subcont",
						"index" => 0,
						"cabinet_name" => "subcont",
					),
				),
				"dom_convert" => array(
					array(
						"name" => "Convert Test",
						"selector" => ".replace-classname-from",
						"replace_to" => '<div><p>{$innerHTML}</p></div>',
					),
				),
				"select_breadcrumb" => array(
					array(
						"name" => "Default Breadcrumb",
						"selector" => ".breadcrumb",
						"index" => 0,
					),
				),
				"replace_title" => array(
					array(
						"name" => "Default Title",
						"preg_pattern" => '/^(.*) \- test site 001$/s',
						"replace_to" => '$1',
					),
				),
				"replace_strings" => array(
					array(
						"name" => "Default Replacement",
						"preg_pattern" => '/コンテンツエリア/s',
						"replace_to" => 'こんてーんつえりあぁ',
					),
				),
				"ignore_common_resources" => array(
					array(
						"name" => "test.js",
						"path" => "/js/test.js",
					),
				),
			)
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

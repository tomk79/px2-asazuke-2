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
		$path_output = __DIR__."/output/";
		if(!is_dir($path_output)){
			mkdir( $path_output );
		}
		$az = new tomk79\pickles2\asazuke2\az( array(
			"path_docroot" => __DIR__."/testdata/htdocs_001/",
			"path_output" => $path_output,
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
		) );
		$this->assertTrue( is_object($az) );
		$crawlctrl = $az->factory_crawlctrl(array('run'));
		$this->assertTrue( is_object($crawlctrl) );
		$crawlctrl->start();
	}

}

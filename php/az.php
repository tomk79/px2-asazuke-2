<?php
/**
 * Asazuke 2
 */
namespace tomk79\pickles2\asazuke2;

/**
 * Asazuke 2
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class az{

	private $fs;
	private $config;

	private $path_docroot;
	private $path_output;

	/**
	 * Before sitemap function
	 * @param object $px Picklesオブジェクト
	 * @param object $json プラグイン設定
	 */
	public static function register( $px, $json ){

		if( !is_object($json) ){
			$json = json_decode('{}');
		}
		if( !property_exists($json, 'path_docroot') ){
			$json->path_docroot = null;
		}
		if( !property_exists($json, 'path_output') ){
			$json->path_output = null;
		}
		if( !property_exists($json, 'options') ){
			$json->options = array();
		}
		$json->options = json_decode( json_encode($json->options), true );
		// var_dump($json);

		$self = new self(
			$json->path_docroot,
			$json->path_output,
			$json->options
		);

		$px->pxcmd()->register('asazuke2', function($px) use($self){
			$pxcmd = $px->get_px_command();
			if( @$pxcmd[1] == 'run' ){
				$self->start();
				exit;
			}
		});
	}

	/**
	 * コンストラクタ
	 */
	public function __construct( $path_docroot, $path_output, $config = array() ){
		$this->path_docroot = $path_docroot;
		$this->path_output = $path_output;
		$config = json_decode( json_encode( $config ), true );
		if( !is_array( $config ) ){
			return false;
		}

		// オプション値の初期化

		// トップページのパス
		if( !array_key_exists('path_startpage', $config) || !strlen($config['path_startpage']) ){
			$config['path_startpage'] = '/';
		}
		// 解析するHTMLファイルの最大容量(bytes)
		if( !array_key_exists('accept_html_file_max_size', $config) || !is_int($config['accept_html_file_max_size']) ){
			$config['accept_html_file_max_size'] = 10000000;
		}
		// 処理するURL数(=ファイル数) の上限
		if( !array_key_exists('crawl_max_url_number', $config) || !is_int($config['crawl_max_url_number']) ){
			$config['crawl_max_url_number'] = 10000000;
		}
		// `execute_list.csv` の文字セット
		if( !array_key_exists('execute_list_csv_charset', $config) || !strlen($config['execute_list_csv_charset']) ){
			$config['execute_list_csv_charset'] = 'UTF-8';
		}
		// メインコンテンツエリアの抽出条件
		if( !array_key_exists('select_cont_main', $config) || !is_array($config['select_cont_main']) ){
			$config['select_cont_main'] = array();
		}
		// サブコンテンツエリアの抽出条件
		if( !array_key_exists('select_cont_subs', $config) || !is_array($config['select_cont_subs']) ){
			$config['select_cont_subs'] = array();
		}
		// 特定のDOM要素を別のタグやクラス名などの構造に変換する
		if( !array_key_exists('dom_convert', $config) || !is_array($config['dom_convert']) ){
			$config['dom_convert'] = array();
		}
		// パンくず情報の抽出条件
		if( !array_key_exists('select_breadcrumb', $config) || !is_array($config['select_breadcrumb']) ){
			$config['select_breadcrumb'] = array();
		}
		// ページ名の置換条件
		if( !array_key_exists('replace_title', $config) || !is_array($config['replace_title']) ){
			$config['replace_title'] = array();
		}
		// 文字列置換パターン
		if( !array_key_exists('replace_strings', $config) || !is_array($config['replace_strings']) ){
			$config['replace_strings'] = array();
		}
		// コンテンツから取り除く共有のリソースのパス
		if( !array_key_exists('ignore_common_resources', $config) || !is_array($config['ignore_common_resources']) ){
			$config['ignore_common_resources'] = array();
		}

		array_push($config['select_cont_main'], array(
			"name" => "<body>",
			"selector" => "body",
			"index" => 0,
		));
		array_push($config['select_cont_main'], array(
			"name" => "<html>",
			"selector" => "html",
			"index" => 0,
		));
		array_push($config['select_breadcrumb'], array(
			"name" => ".breadcrumb",
			"selector" => ".breadcrumb",
			"index" => 0,
		));

		$this->config = $config;
		$this->fs = new \tomk79\filesystem();
	}

	/**
	 * $fs
	 */
	public function fs(){
		return $this->fs;
	}

	/**
	 * $config
	 */
	public function config(){
		return $this->config;
	}

	/**
	 * エラーログを出力
	 */
	public function error_log( $msg, $file, $line ){
		echo( trim($msg).' - '.$file.' Line:'.$line."\n" );
	}


	/**
	 * ドキュメントルートディレクトリの取得
	 */
	public function get_path_docroot(){
		return $this->path_docroot;
	}

	/**
	 * 出力先ディレクトリの取得
	 */
	public function get_path_output_dir(){
		return $this->path_output;
	}

	/**
	 * Asazuke 2 を実行する
	 */
	public function start(){
		$exec = new execute( $this );
		return $exec->start();
	}

	/**
	 * 出力先フォルダのファイルを消去する
	 */
	public function clear_output_files(){
		$target_dir = $this->get_path_output_dir();
		$this->fs()->rm( $target_dir.'/_logs/' );
		$this->fs()->rm( $target_dir.'/contents/' );
		$this->fs()->rm( $target_dir.'/sitemaps/' );

		$res1 = !$this->fs()->is_dir( $target_dir.'/_logs/' );
		$res2 = !$this->fs()->is_dir( $target_dir.'/contents/' );
		$res3 = !$this->fs()->is_dir( $target_dir.'/sitemaps/' );
		return ($res1 && $res2 && $res3);
	}


	/**
	 * ファクトリ：プロジェクトモデル
	 */
	public function factory_model_project(){
		$obj = new model_project( $this );
		return	$obj;
	}



	/**
	 * サイトマップCSVの定義を取得する
	 * @return array サイトマップCSV定義配列
	 */
	public function get_sitemap_definition(){
		$col = 'A';
		$num = 0;
		$rtn = array();
		$rtn['path'] = array('num'=>$num++,'col'=>$col++,'key'=>'path','name'=>'ページのパス');
		$rtn['content'] = array('num'=>$num++,'col'=>$col++,'key'=>'content','name'=>'コンテンツファイルの格納先');
		$rtn['id'] = array('num'=>$num++,'col'=>$col++,'key'=>'id','name'=>'ページID');
		$rtn['title'] = array('num'=>$num++,'col'=>$col++,'key'=>'title','name'=>'ページタイトル');
		$rtn['title_breadcrumb'] = array('num'=>$num++,'col'=>$col++,'key'=>'title_breadcrumb','name'=>'ページタイトル(パン屑表示用)');
		$rtn['title_h1'] = array('num'=>$num++,'col'=>$col++,'key'=>'title_h1','name'=>'ページタイトル(H1表示用)');
		$rtn['title_label'] = array('num'=>$num++,'col'=>$col++,'key'=>'title_label','name'=>'ページタイトル(リンク表示用)');
		$rtn['title_full'] = array('num'=>$num++,'col'=>$col++,'key'=>'title_full','name'=>'ページタイトル(タイトルタグ用)');
		$rtn['logical_path'] = array('num'=>$num++,'col'=>$col++,'key'=>'logical_path','name'=>'論理構造上のパス');
		$rtn['list_flg'] = array('num'=>$num++,'col'=>$col++,'key'=>'list_flg','name'=>'一覧表示フラグ');
		$rtn['layout'] = array('num'=>$num++,'col'=>$col++,'key'=>'layout','name'=>'レイアウト');
		$rtn['orderby'] = array('num'=>$num++,'col'=>$col++,'key'=>'orderby','name'=>'表示順');
		$rtn['keywords'] = array('num'=>$num++,'col'=>$col++,'key'=>'keywords','name'=>'metaキーワード');
		$rtn['description'] = array('num'=>$num++,'col'=>$col++,'key'=>'description','name'=>'metaディスクリプション');
		$rtn['category_top_flg'] = array('num'=>$num++,'col'=>$col++,'key'=>'category_top_flg','name'=>'カテゴリトップフラグ');
		$rtn['role'] = array('num'=>$num++,'col'=>$col++,'key'=>'role','name'=>'ロール');
		$rtn['proc_type'] = array('num'=>$num++,'col'=>$col++,'key'=>'proc_type','name'=>'コンテンツの処理方法');
		return $rtn;
	}

}

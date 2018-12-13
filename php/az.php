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

	#--------------------------------------
	#	設定項目
	private $conf_crawl_max_url_number = 10000000;
	private $conf_download_list_csv_charset = 'SJIS-win';
	#	/ 設定項目
	#--------------------------------------

	/**
	 * コンストラクタ
	 */
	public function __construct( $config ){
		$config = json_decode( json_encode( $config ), true );
		if( !is_array( $config ) ){
			return false;
		}
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
	 * 出力先ディレクトリの取得
	 */
	public function get_output_dir(){
		return $this->config['path_output'];
	}


	/**
	 * ファクトリ：プロジェクトモデル
	 */
	public function factory_model_project(){
		$obj = new model_project( $this );
		return	$obj;
	}



	/**
	 * ファクトリ：クローラインスタンスを取得
	 */
	public function factory_crawlctrl($cmd){
		$obj = new crawlctrl( $this, $cmd );
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

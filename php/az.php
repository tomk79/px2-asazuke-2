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
	private $req;

	private $config;

	#--------------------------------------
	#	設定項目

	/** プロジェクトディレクトリ */
	private $localpath_proj_dir = '/proj';

	/** ログディレクトリ */
	private $localpath_log_dir = '/logs';

	/** プロセス記憶ディレクトリ */
	private $localpath_proc_dir = '/proc';

	/**
	 * 1回のクロールで処理できる最大URL数
	 * URLなので、画像などのリソースファイルも含まれる。
	 */
	private $conf_crawl_max_url_number = 10000000;

	/**
	 * クロール結果を管理画面からダウンロードするときに、
	 * ファイル名にクロール日時を含めるか否か
	 */
	private $conf_dl_datetime_in_filename = true;

	/**
	 * ダウンロードリストCSVの文字コード
	 * `null` が指定される場合、 `mb_internal_encoding()` を参照する。
	 */
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
		$this->req = new \tomk79\request();
	}

	/**
	 * $fs
	 */
	public function fs(){
		return $this->fs;
	}

	/**
	 * $req
	 */
	public function req(){
		return $this->req;
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
	 * 設定値を取得
	 */
	public function get_value( $key ){
		if( !preg_match( '/^[a-zA-Z][a-zA-Z0-9_]*$/' , $key ) ){ return false; }
		$RTN = @eval( 'return $this->conf_'.strtolower( $key ).';' );
		return	$RTN;
	}

	/**
	 * 値を設定
	 */
	public function set_value( $key , $val ){
		if( !preg_match( '/^[a-zA-Z][a-zA-Z0-9_]*$/' , $key ) ){ return false; }
		@eval( '$this->conf_'.strtolower( $key ).' = '.json_encode( $val ).';' );
		return	true;
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

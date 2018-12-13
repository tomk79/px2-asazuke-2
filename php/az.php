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

	#--------------------------------------
	#	設定項目

	/** PicklesCrawlerのホームディレクトリ設定 */
	private $path_home_dir = null;

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

	/** 外部コマンドのパス */
	private $path_commands = array(
		'tar'=>'tar' ,
	);

	#	/ 設定項目
	#--------------------------------------

	/**
	 * コンストラクタ
	 */
	public function __construct(){
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
	 * ホームディレクトリの設定
	 */
	public function set_home_dir( $path ){
		if( !strlen( $path ) ){ return false; }
		$path = $this->fs->get_realpath( $path );
		if( !$this->fs->is_writable( $path ) ){
			return	false;
		}

		$this->path_home_dir = $path;
		return	true;
	}
	/**
	 * ホームディレクトリの取得
	 */
	public function get_home_dir(){
		return	$this->path_home_dir;
	}

	/**
	 * プロジェクトディレクトリの取得
	 */
	public function get_proj_dir(){
		if( !is_dir( $this->get_home_dir().$this->localpath_proj_dir ) ){
			if( !$this->fs->mkdir_r( $this->get_home_dir().$this->localpath_proj_dir ) ){
				return	false;
			}
		}
		return	$this->get_home_dir().$this->localpath_proj_dir;
	}

	/**
	 * プログラムディレクトリの取得
	 */
	public function get_program_home_dir(){
		// if( !strlen( $project_id ) ){ return false; }
		$proj_dir = $this->get_proj_dir();
		if( !is_dir( $proj_dir ) ){
			return	false;
		}
		if( !is_dir( $proj_dir.'/prg' ) ){
			if( !$this->fs->mkdir( $proj_dir.'/prg' ) ){
				return	false;
			}
		}
		// if( strlen( $program_id ) ){
		// 	return	$proj_dir.'/prg/';
		// }
		return	$proj_dir.'/prg';
	}

	/**
	 * ログディレクトリの取得
	 */
	public function get_log_dir(){
		if( !is_dir( $this->get_home_dir().$this->localpath_log_dir ) ){
			if( !$this->fs->mkdir( $this->get_home_dir().$this->localpath_log_dir ) ){
				return	false;
			}
		}
		return	$this->get_home_dir().$this->localpath_log_dir;
	}

	/**
	 * プロセス記憶ディレクトリの取得
	 */
	public function get_proc_dir(){
		if( !is_dir( $this->get_home_dir().$this->localpath_proc_dir ) ){
			if( !$this->fs->mkdir( $this->get_home_dir().$this->localpath_proc_dir ) ){
				return	false;
			}
		}
		return	$this->get_home_dir().$this->localpath_proc_dir;
	}


	/**
	 * コマンドのパスを取得する
	 */
	public function get_path_command($cmd){
		if( !strlen( $this->path_commands[$cmd] ) ){
			return false;
		}
		return trim($this->path_commands[$cmd]);
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

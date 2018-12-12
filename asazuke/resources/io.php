<?php

/**
 * インポート・エクスポート
 * Copyright (C)Tomoya Koyanagi.
 */
class pxplugin_asazuke_resources_io{

	private $pcconf;

	/**
	 * コンストラクタ
	 */
	public function __construct( $pcconf ){
		$this->pcconf = $pcconf;
	}

	/**
	 * エクスポートファイルを作成する
	 */
	public function mk_export_file( $ziptype , $options = array() ){

		#	エクスポートを実行
		if( !$this->local_export( $options ) ){
			return false;
		}

		$path_export_dir = $this->pcconf->get_home_dir().'/_export/';

		$download_content_path = $path_export_dir.'tmp/';
		$download_zipto_path = $path_export_dir.'PxCrawer_export_'.date('Ymd_His');
		if( !is_dir( $download_content_path ) ){
			return false;//←圧縮対象が存在しません。
		}

		if( strtolower($ziptype) == 'tgz' && strlen( $this->pcconf->get_path_command('tar') ) ){
			#	tarコマンドが使えたら(UNIXのみ)
			$className = 'pxplugin_asazuke_resources_tgz';
			$obj_tgz = new $className( $this->pcconf, $this->pcconf->get_path_command('tar') );

			if( !$obj_tgz->zip( $download_content_path , $download_zipto_path.'.tgz' ) ){
				return false;//圧縮に失敗しました。
			}

			if( !is_file( $download_zipto_path.'.tgz' ) ){
				return false;//圧縮されたアーカイブファイルは現在は存在しません。
			}

			$download_zipto_path = $download_zipto_path.'.tgz';

		}elseif( strtolower($ziptype) == 'zip' && class_exists( 'ZipArchive' ) ){
			#	ZIP関数が有効だったら
			$className = 'pxplugin_asazuke_resources_zip';
			$obj_zip = new $className( $this->pcconf );

			if( !$obj_zip->zip( $download_content_path , $download_zipto_path.'.zip' ) ){
				return false;//圧縮に失敗しました。
			}

			if( !is_file( $download_zipto_path.'.zip' ) ){
				return false;//圧縮されたアーカイブファイルは現在は存在しません。
			}

			$download_zipto_path = $download_zipto_path.'.zip';

		}

		if( is_file( $download_zipto_path ) ){
			return $download_zipto_path;
		}

		return false;
	}// mk_export_file()

	/**
	 * エクスポートデータを作成
	 */
	private function local_export( $options = array() ){
		$path_export_dir = $this->pcconf->get_home_dir().'/_export/';

		$this->pcconf->fs()->rm( $path_export_dir );
		$this->pcconf->fs()->mkdir_r( $path_export_dir );
		$this->pcconf->fs()->mkdir_r( $path_export_dir.'tmp/' );

		$projList = $this->pcconf->fs()->ls( $this->pcconf->get_home_dir().'/proj/' );
		foreach( $projList as $project_id ){
			if( @count( $options['project'] ) && !$options['project'][$project_id] ){
				continue;
			}
			$this->pcconf->fs()->mkdir_r( $path_export_dir.'tmp/'.$project_id.'/' );
			$this->local_export_project(
				$this->pcconf->get_home_dir().'/proj/'.$project_id.'/' ,
				$path_export_dir.'tmp/'.$project_id.'/'
			);
		}

		return true;
	}//local_export()

	/**
	 * プロジェクトをエクスポートフォルダにコピーする
	 */
	private function local_export_project( $from , $to ){
		$projFileList = $this->pcconf->fs()->ls( $from );
		foreach( $projFileList as $project_filename ){
			$tmp_path = $from.$project_filename;
			if( is_dir( $tmp_path ) ){
				$this->pcconf->fs()->mkdir_r( $to.$project_filename.'/' );
				if( $project_filename == 'prg' ){
					$projPrgList = $this->pcconf->fs()->ls( $from.$project_filename.'/' );
					foreach( $projPrgList as $program_id ){
						$this->pcconf->fs()->mkdir_r( $to.$project_filename.'/'.$program_id.'/' );
						$result = $this->local_export_program(
							$from.$project_filename.'/'.$program_id.'/' ,
							$to.$project_filename.'/'.$program_id.'/'
						);
					}
				}
			}elseif( is_file( $tmp_path ) ){
				$this->pcconf->fs()->copy(
					$tmp_path ,
					$to.$project_filename
				);
			}
		}
		return true;
	}// local_export_project()

	/**
	 * プログラムをエクスポートフォルダにコピーする
	 */
	private function local_export_program( $from , $to ){
		if( !is_dir( $from ) ){ return false; }
		$from = $this->pcconf->fs()->get_realpath( $from ).'/';
		if( !is_dir( $to ) ){ return false; }
		$to = $this->pcconf->fs()->get_realpath( $to ).'/';

		$prgFileList = $this->pcconf->fs()->ls( $from );
		foreach( $prgFileList as $prgFile ){
			if( is_dir( $from.$prgFile ) ){
				$this->pcconf->fs()->mkdir($to.$prgFile);
			}elseif( is_file( $from.$prgFile ) ){
				$this->pcconf->fs()->copy(
					$from.$prgFile ,
					$to.$prgFile
				);
			}
		}

		return true;
	}// local_export_program()

}

?>
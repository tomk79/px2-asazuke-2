<?php
/**
 * Asazuke 2
 */
namespace tomk79\pickles2\asazuke2;

/**
 * インポート・エクスポート
 * Copyright (C)Tomoya Koyanagi.
 */
class resources_io{

	private $az;

	/**
	 * コンストラクタ
	 */
	public function __construct( $az ){
		$this->az = $az;
	}

	/**
	 * エクスポートファイルを作成する
	 */
	public function mk_export_file( $ziptype , $options = array() ){

		#	エクスポートを実行
		if( !$this->local_export( $options ) ){
			return false;
		}

		$path_export_dir = $this->az->get_home_dir().'/_export/';

		$download_content_path = $path_export_dir.'tmp/';
		$download_zipto_path = $path_export_dir.'PxCrawer_export_'.date('Ymd_His');
		if( !is_dir( $download_content_path ) ){
			return false;//←圧縮対象が存在しません。
		}

		if( strtolower($ziptype) == 'tgz' && strlen( $this->az->get_path_command('tar') ) ){
			#	tarコマンドが使えたら(UNIXのみ)
			$obj_tgz = new resources_tgz( $this->az, $this->az->get_path_command('tar') );

			if( !$obj_tgz->zip( $download_content_path , $download_zipto_path.'.tgz' ) ){
				return false;//圧縮に失敗しました。
			}

			if( !is_file( $download_zipto_path.'.tgz' ) ){
				return false;//圧縮されたアーカイブファイルは現在は存在しません。
			}

			$download_zipto_path = $download_zipto_path.'.tgz';

		}elseif( strtolower($ziptype) == 'zip' && class_exists( 'ZipArchive' ) ){
			#	ZIP関数が有効だったら
			$obj_zip = new resources_zip( $this->az );

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
		$path_export_dir = $this->az->get_home_dir().'/_export/';

		$this->az->fs()->rm( $path_export_dir );
		$this->az->fs()->mkdir_r( $path_export_dir );
		$this->az->fs()->mkdir_r( $path_export_dir.'tmp/' );

		$projList = $this->az->fs()->ls( $this->az->get_home_dir().'/proj/' );
		foreach( $projList as $project_id ){
			if( @count( $options['project'] ) && !$options['project'][$project_id] ){
				continue;
			}
			$this->az->fs()->mkdir_r( $path_export_dir.'tmp/'.$project_id.'/' );
			$this->local_export_project(
				$this->az->get_home_dir().'/proj/'.$project_id.'/' ,
				$path_export_dir.'tmp/'.$project_id.'/'
			);
		}

		return true;
	}//local_export()

	/**
	 * プロジェクトをエクスポートフォルダにコピーする
	 */
	private function local_export_project( $from , $to ){
		$projFileList = $this->az->fs()->ls( $from );
		foreach( $projFileList as $project_filename ){
			$tmp_path = $from.$project_filename;
			if( is_dir( $tmp_path ) ){
				$this->az->fs()->mkdir_r( $to.$project_filename.'/' );
				if( $project_filename == 'prg' ){
					$projPrgList = $this->az->fs()->ls( $from.$project_filename.'/' );
					foreach( $projPrgList as $program_id ){
						$this->az->fs()->mkdir_r( $to.$project_filename.'/'.$program_id.'/' );
						$result = $this->local_export_program(
							$from.$project_filename.'/'.$program_id.'/' ,
							$to.$project_filename.'/'.$program_id.'/'
						);
					}
				}
			}elseif( is_file( $tmp_path ) ){
				$this->az->fs()->copy(
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
		$from = $this->az->fs()->get_realpath( $from ).'/';
		if( !is_dir( $to ) ){ return false; }
		$to = $this->az->fs()->get_realpath( $to ).'/';

		$prgFileList = $this->az->fs()->ls( $from );
		foreach( $prgFileList as $prgFile ){
			if( is_dir( $from.$prgFile ) ){
				$this->az->fs()->mkdir($to.$prgFile);
			}elseif( is_file( $from.$prgFile ) ){
				$this->az->fs()->copy(
					$from.$prgFile ,
					$to.$prgFile
				);
			}
		}

		return true;
	}// local_export_program()

}

?>
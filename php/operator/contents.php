<?php
/**
 * Asazuke 2
 */
namespace tomk79\pickles2\asazuke2;

/**
 * オペレータ：コンテンツ
 * Copyright (C)Tomoya Koyanagi.
 */
class operator_contents{

	private $az;
	private $obj_proj;
	private $path = null;
	private $report = array();

	private $dom_convert_replace_template = null;

	/**
	 * コンストラクタ
	 */
	public function __construct( $az, $obj_proj ){
		$this->az = $az;
		$this->obj_proj = $obj_proj;
	}

	/**
	 * ファクトリ：DOMパーサー
	 */
	private function factory_dom_parser($path, $type = 'path'){
		$obj = new resources_PxXMLDomParser( $path, $type );
		return	$obj;
	}

	/**
	 * 結果を受け取る
	 */
	public function get_result(){
		return $this->report;
	}

	/**
	 * スクレイピングを実行する
	 */
	public function scrape($path, $fullpath_savetmpfile_to , $fullpath_save_to){
		$this->path = $path;
		$ext = $this->az->fs()->get_extension($this->path);
		switch( strtolower($ext) ){
			case 'html':
				break;
			default:
				// HTML以外はコピーするだけ。
				$this->az->fs()->mkdir_all( dirname($fullpath_save_to) );
				return $this->az->fs()->copy( $fullpath_savetmpfile_to, $fullpath_save_to );
				break;
		}

		if( $this->obj_proj->get_accept_html_file_max_size() > 0 && filesize( $fullpath_savetmpfile_to ) > $this->obj_proj->get_accept_html_file_max_size() ){
			// 設定より大きいファイルは、コピーするだけ。
			$this->report['errors'] = '[error] file size '.filesize( $fullpath_savetmpfile_to ).' byte(s) is over accept_html_file_max_size '.$this->obj_proj->get_accept_html_file_max_size().' byte(s).';
			$this->az->fs()->mkdir_all( dirname($fullpath_save_to) );
			return $this->az->fs()->copy( $fullpath_savetmpfile_to, $fullpath_save_to );
		}

		$content_src = '';

		// ヘッドセクションのソースを取得
		$content_src .= $this->get_header_src( $fullpath_savetmpfile_to );
		$content_src .= "\n"."\n";

		// メインコンテンツを取得
		$content_src .= $this->get_main_contents_src( $fullpath_savetmpfile_to );
		$content_src .= "\n"."\n";

		// サブコンテンツを取得
		$content_src .= $this->get_sub_contents_src( $fullpath_savetmpfile_to );
		$content_src .= "\n"."\n";

		$content_src = preg_replace( '/\r\n|\r|\n/si', "\r\n", $content_src );//CRLFに変換

		$result = $this->az->fs()->file_overwrite( $fullpath_save_to, $content_src );
		return $result;
	}//scrape()


	/**
	 * ヘッダー部分のソースを取得する
	 */
	private function get_header_src( $fullpath_savetmpfile_to ){
		$selectRules = $this->obj_proj->get_select_cont_subs();

		$tmpDOM = null;
		$src = '';
		$domParser = $this->factory_dom_parser($fullpath_savetmpfile_to);
		$tmpDOM = $domParser->find( 'head' );
		$header_src = $tmpDOM[0]['innerHTML'];

		$domParser = $this->factory_dom_parser($header_src, 'bin');
		// titleタグを削除
		$domParser->select('title');
		$domParser->replace( array( $this , 'callback_replace_dom_title' ) );
		// metaタグを精査
		$domParser->select('meta');
		$domParser->replace( array( $this , 'callback_replace_dom_meta' ) );
		// scriptタグを精査
		$domParser->select('script');
		$domParser->replace( array( $this , 'callback_replace_dom_script' ) );
		// linkタグを精査
		$domParser->select('link');
		$domParser->replace( array( $this , 'callback_replace_dom_link' ) );

		$header_src = $domParser->get_src();

		$src .= '<'.'?php ob_start(); ?'.'>'."\n";
		$src .= '<'.'?php /* ------ head section contents ------ */ ?'.'>'."\n";
		$src .= $header_src."\n";
		$src .= '<'.'?php $px->theme()->send_content(ob_get_clean(), '.json_encode( 'head' ).'); ?'.'>'."\n";
		$src .= "\n";

		return $src;
	}
	/**
	 * callback: titleタグを置き換える。
	 */
	public function callback_replace_dom_title( $dom , $num ){
		// タイトルタグは削除
		// サイトマップパースのプロセスで拾っているので捨ててよし。
		return '';
	}//callback_replace_dom_title()
	/**
	 * callback: metaタグを置き換える。
	 */
	public function callback_replace_dom_meta( $dom , $num ){
		return '';
	}//callback_replace_dom_meta()
	/**
	 * callback: scriptタグを置き換える。
	 */
	public function callback_replace_dom_script( $dom , $num ){
		$src = trim($dom['attributes']['src']);
		if( !preg_match('/^\//', $src) ){
			$src = $this->az->fs()->get_realpath( dirname($this->path).'/'.$src );
		}
		if( $this->obj_proj->is_ignore_common_resources( $src ) ){
			// 除外リソースなら削除する
			return '';
		}
		return $dom['outerHTML'];
	}//callback_replace_dom_script()
	/**
	 * callback: linkタグを置き換える。
	 */
	public function callback_replace_dom_link( $dom , $num ){
		$rel = trim(strtolower($dom['attributes']['rel']));
		switch( $rel ){
			case 'stylesheet':
				break;
			default:
				return '';
		}
		$href = trim($dom['attributes']['href']);
		if( !preg_match('/^\//', $href) ){
			$href = $this->az->fs()->get_realpath( dirname($this->path).'/'.$href );
		}
		if( $this->obj_proj->is_ignore_common_resources( $href ) ){
			// 除外リソースなら削除する
			// 報告
			if( !is_array($this->report['ignore_common_resources']) ){
				$this->report['ignore_common_resources'] = array();
			}
			array_push( $this->report['ignore_common_resources'], $this->obj_proj->last_matched_ignore_common_resources() );
			return '';
		}
		return $dom['outerHTML'];
	}//callback_replace_dom_link()


	/**
	 * メインコンテンツソースを取得する
	 */
	private function get_main_contents_src( $fullpath_savetmpfile_to ){
		$domParser = $this->factory_dom_parser($fullpath_savetmpfile_to);
		$selectRules = $this->obj_proj->get_select_cont_main();

		$tmpDOM = null;
		$src = '';
		foreach( $selectRules as $ruleRow ){
			$tmpDOM = $domParser->find( $ruleRow['selector'] );
			if( is_null($tmpDOM[$ruleRow['index']]) ){
				continue;
			}
			$src .= $this->src_standard_replacement( $tmpDOM[$ruleRow['index']]['innerHTML'] );

			// 報告
			$this->report['main_contents:pattern'] = $ruleRow['name'];
			break;
		}
		return $src;
	}

	/**
	 * サブコンテンツソースを取得する
	 */
	private function get_sub_contents_src( $fullpath_savetmpfile_to ){
		$domParser = $this->factory_dom_parser($fullpath_savetmpfile_to);
		$selectRules = $this->obj_proj->get_select_cont_subs();

		$tmpDOM = null;
		$src = '';
		foreach( $selectRules as $ruleRow ){
			$tmpDOM = $domParser->find( $ruleRow['selector'] );
			if( is_null($tmpDOM[$ruleRow['index']]) ){
				continue;
			}
			$src .= '<'.'?php ob_start(); ?'.'>'."\n";
			$src .= '<'.'?php /* ------ sub contents '.json_encode( $ruleRow['cabinet_name'] ).' ------ */ ?'.'>'."\n";
			$src .= $this->src_standard_replacement( $tmpDOM[$ruleRow['index']]['innerHTML'] )."\n";
			$src .= '<'.'?php $px->theme()->send_content(ob_get_clean(), '.json_encode( $ruleRow['cabinet_name'] ).'); ?'.'>'."\n";
			$src .= "\n";

			// 報告
			if( !is_array($this->report['sub_contents:pattern']) ){
				$this->report['sub_contents:pattern'] = array();
			}
			array_push( $this->report['sub_contents:pattern'], $ruleRow['name'] );
		}
		return $src;
	}

	/**
	 * ソースの標準置換処理
	 */
	private function src_standard_replacement( $src ){
		$src = $this->dom_convert( $src );
		$src = $this->replace_strings( $src );
		return $src;
	}

	/**
	 * 文字列置換を実行する。
	 */
	private function replace_strings( $str ){
		$replaceRules = $this->obj_proj->get_replace_strings();
		foreach( $replaceRules as $ruleRow ){
			if( preg_match($ruleRow['preg_pattern'], $str) ){
				$str = preg_replace($ruleRow['preg_pattern'], $ruleRow['replace_to'], $str);

				// 報告
				if( !is_array($this->report['replace_strings']) ){
					$this->report['replace_strings'] = array();
				}
				array_push( $this->report['replace_strings'], $ruleRow['name'] );
			}
		}
		return $str;
	}

	/**
	 * DOM置換を実行する。
	 */
	private function dom_convert( $str ){
		// [UTODO] 開発中
		$replaceRules = $this->obj_proj->get_dom_convert();

		foreach( $replaceRules as $ruleRow ){
			$domParser = $this->factory_dom_parser($str, 'bin');
			// DOM変換
			if( count( $domParser->find($ruleRow['selector']) ) ){
				$domParser->select($ruleRow['selector']);
				$this->dom_convert_replace_template = $ruleRow['replace_to'];
				$domParser->replace( array( $this , 'callback_replace_dom_convert' ) );
				$this->dom_convert_replace_template = null;
				$str = $domParser->get_src();

				// 報告
				if( !is_array($this->report['dom_convert']) ){
					$this->report['dom_convert'] = array();
				}
				array_push( $this->report['dom_convert'], $ruleRow['name'] );
			}

		}

		return $str;
	}
	/**
	 * callback: DOMを置き換える。
	 */
	public function callback_replace_dom_convert( $dom , $num ){
		$template = $this->dom_convert_replace_template;
		$template = preg_replace( '/'.preg_quote('{$innerHTML}','/').'/s', $dom['innerHTML'], $template );
		return $template;
	}//callback_replace_dom_convert()

}

?>
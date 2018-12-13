<?php
/**
 * Asazuke 2
 */
namespace tomk79\pickles2\asazuke2;

/**
 * オペレータ：サイトマップ
 * Copyright (C)Tomoya Koyanagi.
 */
class operator_sitemap{

	private $az;
	private $obj_proj;
	private $path_sitemap_csv;
	private $report = array();

	/**
	 * コンストラクタ
	 */
	public function __construct( $az, $obj_proj, $path_sitemap_csv ){
		$this->az = $az;
		$this->obj_proj = $obj_proj;
		$this->path_sitemap_csv = $path_sitemap_csv;
	}

	/**
	 * ファクトリ：DOMパーサー
	 */
	private function factory_dom_parser($path, $type = 'path'){
		$obj = new libs_PxXMLDomParser( $path , $type );
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
	public function scrape($path, $fullpath_savetmpfile_to){
		$ext = $this->az->fs()->get_extension($path);
		switch( strtolower($ext) ){
			case 'html':
				break;
			default:
				// HTML以外はパースしない
				return true;
				break;
		}

		$row_info = array();
		$row_info['path'] = preg_replace('/\/index\.html$/s', '/', $path);
		if( $this->obj_proj->get_accept_html_file_max_size() > 0 && filesize( $fullpath_savetmpfile_to ) > $this->obj_proj->get_accept_html_file_max_size() ){
			// 設定より大きいファイルは、パースしない
			$row_info['title'] = '[error] file size '.filesize( $fullpath_savetmpfile_to ).' byte(s) is over accept_html_file_max_size '.$this->obj_proj->get_accept_html_file_max_size().' byte(s).';
			$row_info['keywords'] = '';
			$row_info['description'] = '';
			$row_info['logical_path'] = '';
			$row_info['list_flg'] = 1;
		}else{
			// 小さいページは普通にパースする
			$row_info['title'] = $this->get_page_title($fullpath_savetmpfile_to);
			$row_info['keywords'] = $this->get_page_keywords($fullpath_savetmpfile_to);
			$row_info['description'] = $this->get_page_description($fullpath_savetmpfile_to);
			$row_info['logical_path'] = $this->get_page_logical_path($path, $fullpath_savetmpfile_to);
			$row_info['list_flg'] = 1;
		}

		$this->report['title'] = $row_info['title'];

		$this->save_sitemap_row( $row_info );
		return true;
	}//scrape()

	/**
	 * ページタイトル を取得
	 */
	private function get_page_title($path){
		$domParser = $this->factory_dom_parser($path);
		$title = $domParser->find('title');
		$title = htmlspecialchars_decode( $title[0]['innerHTML'] );
		$title_replace_rules = $this->obj_proj->get_replace_title();
		foreach( $title_replace_rules as $ruleRow ){
			if( preg_match($ruleRow['preg_pattern'], $title) ){
				$title = preg_replace($ruleRow['preg_pattern'], $ruleRow['replace_to'], $title);
				$this->report['title:replace_pattern'] = $ruleRow['name'];
				break;
			}
		}
		return $title;
	}

	/**
	 * キーワード を取得
	 */
	private function get_page_keywords($path){
		$domParser = $this->factory_dom_parser($path);
		$meta = $domParser->find('meta[name=keywords]');
		$rtn = htmlspecialchars_decode( @$meta[0]['attributes']['content'] );
		return $rtn;
	}

	/**
	 * description を取得
	 */
	private function get_page_description($path){
		$domParser = $this->factory_dom_parser($path);
		$meta = $domParser->find('meta[name=description]');
		$rtn = htmlspecialchars_decode( @$meta[0]['attributes']['content'] );
		return $rtn;
	}

	/**
	 * パンくず情報を抜き出す
	 */
	private function get_page_logical_path($path, $fullpath_savetmpfile_to){
		$domParser = $this->factory_dom_parser($fullpath_savetmpfile_to);

		$breadcrumbSelector = $this->obj_proj->get_select_breadcrumb();
		$breadcrumb = null;
		foreach( $breadcrumbSelector as $selectorRow ){
			$tmpDOM = $domParser->find($selectorRow['selector']);
			if( @is_null($tmpDOM[$selectorRow['index']]) ){
				continue;
			}
			$breadcrumb = $tmpDOM[$selectorRow['index']]['innerHTML'];
		}
		if(!is_string($breadcrumb)){
			// 設定されたリストでパンくずを発見できなかった場合。
			return '';
		}

		$domParser = $this->factory_dom_parser($breadcrumb, 'bin');
		$links = $domParser->find('a');
		$paths = array();
		foreach($links as $link){
			$href = $link['attributes']['href'];
			if( !preg_match('/^\//', $href) ){
				$href = $this->az->fs()->get_realpath(dirname($path).'/'.$href);
			}
			$href = preg_replace('/\/index\.html((?:\?|\#).*)?$/', '/$1', $href);
			if( $href == $this->obj_proj->get_path_startpage() ){
				// トップページは追加しない
				continue;
			}
			array_push( $paths, $href );
		}
		return implode('>', $paths);
	}

	/**
	 * サイトマップ行を書き出す
	 */
	private function save_sitemap_row( $row_info ){
		$sitemap_definition = $this->az->get_sitemap_definition();
		$sitemap_val_list = array();
		foreach( $sitemap_definition as $row ){
			$cell = null;
			if( array_key_exists($row['key'], $row_info) ){
				$cell = $row_info[$row['key']];
			}
			array_push( $sitemap_val_list, $cell );
		}
		$LINE = '';
		$LINE .= $this->az->fs()->mk_csv(array($sitemap_val_list), array('charset'=>'UTF-8'));

		error_log( $LINE , 3 , $this->path_sitemap_csv );
		return true;
	}

}

?>
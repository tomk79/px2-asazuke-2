# Asazuke 2

Asazuke 2 は、既存のウェブサイトのデータを解析し、[Pickles 2](https://pickles2.pxt.jp/) の形式に置換するスクレイピングツールです。

Pickles Framework 1.x 用に開発された [asazuke plugin](https://github.com/tomk79/PxPlugin_asazuke) を Pickles 2 向けに移植したものです。

Asazuke 2 は、ウェブサイトのクロールは行いません。
先に PicklesCrawler などを使って巡回収集したデータを用意し、
そのデータを Asazuke 2 でスクレイピングする手順で行うと効率的です。

Pickles 2 については、下記のウェブサイトを参照してください。

https://pickles2.pxt.jp/

PicklesCrawler については、下記にリポジトリが公開されています。

https://github.com/tomk79/PxPlugin_PicklesCrawler


## インストール - Install

```
$ git clone https://github.com/tomk79/px2-asazuke2.git
$ cd px2-asazuke2/
$ composer install
```

## 使い方 - Usage

### PHPライブラリとして使う

```php
<?php
require_once('vendor/autoload.php');

$az = new new tomk79\pickles2\asazuke2\az(
    "path/to/documentroot/", // HTMLファイルが格納されているディレクトリ
    "path/to/output_dir/", // 結果を出力する先のディレクトリ (空白のディレクトリをお勧めします)
    array(
        // オプション (後述)
    )
);
$az->start();
```

### コマンドラインで使う

```
$ php az2 --json options.json path/to/documentroot/ path/to/output_dir/
```

1つ目の引数には HTMLファイルが格納されているディレクトリを、
2つ目の引数には 結果を出力する先のディレクトリ (空白のディレクトリをお勧めします) を渡します。

`--json` に、オプション(後述)をJSON形式で保存したファイルのパスを指定します。
JSONファイルの記述サンプルが [options_sample.json](options_sample.json) に同梱されていますので参考にしてください。


### PXコマンドとして使う

`px-files/config.php` に次のように設定します。

```php
/**
 * funcs: Before sitemap
 *
 * サイトマップ読み込みの前に実行するプラグインを設定します。
 */
$conf->funcs->before_sitemap = array(

    /* ... 中略... */

    // PX=asazuke2
    'tomk79\pickles2\asazuke2\az::register('.json_encode(array(
        'path_docroot' => "path/to/documentroot/", // HTMLファイルが格納されているディレクトリ
        'path_output' => "path/to/output_dir/", // 結果を出力する先のディレクトリ (空白のディレクトリをお勧めします)
        'options' => array(
            // オプション (後述)
        ),
    )).')' ,

    /* ... 中略... */

);
```

設定したら、次のPXコマンドからスクレイピングを実行できるようになります。

```
$ php .px_execute.php /?PX=asazuke2.run
```


## オプション - Options

### path_startpage (default: `/`)

トップページのパスを設定します。

### accept_html_file_max_size (default: `10000000`)

解析するHTMLファイルの最大容量を設定します。

### crawl_max_url_number (default: `10000000`)

処理するURL数(=ファイル数) の上限値を設定します。

### execute_list_csv_charset (default: `UTF-8`)

処理されたファイルをレポートする `execute_list.csv` の文字セットを指定します。

### select_cont_main

メインコンテンツエリアの抽出条件を設定します。

配列で複数設定することができます。先頭から検索を始め、最初にマッチした条件を使ってコンテンツを抽出します。

デフォルトで `body`, `html` の各要素を検索する条件が最後尾に追加されます。

```php
    "select_cont_main" => array(
        array(
            "name" => "Primary Contents 1", // 設定名(適用された場合にレポートで使用されます)
            "selector" => ".contents", // 対象要素を示すCSSセレクタ
            "index" => 0, // 複数の要素が見つかる場合のインデックス番号
        ),
        array(
            "name" => "Primary Contents 2",
            "selector" => ".contents2",
            "index" => 2,
        ),
    ),
```

### select_cont_subs

サブコンテンツエリアの抽出条件を設定します。

配列で複数設定することができます。すべての条件で検索し、マッチしたものをすべて抽出します。

```php
    "select_cont_subs" => array(
        array(
            "name" => "Secondary Contents (Under Sidenavi)", // 設定名(適用された場合にレポートで使用されます)
            "selector" => ".sidenavi", // 対象要素を示すCSSセレクタ
            "index" => 0, // 複数の要素が見つかる場合のインデックス番号
            "bowl_name" => "sidenavi", // $px->bowl() に与えられる名前
        ),
        array(
            "name" => "Secondary Contents (Contents Footer)",
            "selector" => ".contents-footer",
            "index" => 0,
            "bowl_name" => "contents-footer",
        ),
    ),
```

### dom_convert

特定のDOM要素を別のタグやクラス名などの構造に変換する場合に使います。

`selector` で見つけた要素が、 `replace_to` で指定されたテンプレートに合わせて置き換えられます。
変換前の要素の `innerHTML` が、 `replace_to` 内の `{$innerHTML}` に置き換えられます。

```php
    "dom_convert" => array(
        array(
            "name" => "Convert Test", // 設定名(適用された場合にレポートで使用されます)
            "selector" => ".replace-classname-from", // 対象要素を示すCSSセレクタ
            "replace_to" => '<div><p>{$innerHTML}</p></div>', // 変換後の構造
        ),
    ),
```

### select_breadcrumb

パンくず情報の抽出条件を設定します。

配列で複数設定することができます。先頭から検索を始め、最初にマッチした条件を使ってパンくず情報を抽出します。

パンくずを見つけると、その内側にある a要素を探し、 先頭から順番に階層構造とみなして抽出します。
ここで抽出された階層情報は、 `sitemap.csv` の `logical_path` にセットされます。

デフォルトで `.breadcrumb` を検索する条件が最後尾に追加されます。

```php
    "select_breadcrumb" => array(
        array(
            "name" => "Default Breadcrumb", // 設定名(適用された場合にレポートで使用されます)
            "selector" => ".breadcrumb", // 対象要素を示すCSSセレクタ
            "index" => 0, // 複数の要素が見つかる場合のインデックス番号
        ),
    ),
```

### replace_title

ページ名の置換条件を設定します。

ページ名は title要素 から取得しますが、 多くのサイトで title要素 にはサイト名やカテゴリ名、タグラインなどの ページ名以外の文字列が合成されてセットされています。 この中から、 純粋な ページ名のみを指す文字列を見つけ出すためのパターンをここで設定してください。

配列で複数設定することができます。先頭から検索を始め、最初にマッチした条件を使ってページ名を抽出します。

```php
    "replace_title" => array(
        array(
            "name" => "Default Title", // 設定名(適用された場合にレポートで使用されます)
            "preg_pattern" => '/^(.*) \- Site Name$/s', // 正規表現のパターン, preg_replace() の第1引数に使用
            "replace_to" => '$1', // 正規表現による変換パターン, preg_replace() の第2引数に使用
        ),
    ),
```

### replace_strings

文字列置換パターンを設定します。

配列で複数設定することができます。すべての条件で検索し、マッチしたものをすべて置換します。

```php
    "replace_strings" => array(
        array(
            "name" => "Default Replacement", // 設定名(適用された場合にレポートで使用されます)
            "preg_pattern" => '/コンテンツエリア/s', // 正規表現のパターン, preg_replace() の第1引数に使用
            "replace_to" => 'こんてーんつえりあぁ', // 正規表現による変換パターン, preg_replace() の第2引数に使用
        ),
    ),
```

### ignore_common_resources

コンテンツから取り除く共有のリソースのパスを設定します。

Pickles 2 のテンプレート構造では、 サイト全体で共有するリソースを読み込むのは テーマが担います。
コンテンツそれぞれから読み込まれていると2重に読み込まれてしまうため、この機能を使って削除します。

配列で複数設定することができます。すべての条件で検索し、マッチしたものをすべて除外します。

```php
    "ignore_common_resources" => array(
        array(
            "name" => "test.js", // 設定名(適用された場合にレポートで使用されます)
            "path" => "/js/test.js", // 除外する対象のパス
        ),
        array(
            "name" => "Common Resources",
            "path" => "/common/*", // アスタリスクを使ってワイルドカードを表現することもできます。
        ),
    ),
```


## 更新履歴 - Change log

### tomk79/px2-asazuke2 v0.0.1 (リリース日未定)

- Initial Release.


## ライセンス - License

[MIT license](http://opensource.org/licenses/MIT).


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <https://www.pxt.jp/>
- Twitter: @tomk79 <https://twitter.com/tomk79/>

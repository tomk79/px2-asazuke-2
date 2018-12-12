# Asazuke 2

Asazuke 2 は、既存のウェブサイトのデータを解析し、Pickles 2 の形式に置換するスクレイピングツールです。

Pickles Framework 1.x 用に開発された [asazuke plugin](https://github.com/tomk79/PxPlugin_asazuke) を Pickles 2 向けに移植したものです。

Asazuke 2 は、ウェブサイトのクロールは行いません。
先に PicklesCrawler などを使って巡回収集したデータを用意し、
そのデータを Asazuke 2 でスクレイピングする手順で行うと効率的です。

Pickles 2 については、下記のウェブサイトを参照してください。

https://pickles2.pxt.jp/

PicklesCrawler については、下記にリポジトリが公開されています。

https://github.com/tomk79/PxPlugin_PicklesCrawler


## インストール - Install

TBD

## 使い方 - Usage

TBD

## 設定 - Settings

`./_PX/_sys/ramdata/plugins/asazuke/proj/*` に次のファイルを設置します。

・DOM変換ルール:
    dom_convert.csv
    A列: "name" => 設定名
    B列: "selector" => CSSセレクタ
    C列: "replace_to" => 置換後のHTMLソース
    ※上から順に全行適用
・除外共通リソース設定:
    ignore_common_resources.csv
    A列: "name" => 設定名
    B列: "path" => 場外するリソースのパス
    ※上から順に全行適用
・文字列置換ルール:
    replace_strings.csv
    A列: "name" => 設定名
    B列: "preg_pattern" => 正規表現パターン
    C列: "replace_to" => 置換後の文字列
    ※上から順に全行適用
・タイトルの置換ルール:
    replace_title.csv
    A列: "name" => 設定名
    B列: "preg_pattern" => 正規表現パターン
    C列: "replace_to" => 置換後の文字列
    ※上から順にはじめにマッチした行のみ適用
・パンくずエリアセレクタ:
    select_breadcrumb.csv
    A列: "name" => 設定名
    B列: "selector" => CSSセレクタ
    C列: "index" => ヒットしたDOM要素のインデックス番号
    ※上から順にはじめにマッチした行のみ適用
・メインコンテンツエリアのセレクタ:
    select_cont_main.csv
    A列: "name" => 設定名
    B列: "selector" => CSSセレクタ
    C列: "index" => ヒットしたDOM要素のインデックス番号
    ※上から順にはじめにマッチした行のみ適用
・サブコンテンツエリアのセレクタ:
    select_cont_subs.csv
    A列: "name" => 設定名
    B列: "selector" => CSSセレクタ
    C列: "index" => ヒットしたDOM要素のインデックス番号
    D列: "cabinet_name" => 格納先のコンテンツキャビネット名
    ※上から順に全行適用


## 更新履歴 - Change log

### tomk79/px2-asazuke2 v0.0.1 (リリース日未定)

- Initial Release.


## ライセンス - License

[MIT license](http://opensource.org/licenses/MIT).


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <https://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>

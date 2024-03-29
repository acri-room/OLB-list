OLB Lightweight List
====================

概要
----

このリポジトリに含まれるのは，WordPress用プラグイン
<a href="https://olbsys.com/">「オンラインレッスン予約システム」</a>に，
軽量かつスクロール可能なスケジュール表の機能を加える補助プラグインです．
プラグインの利用例は，
<a href="https://gw.acri.c.titech.ac.jp/wp/">ACRi ルーム</a>
のトップページを参照してください．

特徴
----

- データベースへのアクセス回数削減により，ページ表示速度が向上
- スクロールする表により，多数の講師の予約状況をページ遷移なく確認可能
- ドロップダウンとボタンでの日付選択に対応

実装は ACRi ルームでの運用に特化していますので，他のサイトで利用する
場合は一定量のコードの修正が必要になるかと思いますが，ご了承ください．

使用方法
--------

事前に<a href="https://olbsys.com/">「オンラインレッスン予約システム」</a>
をインストールし，設定を済ませておく必要があります．
プラグインをインストールしたら，スケジュール表を表示させたい箇所に
以下のショートコードを挿入してください．
>     [olblist_daily]
講師リストはユーザ名（user_nicename）の順に並び替えられます．
また，住所（user_address）が設定されている場合，その内容がユーザ名の下に
カッコ書きで表示されます．

注意
----

本プラグインは，「オンラインレッスン予約システム」とは独立した開発者
によって開発・メンテナンスされており，実装は ACRi ルームでの運用に特化
しています．他環境でも同様に動作することを保証するものではありません．
また，本プラグインに関する質問を本家サイトで行うことはおやめください．

改版履歴
--------

- v0.9.5 (2022-10-06)
  - スケジュール表の一部の列をドロップダウンで非表示にする機能を追加．
  - 日付選択において，表示中の日にうまく移動できない問題を修正．
- v0.9.4 (2021-11-12)
  - スケジュール表の日付選択リストにおいて，土曜・日曜が異なる背景色で表示されるように変更．
- v0.9.3 (2021-08-25)
  - リンクテキストをユーザ名（user_nicename）から表示名（display_name）に変更．
- v0.9.2 (2021-02-19)
  - スケジュール表の見出し列（一番左の列）を固定表示に変更．
- v0.9.1 (2021-01-31)
  - 住所欄の文字列をリスト中にカッコ書きで表示する機能を追加．
- v0.9.0 (2020-12-29)
  - 最初のバージョン．2021年1月14日より ACRi ルームでの運用を開始．
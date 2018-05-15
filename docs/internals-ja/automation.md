自動化
======

Yii の開発に取り組む際に、自動化できるタスクがいくつかあります:

- フレームワークのルート・ディレクトリに配置されるクラス・マップ `classes.php` の生成。
  `./build/build classmap` を実行して生成してください。

- クラス・ファイルの中の、ゲッターとセッターによって導入されるプロパティを記述する `@property` 注釈の生成。
  `./build/build php-doc/property` を実行して注釈を更新してください。

- コード・スタイルと phpdoc コメントの細かい問題の修正。
  `./build/build php-doc/fix` を実行して修正してください。
  このコマンドは完璧なものではないため、望ましくない変更があるかもしれませんので、コミットする前に変更点をチェックしてください。
  `git add -p` を使って変更をレビューすることが出来ます。

- Mime タイプ・マジック・ファイル (`framework/helpers/mimeTypes.php`) の Apache HTTPd レポジトリによる更新。
  `./build/build mime-type` を実行してファイルを更新して下さい。

- CHANGELOG ファイルのエントリの出現順序は、`./build/build release/sort-changelog framework` を実行することで更新することが出来ます。

上記のコマンドの全てが [リリースの工程]() に含まれています。これらをリリースとリリースの間に実行しても構いませんが、必要ではありません。

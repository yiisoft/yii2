自動化
======

Yii の開発に取り組む際に、自動化できるタスクがいくつかあります:

- フレームワークのルート・ディレクトリに配置されるクラス・マップ `classes.php` の生成。
  `./build/build classmap` を実行して生成してください。

- Mime タイプ・マジック・ファイル (`framework/helpers/mimeTypes.php`) の Apache HTTPd レポジトリによる更新。
  `./build/build mime-type` を実行してファイルを更新して下さい。

- CHANGELOG ファイルのエントリの出現順序は、`./build/build release/sort-changelog framework` を実行することで更新することが出来ます。

上記のコマンドの全てが [リリースの工程]() に含まれています。これらをリリースとリリースの間に実行しても構いませんが、必要ではありません。

翻訳ワークフロー
================

Yii は国際的なアプリケーションと開発者にとって役に立つように、数多くの言語に翻訳されています。
貢献が大いに歓迎される主な領域はドキュメントとフレームワークメッセージです。

フレームワークメッセージ
------------------------

フレームワークは二種類のメッセージを持っています。一つは開発者向けの例外メッセージで、これは決して翻訳されません。
もう一つはエンドユーザーが実際に目にする検証エラーのようなメッセージです。

メッセージの翻訳を開始するためには:

1. `framework/messages/config.php` をチェックして、あなたの言語が `languages` のリストに載っていることを確認してください。
   もし無ければ、あなたの言語をそこに追加します (リストをアルファベット順に保つことを忘れないでください)。
   言語コードの形式は、例えば `ru` や `zh-CN` のように、[IETF言語タグ](http://ja.wikipedia.org/wiki/IETF%E8%A8%80%E8%AA%9E%E3%82%BF%E3%82%B0) に従うべきです。
2. `framework` に入って、`./yii message/extract @yii/messages/config.php --languages=<your_language>` を走らせます。
3. `framework/messages/your_language/yii.php` のメッセージを翻訳します。ファイルは必ず UTF-8 エンコーディングを使って保存してください。
4. [プルリクエスト](git-workflow.md) をします。

あなたの翻訳を最新状態に保つために、`./yii message/extract @yii/messages/config.php --languages=<your_language>` を再び走らせることが出来ます。
このコマンドは、変更のなかった箇所には触れることなく、自動的にメッセージを再抽出してくれます。

翻訳ファイルの中で、配列の各要素は、メッセージ(キー)と翻訳(値)をあらわします。
値が空文字列の場合は、メッセージは翻訳されないものと見なされます。
翻訳が不要になったメッセージは、翻訳が一組の '@@' マークで囲まれます。
メッセージ文字列は複数形書式とともに使うことが出来ます。
詳細はガイドの [国際化](../guide-ja/tutorial-i18n.md) の節を参照してください。

ドキュメント
------------

ドキュメントの翻訳は `docs/<original>-<language>` の下に置きます。
ここで `<original>` は、`guide` や `internals` などの元の文書の名前であり、`<language>` は文書の翻訳先の言語コードです。
例えば、ロシア語のガイドの翻訳は `docs/guide-ru` です。

初期の仕事が完了した後は、最新の翻訳以後に変更されたソース文書の箇所を取得するために、`build` ディレクトリにある専用のコマンドを使うことが出来ます。

```
php build translation "../docs/guide" "../docs/guide-ru" "Russian guide translation report" > report_guide_ru.html
```

このコマンドが composer に関して不平を言うようであれば、ソースのルートディレクトリで `composer install` を実行してください。

ドキュメントの文法の情報およびスタイルガイドに関して、[documentation_style_guide.md](../documentation_style_guide.md) を参照して下さい。

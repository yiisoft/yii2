Json ヘルパ
===========

Json ヘルパは JSON をエンコードおよびデコードする一連の静的メソッドを提供します。
`[[yii\helpers\Json::encode()]]` メソッドはエンコード・エラーを処理しますが、
 `[[yii\web\JsExpression]]` オブジェクトの形式で表現された JavaScript の式はエンコードしません。
既定ではエンコードは `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE` のオプションで行われます。
詳細については [PHP:json_encode](https://www.php.net/manual/ja/function.json-encode.php) を参照して下さい。

## 整形出力 <span id="pretty-print"></span>

既定では `[[yii\helpers\Json::encode()]]` メソッドは整形されていない JSON (すなわち空白無しのもの) を出力します。
人間にとって読みやすいものにするために、「整形出力 pretty printing」を ON にすることが出来ます。

> Note: 整形出力は開発中のデバッグには役立つでしょうが、製品環境では推奨されません。
インスタンスごとに整形出力を有効にするためにはオプションを指定することが出来ます。すなわち :

```php
$data = ['a' => 1, 'b' => 2];
$json = yii\helpers\Json::encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
```
JSON ヘルパの整形出力をグローバルに有効にすることも出来ます。例えば、設定ファイルや index.php の中で :
```php
yii\helpers\Json::$prettyPrint = YII_DEBUG; // デバッグ・モードでは整形出力を使用
```

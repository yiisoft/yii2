クライアント・サイドで ActiveForm を拡張する
============================================

[[yii\widgets\ActiveForm]] ウィジェットは、クライアント・サイドの検証に使う一連の JavaScript メソッドを備えています。
その実装は非常に柔軟で、様々な方法で拡張することが可能になっています。
下記でそれについて解説します。

## ActiveForm イベント

ActiveForm は、一連の専用のイベントを発生させます。
次のようなコードを使って、これらのイベントを購読して処理することが出来ます。

```javascript
$('#contact-form').on('beforeSubmit', function (e) {
	if (!confirm("全てオーケー。送信しますか?")) {
		return false;
	}
	return true;
});
```

以下、利用できるイベントを見ていきましょう。

### `beforeValidate`

`beforeValidate` は、フォーム全体を検証する前にトリガされます。

イベント・ハンドラのシグニチャは以下の通り:

```javascript
function (event, messages, deferreds)
```

引数は以下の通り:

- `event`: イベントのオブジェクト。
- `messages`: 連想配列で、キーは属性の ID、
  値は対応する属性のエラー・メッセージの配列です。
- `deferreds`: Deferred オブジェクトの配列。`deferreds.add(callback)` を使って、
  新しい deferrd な検証を追加することが出来ます。

ハンドラが真偽値 `false` を返すと、このイベントに続くフォームの検証は中止されます。
その結果、`afterValidate` イベントもトリガされません。

### `afterValidate`

`afterValidate` イベントは、フォーム全体を検証した後でトリガされます。

イベント・ハンドラのシグニチャは以下の通り:

```javascript
function (event, messages, errorAttributes)
```

引数は以下の通り:

- `event`: イベントのオブジェクト。
- `messages`: 連想配列で、キーは属性の ID、
  値は対応する属性のエラー・メッセージの配列です。
- `errorAttributes`: 検証エラーがある属性の配列。
  この引数の構造については `attributeDefaults` を参照して下さい。

### `beforeValidateAttribute`

`beforeValidateAttribute` イベントは、属性を検証する前にトリガされます。
イベント・ハンドラのシグニチャは以下の通り:

```javascript
function (event, attribute, messages, deferreds)
```
     
引数は以下の通り:

- `event`: イベントのオブジェクト。
- `attribute`: 検証される属性。
  この引数の構造については `attributeDefaults` を参照して下さい。
- `messages`: 指定された属性に対する検証エラー・メッセージを追加することが出来る配列。
- `deferreds`: Deferred オブジェクトの配列。
  `deferreds.add(callback)` を使って、新しい deferrd な検証を追加することが出来ます。

ハンドラが真偽値 `false` を返すと、指定された属性の検証は中止されます。
その結果、`afterValidateAttribute` イベントもトリガされません。

### `afterValidateAttribute`

`afterValidateAttribute` イベントは、フォーム全体および各属性の検証の後にトリガされます。

イベント・ハンドラのシグニチャは以下の通り:

```javascript
function (event, attribute, messages)
```

引数は以下の通り:

- `event`: イベントのオブジェクト。
- `attribute`: 検証される属性。
  この引数の構造については `attributeDefaults` を参照して下さい。
- `messages`: 指定された属性に対する追加の検証エラー・メッセージを追加することが
  出来る配列。

### `beforeSubmit`

`beforeSubmit` イベントは、全ての検証が通った後、フォームを送信する前にトリガされます。

イベント・ハンドラのシグニチャは以下の通り:

```javascript
function (event)
```

ここで、`event` は、イベントのオブジェクトです。

ハンドラが真偽値 `false` を返すと、フォームの送信は中止されます。

### `ajaxBeforeSend`
         
`ajaxBeforeSend` イベントは、AJAX ベースの検証のための AJAX リクエストを送信する前にトリガされます。

イベント・ハンドラのシグニチャは以下の通り:

```javascript
function (event, jqXHR, settings)
```

引数は以下の通り:

- `event`: イベントのオブジェクト。
- `jqXHR`: jqXHR のオブジェクト。
- `settings`: AJAX リクエストの設定。

### `ajaxComplete`

`ajaxComplete` イベントはAJAX ベースの検証のための AJAX リクエストが完了した後にトリガされます。

イベント・ハンドラのシグニチャは以下の通り:

```javascript
function (event, jqXHR, textStatus)
```

引数は以下の通り:

- `event`: イベントのオブジェクト。
- `jqXHR`: jqXHR のオブジェクト。
- `textStatus`: リクエストの状態 ("success", "notmodified", "error", "timeout",
"abort", または "parsererror")。

## AJAX でフォームを送信する

検証(バリデーション)は、クライアント・サイドまたは AJAX リクエストによって行うことが出来ますが、
フォームの送信そのものはデフォルトでは通常のリクエストとして実行されます。
フォームを AJAX で送信したい場合は、次のように、フォームの `beforeSubmit` イベントを処理することによって達成することが出来ます。

```javascript
var $form = $('#formId');
$form.on('beforeSubmit', function() {
    var data = $form.serialize();
    $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: data,
        success: function (data) {
            // 成功したときの実装
        },
        error: function(jqXHR, errMsg) {
            alert(errMsg);
        }
     });
     return false; // デフォルトの送信を抑止
});
```

jQuery の `ajax()` 関数について更に学習するためには、[jQuery documentation](https://api.jquery.com/jQuery.ajax/) を参照して下さい。


## フィールドを動的に追加する

現在のウェブ・アプリケーションでは、ユーザに対して表示した後でフォームを変更する必要がある場合がよくあります。
例えば、"追加"アイコンをクリックするとフィールドが追加される場合などです。
このようなフィールドに対するクライアント・サイドの検証を有効にするためには、フィールドを ActiveForm JavaScript プラグインに登録しなければなりません。

フィールドそのものを追加して、そして、検証のリストに追加しなければなりません。

```javascript
$('#contact-form').yiiActiveForm('add', {
    id: 'address',
    name: 'address',
    container: '.field-address',
    input: '#address',
    error: '.help-block',
    validate:  function (attribute, value, messages, deferred, $form) {
        yii.validation.required(value, messages, {message: "Validation Message Here"});
    }
});
```

フィールドを検証のリストから削除して検証されないようにするためには、次のようにします。

```javascript
$('#contact-form').yiiActiveForm('remove', 'address');
```

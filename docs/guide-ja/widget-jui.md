jQuery UI ウィジェット
======================

> Note|注意: この節はまだ執筆中です。

Yii は公式エクステンションによって [jQuery UI](http://api.jqueryui.com/) ライブラリをサポートしています。
jQuery UI は、jQuery JavaScript ライブラリの上に構築された、一連のユーザインタフェイスインタラクション、イフェクト、ウィジェットおよびテーマです。

インストール
------------

このエクステンションの推奨されるインストール方法は、[composer](http://getcomposer.org/download/) を使う方法です。

下記を実行します。

```
php composer.phar require --prefer-dist yiisoft/yii2-jui "*"
```

または、`composer.json` ファイルの `require` セクションに下記を追加します。

```
"yiisoft/yii2-jui": "*"
```

Yii ウィジェット
----------------

複雑な jQuery UI コンポーネントのほとんどは Yii ウィジェットでラップされて、より堅牢な構文を与えられ、フレームワークの諸機能と統合されています。
全てのウィジェットは `\yii\jui` 名前空間に属します。

- [[yii\jui\Accordion|Accordion]]
- [[yii\jui\AutoComplete|AutoComplete]]
- [[yii\jui\DatePicker|DatePicker]]
- [[yii\jui\Dialog|Dialog]]
- [[yii\jui\Draggable|Draggable]]
- [[yii\jui\Droppable|Droppable]]
- [[yii\jui\Menu|Menu]]
- [[yii\jui\ProgressBar|ProgressBar]]
- [[yii\jui\Resizable|Resizable]]
- [[yii\jui\Selectable|Selectable]]
- [[yii\jui\Slider|Slider]]
- [[yii\jui\SliderInput|SliderInput]]
- [[yii\jui\Sortable|Sortable]]
- [[yii\jui\Spinner|Spinner]]
- [[yii\jui\Tabs|Tabs]]

以下の節において、これらのウィジェットの使用例をいくつか紹介します。


DatePicker で日付の入力を扱う <span id="datepicker-date-input"></span>
-----------------------------

[[yii\jui\DatePicker|DatePicker]] ウィジェットを使うと、非常に便利な方法でユーザから日付の入力を収集することが出来ます。
下記の例では、`deadline` 属性を持つ `Task` モデルを使用します。
`deadline` 属性は、[ActiveForm](input-forms.md) を使ってユーザによって設定され、その値は unix タイムスタンプとしてデータベースに保存されます。

この状況において、共同して働くコンポーネントが三つあります。

- [[yii\jui\DatePicker|DatePicker]] ウィジェット。フォームの中で用いられ、モデルの `deadline` 属性のためのインプットフィールドを表示します。
- [フォーマッタ](output-formatter.md) アプリケーションコンポーネント。ユーザに表示される日付の書式について責任を持ちます。
- [DateValidator](tutorial-core-validators.md#date)。ユーザの入力を検証し、それを unix タイムスタンプに変換します。

最初に、フォームフィールドの [[yii\widgets\ActiveField::widget()|widget()]] メソッドを使って、フォームに日付選択のインプットフィールドを追加します。

```php
<?= $form->field($model, 'deadline')->widget(\yii\jui\DatePicker::className(), [
    // bootstrap を使っている場合は、次の行がインプットフィールドの正しいスタイルをセットします
    'options' => ['class' => 'form-control'],
    // ... ここで、DatePicker のプロパティをさらに構成することが出来ます
]) ?>
```

第二のステップは、[モデルの rules() メソッド](input-validation.md#declaring-rules) において、date バリデータを構成することです。

```php
public function rules()
{
    return [
        // ...

        // 空の値がデータベースで NULL として保存されることを保証する
        ['deadline', 'default', 'value' => null],

        // 日付を検証し、`deadline` 属性を unix タイムスタンプで上書きする
        ['deadline', 'date', 'timestampAttribute' => 'deadline'],
    ];
}
```

ここでは、追加で [デフォルト値フィルタ](input-validation.md#handling-empty-inputs) を使って、空の値がデータベースで `NULL` として保存されることを保証しています。
日付の値が [required](tutorial-core-validators.md#required) である場合は、このフィルタを省略することが出来ます。

日付選択ウィジェットと date バリデータのデフォルトの日付書式は、両方とも、`Yii::$app->formatter->dateFormat` の値です。
このプロパティを使えば、アプリケーション全体の日付書式を構成することが出来ます。
日付書式を変更するためには、[[yii\validators\DateValidator::format]] と [[yii\jui\DatePicker::dateFormat]] を構成しなければなりません。

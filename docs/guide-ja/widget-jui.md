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

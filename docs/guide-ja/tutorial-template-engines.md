テンプレートエンジンを使う
==========================

デフォルトでは、Yii は PHP をテンプレート言語として使いますが、[Twig](http://twig.sensiolabs.org/) や [Smarty](http://www.smarty.net/) などの他のレンダリングエンジンをサポートするように Yii を構成することが出来ます。

`view` コンポーネントがビューのレンダリングに責任を持っています。
このコンポーネントのビヘイビアを構成することによって、カスタムテンプレートエンジンを追加することが出来ます。

```php
[
    'components' => [
        'view' => [
            'class' => 'yii\web\View',
            'renderers' => [
                'tpl' => [
                    'class' => 'yii\smarty\ViewRenderer',
                    //'cachePath' => '@runtime/Smarty/cache',
                ],
                'twig' => [
                    'class' => 'yii\twig\ViewRenderer',
                    'cachePath' => '@runtime/Twig/cache',
                    // twig のオプションの配列
                    'options' => [
                        'auto_reload' => true,
                    ],
                    'globals' => ['html' => '\yii\helpers\Html'],
                    'uses' => ['yii\bootstrap'],
                ],
                // ...
            ],
        ],
    ],
]
```

上記のコードにおいては、Smarty と Twig の両者がビューファイルによって使用可能なものとして構成されています。
しかし、これらのエクステンションをプロジェクトで使うためには、`composer.json` ファイルも修正して、これらのエクステンションを含める必要があります。

```
"yiisoft/yii2-smarty": "*",
"yiisoft/yii2-twig": "*",
```

上のコードを `composer.json` の `require` セクションに追加します。
変更をファイルに保存した後、コマンドラインで `composer update --prefer-dist` を実行することによってエクステンションをインストールすることが出来ます。

具体的にテンプレートエンジンを使用する方法については、それぞれのドキュメントで詳細を参照してください。

- [Twig ガイド](https://github.com/yiisoft/yii2-twig/tree/master/docs/guide-ja)
- [Smarty ガイド](https://github.com/yiisoft/yii2-smarty/tree/master/docs/guide-ja)


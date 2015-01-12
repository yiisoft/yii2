Bootstrap ウィジェット
======================

> Note|注意: この節はまだ執筆中です。

Yii は、追加設定なしで、マークアップとコンポーネントのフレームワーク [Bootstrap 3](http://getbootstrap.com/) ("Twitter Bootstrap" としても知られています) をサポートしています。
Bootstrap は優れた、レスポンシブなフレームワークであり、クライアント側の開発プロセスを大いにスピードアップすることが出来るものです。

Bootstrap のコアは二つの部分によって表されます。

- CSS の基礎。例えば、グリッドのレイアウトシステム、タイポグラフィ、ヘルパクラス、レスポンシブユーティリティなど。
- そのまま使えるコンポーネント。フォーム、メニュー、ページネーション、モーダルボックス、タブなど。

基礎
----

Yii は bootstrap の基礎を PHP コードでラップすることをしていません。
なぜなら、この場合の HTML コードがそれ自体として非常にシンプルだからです。
bootstrap の基礎を使用することに関する詳細は、[bootstrap ドキュメントウェブサイト](http://getbootstrap.com/css/) で見ることが出来ます。
それでも、Yii はあなたのページに bootstrap のアセットをインクルードするための便利な方法を提供しています。
`@app/assets` ディレクトリに配置されている `AppAsset.php` に一行を加えるだけで大丈夫です。

```php
public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapAsset', // この行です
];
```

Yii のアセットマネージャによって bootstrap を使うと、bootstrap のリソースを最小化したり、必要な場合にはあなた自身のリソースと結合したりすることが出来ます。

Yii ウィジェット
----------------

複雑な bootstrap コンポーネントのほとんどは Yii ウィジェットでラップされて、より堅牢な構文を与えられ、フレームワークの諸機能と統合されています。
全てのウィジェットは `\yii\bootstrap` 名前空間に属します。

- [[yii\bootstrap\ActiveForm|ActiveForm]]
- [[yii\bootstrap\Alert|Alert]]
- [[yii\bootstrap\Button|Button]]
- [[yii\bootstrap\ButtonDropdown|ButtonDropdown]]
- [[yii\bootstrap\ButtonGroup|ButtonGroup]]
- [[yii\bootstrap\Carousel|Carousel]]
- [[yii\bootstrap\Collapse|Collapse]]
- [[yii\bootstrap\Dropdown|Dropdown]]
- [[yii\bootstrap\Modal|Modal]]
- [[yii\bootstrap\Nav|Nav]]
- [[yii\bootstrap\NavBar|NavBar]]
- [[yii\bootstrap\Progress|Progress]]
- [[yii\bootstrap\Tabs|Tabs]]


Bootstrap の .less ファイルを直接に使用する
-------------------------------------------

あなたが [Bootstrap CSS をあなたの less ファイルに直接含める](http://getbootstrap.com/getting-started/#customizing) ことを望む場合は、オリジナルの CSS ファイルがロードされないように無効化する必要があるでしょう。
[[yii\bootstrap\BootstrapAsset|BootstrapAsset]] の `css` プロパティを空に設定することによって、そうすることが出来ます。
そのためには、`assetManager` [アプリケーションコンポーネント](structure-application-components.md) を以下のように構成します。

```php
    'assetManager' => [
        'bundles' => [
            'yii\bootstrap\BootstrapAsset' => [
                'css' => [],
            ]
        ]
    ]
```

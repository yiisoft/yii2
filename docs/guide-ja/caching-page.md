ページ・キャッシュ
==================

ページ・キャッシュはサーバ・サイドでページ全体のコンテントをキャッシュするものです。
後で再び同じページがリクエストされた場合に、その内容を一から生成するのではなく、キャッシュから提供します。

ページ・キャッシュは [[yii\filters\PageCache]] という [アクション・フィルタ](structure-filters.md) によってサポートされています。
これは、コントローラ・クラスで以下のように使用することができます：

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\PageCache',
            'only' => ['index'],
            'duration' => 60,
            'variations' => [
                \Yii::$app->language,
            ],
            'dependency' => [
                'class' => 'yii\caching\DbDependency',
                'sql' => 'SELECT COUNT(*) FROM post',
            ],
        ],
    ];
}
```

上記のコードは、ページ・キャッシュが `index` アクションのみで使用されることを示しています。
ページのコンテントは最大 60 秒間キャッシュされ、現在のアプリケーションの言語によるバリエーションを持ち、
投稿の総数に変化があった場合キャッシュされたページが無効になります。

御覧のように、ページ・キャッシュは [フラグメント・キャッシュ](caching-fragment.md) ととてもよく似ています。
それらは両方とも `duration`、`dependencies`、`variations`、そして `enabled` などのオプションをサポートしています。
主な違いとしては、ページ・キャッシュは [アクション・フィルタ](structure-filters.md) として、フラグメント・キャッシュは [ウィジェット](structure-widgets.md) として実装されているということです。

[フラグメント・キャッシュ](caching-fragment.md) も、[ダイナミック・コンテント](caching-fragment.md#dynamic-content) も、
ページ・キャッシュと併用することができます。


ページキャッシュ
============

ページキャッシュはサーバサイドでページ全体のコンテントをキャッシュすることを言います。あとで、同じページに再度リクエストがあった場合、その内容を一から再び生成させるのではなく、キャッシュから提供するようにします。

ページキャッシュは [[yii\filters\PageCache]] という [アクションフィルタ](structure-filters.md) によってサポートされています。これは、コントローラクラスで以下のように使用することができます：

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

上記のコードは、ページキャッシュが `index` アクションのみで使用され、そのページのコンテントは最大 60 秒間キャッシュされ、現在のアプリケーションの言語によるバリエーションを持ち、投稿の総数に変化があった場合キャッシュされたページが無効になる、ということを示しています。

見てわかるように、ページキャッシュは [フラグメントキャッシュ](caching-fragment.md) ととてもよく似ています。それらは両方とも `duration`、`dependencies`、`variations`、そして `enabled` などのオプションをサポートしています。主な違いとしては、ページキャッシュは [アクションフィルタ](structure-filters.md) として、フラグメントキャッシュは [ウィジェット](structure-widgets.md) として実装されているということです。

また、ページキャッシュと一緒に [フラグメントキャッシュ](caching-fragment.md) だけでなく [ダイナミックコンテント](caching-fragment.md#dynamic-content) も使用することができます。

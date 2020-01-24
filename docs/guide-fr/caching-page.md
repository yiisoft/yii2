Mise en cache de pages
======================

La mise en cache de pages fait référence à la mise en cache du contenu d'une page entière du côté serveur. Plus tard, lorsque la même page est demandée à nouveau, son contenu est servi à partir du cache plutôt que d'être régénéré entièrement.

La mise en cache de pages est prise en charge par [[yii\filters\PageCache]], un [filtre d'action](structure-filters.md). On peut l'utiliser de la manière suivante dans une classe contrôleur :

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

Le code ci-dessus établit que la mise en cache de pages doit être utilisée uniquement pour l'action `index`. Le contenu de la page doit être mis en cache pour au plus 60 secondes et doit varier selon la langue courante de l'application. De plus, le contenu de la page mis en cache doit être invalidé si le nombre total d'articles (post) change. 

Comme vous pouvez le constater, la mise en cache de pages est très similaire à la [mise en cache de fragments](caching-fragment.md). Les deux prennent en charge les options telles que `duration`, `dependencies`, `variations` et `enabled`. La différence principale est que la mise en cache de pages est mis en œuvre comme un [filtre d'action](structure-filters.md) alors que la mise en cache de framgents l'est comme un [composant graphique](structure-widgets.md).

Vous pouvez utiliser la [mise en cache de fragments](caching-fragment.md) ainsi que le [contenu dynamique](caching-fragment.md#dynamic-content) en simultanéité avec la mise en cache de pages.


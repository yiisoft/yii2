Pamięć podręczna stron
======================

Pamięć podręczna stron odnosi się do zapisu zawartości całej strony po stronie serwera. Kiedy zostanie ona ponownie wywołana, 
zawartość zostanie wyświetlona od razu z pamięci podręcznej zamiast generować ją ponownie od podstaw.

Pamięć podręczna stron jest obsługiwana przez [filtr akcji](structure-filters.md) [[yii\filters\PageCache|PageCache]].
Poniżej znajdziesz przykładowy sposób użycia go w klasie kontrolera:

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

W powyższym przykładzie zakładamy użycie pamięci podręcznej tylko dla akcji `index` - zawartość strony powinna zostać zapisana na maksymalnie 
60 sekund i powinna różnić się w zależności od wybranego w aplikacji języka. Dodatkowo, jeśli całkowita liczba postów w bazie danych ulegnie zmianie, 
zawartość pamięci powinna natychmiast stracić ważność i zostać pobrana ponownie.

Jak widać, pamięć podręczna stron jest bardzo podobna do [pamięci podręcznej fragmentów](caching-fragment.md). W obu przypadkach można 
użyć opcji takich jak `duration` (czas ważności), `dependencies` (zależności), `variations` (warianty) oraz `enabled` (flaga aktywowania). 
Główną różnicą w tych dwóch przypadkach jest to, że pamięć podręczna stron jest implemetowana jako [filtr akcji](structure-filters.md), a 
pamięć podręczna fragmentów jako [widżet](structure-widgets.md).

Oczywiście nic nie stoi na przeszkodzie, aby używać [pamięci podręcznej fragmentów](caching-fragment.md) jak 
i [zawartości dynamicznej](caching-fragment.md#dynamic-content) w połączeniu z pamięcią podręczną stron.


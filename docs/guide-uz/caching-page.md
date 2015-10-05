Sahifalarni keshlash
=================

Sahifalarni keshlash — bu sahifadagi butun ma'lumotni server tomonida keshda saqlashga aytiladi. Keginchalik sahifani serverdan 
talab qilsak bizga sahifadagi ma'lumotni keshdan qaytaradi. 

Sahifalarni keshlash [harakat filtrlari](structure-filters.md) [[yii\filters\PageCache]] yordamida amalga oshiriladi va 
kontroller sinfida quyidagi shaklda ishlatilishi mumkun:

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

Keltirilgan kodda kesh faqat indeks harakati uchun amalga oshiriladi. Nazarda tutulgan sahifa  60 sekundga 
kesh qilinadi va dasturning joriy tiliga ko'ra o'zgarib turadi. Kesh qilingan sahifa muddati o'z o'zidan 
o'zgaradi. Agarki shahrlarning umumiy soni o'zgarsa.

Sahifalarni keshlash [fragmentlarni keshlashga](caching-fragment.md) juda o'hshaydi. Ikki holatda ham `duration` `dependencies`
`variations` va `enabled` parametrlari qo'llaniladi. Asosiy farqi shundaki sahifalarni keshlash [harakat filtri](structure-filters.md) shaklida 
amalga oshiriladi. Fragmentlarni keshlash esa [vidjet shaklida](structure-widgets.md) amalga oshiriladi.

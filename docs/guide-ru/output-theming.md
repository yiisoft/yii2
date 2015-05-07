���������
=========

��������� � ��� ������ �������� ���� ����� [�������������](structure-views.md) ������ ��� �������������� ����, ���
������������ �������� ��� ��������� �������� ���� ����������.

��� ����, ����� ������ ������������ ���������, ��������� �������� [[yii\base\View::theme|theme]] ����������
���������� `view`. ������������ ����������� ������ [[yii\base\Theme]], ������� �������� �� ��, ��� ��� ������
���������� ����� �����������. ������� �������, ����� ��������� ��������� �������� [[yii\base\Theme]]:

- [[yii\base\Theme::basePath]]: ������� ����������, � ������� ��������� �������������� ������� (CSS, JS, �����������,
  � ��� �����).
- [[yii\base\Theme::baseUrl]]: ������� URL ��� ������� � ������������� ��������.
- [[yii\base\Theme::pathMap]]: ������� ������ ������ �������������. �������� ������� �����.
 
��������, ���� �� ��������� `$this->render('about')` � `SiteController`, �� ����� �������������� ���� �����������
`@app/views/site/about.php`. ��� �� �����, ���� �� �������� ��������� ��� �������� ����, �� ������ ���� �����
�������������� `@app/themes/basic/site/about.php`. 

```php
return [
    'components' => [
        'view' => [
            'theme' => [
                'basePath' => '@app/themes/basic'
                'baseUrl' => '@web/themes/basic',
                'pathMap' => [
                    '@app/views' => '@app/themes/basic',
                ],
            ],
        ],
    ],
];
```

> ����������: ��� ��������� ��� �������������� ���������� ����. ��� ������ ����������� ��� ������������� � ��������
  ���� � �������� ������� ��� URL.

�� ������ ���������� � ������� [[yii\base\Theme]] ����� �������� [[yii\base\View::theme]]. ��������,
� ����� �����������, ��� ����� ���������� ��������� ������� (������ view �������� ��� `$this`):

```php
$theme = $this->theme;

// returns: $theme->baseUrl . '/img/logo.gif'
$url = $theme->getUrl('img/logo.gif');

// returns: $theme->basePath . '/img/logo.gif'
$file = $theme->getPath('img/logo.gif');
```

�������� [[yii\base\Theme::pathMap]] ���������� ��, ��� ���������� ����� �������������. �������� ��������� ������ ��� 
����-�������� ��� ����� �������� ������ � ������������ ������, ������� �� ����� ��������, � �������� � ���������������� 
������ � ������ �� ����. ������ �������� �� ��������� ����������: ���� ���� � ������������� ���������� � ������ �� ������ 
������� [[yii\base\Theme::pathMap|pathMap]], �� ��������������� ��� ����� ����� �������� ��������� �� ���� �� �������.
��� ���������� ���� ������������ `@app/views/site/about.php` �������� ��������� � ������ `@app/views` � �����
������ �� `@app/themes/basic/site/about.php`.


## ��������� ������� <span id="theming-modules"></span>

��� ����, ����� ������������ ������, �������� [[yii\base\Theme::pathMap]] ����� ���� ��������� ��������� �������:

```php
'pathMap' => [
    '@app/views' => '@app/themes/basic',
    '@app/modules' => '@app/themes/basic/modules', // <-- !!!
],
```

��� �������� ��� ������������ `@app/modules/blog/views/comment/index.php` � `@app/themes/basic/modules/blog/views/comment/index.php`.


## ��������� �������� <span id="theming-widgets"></span>

��� ����, ����� ������������ ������� �� ������ ��������� �������� [[yii\base\Theme::pathMap]] ��������� �������:

```php
'pathMap' => [
    '@app/views' => '@app/themes/basic',
    '@app/widgets' => '@app/themes/basic/widgets', // <-- !!!
],
```

��� �������� ��� ������������ `@app/widgets/currency/views/index.php` � `@app/themes/basic/widgets/currency/index.php`.


## ������������ ��� <span id="theme-inheritance"></span>

������ ��������� ������� ������� ����, �������� ����� ��� ���������� � ����� �������� ���� ��� � �����������, ��������,
�� ������������ ���������. �������� ����� ����� ��� ������ ������������ ���. ��� ���� ���� ���� � ����� �������� � 
������������ ���������� ����� �� ����:

```php
'pathMap' => [
    '@app/views' => [
        '@app/themes/christmas',
        '@app/themes/basic',
    ],
]
```

� ���� ������ ������������� `@app/views/site/about.php` ������������ ���� � `@app/themes/christmas/site/index.php`, 
���� � `@app/themes/basic/site/index.php` � ����������� �� ����, � ����� �� ��� ���� ������ ����. ���� ����� ������������
� ��� � ���, ������������ ������ �� ���. �� �������� ����������� �������������� ������ ����� �����������
� `@app/themes/basic`, � �� ������ ��� ���������� � `@app/themes/christmas`.

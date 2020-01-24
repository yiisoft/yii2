Temas
=====
 
Tema é uma forma de substituir um conjunto de [views](structure-views.md) por outras, sem a necessidade de tocar no código de renderização de view original. Você pode usar tema para alterar sistematicamente a aparência de uma aplicação.
 
Para usar tema, você deve configurar a propriedade [[yii\base\View::theme|theme]] da `view (visão)` da aplicação.
A propriedade configura um objeto [[yii\base\Theme]] que rege a forma como os arquivos de views serão substituídos. Você deve principalmente especificar as seguintes propriedades de [[yii\base\Theme]]:
 
- [[yii\base\Theme::basePath]]: determina o diretório de base que contém os recursos temáticos (CSS, JS, images, etc.)
- [[yii\base\Theme::baseUrl]]: determina a URL base dos recursos temáticos.
- [[yii\base\Theme::pathMap]]: determina as regras de substituição dos arquivos de view. Mais detalhes serão mostradas nas subseções logo a seguir.
 
Por exemplo, se você chama `$this->render('about')` no `SiteController`, você estará renderizando a view
`@app/views/site/about.php`. Todavia, se você habilitar tema na seguinte configuração da aplicação, a view `@app/themes/basic/site/about.php` será renderizada, no lugar da primeira.
 
```php
return [
    'components' => [
        'view' => [
            'theme' => [
                'basePath' => '@app/themes/basic',
                'baseUrl' => '@web/themes/basic',
                'pathMap' => [
                    '@app/views' => '@app/themes/basic',
                ],
            ],
        ],
    ],
];
```
 
> Observação: Aliases de caminhos são suportados por temas. Ao fazer substituição de view, aliases de caminho serão transformados nos caminhos ou URLs reais.
 
Você pode acessar o objeto [[yii\base\Theme]] através da propriedade [[yii\base\View::theme]]. Por exemplo, na view, você pode escrever o seguinte código, pois `$this` refere-se ao objeto view:
 
```php
$theme = $this->theme;
 
// retorno: $theme->baseUrl . '/img/logo.gif'
$url = $theme->getUrl('img/logo.gif');
 
// retorno: $theme->basePath . '/img/logo.gif'
$file = $theme->getPath('img/logo.gif');
```
 
A propriedade [[yii\base\Theme::pathMap]] rege como a view deve ser substituída. É preciso um array de pares de valores-chave, onde as chaves são os caminhos originais da view que serão substituídos e os valores são os caminhos dos temas correspondentes. A substituição é baseada na correspondência parcial: Se um caminho de view inicia com alguma chave no array [[yii\base\Theme::pathMap|pathMap]], a parte correspondente será substituída pelo valor do array.
Usando o exemplo de configuração acima,
`@app/views/site/about.php` corresponde parcialmente a chave
`@app/views`, ele será substituído por `@app/themes/basic/site/about.php`.
 
 
## Tema de Módulos <span id="theming-modules"></span>
 
A fim de configurar temas por módulos, [[yii\base\Theme::pathMap]] pode ser configurado da seguinte forma:
 
```php
'pathMap' => [
    '@app/views' => '@app/themes/basic',
    '@app/modules' => '@app/themes/basic/modules', // <-- !!!
],
```
 
Isto lhe permitirá tematizar `@app/modules/blog/views/comment/index.php` com `@app/themes/basic/modules/blog/views/comment/index.php`.
 
 
## Tema de Widgets <span id="theming-widgets"></span>
 
A fim de configurar temas por widgets, você pode configurar [[yii\base\Theme::pathMap]] da seguinte forma:
 
```php
'pathMap' => [
    '@app/views' => '@app/themes/basic',
    '@app/widgets' => '@app/themes/basic/widgets', // <-- !!!
],
```
 
Isto lhe permitirá tematizar `@app/widgets/currency/views/index.php` com `@app/themes/basic/widgets/currency/views/index.php`.
 
 
## Herança de Tema <span id="theme-inheritance"></span>
 
Algumas vezes você pode querer definir um tema que contém um visual básico da aplicação, e em seguida, com base em algum feriado, você pode querer variar o visual levemente. Você pode atingir este objetivo usando herança de tema que é feito através do mapeamento de um único caminho de view para múltiplos alvos. Por exemplo:
 
```php
'pathMap' => [
    '@app/views' => [
        '@app/themes/christmas',
        '@app/themes/basic',
    ],
]
```
 
Neste caso, a view `@app/views/site/index.php` seria tematizada tanto como `@app/themes/christmas/site/index.php` ou
`@app/themes/basic/site/index.php`, dependendo de qual arquivo de tema existir. Se os dois arquivos existirem, o primeiro terá precedência. Na prática, você iria manter mais arquivos de temas em `@app/themes/basic` e personalizar alguns deles em `@app/themes/christmas`.


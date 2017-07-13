Assets
======

Um asset no Yii é um arquivo que pode ser referenciado em uma página Web. Pode 
ser um arquivo CSS, JavaScript, imagem, vídeo, etc. Os assets estão localizados 
em um diretório acessível pela Web e estão diretamente disponibilizados por 
servidores Web.

Muitas vezes, é preferível gerenciá-los programaticamente. Por exemplo, quando 
você usar o widget [[yii\jui\DatePicker]] em uma página, será incluído 
automaticamente os arquivos CSS e JavaScript requeridos, ao invés de pedir para 
você encontrar estes arquivos e incluí-los manualmente. E quando você atualizar 
o widget para uma nova versão, automaticamente usará a nova versão dos arquivos 
de assets. Neste tutorial, iremos descrever esta poderosa capacidade de gerência 
de assets fornecidas pelo Yii.


## Asset Bundles <span id="asset-bundles"></span>

O Yii gerencia os assets na unidade de *asset bundle*. Um asset bundle é 
simplesmente uma coleção de assets localizados em um diretório. Quando você 
registrar um asset bundle em uma [view (visão)](structure-views.md), serão  
incluídos os arquivos CSS e JavaScript do bundle na página Web renderizada.


## Definindo os Asset Bundles <span id="defining-asset-bundles"></span>

Os asset bundles são especificados como classes PHP que estendem de 
[[yii\web\AssetBundle]]. O nome de um bundle corresponde simplesmente a um nome 
de classe PHP totalmente qualificada (sem a primeira barra invertida). Uma classe 
de asset bundle deve ser [autoloadable](concept-autoloading.md). Geralmente é 
especificado onde os asset estão localizados, quais arquivos CSS e JavaScript 
possuem e como o bundle depende de outro bundles.

O código a seguir define o asset bundle principal que é usado pelo 
[template básico de projetos](start-installation.md):

```php
<?php

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
```

A classe `AppAsset` acima, especifica que os arquivos de assets estão localizadas 
sob o diretório `@webroot` que corresponde à URL `@web`;
O bundle contém um único arquivo CSS `css/site.css` e nenhum arquivo JavaScript;
O bundle depende de outros dois bundles: [[yii\web\YiiAsset]] e 
[[yii\bootstrap\BootstrapAsset]]. Mais detalhes sobre as propriedades do 
[[yii\web\AssetBundle]] serão encontradas a seguir:

* [[yii\web\AssetBundle::sourcePath|sourcePath]]: especifica o diretório que 
  contém os arquivos de assets neste bundle. Esta propriedade deve ser definida 
  se o diretório root não for acessível pela Web. Caso contrário, você deve definir 
  as propriedades [[yii\web\AssetBundle::basePath|basePath]] e 
  [[yii\web\AssetBundle::baseUrl|baseUrl]]. Os [alias de caminhos](concept-aliases.md) 
  podem ser usados nesta propriedade.
* [[yii\web\AssetBundle::basePath|basePath]]: especifica um diretório acessível 
  pela Web que contém os arquivos de assets neste bundle. Quando você especificar 
  a propriedade [[yii\web\AssetBundle::sourcePath|sourcePath]], o 
  [gerenciador de asset](#asset-manager) publicará os assets deste bundle para um 
  diretório acessível pela Web e sobrescreverá a propriedade `basePath` para ficar 
  em conformidade. Você deve definir esta propriedade caso os seus arquivos de 
  asset já estejam em um diretório acessível pela Web e não precisam ser publicados.  
  As [alias de caminhos](concept-aliases.md) podem ser usados aqui.
* [[yii\web\AssetBundle::baseUrl|baseUrl]]: especifica a URL correspondente ao 
  diretório [[yii\web\AssetBundle::basePath|basePath]]. Assim como a propriedade 
  [[yii\web\AssetBundle::basePath|basePath]], se você especificar a propriedade 
  [[yii\web\AssetBundle::sourcePath|sourcePath]], o 
  [gerenciador de asset](#asset-manager) publicará os assets e sobrescreverá esta 
  propriedade para entrar em conformidade. Os [alias de caminhos](concept-aliases.md) 
  podem ser usados aqui.
* [[yii\web\AssetBundle::js|js]]: um array listando os arquivos JavaScript contidos 
  neste bundle. Observe que apenas a barra "/" pode ser usada como separadores de 
  diretórios. Cada arquivo JavaScript deve ser especificado em um dos dois seguintes 
  formatos:
  - um caminho relativo representando um local do arquivo JavaScript (por exemplo,  
    `js/main.js`). O caminho real do arquivo pode ser determinado pela precedência 
    do [[yii\web\AssetManager::basePath]] no caminho relativo e a URL real do 
    arquivo pode ser determinado pela precedência do [[yii\web\AssetManager::baseUrl]] 
    no caminho relativo.
  - uma URL absoluta representando um arquivo JavaScript externo. Por exemplo, 
    `http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js` ou 
    `//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js`.
* [[yii\web\AssetBundle::css|css]]: uma array listando os arquivos CSS contidos 
  neste bundle. O formato deste array é igual ao que foi mencionado no 
  [[yii\web\AssetBundle::js|js]].
* [[yii\web\AssetBundle::depends|depends]]: um array listando os nomes dos asset 
  bundles que este bundle depende (será explicado em breve).
* [[yii\web\AssetBundle::jsOptions|jsOptions]]: especifica as opções que serão 
  passadas para o método [[yii\web\View::registerJsFile()]] quando for chamado 
  para registrar *cada* arquivo JavaScript neste bundle.
* [[yii\web\AssetBundle::cssOptions|cssOptions]]: especifica as opções que serão 
  passadas para o método [[yii\web\View::registerCssFile()]] quando for chamado 
  para registrar *cada* arquivo CSS neste bundle.
* [[yii\web\AssetBundle::publishOptions|publishOptions]]: especifica as opções 
  que serão passadas para o método [[yii\web\AssetManager::publish()]] quando for 
  chamado para publicar os arquivos de asset para um diretório Web. Este é usado 
  apenas se você especificar a propriedade [[yii\web\AssetBundle::sourcePath|sourcePath]].


### Localização dos Assets <span id="asset-locations"></span>

Os assets, com base em sua localização, podem ser classificados como:

* assets fonte: os arquivos de asset estão localizados juntos ao código fonte 
  PHP que não podem ser acessados diretamente na Web. Para utilizar os assets 
  fonte em uma página, devem ser copiados para um diretório Web a fim de 
  torna-los como os chamados assets publicados. Este processo é chamado de 
  *publicação de asset* que será descrito em detalhes ainda nesta seção.
* assets publicados: os arquivos de asset estão localizados em um diretório Web 
  e podendo, assim, serem acessados diretamente na Web.
* assets externos: os arquivos de asset estão localizados em um servidor Web 
  diferente do que a aplicação está hospedada. 

Ao definir uma classe de asset bundle e especificar a propriedade 
[[yii\web\AssetBundle::sourcePath|sourcePath]], significará que quaisquer assets 
listados usando caminhos relativos serão considerados como assets fonte. Se você 
não especificar esta propriedade, significará que estes assets serão assets 
publicados (portanto, você deve especificar as propriedades 
[[yii\web\AssetBundle::basePath|basePath]] e [[yii\web\AssetBundle::baseUrl|baseUrl]] 
para deixar o Yii saber onde eles estão localizados).

É recomendado que você coloque os assets da aplicação em um diretório Web para 
evitar o processo de publicação de assets desnecessários. É por isso que o 
`AppAsset` do exemplo anterior especifica a propriedade 
[[yii\web\AssetBundle::basePath|basePath]] ao invés da propriedade 
[[yii\web\AssetBundle::sourcePath|sourcePath]].

Para as [extensões](structure-extensions.md), por seus assets estarem localizados 
juntamente com seus códigos fonte em um diretório não acessível pela Web, você 
terá que especificar a propriedade [[yii\web\AssetBundle::sourcePath|sourcePath]] 
ao definir as classes de asset bundle.

> Observação: Não use o `@webroot/assets` como o [[yii\web\AssetBundle::sourcePath|caminho da fonte]]. 
  Este diretório é usado por padrão pelo [[yii\web\AssetManager|gerenciador de asset]] 
  para salvar os arquivos de asset publicados a partir de seu local de origem. 
  Qualquer conteúdo deste diretório será considerado como temporário e podem 
  estar sujeitos a serem deletados.


### Dependências de Assets <span id="asset-dependencies"></span>

Ao incluir vários arquivos CSS ou JavaScript em uma página Web, devem seguir uma 
determinada ordem para evitar problemas de sobrescritas. Por exemplo, se você 
estiver usando um widget JQuery UI em um página, você deve garantir que o arquivo 
JavaScript do JQuery esteja incluído antes que o arquivo JavaScript do JQuery UI. 
Chamamos esta tal ordenação de dependência entre os assets.

A dependência de assets são especificados principalmente através da propriedade 
[[yii\web\AssetBundle::depends]]. No exemplo do `AppAsset`, o asset bundle depende 
de outros dois asset bundles: [[yii\web\YiiAsset]] e [[yii\bootstrap\BootstrapAsset]], 
o que significa que os arquivos CSS e JavaScript do `AppAsset` serão incluídos 
*após* a inclusão dos arquivos dos dois bundles dependentes.

As dependências de assets são transitivas. Isto significa que se um asset bundle 
A depende de B e que o B depende de C, o A também dependerá de C.


### Opções do Asset <span id="asset-options"></span>

Você pode especificar as propriedades [[yii\web\AssetBundle::cssOptions|cssOptions]] 
e [[yii\web\AssetBundle::jsOptions|jsOptions]] para personalizar o modo que os 
arquivos CSS e JavaScript serão incluídos em uma página. Os valores destas propriedades 
serão passadas respectivamente para os métodos [[yii\web\View::registerCssFile()]] 
e [[yii\web\View::registerJsFile()]], quando forem chamados pela 
[view (visão)](structure-views.md) para incluir os arquivos CSS e JavaScript.

> Observação: As opções definidas em uma classe bundle aplicam-se para *todos* 
os arquivos CSS/JavaScript de um bundle. Se você quiser usar opções diferentes 
para arquivos diferentes, você deve criar asset bundles separados e usar um 
conjunto de opções para cada bundle.

Por exemplo, para incluir condicionalmente um arquivo CSS para navegadores IE9 
ou mais antigo, você pode usar a seguinte opção:

```php
public $cssOptions = ['condition' => 'lte IE9'];
```

Isto fara com que um arquivo CSS do bundle seja incluído usando as seguintes tags 
HTML:

```html
<!--[if lte IE9]>
<link rel="stylesheet" href="path/to/foo.css">
<![endif]-->
```

Para envolver as tags links do CSS dentro do `<noscript>`, você poderá configurar 
o `cssOptions` da seguinte forma,

```php
public $cssOptions = ['noscript' => true];
```

Para incluir um arquivo JavaScript na seção `<head>` de uma página (por padrão, 
os arquivos JavaScript são incluídos no final da seção `<body>`, use a seguinte 
opção:

```php
public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
```

Por padrão, quando um asset bundle está sendo publicado, todo o conteúdo do 
diretório especificado pela propriedade [[yii\web\AssetBundle::sourcePath]] serão 
publicados. Para você personalizar este comportamento configurando a propriedade 
[[yii\web\AssetBundle::publishOptions|publishOptions]]. Por exemplo, para publicar 
apenas um ou alguns subdiretórios do [[yii\web\AssetBundle::sourcePath]], você 
pode fazer a seguinte classe de asset bundle.

```php
<?php
namespace app\assets;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle 
{
    public $sourcePath = '@bower/font-awesome'; 
    public $css = [ 
        'css/font-awesome.min.css', 
    ]; 
    
    public function init()
    {
        parent::init();
        $this->publishOptions['beforeCopy'] = function ($from, $to) {
            $dirname = basename(dirname($from));
            return $dirname === 'fonts' || $dirname === 'css';
        };
    }
}  
```

O exemplo anterior define um asset bundle para o 
[pacode de "fontawesome"](http://fontawesome.io/). Ao especificar a opção de 
publicação `beforeCopy`, apenas os subdiretórios `fonts` e `css` serão publicados.


### Assets do Bower e NPM<span id="bower-npm-assets"></span>

A maioria dos pacotes JavaScript/CSS são gerenciados pelo [Bower](http://bower.io/) 
e/ou [NPM](https://www.npmjs.org/).
Se sua aplicação ou extensão estiver usando um destes pacotes, é recomendado que 
você siga os passos a seguir para gerenciar os assets na biblioteca:

1. Modifique o arquivo de sua aplicação ou extensão e informe os pacotes na 
   entrada `require`. Você deve usar `bower-asset/PackageName` (para pacotes Bower) 
   ou `npm-asset/PackageName` (para pacotes NPM) para referenciar à biblioteca.
2. Crie uma classe asset bundle e informe os arquivos JavaScript/CSS que você 
   pretende usar em sua aplicação ou extensão. Você deve especificar a propriedade 
   [[yii\web\AssetBundle::sourcePath|sourcePath]] como `@bower/PackageName` ou 
   `@npm/PackageName`. Isto porque o Composer irá instalar os pacotes Bower ou 
   NPM no diretório correspondente a estas alias.

> Observação: Alguns pacotes podem colocar todos os seus arquivos distribuídos 
  em um subdiretório. Se este for o caso, você deve especificar o subdiretório 
  como o valor da propriedade [[yii\web\AssetBundle::sourcePath|sourcePath]]. 
  Por exemplo, o [[yii\web\JqueryAsset]] usa `@bower/jquery/dist` ao invés de 
  `@bower/jquery`.


## Usando Asset Bundles <span id="using-asset-bundles"></span>

Para usar um asset bundle, registre uma [view (visão)](structure-views.md) 
chamando o método [[yii\web\AssetBundle::register()]]. Por exemplo, no template 
da view (visão) você pode registrar um asset bundle conforme o exemplo a seguir:

```php
use app\assets\AppAsset;
AppAsset::register($this);  // $this representa o objeto da view (visão)
```

> Informação: O método [[yii\web\AssetBundle::register()]] retorna um objeto 
  asset bundle contendo informações sobre os assets publicados, tais como o 
  [[yii\web\AssetBundle::basePath|basePath]] ou [[yii\web\AssetBundle::baseUrl|baseUrl]].

Se você estiver registrando um asset bundle em outros lugares, você deve fornecer 
o objeto da view (visão) necessário. Por exemplo, para registrar um asset bundle 
em uma classe [widget](structure-widgets.md), você pode obter o objeto da view 
(visão) pelo `$this->view`.

Quando um asset bundle for registrado em um view (visão), o Yii registrará todos 
os seus asset bundles dependentes. E, se um asset bundle estiver localizado em 
um diretório inacessível pela Web, será publicado em um diretório Web.
Em seguida, quando a view (visão) renderizar uma página, será gerado as tags 
`<link>` e `<script>` para os arquivos CSS e JavaScript informados nos bundles 
registrados. A ordem destas tags são determinados pelas dependências dos bundles 
registrados e pela ordem dos assets informados nas propriedades 
[[yii\web\AssetBundle::css]] e [[yii\web\AssetBundle::js]].


### Personalizando os Asset Bundles <span id="customizing-asset-bundles"></span>

O Yii gerencia os asset bundles através do componente de aplicação chamado 
`assetManager` que é implementado pelo [[yii\web\AssetManager]]. 
Ao configurar a propriedade [[yii\web\AssetManager::bundles]], é possível 
personalizar o comportamento de um asset bundle.
Por exemplo, o asset bundle padrão [[yii\web\JqueryAsset]] usa o arquivo 
`jquery.js` do pacote JQuery instalado pelo Bower. Para melhorar a disponibilidade 
e o desempenho, você pode querer usar uma versão hospedada pelo Google. 
Isto pode ser feito configurando o `assetManager` na configuração da aplicação 
conforme o exemplo a seguir:

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'sourcePath' => null,   // do not publish the bundle
                    'js' => [
                        '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
                    ]
                ],
            ],
        ],
    ],
];
```

Você pode configurar diversos asset bundles de forma semelhante através da 
propriedade [[yii\web\AssetManager::bundles]]. As chaves do array devem ser os 
nomes das classes (sem a barra invertida) dos asset bundles e os valores do 
array devem corresponder aos [arrays de configuração](concept-configurations.md).

> Dica: Você pode, de forma condicional, escolher os assets que queira usar em 
> um asset bundle. O exemplo a seguir mostra como usar o `jquery.js` no ambiente 
> de desenvolvimento e o `jquery.min.js` em outra situação:
>
> ```php
> 'yii\web\JqueryAsset' => [
>     'js' => [
>         YII_ENV_DEV ? 'jquery.js' : 'jquery.min.js'
>     ]
> ],
> ```

Você pode desabilitar um ou vários asset bundles, associando `false` aos nomes 
dos asset bundles que queira ser desabilitado. Ao registrar um asset bundle 
desabilitado em um view (visão), nenhuma das suas dependências serão registradas 
e a view (visão) também não incluirá quaisquer assets do bundle na página renderizada.
Por exemplo, para desabilitar o [[yii\web\JqueryAsset]], você pode usando a 
seguinte configuração.

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => false,
            ],
        ],
    ],
];
```

Você também pode desabilitar *todos* os asset bundles definindo o 
[[yii\web\AssetManager::bundles]] como `false`.


### Mapeando Asset <span id="asset-mapping"></span>

Às vezes, você pode querer "corrigir" os caminhos dos arquivos de asset 
incorretos/incompatíveis em vários asset bundles. Por exemplo, o bundle A usa o 
`jquery.min.js` com a versão 1.11.1 e o bundle B usa o `jquery.js` com a versão 
2.1.1. Embora você possa corrigir o problema personalizando cada bundle, existe 
um modo mais simples usando o recurso de *mapeamento de asset* para mapear todos 
os assets incorretos para os assets desejados de uma vez. Para fazer isso, 
configure a propriedade [[yii\web\AssetManager::assetMap]] conforme o exemplo a 
seguir:

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'assetMap' => [
                'jquery.js' => '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            ],
        ],
    ],
];
```

As chaves do array [[yii\web\AssetManager::assetMap|assetMap]] são os nomes dos 
assets que você deseja corrigir e o valor são os caminhos dos assets desejados. 
Ao registrar um asset bundle em uma view (visão), cada arquivo de asset relativo 
aos arrays [[yii\web\AssetBundle::css|css]] e [[yii\web\AssetBundle::js|js]] 
serão examinados a partir deste mapeamento.
Se qualquer uma das chaves forem encontradas para serem a última parte de um 
arquivo de asset (que é prefixado com o [[yii\web\AssetBundle::sourcePath]] se 
disponível), o valor correspondente substituirá o asset a ser registrado na view 
(visão). Por exemplo, o arquivo de asset `my/path/to/jquery.js` corresponde a 
chave a chave `jquery.js`.

> Observação: Apenas os assets especificados usando caminhos relativos estão  
  sujeitos ao mapeamento de assets. O caminho dos assets devem ser URLs absolutas 
   ou caminhos relativos ao caminho da propriedade [[yii\web\AssetManager::basePath]].


### Publicação de Asset <span id="asset-publishing"></span>

Como mencionado anteriormente, se um asset bundle for localizado em um diretório
 que não é acessível pela Web, os seus assets serão copiados para um diretório 
Web quando o bundle estiver sendo registrado na view (visão). Este processo é 
chamado de *publicação de asset* e é feito automaticamente pelo 
[[yii\web\AssetManager|gerenciador de asset]].

Por padrão, os assets são publicados para o diretório `@webroot/assets` que 
corresponde a URL `@web/assets`. Você pode personalizar este local configurando 
as propriedades [[yii\web\AssetManager::basePath|basePath]] e 
[[yii\web\AssetManager::baseUrl|baseUrl]].

Ao invés de publicar os assets pela cópia de arquivos, você pode considerar o uso 
de links simbólicos, caso o seu sistema operacional e o servidor Web permita-os. 
Este recurso pode ser habilitado definindo o 
[[yii\web\AssetManager::linkAssets|linkAssets]] como `true`.

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'linkAssets' => true,
        ],
    ],
];
```

Com a configuração acima, o gerenciador de asset irá criar um link simbólico para 
o caminho fonte de um asset bundle quando estiver sendo publicado. Isto é mais 
rápido que a cópia de arquivos e também pode garantir que os assets publicados 
estejam sempre atualizados.

### Cache Busting <span id="cache-busting"></span>

Para aplicações Web que estam rodando no modo produção, é uma prática comum habilitar
o cache HTTP paraassets e outros recursos estáticos. A desvantagem desta prática é que 
sempre que você modificar um asset e implantá-lo em produção, um cliente pode ainda
estar usando a versão antiga, devido ao cache HTTP. Para superar esta desvantagem, 
você pode utilizar o recurso cache busting, que foi implementado na versão 2.0.3, 
configurando o [[yii\web\AssetManager]] como mostrado a seguir:
  
```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'appendTimestamp' => true,
        ],
    ],
];
```

Ao fazer isto, a URL de cada asset publicado será anexada ao seu último horário de 
modificação. Por exemplo, a URL do `yii.js` pode parecer com `/assets/5515a87c/yii.js?v=1423448645"`,
onde o parâmetro `V` representa o último horário de modificação do arquivo `yii.js`.
Agora se você modificar um asset, a sua URL será alterada, fazendo com que o cliente
busque a versão mais recente do asset.

## Asset Bundles de Uso Comum <span id="common-asset-bundles"></span>

O código nativo do Yii definiu vários asset bundles. Entre eles, os bundles a 
seguir são de uso comum e podem ser referenciados em sua aplicação ou no código 
de extensão.

- [[yii\web\YiiAsset]]: Inclui principalmente o arquivo `yii.js` que implementa 
  um mecanismo para organizar os códigos JavaScript em módulos. Ele também fornece 
  um suporte especial para os atributos `data-method` e `data-confirm` e outros 
  recursos úteis.
- [[yii\web\JqueryAsset]]: Inclui o arquivo `jquery.js` do pacote jQuery do Bower.
- [[yii\bootstrap\BootstrapAsset]]: Inclui o arquivo CSS do framework Twitter 
  Bootstrap.
- [[yii\bootstrap\BootstrapPluginAsset]]: Inclui o arquivo JavaScript do framework 
  Twitter Bootstrap para dar suporte aos plug-ins JavaScript do Bootstrap.
- [[yii\jui\JuiAsset]]: Inclui os arquivos CSS e JavaScript do biblioteca jQuery UI.

Se o seu código depende do jQuery, jQuery UI ou Bootstrap, você deve usar estes 
asset bundles predefinidos ao invés de criar suas próprias versões. Se a definição 
padrão destes bundles não satisfazer o que precisa, você pode personaliza-los 
conforme descrito na subseção [Personalizando os Asset Bundles](#customizing-asset-bundles). 


## Conversão de Assets <span id="asset-conversion"></span>

Ao invés de escrever diretamente códigos CSS e/ou JavaScript, os desenvolvedores 
geralmente os escrevem em alguma sintaxe estendida e usam ferramentas especiais 
para converte-los em CSS/JavaScript. Por exemplo, para o código CSS você pode 
usar [LESS](http://lesscss.org/) ou [SCSS](http://sass-lang.com/); e para o 
JavaScript você pode usar o [TypeScript](http://www.typescriptlang.org/).

Você pode listar os arquivos de asset em sintaxe estendida nas propriedades 
[[yii\web\AssetBundle::css|css]] e [[yii\web\AssetBundle::js|js]] de um asset 
bundle. Por exemplo, 

```php
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.less',
    ];
    public $js = [
        'js/site.ts',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
```

Ao registrar um determinado asset bundle em uma view (visão), o 
[[yii\web\AssetManager|gerenciador de asset]] automaticamente rodará as 
ferramentas de pré-processamento para converter os assets de sintaxe estendida 
em CSS/JavaScript. Quando a view (visão) finalmente renderizar uma página, será 
incluído os arquivos CSS/JavaScript convertidos ao invés dos arquivos de assets 
originais em sintaxe estendida.

O Yii usa as extensões dos nomes de arquivos para identificar se é um asset com 
sintaxe estendida. Por padrão, o Yii reconhecerá as seguintes sintaxes e extensões 
de arquivos:

- [LESS](http://lesscss.org/): `.less`
- [SCSS](http://sass-lang.com/): `.scss`
- [Stylus](http://learnboost.github.io/stylus/): `.styl`
- [CoffeeScript](http://coffeescript.org/): `.coffee`
- [TypeScript](http://www.typescriptlang.org/): `.ts`

O Yii conta com ferramentas de pré-processamento instalados para converter os 
assets. Por exemplo, para usar o [LESS](http://lesscss.org/) você deve instalar 
o comando de pré-processamento `lessc`.

Você pode personalizar os comandos de pré-processamento e o da sintaxe estendida 
suportada configurando o [[yii\web\AssetManager::converter]] conforme o exemplo 
a seguir:

```php
return [
    'components' => [
        'assetManager' => [
            'converter' => [
                'class' => 'yii\web\AssetConverter',
                'commands' => [
                    'less' => ['css', 'lessc {from} {to} --no-color'],
                    'ts' => ['js', 'tsc --out {to} {from}'],
                ],
            ],
        ],
    ],
];
```

No exemplo acima, especificamos a sintaxe estendida suportada pela propriedade 
[[yii\web\AssetConverter::commands]]. As chaves do array correspondem a extensão 
dos arquivos (sem o ponto a esquerda) e o valor do array possui a extensão do 
arquivo de asset resultante e o comando para executar a conversão do asset. Os 
tokens `{from}` e `{to}` nos comandos serão substituídos pelo caminho do arquivo 
de asset fonte e pelo caminho do arquivo de asset de destino.

> Informação: Existem outros modos de trabalhar com assets em sintaxe estendida, 
  além do descrito acima. Por exemplo, você pode usar ferramentas de compilação 
  tais como o [grunt](http://gruntjs.com/) para monitorar e automatizar a conversão 
  de assets em sintaxe estendidas. Neste caso, você deve listar os arquivos de 
  CSS/JavaScript resultantes nos asset bundles ao invés dos arquivos originais.


## Combinando e Comprimindo Assets <span id="combining-compressing-assets"></span>

Uma página Web pode incluir muitos arquivos CSS e/ou JavaScript. Para reduzir o 
número de requisições HTTP e o tamanho total de downloads destes arquivos, uma 
prática comum é combinar e comprimir vários arquivos CSS/JavaScript em um ou em 
poucos arquivos e em seguida incluir estes arquivos comprimidos nas páginas Web 
ao invés dos originais.

> Informação: A combinação e compressão de assets normalmente são necessárias 
  quando uma aplicação está em modo de produção. No modo de desenvolvimento, 
  usar os arquivos CSS/JavaScript originais muitas vezes são mais convenientes 
  para depuração.

A seguir, apresentaremos uma abordagem para combinar e comprimir arquivos de 
assets sem precisar modificar o código da aplicação existente.

1. Localize todos os asset bundles em sua aplicação que você deseja combinar e 
   comprimir.
2. Divida estes bundles em um ou alguns grupos. Observe que cada bundle pode 
   apenas pertencer a um único grupo.
3. Combinar/Comprimir os arquivos CSS de cada grupo em um único arquivo. Faça 
   isto de forma semelhante para os arquivos JavaScript.
4. Defina um novo asset bundle para cada grupo:
   * Defina as propriedade [[yii\web\AssetBundle::css|css]] e 
     [[yii\web\AssetBundle::js|js]] com os arquivos CSS e JavaScript combinados, 
     respectivamente.
   * Personalize os asset bundles de cada grupo definindo as suas propriedades 
     [[yii\web\AssetBundle::css|css]] e [[yii\web\AssetBundle::js|js]] como vazias 
     e definindo a sua propriedade [[yii\web\AssetBundle::depends|depends]] para 
     ser o novo asset bundle criado para o grupo.

Usando esta abordagem, quando você registrar um asset bundle em uma view (visão), 
fará com que registre automaticamente o novo asset bundle do grupo que o bundle 
original pertence. E, como resultado, os arquivos de asset combinados/comprimidos 
serão incluídos na página, ao invés dos originais.


### Um Exemplo <span id="example"></span>

Vamos usar um exemplo para explicar melhor o exemplo acima: 

Assuma que sua aplicação possua duas páginas, X e Y. A página X usa os asset 
bundles A, B e C, enquanto a página Y usa os asset bundles B, C e D. 

Você tem duas maneiras de dividir estes asset bundles. Uma delas é a utilização 
de um único grupo para incluir todos os asset bundles e a outra é colocar o A no 
Grupo X, o D no Grupo Y e (B, C) no Grupo S. Qual deles é o melhor? Isto depende. 
A primeira maneira tem a vantagem de ambas as páginas compartilharem os mesmos 
arquivos CSS e JavaScript combinados, o que torna o cache HTTP mais eficaz. Por 
outro lado, pelo fato de um único grupo conter todos os bundles, o tamanho dos 
arquivos CSS e JavaScript combinados será maior e, assim, aumentará o tempo de 
carregamento inicial. Para simplificar este exemplo, vamos usar a primeira 
maneira, ou seja, usaremos um único grupo para conter todos os bundles.

> Informação: Dividir os asset bundles em grupos não é uma tarefa trivial. 
Geralmente requer que análise sobre o real trafego de dados de diversos assets 
em páginas diferentes. Para começar, você pode usar um único grupo para simplificar.

Use as ferramentas existentes (por exemplo, 
[Closure Compiler](https://developers.google.com/closure/compiler/), 
[YUI Compressor](https://github.com/yui/yuicompressor/)) para combinar e 
comprimir os arquivos CSS e JavaScript em todos os bundles. Observer que os 
arquivos devem ser combinados na ordem que satisfaça as dependências entre os 
bundles. Por exemplo, se o bundle A depende do B e que dependa tanto do C quanto 
do D, você deve listar os arquivos de asset a partir do C e D, em seguida pelo B 
e finalmente pelo A.

Depois de combinar e comprimir, obteremos um arquivo CSS e um arquivo JavaScript. 
Suponha que os arquivos serão chamados de `all-xyz.css` e `all-xyz.js`, onde o 
`xyz` significa um timestamp ou um hash que é usado para criar um nome de arquivo 
único para evitar problemas de cache HTTP.

Nós estamos na última etapa agora. Configure o 
[[yii\web\AssetManager|gerenciador de asset]] como o seguinte na configuração da 
aplicação:

```php
return [
    'components' => [
        'assetManager' => [
            'bundles' => [
                'all' => [
                    'class' => 'yii\web\AssetBundle',
                    'basePath' => '@webroot/assets',
                    'baseUrl' => '@web/assets',
                    'css' => ['all-xyz.css'],
                    'js' => ['all-xyz.js'],
                ],
                'A' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'B' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'C' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'D' => ['css' => [], 'js' => [], 'depends' => ['all']],
            ],
        ],
    ],
];
```

Como foi explicado na subseção [Personalizando os Asset Bundles](#customizing-asset-bundles), 
a configuração acima altera o comportamento padrão de cada bundle. Em particular, 
os bundles A, B, C e D não precisam mais de arquivos de asset.
Agora todos dependem do bundle `all` que contém os arquivos `all-xyz.css` e 
`all-xyz.js` combinados. Consequentemente, para a página X, ao invés de incluir 
os arquivos fontes originais dos bundles A, B e C, apenas estes dois arquivos 
combinados serão incluídos; a mesma coisa acontece com a página Y.

Existe um truque final para fazer o trabalho da abordagem acima de forma mais 
simples. Ao invés de modificar diretamente o arquivo de configuração da aplicação, 
você pode colocar o array de personalização do bundle em um arquivo separado e 
condicionalmente incluir este arquivo na configuração da aplicação. Por exemplo, 

```php
return [
    'components' => [
        'assetManager' => [
            'bundles' => require __DIR__ . '/' . (YII_ENV_PROD ? 'assets-prod.php' : 'assets-dev.php'),  
        ],
    ],
];
```

Ou seja, o array de configuração do asset bundle será salvo no arquivo 
`assets-prod.php` quando estiver em modo de produção e o arquivo `assets-dev.php` 
quando não estiver em produção.


### Usando o Comando `asset` <span id="using-asset-command"></span>

O Yii fornece um comando console chamado `asset` para automatizar a abordagem que 
acabamos de descrever.

Para usar este comando, você deve primeiro criar um arquivo de configuração para 
descrever quais asset bundles devem ser combinados e como devem ser agrupados. 
Você pode usar o subcomando `asset/template` para gerar um template para que 
possa modificá-lo para atender as suas necessidades.

```
yii asset/template assets.php
```

O comando gera um arquivo chamado `assets.php` no diretório onde foi executado. 
O conteúdo deste arquivo assemelha-se ao seguinte:

```php
<?php
/**
 * Arquivo de configuração para o comando console "yii asset".
 * Observer que no ambiente de console, alguns caminhos de alias como '@webroot' e o '@web' podem não existir.
 * Por favor, defina os caminhos de alias inexistentes.
 */
return [
    // Ajuste do comando/call-back para a compressão os arquivos JavaScript:
    'jsCompressor' => 'java -jar compiler.jar --js {from} --js_output_file {to}',
    // Ajuste de comando/callback para a compressão dos arquivos CSS:
    'cssCompressor' => 'java -jar yuicompressor.jar --type css {from} -o {to}',
    // A lista de asset bundles que serão comprimidos:
    'bundles' => [
        // 'yii\web\YiiAsset',
        // 'yii\web\JqueryAsset',
    ],
    // Asset bundle do resultado da compressão:
    'targets' => [
        'all' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
        ],
    ],
    // Configuração do gerenciados de asset:
    'assetManager' => [
    ],
];
```

Você deve modificar este arquivo e especificar quais bundles você deseja combinar 
na opção `bundles`. Na opção `targets` você deve especificar como os bundles 
devem ser divididos em grupos. Você pode especificar um ou vários grupos, como 
mencionado anteriormente.

> Observação: Como as alias `@webroot` e `@web` não estão disponíveis na aplicação 
  console, você deve defini-los explicitamente na configuração.

Os arquivos JavaScript são combinados, comprimidos e escritos no arquivo 
`js/all-{hash}.js` onde {hash} será substituído pelo hash do arquivo resultante.

As opções `jsCompressor` e `cssCompressor` especificam os comando ou callbacks 
PHP para realizar a combinação/compressão do JavaScript e do CSS. Por padrão, o 
Yii usa o [Closure Compiler](https://developers.google.com/closure/compiler/) 
para combinar os arquivos JavaScript e o 
[YUI Compressor](https://github.com/yui/yuicompressor/) para combinar os arquivos CSS.
Você deve instalar estas ferramentas manualmente ou ajustar estas opções para 
usar as suas ferramentas favoritas.

Com o arquivo de configuração, você pode executar o comando `asset` para combinar
e comprimir os arquivos de asset e em seguida gerar um novo arquivo de configuração 
de asset bundle `assets-prod.php`:

```
yii asset assets.php config/assets-prod.php
```

O arquivo de configuração gerado pode ser incluído na configuração da aplicação, 
conforme descrito na última subseção.


> Informação: O uso do comando `asset` não é a única opção para automatizar o 
  processo de combinação e compressão de asset. Você pode usar a excelente 
  ferramenta chamada [grunt](http://gruntjs.com/) para atingir o mesmo objetivo.

### Agrupando Asset Bundles <span id="grouping-asset-bundles"></span>

Na última subseção, nós explicamos como combinar todos os asset bundles em um
único bundle, a fim de minimizar as requisições HTTP de arquivos de asset referenciados
em uma aplicação. Porém, isto nem sempre é desejável na prática. Por exemplo, imagine 
que sua aplicação possua um "front end", bem como um "back end", cada um possuindo um 
conjunto diferente de JavaScript e CSS. Neste caso, combinando todos os asset bundles
de ambas as extremidades em um único bundle não faz sentido, porque os asset bundles 
pata o "front end" não são utilizados pelo "back end" e seria um desperdício de uso 
de banda de rede para enviar os assets do "back end" quando uma página de "front end"
for solicitada.

Para resolver este problema, você pode dividir asset bundles e, grupos e combinar 
asset bundles para cada grupo. A configuração a seguir mostra como pode agrupar 
os asset bundles:

```php
return [
    ...
    // Especifique o bundle de saída com os grupos:
    'targets' => [
        'allShared' => [
            'js' => 'js/all-shared-{hash}.js',
            'css' => 'css/all-shared-{hash}.css',
            'depends' => [
                // Inclua todos os asset que serão compartilhados entre o 'backend' e o 'frontend'
                'yii\web\YiiAsset',
                'app\assets\SharedAsset',
            ],
        ],
        'allBackEnd' => [
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
            'depends' => [
                // Inclua apenas os assets do 'backend':
                'app\assets\AdminAsset'
            ],
        ],
        'allFrontEnd' => [
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
            'depends' => [], // Inclua todos os asset restantes
        ],
    ],
    ...
];
```

Como você pode ver, os asset bundles são divididos em três grupos: `allShared`, `allBackEnd` e `allFrontEnd`.
Cada um deles dependem de um conjunto de asset bundles. Por exemplo, o `allBackEnd` depende de `app\assets\AdminAsset`.
Ao executar o comando `asset` com essa configuração, será combinado os asset bundles de acordo com as especificações acima.

> Informação: Voce pode deixar a configuração `depends` em branco para um determinado bundle.
Ao fazer isso, esse asset bundle dependerá de todos os asset bundles restantes que outros
determinados bundles não dependam.

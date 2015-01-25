Extensões 
=========

As extensões são pacotes de software redistribuíveis especialmente projetadas 
para serem usadas em aplicações Yii e fornecem recursos prontos para o uso. Por 
exemplo, a extensão [yiisoft/yii2-debug](tool-debugger.md) adiciona uma barra de 
ferramentas de depuração na parte inferior de todas as páginas em sua aplicação 
para ajudar a compreender mais facilmente como as páginas são geradas. Você pode 
usar as extensões para acelerar o processo de desenvolvimento. Você também pode 
empacotar seus códigos como extensões para compartilhar com outras pessoas o seu 
bom trabalho.

> Informação: Usamos o termo "extensão" para referenciar os pacotes de software 
  específicos do Yii. Para propósito geral, os pacotes de software que podem ser 
  usados sem o Yii, referenciamos sob o termo de "pacote" ou "biblioteca".


## Usando Extensões <span id="using-extensions"></span>

Para usar uma extensão, você precisa instalá-lo primeiro. A maioria das extensões 
são distribuídas como pacotes do [Composer](https://getcomposer.org/) que podem 
ser instaladas seguindo dois passos:

1. modifique o arquivo `composer.json` de sua aplicação e especifique quais 
   extensões (pacotes do Composer) você deseja instalar.
2. execute `composer install` para instalar as extensões especificadas.

Observe que você pode precisa instalar o [Composer](https://getcomposer.org/) 
caso você não tenha feito isto antes.

Por padrão, o Composer instala pacotes registados no [Packagist](https://packagist.org/) - 
o maior repositório open source de pacotes do Composer. Você também pode [criar 
o seu próprio repositório](https://getcomposer.org/doc/05-repositories.md#repository) 
e configurar o Composer para usá-lo. Isto é útil caso você desenvolva extensões 
privadas que você deseja compartilhar apenas em seus projetos.

As extensões instaladas pelo Composer são armazenadas no diretório `BasePath/vendor`, 
onde o `BasePath` refere-se ao [caminho base](structure-applications.md#basePath) 
da aplicação. Como o Composer é um gerenciador de dependências, quando ele instala 
um pacote, também instala todos os pacotes dependentes.

Por exemplo, para instalar a extensão `yiisoft/yii2-imagine`, modifique seu 
`composer.json` conforme o seguinte exemplo:

```json
{
    // ...

    "require": {
        // ... other dependencies

        "yiisoft/yii2-imagine": "*"
    }
}
```

Depois da instalação, você deve enxergar o diretório `yiisoft/yii2-imagine` sob 
o diretório `BasePath/vendor`. Você também deve enxergar outro diretório 
`imagine/imagine` que contém os pacotes dependentes instalados. 

> Informação: O `yiisoft/yii2-imagine` é uma extensão nativa desenvolvida e mantida 
  pela equipe de desenvolvimento do Yii. Todas as extensões nativas estão hospedadas 
  no [Packagist](https://packagist.org/) e são nomeadas como `yiisoft/yii2-xyz`, 
  onde `xyz` varia para cada extensão.

Agora, você pode usar as extensões instaladas como parte de sua aplicação. O 
exemplo a seguir mostra como você pode usar a classe `yii\imagine\Image` 
fornecido pela extensão `yiisoft/yii2-imagine`:

```php
use Yii;
use yii\imagine\Image;

// gera uma imagem thumbnail 
Image::thumbnail('@webroot/img/test-image.jpg', 120, 120)
    ->save(Yii::getAlias('@runtime/thumb-test-image.jpg'), ['quality' => 50]);
```

> Informação: As classes de extensão são carregadas automaticamente pela 
  [classe autoloader do Yii](concept-autoloading.md).


### Instalando Extensões Manualmente <span id="installing-extensions-manually"></span>

Em algumas raras ocasiões, você pode querer instalar algumas ou todas extensões 
manualmente, ao invés de depender do Composer.
Para fazer isto, você deve:

1. fazer o download da extensão com os arquivos zipados e os dezipe no diretório `vendor`
2. instalar as classes autoloaders fornecidas pela extensão, se houver.
3. fazer o download e instalar todas as extensões dependentes que foi instruído.

Se uma extensão não tiver uma classe autoloader seguindo a 
[norma PSR-4](http://www.php-fig.org/psr/psr-4/), você pode usar a classe 
autoloader fornecida pelo Yii para carregar automaticamente as classes de 
extensão. Tudo o que você precisa fazer é declarar uma 
[alias root](concept-aliases.md#defining-aliases) para o diretório root da 
extensão. Por exemplo, assumindo que você instalou uma extensão no diretório 
`vendor/mycompany/myext` e que a classe da extensão está sob o namespace `myext`, 
você pode incluir o código a seguir na configuração de sua aplicação:

```php
[
    'aliases' => [
        '@myext' => '@vendor/mycompany/myext',
    ],
]
```


## Criando Extensões <span id="creating-extensions"></span>

Você pode considerar criar uma extensão quando você sentir a necessidade de 
compartilhar o seu bom código para outras pessoas.
Uma extensão pode conter qualquer código que você deseja, tais como uma classe 
helper, um widget, um módulo, etc.

É recomendado que você crie uma extensão através do 
[pacote doComposer](https://getcomposer.org/) de modo que possa ser mais 
facilmente instalado e usado por outros usuário, como descrito na última subseção.

Abaixo estão as básicas etapas que você pode seguir para criar uma extensão como 
um pacote do Composer.

1. Crie uma projeto para sua extensão e guarde-o em um repositório CVS, como o 
   [github.com](https://github.com). O trabalho de desenvolvimento e de manutenção 
   deve ser feito neste repositório.
2. Sob o diretório root do projeto, crie um arquivo chamado `composer.json` como 
   o requerido pelo Composer. Por favor, consulte a próxima subseção para mais 
   detalhes.
3. Registre sua extensão no repositório do Composer, como o 
   [Packagist](https://packagist.org/), de modo que outros usuário possam achar 
   e instalar suas extensões usando o Composer.


### `composer.json` <span id="composer-json"></span>

Cada pacote do Composer deve ter um arquivo `composer.json` no diretório root. O 
arquivo contém os metadados a respeito do pacote. Você pode achar a especificação 
completa sobre este arquivo no [Manual do Composer](https://getcomposer.org/doc/01-basic-usage.md#composer-json-project-setup).
O exemplo a seguir mostra o arquivo `composer.json` para a extensão `yiisoft/yii2-imagine`:

```json
{
    // nome do pacote
    "name": "yiisoft/yii2-imagine",

    // tipo de pacote
    "type": "yii2-extension",

    "description": "The Imagine integration for the Yii framework",
    "keywords": ["yii2", "imagine", "image", "helper"],
    "license": "BSD-3-Clause",
    "support": {
        "issues": "https://github.com/yiisoft/yii2/issues?labels=ext%3Aimagine",
        "forum": "http://www.yiiframework.com/forum/",
        "wiki": "http://www.yiiframework.com/wiki/",
        "irc": "irc://irc.freenode.net/yii",
        "source": "https://github.com/yiisoft/yii2"
    },
    "authors": [
        {
            "name": "Antonio Ramirez",
            "email": "amigo.cobos@gmail.com"
        }
    ],

    // dependências do pacote
    "require": {
        "yiisoft/yii2": "*",
        "imagine/imagine": "v0.5.0"
    },

    // especifica as classes autoloading 
    "autoload": {
        "psr-4": {
            "yii\\imagine\\": ""
        }
    }
}
```


#### Nome do Pacote <span id="package-name"></span>

Cada pacote do Composer deve ter um nome que identifica unicamente o pacote 
entre todos os outros. Os nomes dos pacotes devem seguir o formato 
`vendorName/projectName`. Por exemplo, no nome do pacote `yiisoft/yii2-imagine`, 
o nome do vendor e o nome do projeto são `yiisoft` e `yii2-imagine`, 
respectivamente.

NÃO utilize `yiisoft` como nome do seu vendor já que ele é usado pelo Yii para 
os códigos nativos.

Recomendamos que você use o prefixo `yii2-` para o nome do projeto dos pacotes 
de extensões em Yii 2, por exemplo, `myname/yii2-mywidget`. Isto permitirá que 
os usuários encontrem mais facilmente uma extensão Yii 2.


#### Tipo de Pacote <span id="package-type"></span>

É importante que você especifique o tipo de pacote de sua extensão como 
`yii2-extension`, de modo que o pacote possa ser reconhecido como uma extensão 
do Yii quando for instalado.

Quando um usuário executar `composer install` para instalar uma extensão, o 
arquivo `vendor/yiisoft/extensions.php` será atualizada automaticamente para 
incluir informações referentes a nova extensão. A partir deste arquivo, as 
aplicações Yii podem saber quais extensões estão instaladas (a informação pode 
ser acessada através da propriedade [[yii\base\Application::extensions]]).


#### Dependências <span id="dependencies"></span>

Sua extensão depende do Yii (claro!). Sendo assim, você deve listar (`yiisoft/yii2`) 
na entrada `require` do `composer.json`. Se sua extensão também depender de outras 
extensões ou de bibliotecas de terceiros, você deve lista-los também. Certifique-se 
de listar as constantes de versões apropriadas (por exemplo, `1.*`, `@stable`) 
para cada pacote dependente. Utilize dependências estáveis quando sua extensão 
estiver em uma versão estável.

A maioria dos pacotes JavaScript/CSS são gerenciados pelo [Bower](http://bower.io/) 
e/ou pelo [NPM](https://www.npmjs.org/), ao invés do Composer. O Yii usa o 
[plugin de asset do Composer](https://github.com/francoispluchino/composer-asset-plugin) 
para habilitar a gerência destes tipos de pacotes através do Composer. Se sua 
extensão depender do pacote do Bower, você pode simplesmente listar a dependência 
no `composer.json` conforme o exemplo a seguir:

```json
{
    // package dependencies
    "require": {
        "bower-asset/jquery": ">=1.11.*"
    }
}
```

O código anterior indica que a extensão depende do pacote `jquery` do Bower. Em 
geral, no `composer.json`, você pode usar o `bower-asset/PackageName` para 
referenciar um pacote do Bower no `composer.json`, e usar o `npm-asset/PackageName` 
para referenciar um pacote do NPM, por padrão o conteúdo do pacote será instalado 
sob os diretórios `@vendor/bower/PackageName` e `@vendor/npm/Packages`, 
respectivamente.
Estes dois diretórios podem ser referenciados para usar alias mais curtas como 
`@bower/PackageName` e `@npm/PackageName`.

Para mais detalhes sobre o gerenciamento de asset, por favor, consulte a seção 
[Assets](structure-assets.md#bower-npm-assets).

#### Classe Autoloading <span id="class-autoloading"></span>

Para que suas classes sejam carregadas automaticamente pela classe autoloader do 
Yii ou da classe autoloader do Composer, você deve especificar a entrada `autoload` 
no arquivo `composer.json`, conforme mostrado a seguir:

```json
{
    // ....

    "autoload": {
        "psr-4": {
            "yii\\imagine\\": ""
        }
    }
}
```

Você pode listar um ou vários namespaces e seus caminhos de arquivos correspondentes.

Quando a extensão estiver instalada em uma aplicação, o Yii irá criar para cada 
namespace listada uma [alias](concept-aliases.md#extension-aliases) que se 
referenciará ao diretório correspondente ao namespace.
Por exemplo, a declaração acima do `autoload` corresponderá a uma alias chamada 
`@yii/imagine`.


### Práticas Recomendadas <span id="recommended-practices"></span>

Como as extensões são destinadas a serem usadas por outras pessoas, você precisará, 
por muitas vezes, fazer um esforço extra durante o desenvolvimento. A seguir, 
apresentaremos algumas práticas comuns e recomendadas na criação de extensões de 
alta qualidade.


#### Namespaces <span id="namespaces"></span>

Para evitar conflitos de nomes e criar classes autocarregáveis em sua extensão, 
você deve usar namespaces e nomear as classes seguindo o 
[padrão PSR-4](http://www.php-fig.org/psr/psr-4/) ou o 
[padrão PSR-0](http://www.php-fig.org/psr/psr-0/).

Seus namespaces de classes devem iniciar com `vendorName\extensionName`, onde a 
`extensionName` é semelhante ao nome da extensão, exceto que ele não deve conter 
o prefixo `yii2-`. Por exemplo, para a extensão `yiisoft/yii2-imagine`, usamos o 
`yii\imagine` como namespace para suas classes.

Não use `yii`, `yii2` ou `yiisoft` como nome do seu vendor. Estes nomes são 
reservados para serem usados para o código nativo do Yii.


#### Inicialização das Classes <span id="bootstrapping-classes"></span>

As vezes, você pode querer que sua extensão execute algum código durante o 
[processo de inicialização](runtime-bootstrapping.md) de uma aplicação. Por 
exemplo, a sua extensão pode querer responder ao evento `beginRequest` da 
aplicação para ajustar alguma configuração do ambiente. Embora você possa 
instruir os usuários que usam a extensão para associar explicitamente a sua 
função ao evento `beginRequest`, a melhor maneira é fazer isso é automaticamente.

Para atingir este objetivo, você pode criar uma *classe de inicialização* 
implementando o [[yii\base\BootstrapInterface]].
Por exemplo,

```php
namespace myname\mywidget;

use yii\base\BootstrapInterface;
use yii\base\Application;

class MyBootstrapClass implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $app->on(Application::EVENT_BEFORE_REQUEST, function () {
             // fazer alguma coisa aqui
        });
    }
}
```

Em seguida, liste esta classe no arquivo `composer.json` de sua extensão conforme 
o seguinte,

```json
{
    // ...

    "extra": {
        "bootstrap": "myname\\mywidget\\MyBootstrapClass"
    }
}
```

Quando a extensão for instalada em uma aplicação, o Yii instanciará 
automaticamente a classe de inicialização e chamará o método 
[[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] durante o processo de 
inicialização para cada requisição.


#### Trabalhando com Banco de Dados <span id="working-with-databases"></span>

Sua extensão pode precisar acessar banco de dados. Não pressupunha que as 
aplicações que usam sua extensão SEMPRE usam o `Yii::$db` como a conexão do 
banco de dados. Em vez disso, você deve declarar a propriedade `db` para as 
classes que necessitam acessar o banco de dados.
A propriedade permitirá que os usuários de sua extensão personalizem quaisquer 
conexão de banco de dados que gostariam de usar.
Como exemplo, você pode consultar a classe [[yii\caching\DbCache]] e ver como 
declara e usa a propriedade `db`.

Se sua extensão precisar criar uma tabela específica no banco de dados ou fazer 
alterações no esquema do banco de dados, você deve: 

- fornecer [migrations](db-migrations.md) para manipular o esquema do banco de 
  dados, ao invés de usar arquivos simples de SQL;
- tentar criar migrations aplicáveis em diferentes SGDB;
- evitar o uso de [Active Record](db-active-record.md) nas migrations.


#### Usando Assets <span id="using-assets"></span>

Se sua extensão usar um widget ou um módulo, pode ter grandes chances de requerer 
algum [assets](structure-assets.md) para funcionar.
Por exemplo, um módulo pode exibir algumas páginas que contém imagens, JavaScript 
e CSS. Como os arquivos de uma extensão estão todos sob o diretório que não é 
acessível pela Web quando instalado em uma aplicação, você tem duas escolhas 
para tornar estes arquivos de asset diretamente acessíveis pela Web:

- informe aos usuários da extensão copiar manualmente os arquivos de asset para 
  uma pasta determinada acessível pela Web;
- declare um [asset bundle](structure-assets.md) e conte com o mecanismo de 
  publicação de asset para copiar automaticamente os arquivos listados no asset 
  bundle para uma pasta acessível pela Web.

Recomendamos que você use a segunda abordagem de modo que sua extensão possa ser 
usada com mais facilidade pelos usuários. Por favor, consulte a seção 
[Assets](structure-assets.md) para mais detalhes sobre como trabalhar com assets 
em geral.


#### Internacionalização e Localização <span id="i18n-l10n"></span>

Sua extensão pode ser usada por aplicações que suportam diferentes idiomas! 
Portanto, se sua extensão exibir conteúdo para os usuários finais, você deve 
tentar usar [internacionalização e localização](tutorial-i18n.md). Em particular,

- Se a extensão exibir mensagens aos usuários finais, as mensagens devem usadas 
  por meio do método `Yii::t()` de modo que eles possam ser traduzidas. As 
  mensagens voltadas para os desenvolvedores (como mensagens internas de exceções) 
  não precisam ser traduzidas.
- Se a extensão exibir números, datas, etc., devem ser formatadas usando a classe 
  [[yii\i18n\Formatter]] com as regras de formatação apropriadas. 

Para mais detalhes, por favor, consulte a seção [Internacionalização](tutorial-i18n.md).


#### Testes <span id="testing"></span>

Você quer que sua extensão execute com perfeição sem trazer problemas para outras 
pessoas. Para alcançar este objetivo, você deve testar sua extensão antes de 
liberá-lo ao público.

É recomendado que você crie várias unidades de testes para realizar simulações 
no código de sua extensão ao invés de depender de testes manuais.
Toda vez que liberar uma nova versão de sua extensão, você pode simplesmente 
rodar as unidades de teste para garantir que tudo esteja em boas condições. O 
Yii fornece suporte para testes, que podem ajuda-los a escrever mais facilmente 
testes unitários, testes de aceitação e testes funcionais. Para mais detalhes, 
por favor, consulte a seção [Testing](test-overview.md).


#### Versionamento <span id="versioning"></span>

Você deve dar para cada liberação de sua extensão um numero de versão (por exemplo, 
`1.0.1`). Recomendamos que você siga a prática [versionamento semântico](http://semver.org) 
ao determinar qual número de versão será usado.


#### Liberando Versões <span id="releasing"></span>

Para que outras pessoas saibam sobre sua extensão, você deve liberá-lo ao público.

Se é a primeira vez que você está liberando uma extensão, você deve registrá-lo 
no repositório do Composer, como o [Packagist](https://packagist.org/). Depois 
disso, tudo o que você precisa fazer é simplesmente criar uma tag de liberação 
(por exemplo, `v1.0.1`) no repositório CVS de sua extensão e notificar o 
repositório do Composer sobre a nova liberação. As pessoas, então, serão capazes 
de encontrar a nova versão e instalá-lo ou atualizá-lo através do repositório do 
Composer.

As versões de sua extensão, além dos arquivos de códigos, você deve também 
considerar a inclusão de roteiros para ajudar as outras pessoas aprenderem a usar 
a sua extensão:

* Um arquivo readme no diretório root do pacote: descreve o que sua extensão faz 
  e como faz para instalá-lo e usá-lo. Recomendamos que você escreva no formato 
  [Markdown](http://daringfireball.net/projects/markdown/) e o nome do arquivo 
  como `readme.md`.
* Um arquivo changelog no diretório root do pacote: lista quais mudanças foram 
  feitas em cada versão. O arquivo pode ser escrito no formato Markdown e 
  nomeado como `changelog.md`.
* Uma arquivo de atualização no diretório root do pacote: fornece as instruções 
  de como atualizar a extensão a partir de versões antigas. O arquivo deve ser 
  escrito no formato Markdown e nomeado como `upgrade.md`.
* Tutoriais, demos, screenshots, etc.: estes são necessários se sua extensão 
  fornece muitos recursos que podem não ser totalmente cobertos no arquivo readme.
* Documentação da API: seu código deve ser bem documentado para permitir que 
  outros usuários possam ler e entender mais facilmente.
  Você pode consultar o [arquivo da classe Object](https://github.com/yiisoft/yii2/blob/master/framework/base/Object.php) 
  para aprender como documentar o seu código.

> Informação: Os seus comentários no código podem ser escritos no formato Markdown. 
  A extensão `yiisoft/yii2-apidoc` fornece uma ferramenta para gerar uma documentação 
  da API com base nos seus comentários.

> Informação: Embora não seja um requisito, sugerimos que sua extensão se conforme 
  a determinados estilos de codificação. Você pode consultar o 
  [estilo de codificação do framework](https://github.com/yiisoft/yii2/wiki/Core-framework-code-style).


## Extensões Nativas <span id="core-extensions"></span>

O Yii fornece as seguintes extensões que são desenvolvidas e mantidas pela equipe 
de desenvolvimento do Yii. Todos são registrados no [Packagist](https://packagist.org/) 
e podem ser facilmente instalados como descrito na subseção [Usando Extensões](#using-extensions).

- [yiisoft/yii2-apidoc](https://github.com/yiisoft/yii2-apidoc):
  fornece um gerador de API de documentação extensível e de alto desempenho. 
  Também é usado para gerar a API de documentação do framework.
- [yiisoft/yii2-authclient](https://github.com/yiisoft/yii2-authclient):
  fornece um conjunto comum de autenticadores de clientes, como Facebook OAuth2 
  client, GitHub OAuth2 client.
- [yiisoft/yii2-bootstrap](https://github.com/yiisoft/yii2-bootstrap):
  fornece um conjunto de widgets que encapsulam os componentes e plug-ins do 
  [Bootstrap](http://getbootstrap.com/).
- [yiisoft/yii2-codeception](https://github.com/yiisoft/yii2-codeception):
  fornece suporte a testes baseados no [Codeception](http://codeception.com/).
- [yiisoft/yii2-debug](https://github.com/yiisoft/yii2-debug):
  fornece suporte a depuração para aplicações Yii. Quando esta extensão é usada, 
  uma barra de ferramenta de depuração aparecerá na parte inferior de cada página. 
  A extensão também fornece um conjunto de páginas independentes para exibir mais 
  detalhes das informações de depuração.
- [yiisoft/yii2-elasticsearch](https://github.com/yiisoft/yii2-elasticsearch):
  fornece suporte para o uso de [Elasticsearch](http://www.elasticsearch.org/). 
  Este inclui suporte a consultas/pesquisas básicas e também implementa o padrão 
  [Active Record](db-active-record.md) que permite que você armazene os active 
  records no Elasticsearch.
- [yiisoft/yii2-faker](https://github.com/yiisoft/yii2-faker):
  fornece suporte para o uso de [Faker](https://github.com/fzaninotto/Faker) para 
  gerar dados falsos para você.
- [yiisoft/yii2-gii](https://github.com/yiisoft/yii2-gii):
  fornece um gerador de código baseado na Web que é altamente extensível e pode 
  ser usado para gerar rapidamente models (modelos), formulários, módulos, CRUD, etc.
- [yiisoft/yii2-imagine](https://github.com/yiisoft/yii2-imagine):
  fornece funções de manipulação de imagens comumente utilizados com base no 
  [Imagine](http://imagine.readthedocs.org/).
- [yiisoft/yii2-jui](https://github.com/yiisoft/yii2-jui):
  fornece um conjunto de widgets que encapsulam as interações e widgets do 
  [JQuery UI](http://jqueryui.com/).
- [yiisoft/yii2-mongodb](https://github.com/yiisoft/yii2-mongodb):
  fornece suporte para o uso do [MongoDB](http://www.mongodb.org/). Este inclui 
  recursos como consultas básicas, Active Record, migrations, cache, geração de 
  códigos, etc.
- [yiisoft/yii2-redis](https://github.com/yiisoft/yii2-redis):
  fornece suporte para o uso do [redis](http://redis.io/). Este inclui recursos 
  como consultas básicas, Active Record, cache, etc.
- [yiisoft/yii2-smarty](https://github.com/yiisoft/yii2-smarty):
  fornece um motor de template baseado no [Smarty](http://www.smarty.net/).
- [yiisoft/yii2-sphinx](https://github.com/yiisoft/yii2-sphinx):
  fornece suporte para o uso do [Sphinx](http://sphinxsearch.com). Este inclui 
  recursos como consultas básicas, Active Record, geração de códigos, etc.
- [yiisoft/yii2-swiftmailer](https://github.com/yiisoft/yii2-swiftmailer):
  fornece recursos para envio de e-mails baseados no [swiftmailer](http://swiftmailer.org/).
- [yiisoft/yii2-twig](https://github.com/yiisoft/yii2-twig):
  fornece um motor de template baseado no [Twig](http://twig.sensiolabs.org/).
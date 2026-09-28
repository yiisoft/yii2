Trabalhando com Códigos de Terceiros
=============================

De tempos em tempos, você pode precisar usar algum código de terceiro na sua aplicação Yii. Ou você pode querer utilizar o Yii como uma biblioteca em alguns sistemas de terceiros. Nesta seção, vamos mostrar como fazer isto.


Usando Bibliotecas de Terceiros no Yii <span id="using-libs-in-yii"></span>
----------------------------------

Para utilizar bibliotecas de terceiros em uma aplicação Yii, você precisa primeiramente garantir que as classes na biblioteca estão devidamente incluídas ou se podem ser carregadas por demanda.


### Usando Pacotes Composer <span id="using-composer-packages"></span>

Muitas bibliotecas de terceiros gerenciam suas versões através de pacotes do [Composer](https://getcomposer.org/). Você pode instalar tais bibliotecas realizando os dois seguintes passos:

1. Modifique o arquivo `composer.json` da sua aplicação e informe quais pacotes Composer você deseja instalar.
2. Execute `composer install` para instalar os pacotes especificados.

As classes nos pacotes Composer instalados podem ser carregadas automaticamente usando o autoloader do Composer. Certifique-se que o [script de entrada](structure-entry-scripts.md) da sua aplicação contém as seguintes linhas para instalar o autoloader do Composer:

```php
// Instala o Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// faz o include da classe Yii
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
```


### Usando Bibliotecas baixadas <span id="using-downloaded-libs"></span>

Se a biblioteca não foi lançada como um pacote Composer, você deve seguir as instruções de instalação para instalá-la. Na maioria dos casos, você precisará baixar manualmente o arquivo de liberação da biblioteca e descompactá-lo no diretório `BasePath/vendor`, onde `BasePath` representa o [caminho base](structure-applications.md#basePath) da sua aplicação.

Se uma biblioteca possui o seu próprio carregador automático, você pode instalá-la no [script de entrada](structure-entry-scripts.md) de sua aplicação. Recomenda-se que a instalação seja feita antes de incluir o arquivo `Yii.php`. Isto porque a classe autoloader Yii pode ter precedência nas classes de carregamento automático da biblioteca a ser instalada.

Se uma biblioteca não oferece um carregador automático de classe, mas seus nomes seguem o padrão [PSR-4](https://www.php-fig.org/psr/psr-4/), você pode usar a classe de autoloader do  Yii para carregar as classes. Tudo que você precisa fazer é apenas declarar um [alias](concept-aliases.md#defining-aliases) para cada namespace raiz utilizados em suas classes. Por exemplo, suponha que você tenha instalado uma biblioteca no diretório `vendor/foo/bar` e as classes de bibliotecas estão sob o namespace raiz `xyz`. Você pode incluir o seguinte código na configuração da sua aplicação:

```php
[
   'aliases' => [
       '@xyz' => '@vendor/foo/bar',
   ],
]
```


Se não for nenhuma das opções acima, é provável que a biblioteca necessite fazer um include PHP com algum caminho específico para localizar corretamente os arquivos das classes. Basta seguir as instruções de como configurar o *include path* do PHP.


No pior dos casos, quando a biblioteca exige explicitamente a inclusão de cada arquivo de classe, você pode usar o seguinte método para incluir as classes por demanda:

* Identificar quais as classes da biblioteca contém.
* Liste as classes e os caminhos dos arquivos correspondentes em `Yii::$classMap` no [script de entrada](structure-entry-scripts.md) da aplicação. Por exemplo:

```php
Yii::$classMap['Class1'] = 'path/to/Class1.php';
Yii::$classMap['Class2'] = 'path/to/Class2.php';
```


Usando o Yii em Sistemas de Terceiros <span id="using-yii-in-others"></span>
--------------------------------

Como o Yii fornece muitas características excelentes, algumas vezes você pode querer utilizar algumas destas características como suporte ao desenvolvimento ou melhorias em sistemas de terceiros, tais como WordPress, Joomla ou aplicações desenvolvidas utilizando outros frameworks PHP. Por exemplo, você pode querer utilizar a classe [[yii\helpers\ArrayHelper]] ou usar o recurso de [Active Record](db-active-record.md) em um sistema de terceiros. Para alcançar este objetivo, você primeiramente precisa realizar dois passos: instale o Yii, e o bootstrap Yii.

Se o sistema em questão utilizar o Composer para gerenciar suas dependências, você pode simplesmente executar o seguinte comando para instalar o Yii:

    composer global require "fxp/composer-asset-plugin:^1.4.1"
    composer require yiisoft/yii2
    composer install

O primeiro comando instala o [Composer asset plugin](https://github.com/fxpio/composer-asset-plugin)
que permite gerenciar o bower e dependências de pacotes npm através do Composer. Mesmo que você apenas queira utilizar a camada de banco de dados ou outros recursos não-ativos relacionados do Yii, isto é necessário para instalar o pacote Composer do Yii.
Veja também a seção [sobre a instalação do Yii](start-installation.md#installing-via-composer) para obter mais informações do Composer e solução para os possíveis problemas que podem surgir durante a instalação.

Caso contrário, você pode fazer o [download](https://www.yiiframework.com/download/) do Yii e descompactá-lo no diretório `BasePath/vendor`.

Em seguida, você deve modificar o script de entrada do sistema de terceiros incluindo o seguinte código no início:

```php
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$yiiConfig = require __DIR__ . '/../config/yii/web.php';
new yii\web\Application($yiiConfig); // NÃO execute o método run() aqui
```

Como você pode ver, o código acima é muito semelhante ao que existe no [script de entrada](structure-entry-scripts.md) de uma aplicação típica do Yii. A única diferença é que depois que a instância da aplicação é criada, o método `run()` não é chamado. Isto porque chamando `run()`, Yii vai assumir o controle do fluxo das requisições que não é necessário neste caso e já é tratado pela aplicação existente.

Como na aplicação Yii, você deve configurar a instância da aplicação com base no ambiente de execução do sistema em questão. Por exemplo, para usar o recurso de [Active Record](db-active-record.md), você precisa configurar o [componente da aplicação](structure-application-components.md) `db` com a configuração de conexão do banco de dados utilizada pelo sistema.

Agora você pode usar a maioria dos recursos fornecidos pelo Yii. Por exemplo, você pode criar classes Active Record e usá-las para trabalhar com o banco de dados.


Usando Yii 2 com Yii 1 <span id="using-both-yii2-yii1"></span>
----------------------
        
Se você estava usando o Yii 1 anteriormente, é provável que você tenha uma aplicação rodando com Yii 1. Em vez de reescrever toda a aplicação em Yii 2, você pode apenas querer melhorá-lo usando apenas alguns dos recursos disponíveis no Yii 2. Isto pode ser alcançado seguindo as instuções a seguir.

> Observação: Yii 2 requer PHP 5.4 ou superior. Você deve certificar-se que o seu servidor e a sua aplicação suportem estes requisitos.

Primeiro, instale o Yii 2 na sua aplicação existente seguindo as instruções dadas na [última subseção](#using-yii-in-others).

Segundo, altere o script de entrada da sua aplicação como a seguir,

```php
// incluir a classe Yii personalizado descrito abaixo
require __DIR__ . '/../components/Yii.php';

// configuração para aplicação Yii 2
$yii2Config = require __DIR__ . '/../config/yii2/web.php';
new yii\web\Application($yii2Config); // NÃO execute o método run() aqui

// configuração para aplicação Yii 1
$yii1Config = require __DIR__ . '/../config/yii1/main.php';
Yii::createWebApplication($yii1Config)->run();
```

Uma vez que ambos Yii 1 e Yii 2 possuem a classe `Yii`, você deve criar uma versão personalizada para combiná-los. O código acima inclui o arquivo de classe personalizado `Yii`, que pode ser criado conforme o exemplo abaixo.

```php
$yii2path = '/path/to/yii2';
require $yii2path . '/BaseYii.php'; // Yii 2.x

$yii1path = '/path/to/yii1';
require $yii1path . '/YiiBase.php'; // Yii 1.x

class Yii extends \yii\BaseYii
{
   // copie e cole o código de YiiBase (1.x) aqui
}

Yii::$classMap = include($yii2path . '/classes.php');
// registrar o autoloader do Yii 2 através do Yii 1
Yii::registerAutoloader(['Yii', 'autoload']);
// criar o contêiner de injeção de dependência
Yii::$container = new yii\di\Container;
```

Isto é tudo! Agora, em qualquer parte do seu código, você pode usar `Yii::$app` para acessar a instância da aplicação Yii 2, enquanto `Yii::app()` lhe dará a instância da aplicação Yii 1:

```php
echo get_class(Yii::app()); // retorna'CWebApplication'
echo get_class(Yii::$app);  // retorna 'yii\web\Application'



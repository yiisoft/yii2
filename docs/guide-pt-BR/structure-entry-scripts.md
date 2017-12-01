Scripts de Entrada
==================

Scripts de entrada são o primeiro passo no processo de inicialização da aplicação.
Uma aplicação (seja uma aplicação Web ou uma aplicação console) possui um único script de
entrada. Os usuários finais fazem requisições nos scripts de entrada que criam
as instâncias da aplicação e redirecionam as requisições para elas.

Os scripts de entrada para aplicações Web devem estar armazenados em diretórios
acessíveis pela Web, de modo que eles possam ser acessados pelos usuários finais.
Frequentemente são chamados de `index.php`, mas também podem usar outros nomes,
desde que os servidores Web consigam localizá-los.

Os scripts de entrada para aplicações do console são geralmente armazenados no
[caminho base](structure-applications.md) das aplicações e são chamados de `yii`
(com o sufixo `.php`). Eles devem ser tornados executáveis para que os usuários
possam executar aplicações do console através do comando
`./yii <rota> [argumentos] [opções]`.

O trabalho principal dos scripts de entrada é o seguinte:

* Definir constantes globais;
* Registrar o [autoloader do Composer](https://getcomposer.org/doc/01-basic-usage.md#autoloading);
* Incluir o arquivo da classe [[Yii]];
* Carregar a configuração da aplicação;
* Criar e configurar uma instância da [aplicação](structure-applications.md);
* Chamar [[yii\base\Application::run()]] para processar as requisições que chegam.


## Aplicações Web <span id="web-applications"></span>

Este é o código no script de entrada para o [Template Básico de Projetos](start-installation.md).

```php
<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// registra o autoloader do Composer
require __DIR__ . '/../vendor/autoload.php';

// inclui o arquivo da classe Yii
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

// carrega a configuração da aplicação
$config = require __DIR__ . '/../config/web.php';

// cria, configura e executa a aplicação
(new yii\web\Application($config))->run();
```


## Aplicações Console <span id="console-applications"></span>

De forma semelhante, o seguinte é o código do script de entrada de uma aplicação
do console:

```php
#!/usr/bin/env php
<?php
/**
 * Yii console bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);

// registra o autoloader do Composer
require __DIR__ . '/vendor/autoload.php';

// inclui o arquivo da classe Yii
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// carrega a configuração da aplicação
$config = require __DIR__ . '/config/console.php';

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
```


## Definindo Constantes <span id="defining-constants"></span>

Os scrips de entrada são o melhor lugar para definir as constantes globais. O
Yii suporta as seguintes três constantes:

* `YII_DEBUG`: especifica se a aplicação está rodando no modo de depuração. No
  modo de depuração, uma aplicação manterá mais informações de log, e revelará
  stacks de chamadas de erros detalhadas se forem lançadas exceções. Por este
  motivo, o modo de depuração deveria ser usado principalmente durante o
  desenvolvimento. O valor padrão de `YII_DEBUG` é `false`.
* `YII_ENV`: especifica em qual ambiente a aplicação está rodando. Isso foi
  descrito em maiores detalhes na seção [Configurações](concept-configurations.md#environment-constants).
  O valor padrão de `YII_ENV` é `'prod'`, significando que a aplicação está
  executando em ambiente de produção.
* `YII_ENABLE_ERROR_HANDLER`: especifica se deve ativar o manipulador de erros
  fornecido pelo Yii. O valor padrão desta constante é `true`.

Ao definir uma constante, frequentemente usamos código como o a seguir:

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
```

que é equivalente ao seguinte código:

```php
if (!defined('YII_DEBUG')) {
    define('YII_DEBUG', true);
}
```

Claramente o primeiro é mais sucinto e fácil de entender.

A definição de constantes deveria ser feita logo no início de um script de entrada,
de modo que obtenha efeito quando outros arquivos PHP estiverem sendo inclusos.

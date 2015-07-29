Log
=======

Yii fornece um poderoso framework de log que é altamente personalizável e extensível. Utilizando este framework, você pode facilmente logar vários tipos de mensagens, filtrá-las, e salva-las em diferentes meios, tais como arquivos, databases, e-mails. 

Usar o Yii framework Log envolve os seguintes passos:

* Registrar [mensagens de log](#log-messages) de vários lugares do seu código;
* Configurar o [destino de log](#log-targets) na configuração da aplicação para filtrar e exportar mensagens de log;
* Examinar as mensagens de erro exportadas  (ex.: [Yii debugger](tool-debugger.md)).

Nesta seção, vamos descrever principalmente os dois primeiros passos.


## Gravar Mensagens <span id="log-messages"></span>

Gravar mensagens de log é tão simples como chamar um dos seguintes métodos de registro:

* [[Yii::trace()]]: gravar uma mensagem para rastrear como um determinado trecho de código é executado. Isso é principalmente para o uso de desenvolvimento.
* [[Yii::info()]]: gravar uma mensagem que transmite algumas informações úteis.
* [[Yii::warning()]]: gravar uma mensagem de aviso que indica que algo inesperado aconteceu.
* [[Yii::error()]]: gravar um erro fatal que deve ser investigado o mais rápido possível.

Estes métodos gravam mensagens de log em vários *níveis* e *categorias*. Eles compartilham a mesma assinatura de função `function ($message, $category = 'application')`, onde `$message` significa a mensagem de log a ser gravada, enquanto `$category` é a categoria da mensagem de log. O código no exemplo a seguir registra uma mensagem de rastreamento sob a categoria padrão `application`:

```php
Yii::trace('start calculating average revenue');
```

> Observação: Mensagens de log podem ser strings, bem como dados complexos, tais como arrays ou objetos. É da responsabilidade dos [destinos de log](#log-targets) lidar adequadamente com as mensagens de log. Por padrão, se uma mensagem de log não for uma string, ela será exportada como uma string chamando [[yii\helpers\VarDumper::export()]].

Para melhor organizar e filtrar as mensagens de log, é recomendável que você especifique uma categoria apropriada para cada mensagem de log. Você pode escolher um esquema de nomenclatura hierárquica para as categorias, o que tornará mais fácil para os [destinos de log](#log-targets) filtrar mensagens com base em suas categorias. Um esquema de nomes simples, mas eficaz é usar a constante mágica PHP `__METHOD__` para os nomes das categorias. Esta é também a abordagem utilizada no códico central do framework Yii. Por exemplo,

```php
Yii::trace('start calculating average revenue', __METHOD__);
```

A constante `__METHOD__` corresponde ao nome do método (prefixado com o caminho completo do nome da classe) onde a constante aparece. Por exemplo, é igual a string `'app\controllers\RevenueController::calculate'` se o código acima for chamado dentro deste método.

> Observação: Os métodos de registro descritos acima são na verdade atalhos para o método [[yii\log\Logger::log()|log()]] do [[yii\log\Logger|logger object]] que é um singleton acessível através da expressão `Yii::getLogger()`. Quando um determinado número de mensagens são logadas ou quando a aplicação termina, o objeto logger irá chamar um [[yii\log\Dispatcher|message dispatcher]] para enviar mensagens de log gravadas [destinos de log](#log-targets).


## Destinos de Log <span id="log-targets"></span>

Um destino de log é uma instância da classe [[yii\log\Target]] ou uma classe filha. Ele filtra as mensagens de log por seus níveis e categorias e, em seguida, às exportam para algum meio. Por exemplo, um [[yii\log\DbTarget|database target]] exporta as mensagens de log para uma tabela no banco de dados, enquanto um [[yii\log\EmailTarget|email target]] exporta as mensagens de log  para algum e-mail especificado.

Você pode registrar vários destinos de log em uma aplicação configurando-os através do [componente da aplicação](structure-application-components.md) `log` na configuração da aplicação, como a seguir:

```php
return [
   // o componente  "log" deve ser carregado durante o tempo de inicialização
   'bootstrap' => ['log'],
   
   'components' => [
       'log' => [
           'targets' => [
               [
                   'class' => 'yii\log\DbTarget',
                   'levels' => ['error', 'warning'],
               ],
               [
                   'class' => 'yii\log\EmailTarget',
                   'levels' => ['error'],
                   'categories' => ['yii\db\*'],
                   'message' => [
                      'from' => ['log@example.com'],
                      'to' => ['admin@example.com', 'developer@example.com'],
                      'subject' => 'Database errors at example.com',
                   ],
               ],
           ],
       ],
   ],
];
```

> Observação: O componente `log` deve ser carregado durante a [inicialização](runtime-bootstrapping.md) para que ele possa enviar mensagens de log para alvos prontamente. É por isso que ele está listado no array `bootstrap` como mostrado acima.

No código acima, dois destinos de log são registrados na propriedade [[yii\log\Dispatcher::targets]]: 

* o primeiro seleciona mensagens de erro e de advertência e os salva em uma tabela de banco de dados;
* o segundo seleciona mensagens de erro sob as categorias cujos nomes começam com `yii\db\`, e as envia para os e-mails `admin@example.com` e `developer@example.com`.

Yii vem com os seguintes destinos de log preparados. Por favor consulte a documentação da API sobre essas classes para aprender como configurar e usá-los. 

* [[yii\log\DbTarget]]: armazena mensagens de log em uma tabela de banco de dados.
* [[yii\log\EmailTarget]]: envia mensagens de log para um endereço de e-mail pré-definido.
* [[yii\log\FileTarget]]: salva mensagens de log em arquivos.
* [[yii\log\SyslogTarget]]: salva mensagens de log para o syslog chamando a função PHP `syslog()`.

A seguir, vamos descrever as características comuns a todos os destinos de log.

 
### Filtragem de Mensagem <span id="message-filtering"></span>

Para cada destino de log, você pode configurar suas propriedades [[yii\log\Target::levels|levels]] e [[yii\log\Target::categories|categories]] para especificar que os níveis e categorias das mensagens o destino de log deve processar.

A propriedade [[yii\log\Target::levels|levels]] é um array que consiste em um ou vários dos seguintes valores:

* `error`: corresponde a mensagens logadas por [[Yii::error()]].
* `warning`: corresponde a mensagens logadas por [[Yii::warning()]].
* `info`: corresponde a mensagens logadas por [[Yii::info()]].
* `trace`: corresponde a mensagens logadas por [[Yii::trace()]].
* `profile`: corresponde a mensagens logadas por [[Yii::beginProfile()]] e [[Yii::endProfile()]], que será explicado em mais detalhes na subseção [Perfil de Desempenho](#performance-profiling).

Se você não especificar a propriedade [[yii\log\Target::levels|levels]], significa que o alvo de log processará mensagens de *qualquer* nível.

A propriedade [[yii\log\Target::categories|categories]] é um array que consiste em categorias de mensagens ou padrões. Um destino de log irá processar apenas mensagens cuja categoria possaser encontrada ou corresponder a um dos padrões do array. Um padrão de categoria é um prefixo de nome de categoria com um asterístico `*` na sua extremidade. Um nome de categoria corresponde a um padrão de categoria se ela iniciar com o mesmo prefixo do padrão. Por exemplo, `yii\db\Command::execute` e `yii\db\Command::query`
são usados como nome de categoria para as mensagens de log gravadas na classe [[yii\db\Command]]. Ambos correspondem ao padrão `yii\db\*`. Se você não especificar a propriedade [[yii\log\Target::categories|categories]], significa que o destino de log processará mensagens de *qualquer* categoria.

Além de criar uma whitelist de categorias através da propriedade [[yii\log\Target::categories|categories]], você também pode criar uma blacklist de categorias através da propriedade [[yii\log\Target::except|except]]. Se a categoria da mensagem for encontrada ou corresponder a um dos padrões desta propriedade, ela não será processada pelo destino de log.

A próxima configuração de destino de log especifica que o destino deve processar somente mensagens de erro e alertas das categorias cujos nomes correspondam a `yii\db\*` ou `yii\web\HttpException:*`, mas não correspondam a `yii\web\HttpException:404`.

```php
[
   'class' => 'yii\log\FileTarget',
   'levels' => ['error', 'warning'],
   'categories' => [
       'yii\db\*',
       'yii\web\HttpException:*',
   ],
   'except' => [
       'yii\web\HttpException:404',
   ],
]
```

> Observação: Quando uma exceção HTTP  é capturada pelo [error handler](runtime-handling-errors.md), uma mensagem de erro será logada com o nome da categoria no formato de `yii\web\HttpException:ErrorCode`. Por exemplo, o [[yii\web\NotFoundHttpException]] causará uma mensagem de erro da categoria `yii\web\HttpException:404`.

### Formatando Mensagem<span id="message-formatting"></span>

Destinos de log exportam as mensagens de logs filtradas em um determinado formato. Por exemplo, se você instalar um destino de log da classe [[yii\log\FileTarget]], você pode encontrar uma mensagem de log semelhante à seguinte no `runtime/log/app.log` file:

```
2014-10-04 18:10:15 [::1][][-][trace][yii\base\Module::getModule] Loading module: debug
```

Por padrão, mensagens de log serão formatadas do seguinte modo pelo [[yii\log\Target::formatMessage()]]:

```
Timestamp [IP address][User ID][Session ID][Severity Level][Category] Message Text
```

Você pode personalizar este formato configurando a propriedade [[yii\log\Target::prefix]] que recebe um PHP callable retornando um prefixo de mensagem personalizado. Por exemplo, o código a seguir configura um destino de log para prefixar cada mensagem de log com o ID do usuário corrente (Endereço IP e ID da sessão são removidos por razões de privacidade).

```php
[
   'class' => 'yii\log\FileTarget',
   'prefix' => function ($message) {
       $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
       $userID = $user ? $user->getId(false) : '-';
       return "[$userID]";
   }
]
```

Além de prefixos de mensagens, destinos de mensagens também anexa algumas informações de contexto para cada lote de mensagens de log. Por padrão, Os valores destas variáveis globais PHP são incluídas: `$_GET`, `$_POST`, `$_FILES`, `$_COOKIE`,
`$_SESSION` e `$_SERVER`. Você pode ajustar este comportamento configurando a propriedade [[yii\log\Target::logVars]] com os nomes das variáveis globais que você deseja incluir para o destino de log. Por exemplo, a seguinte configuração de destino de log especifica que somente valores da variável `$_SERVER` seriam anexadas as mensagens de log.

```php
[
   'class' => 'yii\log\FileTarget',
   'logVars' => ['_SERVER'],
]
```

Você pode configurar `logVars` para ser um array vazio para desativar totalmente a inclusão de informações de contexto. Ou se você quiser implementar sua própria maneira de fornecer informações de contexto, você pode sobrescrever o método [[yii\log\Target::getContextMessage()]].


### Nível de Rastreio de Mensagem <span id="trace-level"></span>

Durante o desenvolvimento, é desejável definir de onde cada mensagem de log virá. Isto pode ser conseguido por meio da configuração da propriedade [[yii\log\Dispatcher::traceLevel|traceLevel]] do componente `log` como a seguir:

```php
return [
   'bootstrap' => ['log'],
   'components' => [
       'log' => [
           'traceLevel' => YII_DEBUG ? 3 : 0,
           'targets' => [...],
       ],
   ],
];
```

A configuração da aplicação acima configura [[yii\log\Dispatcher::traceLevel|traceLevel]] para ser 3 se `YII_DEBUG` estiver ligado e 0 se `YII_DEBUG` estiver desligado. Isso significa, se `YII_DEBUG` estiver ligado, cada mensagem de log será anexada com no máximo 3 níveis de call stack (pilhas de chamadas) em que a mensagem de log é registrada; e se `YII_DEBUG` estiver desligado, nenhuma informação do call stack será incluída.

> Observação: Obter informação do call stack não é trivial. Portanto, você deverá usar somente este recurso durante o desenvolvimento ou durante o debug da aplicação.

### Libertação e Exportação Mensagens <span id="flushing-exporting"></span>

Como já mencionado, mensagens de log são mantidas em um array através do [[yii\log\Logger|logger object]]. Para limitar o consumo de memória por este array, O
o objeto logger irá liberar as mensagens gravadas para os [destinos de log](#log-targets) cada vez que o array acumula um certo número de mensagens de log. Você pode personalizar este número configurando a propriedade [[yii\log\Dispatcher::flushInterval|flushInterval]] do componente `log`:


```php
return [
   'bootstrap' => ['log'],
   'components' => [
       'log' => [
           'flushInterval' => 100,   // default is 1000
           'targets' => [...],
       ],
   ],
];
```

> Observação: Liberação de mensagens também acontece quando a aplicação termina, o que garante que alvos de log possam receber as informações completas de mensagens de log.

Quando o [[yii\log\Logger|logger object]] libera mensagens de log para os [alvos de log](#log-targets), elas não são exportadas imediatamente. Em vez disso, a exportação de mensagem só ocorre quando o alvo de log acumula certo número de mensagens filtradas. Você pode personalizar este número configurando a propriedade [[yii\log\Target::exportInterval|exportInterval]] de cada [alvo de log](#log-targets), como a seguir,

```php
[
   'class' => 'yii\log\FileTarget',
   'exportInterval' => 100,  // default is 1000
]
```

Devido a configuração de nível, liberação e exportação, por padrão quando você chama `Yii::trace()` ou qualquer outro método de log, você NÃO verá a mensagem de log imediatamente no destino. Isto poderia ser um problema para algumas aplicações console de longa execução. Para fazer cada mensagem de log aparecer imediatamente no destino, você deve configurar ambos [[yii\log\Dispatcher::flushInterval|flushInterval]] e [[yii\log\Target::exportInterval|exportInterval]] para  1,
como mostrado abaixo:

```php
return [
   'bootstrap' => ['log'],
   'components' => [
       'log' => [
           'flushInterval' => 1,
           'targets' => [
               [
                   'class' => 'yii\log\FileTarget',
                   'exportInterval' => 1,
               ],
           ],
       ],
   ],
];
```

> Observação: Frequente liberação e exportação de mensagens irá degradar o desempenho da sua aplicação.


### Alternando Destinos de Log <span id="toggling-log-targets"></span>

Você pode habilitar ou desabilitar um destino de log configurando sua propriedade [[yii\log\Target::enabled|enabled]]. Você pode fazê-lo através da configuração do destino de log ou pela seguinte declaração em seu código PHP:

```php
Yii::$app->log->targets['file']->enabled = false;
```

O código acima requer que você nomeie um destino como`file`, como mostrado acima usando chaves de string no array `targets`:

```php
return [
   'bootstrap' => ['log'],
   'components' => [
       'log' => [
           'targets' => [
               'file' => [
                   'class' => 'yii\log\FileTarget',
               ],
               'db' => [
                   'class' => 'yii\log\DbTarget',
               ],
           ],
       ],
   ],
];
```


### Criando Novos Destinos <span id="new-targets"></span>

Criar uma nova classe de destino de log é muito simples. Você primeiramente precisa implementar o método [[yii\log\Target::export()]] enviando o conteúdo do array [[yii\log\Target::messages]] para o meio designado. Você pode chamar o método
[[yii\log\Target::formatMessage()]] para formatar cada mensagem. Para mais detalhes, você pode consultar qualquer uma das classes de destino de log incluído na versão Yii.


## Perfil de Desempenho<span id="performance-profiling"></span>

Perfil de desempenho é um tipo especial de log de mensagem que é usado para medir o tempo que certos blocos de código demora e descobrir quais são os gargalos de desempenho. Por exemplo, a classe [[yii\db\Command]] utiliza perfil de desempenho para descobrir o tempo que cada db query leva.

Para usar perfil de desempenho, primeiro identifique o bloco de código que precisa ser analisado. Então, encapsula cada bloco de código como o seguinte:

```php
\Yii::beginProfile('myBenchmark');

...code block being profiled...

\Yii::endProfile('myBenchmark');
```

onde `myBenchmark` representa um token único de identificação de um bloco de código. Mais tarde quando você for examinar o resultado, você usará este token para localizar o tempo gasto pelo determinado bloco de código.

É importante certificar-se de que os pares de `beginProfile` e `endProfile` estão corretamente aninhadas. Por exemplo,

```php
\Yii::beginProfile('block1');

   // algum código a ser analizado

   \Yii::beginProfile('block2');
       // algum outro código a ser analizado
   \Yii::endProfile('block2');

\Yii::endProfile('block1');
```

Se você esquecer `\Yii::endProfile('block1')` ou trocar a ordem de `\Yii::endProfile('block1')` e
`\Yii::endProfile('block2')`, o perfil de desempenho não funcionará.

Para cada bloco de código iniciado com  `beginProfile`, uma mensagem de log com o nível `profile` é registrada. Você pode configurar um [destino de log](#log-targets) para coletar tais mensagens e exportá-las. O [Yii debugger](tool-debugger.md) implementa um painel de perfil de Desempenho mostrando os seus resultados.


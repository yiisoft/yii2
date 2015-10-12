Tratamento de Erros
===============

O Yii inclui um próprio [[yii\web\ErrorHandler|tratamento de erro]] que o torna uma experiência muito mais agradável do que antes. Em particular, o manipulador de erro do Yii faz o seguinte para melhorar o tratamento de erros:

* Todos os erros não-fatais do PHP (ex. advertências, avisos) são convertidas em exceções capturáveis.

* Exceções e erros fatais do PHP são exibidos com detalhes de informação em uma pilha de chamadas (call stack) e linhas de código-fonte no modo de depuração.

* Suporta o uso de uma [ação do controller](structure-controllers.md#actions) dedicado para exibir erros.

* Suporta diferentes formatos de resposta de erro.

O [[yii\web\ErrorHandler|manipulador de erro]] é habilitado por padrão. Você pode desabilitá-lo definindo a constante `YII_ENABLE_ERROR_HANDLER` como `false` no [script de entrada](structure-entry-scripts.md) da aplicação.


## Usando Manipulador de Erro <span id="using-error-handler"></span>

O [[yii\web\ErrorHandler|manipulador de erro]] é registrado como um [componente da aplicação](structure-application-components.md) chamado `errorHandler`.
Você pode configurá-lo na configuração da aplicação da seguinte forma:

```php
return [
   'components' => [
       'errorHandler' => [
           'maxSourceLines' => 20,
       ],
   ],
];
```

Com a configuração acima, o número de linhas de código fonte para ser exibido em páginas de exceção será de até 20.

Como já informado, o manipulador de erro transforma todos os erros não fatais do PHP em exceções capturáveis. Isto significa que você pode usar o seguinte código para lidar com erros do PHP:

```php
use Yii;
use yii\base\ErrorException;

try {
   10/0;
} catch (ErrorException $e) {
   Yii::warning("Division by zero.");
}

// Continua a execução...
```

Se você deseja mostrar uma página de erro dizendo ao usuário que a sua requisição é inválida ou inesperada, você pode simplesmente lançar uma [[yii\web\HttpException|exceção HTTP]], tal como [[yii\web\NotFoundHttpException]]. O manipulador de erro irá definir corretamente o código de status HTTP da resposta e usar uma exibição de erro apropriada para exibir a mensagem de erro.

```php
use yii\web\NotFoundHttpException;

throw new NotFoundHttpException();
```


## Personalizando a Exibição de Erro <span id="customizing-error-display"></span>

O [[yii\web\ErrorHandler|manipulador de erro]] ajusta a exibição de erro de acordo com o valor da constante `YII_DEBUG`. Quando `YII_DEBUG` for `True` (significa modo de debug), o manipulador de erro irá exibir exceções com informações detalhadas da pilha de chamadas e linhas do código fonte para ajudar na depuração do erro. E quando `YII_DEBUG` for `false`, apenas a mensagem de erro será exibida para evitar revelar informações relevantes sobre a aplicação.

> Observação: Se uma exceção descende de [[yii\base\UserException]], nenhuma pilha de chamadas será exibido independentemente do valor do `YII_DEBUG`. Isso porque tais exceções são consideradas erros causados pelo usuário não havendo nada a ser corrigido por parte dos programadores.

Por padrão, o [[yii\web\ErrorHandler|manipulador de erro]] mostra os erros utilizando duas  [views](structure-views.md):

* `@yii/views/errorHandler/error.php`: utilizada quando os erros devem ser exibidos sem informações pilha de chamadas. Quando `YII_DEBUG` for `false`, esta é a única exibição de erro a ser exibida.

* `@yii/views/errorHandler/exception.php`: utilizada quando os erros devem ser exibidos com informações pilha de chamadas. Você pode configurar as propriedades [[yii\web\ErrorHandler::errorView|errorView]] e [[yii\web\ErrorHandler::exceptionView|exceptionView]]
do manipulador de erros para usar suas próprias views personalizando  a exibição de erro.


### Usando Ações de Erros <span id="using-error-actions"></span>

A melhor maneira de personalizar a exibição de erro é usar uma [ação](structure-controllers.md) dedicada para este fim. Para fazê-lo, primeiro configure a propriedade [[yii\web\ErrorHandler::errorAction|errorAction]] do componente `errorHandler`
como a seguir:

```php
return [
   'components' => [
       'errorHandler' => [
           'errorAction' => 'site/error',
       ],
   ]
];
```

A propriedade [[yii\web\ErrorHandler::errorAction|errorAction]] define uma [rota](structure-controllers.md#routes) para uma ação. A configuração acima afirma que quando um erro precisa ser exibido sem informações da pilha de chamadas, a ação `site/error` deve ser executada.

Você pode criar a ação `site/error` da seguinte forma,

```php
namespace app\controllers;

use Yii;
use yii\web\Controller;

class SiteController extends Controller
{
   public function actions()
   {
       return [
           'error' => [
               'class' => 'yii\web\ErrorAction',
           ],
       ];
   }
}
```

O código acima define a ação `error` usando a classe [[yii\web\ErrorAction]] que renderiza um erro usando a view `error`.

Além de usar [[yii\web\ErrorAction]], você também pode definir a ação `error` usando um método da ação como o seguinte,

```php
public function actionError()
{
   $exception = Yii::$app->errorHandler->exception;
   if ($exception !== null) {
       return $this->render('error', ['exception' => $exception]);
   }
}
```

Agora você deve criar um arquivo de exibição localizado na `views/site/error.php`. Neste arquivo de exibição, você pode acessar as seguintes variáveis se a ação de erro for definida como
[[yii\web\ErrorAction]]:

* `name`: o nome do erro;
* `message`: a mensagem de erro;
* `exception`: o objeto de exceção através do qual você pode recuperar mais informações úteis, como o código de status HTTP, o código de erro, pilha de chamadas de erro, etc.

> Observação: Se você está utilizando o [template básico](start-installation.md) ou o [template avançado](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-pt-BR/README.md), a ação e a view de erro já estão definidas para você.


### Customizando o Formato da Resposta de Erro <span id="error-format"></span>

O manipulador de erro exibe erros de acordo com a definição do formato da [resposta](runtime-responses.md). Se o [[yii\web\Response::format|formato da resposta]] for `html`, ele usará a view de erro ou exceção para exibir os erros, como descrito na última subseção. Para outros formatos de resposta, o manipulador de erro irá atribuir o array de representação da exceção para a propriedade [[yii\web\Response::data]] que será então convertida para diferentes formatos de acordo com o que foi configurado. Por exemplo, se o formato de resposta for `json`, você pode visualizar a seguinte resposta:

```
HTTP/1.1 404 Not Found
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
   "name": "Not Found Exception",
   "message": "The requested resource was not found.",
   "code": 0,
   "status": 404
}
```

Você pode personalizar o formato de resposta de erro, respondendo ao evento `beforeSend` do componente `response` na configuração da aplicação:

```php
return [
   // ...
   'components' => [
       'response' => [
           'class' => 'yii\web\Response',
           'on beforeSend' => function ($event) {
               $response = $event->sender;
               if ($response->data !== null) {
                   $response->data = [
                       'success' => $response->isSuccessful,
                       'data' => $response->data,
                   ];
                   $response->statusCode = 200;
               }
           },
       ],
   ],
];
```

O código acima irá reformatar a resposta de erro como a seguir:

```
HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
   "success": false,
   "data": {
       "name": "Not Found Exception",
       "message": "The requested resource was not found.",
       "code": 0,
       "status": 404
   }
}
```

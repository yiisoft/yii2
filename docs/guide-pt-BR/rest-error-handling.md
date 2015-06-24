Manipulando Erros
==============

Ao manusear uma requisição da API RESTful, se existir um erro na requisição do usuário ou se alguma coisa inesperada acontecer no servidor, você pode simplesmente lançar uma exceção para notificar o usuário de que algo deu errado.
Se você puder identificar a causa do erro (ex., o recurso requisitado não existe), você deve considerar lançar uma exceção juntamente com um código de status HTTP adequado (ex., [[yii\web\NotFoundHttpException]] representa um código de status 404). Yii irá enviar a resposta juntamente com o código e texto do status HTTP correspondente. Yii também incluirá a representação serializada da exceção no corpo da resposta. Por exemplo:

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

A lista a seguir descrimina os códigos de status HTTP que são usados pelo REST framework Yii:

* `200`: OK. Tudo funcionou conforme o esperado.
* `201`: Um recurso foi criado com êxito em resposta a uma requisição `POST`. O cabeçalho `location` contém a URL que aponta para o recurso recém-criado.
* `204`: A requisição foi tratada com sucesso e a resposta não contém nenhum conteúdo no corpo (por exemplo uma requisição `DELETE`).
* `304`: O recurso não foi modificado. Você pode usar a versão em cache.
* `400`: Requisição malfeita. Isto pode ser causado por várias ações por parte do usuário, tais como o fornecimento de um JSON inválido no corpo da requisição, fornecendo parâmetros inválidos, etc.
* `401`: Falha de autenticação.
* `403`: O usuário autenticado não tem permissão para acessar o recurso da API solicitado.
* `404`: O recurso requisitado não existe.
* `405`: Método não permitido. Favor verificar o cabeçalho `Allow` para conhecer os métodos HTTP permitidos.
* `415`: Tipo de mídia não suportada. O número de versão ou tipo content type requisitado é inválido.
* `422`: Falha na validação dos dados (na resposta a uma requisição `POST`, por exemplo). Por favor, verifique o corpo da resposta para mensagens de erro detalhadas.
* `429`: Excesso de requisições. A requisição foi rejeitada devido a limitação de taxa.
* `500`: Erro interno do servidor. Isto pode ser causado por erros internos do programa.

## Customizando Resposta de Erro<span id="customizing-error-response"></span>

Às vezes você pode querer personalizar o formato de resposta de erro padrão. Por exemplo, em vez de confiar em usar diferentes status HTTP para indicar os diversos erros, você pode querer usar sempre o status 200 como resposta e colocar o código de status real como parte da estrutura JSON da resposta, como mostrado abaixo,

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

Para atingir este objetivo, você pode responder o evento `beforeSend` do componente `response` na configuração da aplicação:

```php
return [
   // ...
   'components' => [
       'response' => [
           'class' => 'yii\web\Response',
           'on beforeSend' => function ($event) {
               $response = $event->sender;
               if ($response->data !== null && Yii::$app->request->get('suppress_response_code')) {
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

O código acima formatará a resposta (para ambas as respostas, bem-sucedidas e com falha) como explicado quando `suppress_response_code` é passado como um parâmetro `GET`.



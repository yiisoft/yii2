Autenticação
==============

Ao contrário de aplicações Web, APIs RESTful são geralmente stateless, o que significa que as sessões ou os cookies não devem ser utilizados. Portanto, cada requisição deve vir com algum tipo de credencial de autenticação pois o estado de autenticação do usuário não pode ser mantido por sessões ou cookies. Uma prática comum é enviar um token de acesso secreto com cada solicitação para autenticar o usuário. Uma vez que um token de acesso pode ser utilizado para identificar de forma exclusiva e autenticar um usuário. **Solicitações de API devem sempre ser enviadas via HTTPS para evitar ataques man-in-the-middle (MitM)**.

Existem diferentes maneiras de enviar um token de acesso:

* [Autenticação Básica HTTP](https://en.wikipedia.org/wiki/Basic_access_authentication): o token de acesso é enviado como um nome de usuário. Isso só deve ser usado quando um token de acesso puder ser armazenado com segurança no lado do consumidor da API. Por exemplo, o consumidor API é um programa executado em um servidor.
* Parâmetro de consulta da URL: o token de acesso é enviado como um parâmetro de consulta na URL da API, ex., `https://example.com/users?access-token=xxxxxxxx`. Como a maioria dos servidores Web manterão os parâmetros de consulta nos logs do servidor, esta abordagem deve ser utilizada principalmente para servir requisições `JSONP` que não pode usar cabeçalhos HTTP para enviar tokens de acesso.
* [OAuth 2](https://oauth.net/2/): o token de acesso é obtido pelo consumidor a partir de um servidor de autorização e enviado para o servidor da API via [HTTP Bearer Tokens] (https://datatracker.ietf.org/doc/html/rfc6750),  de acordo com o protocolo OAuth2.

Yii suporta todos os métodos de autenticação descritos acima. Você também pode criar facilmente um novo método de autenticação.

Para ativar a autenticação nas suas APIs, siga os seguintes passos:

1. Configure o [componente de aplicação](structure-application-components.md) `user`:
  - Defina a propriedade [[yii\web\User::enableSession|enableSession]] como `false`.
  - Defina a propriedade [[yii\web\User::loginUrl|loginUrl]] como `null` para mostrar o erro HTTP 403 em vez de redirecionar para a página de login. 
2. Especificar quais métodos de autenticação você planeja usar configurando o behavior `authenticator` na sua classe controller REST.
3. Implemente [[yii\web\IdentityInterface::findIdentityByAccessToken()]] na sua [[yii\web\User::identityClass|classe de identidade do usuário]].

Passo 1 não é obrigatório, mas é recomendado para APIs RESTful stateless. Quando [[yii\web\User::enableSession|enableSession]] está marcado como falso, o status de autenticação de usuário NÃO será mantido entre as requisições usando sessões. Em lugar disso, autenticação será realizada para cada requisição, que é realizado no passo 2 e 3.

> Dica: Você pode configurar [[yii\web\User::enableSession|enableSession]] do componente `user`
> nas configurações da aplicação se você estiver desenvolvendo APIs RESTful para sua aplicação. Se você desenvolver
> APIs RESTful como um módulo, você pode colocar a seguinte linha no método `init()` do módulo, conforme exemplo a seguir:
>
> ```php
> public function init()
> {
>     parent::init();
>     \Yii::$app->user->enableSession = false;
> }
> ```

Por exemplo, para usar autenticação HTTP básica, você pode configurar o behavior `authenticator` como o seguinte:

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
   $behaviors = parent::behaviors();
   $behaviors['authenticator'] = [
       'class' => HttpBasicAuth::class,
   ];
   return $behaviors;
}
```

Se você quiser dar suporte a todos os três métodos de autenticação explicado acima, você pode utilizar o `CompositeAuth` conforme mostrado a seguir:

```php
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

public function behaviors()
{
   $behaviors = parent::behaviors();
   $behaviors['authenticator'] = [
       'class' => CompositeAuth::class,
       'authMethods' => [
           HttpBasicAuth::class,
           HttpBearerAuth::class,
           QueryParamAuth::class,
       ],
   ];
   return $behaviors;
}
```

Cada elemento em `authMethods` deve ser o nome de uma classe de método de autenticação ou um array de configuração.


Implementação de `findIdentityByAccessToken()` é específico por aplicação. Por exemplo, em cenários simples quando cada usuário só pode ter um token de acesso, você pode armazenar o token de acesso em uma coluna `access_token` na tabela `user`. O método pode então ser facilmente implementado na classe `User` como o seguinte:

```php
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
   public static function findIdentityByAccessToken($token, $type = null)
   {
       return static::findOne(['access_token' => $token]);
   }
}
```

Após a autenticação ser ativada, conforme descrito acima, para todas as requisições da API, o controller requisitado irá tentar autenticar o usuário no passo `beforeAction()`.

Se a autenticação retornar com sucesso, o controller irá executar outras verificações (tais como limitação de taxa, autorização) e então executará a ação. As informações de identidade do usuário autenticado podem ser recuperadas através de `Yii::$app->user->identity`.

Se a autenticação falhar, uma resposta  HTTP com status 401 será enviado de volta junto com outros cabeçalhos apropriados (tal como um `WWW-Authenticate` cabeçalho HTTP para Autenticação Básica).


## Autorização <span id="authorization"></span>

Após um usuário se autenticar, você provavelmente vai querer verificar se ele ou ela tem a permissão para executar a ação do recurso solicitado. Este processo é chamado de *autorização* que é tratada em pormenor na seção de [Autorização](security-authorization.md).

Se o seu controller estende de [[yii\rest\ActiveController]], você pode sobrescrever o método [[yii\rest\Controller::checkAccess()|checkAccess()]] para executar a verificação de autorização. O método será chamado pelas ações incorporadas fornecidas pelo [[yii\rest\ActiveController]].


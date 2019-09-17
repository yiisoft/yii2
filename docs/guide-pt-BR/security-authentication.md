Autenticação
==============

Autenticação é o processo de verificação da identidade do usuário. Geralmente é usado um identificador (ex. um nome de usuário ou endereço de e-mail) e um token secreto (ex. uma senha ou um token de acesso) para determinar se o usuário é quem ele diz ser. Autenticação é a base do recurso de login.

O Yii fornece um framework de autenticação com vários componentes que dão suporte ao login. Para usar este framework, você precisará primeiramente fazer o seguinte:

* Configurar o componente [[yii\web\User|user]] da aplicação;
* Criar uma classe que implementa a interface [[yii\web\IdentityInterface]].


## Configurando o [[yii\web\User]] <span id="configuring-user"></span>

O componente [[yii\web\User|user]] da aplicação gerencia o status de autenticação dos usuários. Ele requer que você especifique uma [[yii\web\User::identityClass|classe de identidade]] que contém a atual lógia de autenticação.
Na cofiguração abaixo, a [[yii\web\User::identityClass|classe de identidade]] do
[[yii\web\User|user]] é configurada para ser `app\models\User` cuja implementação é explicada na próxima subseção:

```php
return [
    'components' => [
        'user' => [
            'identityClass' => 'app\models\User',
        ],
    ],
];
```


## Implementação do [[yii\web\IdentityInterface]] <span id="implementing-identity"></span>

A [[yii\web\User::identityClass|classe de identidade]] deve implementar a interface [[yii\web\IdentityInterface]] que contém os seguintes métodos:

* [[yii\web\IdentityInterface::findIdentity()|findIdentity()]]: ele procura por uma instância da classe de identidade usando o ID do usuário especificado. Este método é usado quando você precisa manter o status de login via sessão.
* [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]]: ele procura por uma instância da classe de identidade usando o token de acesso informado. Este método é usado quando você precisa autenticar um usuário por um único token secreto (por exemplo, em uma aplicação stateless RESTful).
* [[yii\web\IdentityInterface::getId()|getId()]]: Retorna o ID do usuário representado por essa instância da classe de identidade.
* [[yii\web\IdentityInterface::getAuthKey()|getAuthKey()]]: retorna uma chave para verificar o login via cookie. A chave é mantida no cookie do login e será comparada com o informação do lado servidor para atestar a validade do cookie.
* [[yii\web\IdentityInterface::validateAuthKey()|validateAuthKey()]]: Implementa a lógica de verificação da chave de login via cookie.

Se um método em particular não for necessário, você pode implementá-lo com um corpo vazio. Por exemplo, se a sua aplicação é somente stateless RESTful, você só precisa implementar [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]]
e [[yii\web\IdentityInterface::getId()|getId()]] deixando todos os outros métodos com um corpo vazio.

No exemplo a seguir, uma [[yii\web\User::identityClass|classe de identidade]] é implementado como uma classe [Active Record](db-active-record.md) associada com a tabela `user` do banco de dados.

```php
<?php

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'user';
    }

    /**
     * Localiza uma identidade pelo ID informado
     *
     * @param string|int $id o ID a ser localizado
     * @return IdentityInterface|null o objeto da identidade que corresponde ao ID informado
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Localiza uma identidade pelo token informado
     *
     * @param string $token o token a ser localizado
     * @return IdentityInterface|null o objeto da identidade que corresponde ao token informado
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string o ID do usuário atual
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string a chave de autenticação do usuário atual
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param string $authKey
     * @return bool se a chave de autenticação do usuário atual for válida
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
}
```

Como explicado anteriormente, você só precisa implementar `getAuthKey()` e `validateAuthKey()` se a sua aplicação usa recurso de login via cookie. Neste caso, você pode utilizar o seguinte código para gerar uma chave de autenticação para cada usuário
e gravá-la na tabela `user`:

```php
class User extends ActiveRecord implements IdentityInterface
{
    ......

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->auth_key = \Yii::$app->security->generateRandomString();
            }
            return true;
        }
        return false;
    }
}
```

> Observação: Não confunda a classe de identidade `User` com [[yii\web\User]]. O primeiro é a classe que implementa a lógica de autenticação. Muitas vezes, é implementado como uma classe  [Active Record](db-active-record.md) associado com algum tipo de armazenamento persistente para armazenar as informações de credenciais do usuário. O último é um componente da aplicação responsável pela gestão do estado da autenticação do usuário.


## Usando o [[yii\web\User]] <span id="using-user"></span>

Você usa o [[yii\web\User]] principalmente como um componente `user` da aplicação.

É possível detectar a identidade do usuário atual utilizando a expressão `Yii::$app->user->identity`. Ele retorna uma instância da [[yii\web\User::identityClass|classe de identidade]] representando o atual usuário logado, ou `null` se o usuário corrente não estiver autenticado (acessando como convidado). O código a seguir mostra como recuperar outras informações relacionadas à autenticação de [[yii\web\User]]:

```php
// identidade do usuário atual. Null se o usuário não estiver autenticado.
$identity = Yii::$app->user->identity;

// o ID do usuário atual. Null se o usuário não estiver autenticado.
$id = Yii::$app->user->id;

// se o usuário atual é um convidado (não autenticado)
$isGuest = Yii::$app->user->isGuest;
```

Para logar um usuário, você pode usar o seguinte código:

```php
// encontrar uma identidade de usuário com o nome de usuário especificado.
// observe que você pode querer checar a senha se necessário
$identity = User::findOne(['username' => $username]);

// logar o usuário
Yii::$app->user->login($identity);
```

O método [[yii\web\User::login()]] define a identidade do usuário atual para o [[yii\web\User]]. Se a sessão estiver [[yii\web\User::enableSession|habilitada]], ele vai manter a identidade na sessão para que o status de autenticação do usuário seja mantido durante toda a sessão. Se o login via cookie (ex. login "remember me") estiver [[yii\web\User::enableAutoLogin|habilitada]], ele também guardará a identidade em um cookie para que o estado de autenticação do usuário possa ser recuperado a partir do cookie enquanto o cookie permanece válido.

A fim de permitir login via cookie, você pode configurar [[yii\web\User::enableAutoLogin]] como `true` na configuração da aplicação. Você também precisará fornecer um parâmetro de tempo de duração quando chamar o método [[yii\web\User::login()]].

Para realizar o logout de um usuário, simplesmente chame:

```php
Yii::$app->user->logout();
```

Observe que o logout de um usuário só tem sentido quando a sessão está habilitada. O método irá limpar o status de autenticação do usuário na memória e na sessão. E por padrão, ele também destruirá *todos* os dados da sessão do usuário. Se você quiser guardar os dados da sessão, você deve chamar `Yii::$app->user->logout(false)`.


## Eventos de Autenticação <span id="auth-events"></span>

A classe [[yii\web\User]] dispara alguns eventos durante os processos de login e logout:

* [[yii\web\User::EVENT_BEFORE_LOGIN|EVENT_BEFORE_LOGIN]]: disparado no início de [[yii\web\User::login()]].
  Se o manipulador de evento define a propriedade [[yii\web\UserEvent::isValid|isValid]] do objeto de evento para `false`, o processo de login será cancelado.
* [[yii\web\User::EVENT_AFTER_LOGIN|EVENT_AFTER_LOGIN]]: dispara após de um login com sucesso.
* [[yii\web\User::EVENT_BEFORE_LOGOUT|EVENT_BEFORE_LOGOUT]]: dispara no início de [[yii\web\User::logout()]]. Se o manipulador de evento define a propriedade [[yii\web\UserEvent::isValid|isValid]] do objeto de evento para `false`, o processo de logout será cancelado.
* [[yii\web\User::EVENT_AFTER_LOGOUT|EVENT_AFTER_LOGOUT]]: dispara após um logout com sucesso.

Você pode responder a estes eventos implementando funcionalidades, tais como auditoria de login, estatísticas de usuários on-line. Por exemplo, no manipulador
[[yii\web\User::EVENT_AFTER_LOGIN|EVENT_AFTER_LOGIN]], você pode registrar o tempo de login e endereço IP na tabela `user`.




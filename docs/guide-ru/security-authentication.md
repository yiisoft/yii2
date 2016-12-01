Аутентификация
==============

Аутентификация — это процесс проверки подлинности пользователя. Обычно используется идентификатор
(например, `username` или адрес электронной почты) и секретный токен (например, пароль или ключ доступа), чтобы судить о
том, что пользователь именно тот, за кого себя выдаёт. Аутентификация является основной функцией формы входа.

Yii предоставляет фреймворк авторизации с различными компонентами, обеспечивающими процесс входа.
Для использования этого фреймворка вам нужно проделать следующее:

* Настроить компонент приложения [[yii\web\User|user]];
* Создать класс, реализующий интерфейс [[yii\web\IdentityInterface]].


## Настройка [[yii\web\User]] <span id="configuring-user"></span>

Компонент [[yii\web\User|user]] управляет статусом аутентификации пользователя.
Он требует, чтобы вы указали [[yii\web\User::identityClass|identity class]], который будет содержать
текущую логику аутентификации. В следующей конфигурации приложения, [[yii\web\User::identityClass|identity class]] для
[[yii\web\User|user]] задан как `app\models\User`, реализация которого будет объяснена в следующем разделе:

```php
return [
    'components' => [
        'user' => [
            'identityClass' => 'app\models\User',
        ],
    ],
];
```


## Реализация [[yii\web\IdentityInterface]] <span id="implementing-identity"></span>

[[yii\web\User::identityClass|identity class]] должен реализовывать [[yii\web\IdentityInterface]],
который содержит следующие методы:

* [[yii\web\IdentityInterface::findIdentity()|findIdentity()]]: Этот метод находит экземпляр `identity class`,
  используя ID пользователя. Этот метод используется, когда необходимо поддерживать состояние аутентификации через сессии.
* [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]]: Этот метод находит экземпляр `identity class`,
  используя токен доступа. Метод используется, когда требуется аутентифицировать пользователя
  только по секретному токену (например в RESTful приложениях, не сохраняющих состояние между запросами).
* [[yii\web\IdentityInterface::getId()|getId()]]: Этот метод возвращает ID пользователя, представленного данным экземпляром `identity`.
* [[yii\web\IdentityInterface::getAuthKey()|getAuthKey()]]: Этот метод возвращает ключ, используемый для основанной на `cookie` аутентификации.
  Ключ сохраняется в аутентификационной cookie и позже сравнивается с версией, находящейся на сервере,
  чтобы удостоверится, что аутентификационная `cookie` верная.
* [[yii\web\IdentityInterface::validateAuthKey()|validateAuthKey()]]: Этот метод реализует логику проверки ключа
  для основанной на `cookie` аутентификации.

Если какой-то из методов не требуется, то можно реализовать его с пустым телом. Для примера,
если у вас RESTful приложение, не сохраняющее состояние между запросами, вы можете реализовать только
[[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]]
и [[yii\web\IdentityInterface::getId()|getId()]], тогда как остальные методы оставить пустыми.

В следующем примере, [[yii\web\User::identityClass|identity class]] реализован
как класс [Active Record](db-active-record.md), связанный с таблицей `user`.

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
     * Finds an identity by the given ID.
     *
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Finds an identity by the given token.
     *
     * @param string $token the token to be looked for
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string current user ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string current user auth key
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param string $authKey
     * @return bool if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
}
```

Как объяснялось ранее, вам нужно реализовать только `getAuthKey()` и `validateAuthKey()`, если ваше приложение использует
только аутентификацию основанную на `cookie`. В этом случае вы можете использовать следующий код для генерации
ключа аутентификации для каждого пользователя и хранения его в таблице `user`:

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

> Note: Не путайте `identity` класс `User` с классом [[yii\web\User]]. Первый является классом, реализующим
  логику аутентификации пользователя. Он часто реализуется как класс [Active Record](db-active-record.md), связанный
  с некоторым постоянным хранилищем, где лежит информация о пользователях. Второй — это класс компонента приложения,
  отвечающий за управление состоянием аутентификации пользователя.


## Использование [[yii\web\User]] <span id="using-user"></span>

В основном класс [[yii\web\User]] используют как компонент приложения `user`.

Можно получить `identity` текущего пользователя, используя выражение `Yii::$app->user->identity`. Оно вернёт экземпляр
[[yii\web\User::identityClass|identity class]], представляющий текущего аутентифицированного пользователя,
или `null`, если текущий пользователь не аутентифицирован (например, гость). Следующий код показывает, как получить
другую связанную с аутентификацией информацию из [[yii\web\User]]:

```php
// `identity` текущего пользователя. `Null`, если пользователь не аутентифицирован.
$identity = Yii::$app->user->identity;

// ID текущего пользователя. `Null`, если пользователь не аутентифицирован.
$id = Yii::$app->user->id;

// проверка на то, что текущий пользователь гость (не аутентифицирован)
$isGuest = Yii::$app->user->isGuest;
```

Для залогинивания пользователя вы можете использовать следующий код:

```php
// найти identity с указанным username.
// замечание: также вы можете проверить и пароль, если это нужно
$identity = User::findOne(['username' => $username]);

// логиним пользователя
Yii::$app->user->login($identity);
```

Метод [[yii\web\User::login()]] устанавливает `identity` текущего пользователя в [[yii\web\User]]. Если сессии
[[yii\web\User::enableSession|включены]], то `identity` будет сохраняться в сессии, так что состояние
статуса аутентификации будет поддерживаться на всём протяжении сессии. Если [[yii\web\User::enableAutoLogin|включен]] вход, основанный на cookie (так называемый "запомни меня" вход), то `identity` также будет сохранена
в `cookie` так, чтобы статус аутентификации пользователя мог быть восстановлен на протяжении всего времени жизни `cookie`.

Для включения входа, основанного на `cookie`, вам нужно установить [[yii\web\User::enableAutoLogin]] в `true`
в конфигурации приложения. Вы также можете настроить время жизни, передав его при вызове метода [[yii\web\User::login()]].

Для выхода пользователя, просто вызовите

```php
Yii::$app->user->logout();
```

Обратите внимание: выход пользователя имеет смысл только если сессии включены. Метод сбрасывает статус аутентификации
сразу и из памяти и из сессии. И по умолчанию, будут также уничтожены *все* сессионные данные пользователя.
Если вы хотите сохранить сессионные данные, вы должны вместо этого вызвать `Yii::$app->user->logout(false)`.


## События аутентификации <span id="auth-events"></span>

Класс [[yii\web\User]] вызывает несколько событий во время процессов входа и выхода.

* [[yii\web\User::EVENT_BEFORE_LOGIN|EVENT_BEFORE_LOGIN]]: вызывается перед вызовом [[yii\web\User::login()]].
  Если обработчик устанавливает свойство [[yii\web\UserEvent::isValid|isValid]] объекта в `false`,
  процесс входа будет прерван.
* [[yii\web\User::EVENT_AFTER_LOGIN|EVENT_AFTER_LOGIN]]: вызывается после успешного входа.
* [[yii\web\User::EVENT_BEFORE_LOGOUT|EVENT_BEFORE_LOGOUT]]: вызывается перед вызовом [[yii\web\User::logout()]].
  Если обработчик устанавливает свойство [[yii\web\UserEvent::isValid|isValid]] объекта в `false`,
  процесс выхода будет прерван.
* [[yii\web\User::EVENT_AFTER_LOGOUT|EVENT_AFTER_LOGOUT]]: вызывается после успешного выхода.

Вы можете использовать эти события для реализации функции аудита входа, сбора статистики онлайн пользователей. Например,
в обработчике для [[yii\web\User::EVENT_AFTER_LOGIN|EVENT_AFTER_LOGIN]] вы можете сделать запись о времени и IP
адресе входа в таблицу `user`.

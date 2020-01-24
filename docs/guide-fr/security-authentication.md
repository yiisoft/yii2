Authentification
================

L'authentification est le processus qui consiste à vérifier l'identité d'un utilisateur. Elle utilise ordinairement un identifiant (p. ex. un nom d'utilisateur ou une adresse de courriels) et un jeton secret (p. ex. un mot de passe ou un jeton d'accès) pour juger si l'utilisateur est bien qui il prétend être. L'authentification est à la base de la fonctionnalité de connexion.

Yii fournit une base structurée d'authentification  qui interconnecte des composants variés pour prendre en charge la connexion. Pour utiliser cette base structurée, vous devez essentiellement accomplir les tâches suivantes :

* Configurer le composant d'application [[yii\web\User|user]] ;
* Créer une classe qui implémente l'interface [[yii\web\IdentityInterface]].


## Configuration de  [[yii\web\User]] <span id="configuring-user"></span>

Le composant d'application [[yii\web\User|user]] gère l'état d'authentification de l'utilisateur. Il requiert que vous spécifiiez une [[yii\web\User::identityClass|classe d'identité]] contenant la logique réelle d'authentification.
Dans la configuration suivante de l'application, la [[yii\web\User::identityClass|classe d'identité]] pour [[yii\web\User|user]] est configurée sous le nom `app\models\User` dont la mise en œuvre est expliquée dans la sous-section suivante :

```php
return [
    'components' => [
        'user' => [
            'identityClass' => 'app\models\User',
        ],
    ],
];
```


## Mise en œuvre de [[yii\web\IdentityInterface]] <span id="implementing-identity"></span>

La  [[yii\web\User::identityClass|classe d'identité]] doit implémenter l'interface [[yii\web\IdentityInterface]] qui comprend les méthodes suivantes :

* [[yii\web\IdentityInterface::findIdentity()|findIdentity()]]: cette méthode recherche une instance de la classe d'identité à partir de l'identifiant utilisateur spécifié. Elle est utilisée lorsque vous devez conserver l'état de connexion via et durant la session.
* [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]]: cette méthode recherche une instance de la classe d'identité à partir du jeton d'accès spécifié. Elle est utilisée lorsque vous avez besoin d'authentifier un utilisateur par un jeton secret (p. ex. dans une application pleinement REST sans état).
* [[yii\web\IdentityInterface::getId()|getId()]]: cette méthode retourne l'identifiant de l'utilisateur que cette instance de la classe d'identité représente.
* [[yii\web\IdentityInterface::getAuthKey()|getAuthKey()]]: cette méthode retourne une clé utilisée pour vérifier la connexion basée sur les témoins de connexion (*cookies*). La clé est stockée dans le témoin de connexion `login` et est ensuite comparée avec la version côté serveur pour s'assurer que le témoin de connexion est valide.
* [[yii\web\IdentityInterface::validateAuthKey()|validateAuthKey()]]: cette méthode met en œuvre la logique de vérification de la clé de connexion basée sur les témoins de connexion.

Si une méthode particulière n'est pas nécessaire, vous devez l'implémenter avec un corps vide. Par exemple, si votre application est une application sans état et pleinement REST, vous devez seulement implémenter [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]] et [[yii\web\IdentityInterface::getId()|getId()]] et laisser toutes les autres méthodes avec un corps vide.

Dans l'exemple qui suit, une [[yii\web\User::identityClass|classe d'identité]] est mise en œuvre en tant que classe [Active Record](db-active-record.md) associée à la table de base de données  `user`.

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
     * Trouve une identité à partir de l'identifiant donné.
     *
     * @param string|int $id l'identifiant à rechercher
     * @return IdentityInterface|null l'objet identité qui correspond à l'identifiant donné
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Trouve une identité à partir du jeton donné
     *
     * @param string $token le jeton à rechercher
     * @return IdentityInterface|null l'objet identité qui correspond au jeton donné
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string l'identifiant de l'utilisateur courant
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string la clé d'authentification de l'utilisateur courant
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param string $authKey
     * @return bool si la clé d'authentification est valide pour l'utilisateur courant
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
}
```

Comme nous l'avons expliqué précédemment, vous devez seulement implémenter `getAuthKey()` et `validateAuthKey()` si votre application utilise la fonctionnalité de connexion basée sur les témoins de connexion. Dans ce cas, vous devez utiliser le code suivant pour générer une clé d'authentification pour chacun des utilisateurs et la stocker dans la table `user` :

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

> Note: ne confondez pas la classe d'identité `User` avec la classe [[yii\web\User]]. La première est la classe mettant en œuvre la logique d'authentification. Elle est souvent mise en œuvre sous forme de classe [Active Record](db-active-record.md) associée à un moyen de stockage persistant pour conserver les éléments d'authentification de l'utilisateur. La deuxième est une classe de composant d'application qui gère l'état d'authentification de l'utilisateur.


## Utilisation de [[yii\web\User]] <span id="using-user"></span>

Vous utilisez  [[yii\web\User]] essentiellement en terme de composant d'application `user`.

Vous pouvez détecter l'identité de l'utilisateur courant en utilisant l'expression `Yii::$app->user->identity`. Elle retourne une instance de la [[yii\web\User::identityClass|classe d'identité]] représentant l'utilisateur connecté actuellement ou `null` si l'utilisateur courant n'est pas authentifié (soit un simple visiteur). Le code suivant montre comment retrouver les autres informations relatives à l'authentification à partir de [[yii\web\User]]:

```php
// l'identité de l'utilisateur courant. Null si l'utilisateur n'est pas authentifié.
$identity = Yii::$app->user->identity;

// l'identifiant de l'utilisateur courant. Null si l'utilisateur n'est pas authentifié.
$id = Yii::$app->user->id;

// si l'utilisateur courant est un visiteur (non authentifié).
$isGuest = Yii::$app->user->isGuest;
```

Pour connecter un utilisateur, vous devez utiliser le code suivant :

```php
// trouve une identité d'utilisateur à partir du nom d'utilisateur spécifié
// notez que vous pouvez vouloir vérifier le mot de passe si besoin.
$identity = User::findOne(['username' => $username]);

// connecte l'utilisateur
Yii::$app->user->login($identity);
```

La méthode  [[yii\web\User::login()]] assigne l'identité de l'utilisateur courant à [[yii\web\User]]. Si la session est [[yii\web\User::enableSession|activée]], elle conserve l'identité de façon à ce que l'état d'authentification de l'utilisateur soit maintenu durant la session tout entière. Si la connexion basée sur les témoins de connexion (*cookies*) est [[yii\web\User::enableAutoLogin|activée]], elle sauvegarde également l'identité dans un témoin de connexion de façon à ce que l'état d'authentification de l'utilisateur puisse être récupéré du témoin de connexion durant toute la période de validité du témoin de connexion.

Pour activer la connexion basée sur les témoins de connexion, vous devez configurer [[yii\web\User::enableAutoLogin]] à `true` (vrai) dans la configuration de l'application. Vous devez également fournir une durée de vie lorsque vous appelez la méthode [[yii\web\User::login()]].

Pour déconnecter un utilisateur, appelez simplement

```php
Yii::$app->user->logout();
```

Notez que déconnecter un utilisateur n'a de sens que si la session est activée. La méthode nettoie l'état d'authentification de l'utilisateur à la fois de la mémoire et de la session. Et, par défaut, elle détruit aussi *toutes* les données de session. Si vous voulez conserver les données de session, vous devez appeler  `Yii::$app->user->logout(false)`, à la place.


## Événement d'authentification <span id="auth-events"></span>

La classe [[yii\web\User]] lève quelques événements durant le processus de connexion et celui de déconnexion.

* [[yii\web\User::EVENT_BEFORE_LOGIN|EVENT_BEFORE_LOGIN]]: levé au début de [[yii\web\User::login()]].
  Si le gestionnaire d'événement définit la propriété  [[yii\web\UserEvent::isValid|isValid]] de l'objet événement à `false` (faux), le processus de connexion avorte.
* [[yii\web\User::EVENT_AFTER_LOGIN|EVENT_AFTER_LOGIN]]: levé après une connexion réussie.
* [[yii\web\User::EVENT_BEFORE_LOGOUT|EVENT_BEFORE_LOGOUT]]: levé au début de [[yii\web\User::logout()]].
  Si le gestionnaire d'événement définit la propriété [[yii\web\UserEvent::isValid|isValid]] à `false` (faux) le processus de déconnexion avorte.
* [[yii\web\User::EVENT_AFTER_LOGOUT|EVENT_AFTER_LOGOUT]]: levé après une déconnexion réussie.

Vous pouvez répondre à ces événements pour mettre en œuvre des fonctionnalités telles que l'audit de connexion, les statistiques d'utilisateurs en ligne. Par exemple, dans le gestionnaire pour l'événement [[yii\web\User::EVENT_AFTER_LOGIN|EVENT_AFTER_LOGIN]], vous pouvez enregistrer le temps de connexion et l'adresse IP dans la tale `user`.

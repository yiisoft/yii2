AuthClient Extension for Yii 2
==============================

This extension adds [OpenID](http://openid.net/), [OAuth](http://oauth.net/) and [OAuth2](http://oauth.net/2/) consumers for the Yii 2 framework.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require yiisoft/yii2-authclient "*"
```

or add

```json
"yiisoft/yii2-authclient": "*"
```

to the `require` section of your composer.json.


Usage & Documentation
---------------------

This extension provides the ability of the authentication via external credentials providers.
It covers OpenID, OAuth1 and OAuth2 protocols.

You need to setup auth client collection application component:

```
'components' => [
    'authClientCollection' => [
        'class' => 'yii\authclient\Collection',
        'clients' => [
            'google' => [
                'class' => 'yii\authclient\clients\GoogleOpenId'
            ],
            'facebook' => [
                'class' => 'yii\authclient\clients\Facebook',
                'clientId' => 'facebook_client_id',
                'clientSecret' => 'facebook_client_secret',
            ],
        ],
    ]
    ...
]
```

Then you need to add [[yii\authclient\AuthAction]] to some of your web controllers:

```
class SiteController extends Controller
{
    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'successCallback'],
            ],
        ]
    }

    public function successCallback($client)
    {
        $attributes = $client->getUserAttributes();
        // user login or signup comes here
    }
}
```

You may use [[yii\authclient\widgets\Choice]] to compose auth client selection:

```
<?= yii\authclient\Choice::widget([
     'baseAuthUrl' => ['site/auth']
]) ?>
```

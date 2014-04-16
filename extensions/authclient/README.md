AuthClient Extension for Yii 2
==============================

This extension adds [OpenID](http://openid.net/), [OAuth](http://oauth.net/) and [OAuth2](http://oauth.net/2/) consumers for the Yii framework 2.0.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-authclient "*"
```

or add

```json
"yiisoft/yii2-authclient": "*"
```

to the `require` section of your composer.json.


Quick start
-----------

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

You may use [[yii\authclient\widgets\AuthChoice]] to compose auth client selection:

```
<?= yii\authclient\widgets\AuthChoice::widget([
     'baseAuthUrl' => ['site/auth']
]) ?>
```


Base auth clients overview
--------------------------

This extension provides the base client class for each supported auth protocols:
 - [[yii\authclient\OpenId]] supports [OpenID](http://openid.net/)
 - [[yii\authclient\OAuth1]] supports [OAuth 1/1.0a](http://oauth.net/)
 - [[yii\authclient\OAuth2]] supports [OAuth 2](http://oauth.net/2/)

You may use these classes as they are to use external auth provider, or extend them
in order to create some particular provider oriented client.

Please, refer to the particular base client class documentation for its actual usage.

Although, all clients are different they shares same basic interface [[yii\authclient\ClientInterface]],
which governs some common API.

Each client has some descriptive data, which can be used for different purposes:
 - id - unique client id, which separates it from other clients, it could be used in URLs, logs etc.
 - name - external auth provider name, which this client is match too. Different auth clients
 can share the same name, if they refer to the same external auth provider.
 For example: clients for Google OpenID and Google OAuth have same name "google".
 This attribute can be used inside the database, CSS styles and so on.
 - title - user friendly name for the external auth provider, it is used to present auth client
 at the view layer.

Each auth client has different auth flow, but all of them supports "getUserAttributes()" method,
which can be invoked if authentication was successful.
This method allows you to get information about external user account, such as id, email address,
full name, preferred language etc.
Defining list of attributes, which external auth provider should return, depends on client type:
 - [[yii\authclient\OpenId]]: combination of "requiredAttributes" and "optionalAttributes"
 - [[yii\authclient\OAuth1]] and [[yii\authclient\OAuth2]]: field "scope", note that different
 providers use different formats for the scope.

Each auth client has "viewOptions" attribute. It is an array, which stores name-value pairs,
which serve to compose client representation in the view.
For example widget [[yii\authclient\widgets\AuthChoice]] uses keys "popupWidth" and "popupHeight" to
determine the size of authentication popup window.


External API usage
------------------

Both [[yii\authclient\OAuth1]] and [[yii\authclient\OAuth2]] provide method "api()", which
can be used to access external auth provider REST API. However this method is very basic and
it may be not enough to access full external API functionality. This method is mainly used to
fetch the external user account data.
To use API calls, you need to setup [[yii\authclient\BaseOAuth::apiBaseUrl]] according to the
API specification. Then you can call [[yii\authclient\BaseOAuth::api()]] method:
```
use yii\authclient\OAuth2;

$client = new OAuth2;
...
$client->apiBaseUrl = 'https://www.googleapis.com/oauth2/v1';
$userInfo = $client->api('userinfo', 'GET');
```


Predefined auth clients
-----------------------

This extension provides the list of ready to use auth clients, which covers most
popular external authentication providers. These clients are located under "yii\authclient\clients"
namespace.

Following predefined auth clients are available:
 - [[yii\authclient\clients\Facebook]] - [Facebook](https://www.facebook.com/) OAuth2 client
 - [[yii\authclient\clients\GitHub]] - [GitHub](https://github.com/) OAuth2 client
 - [[yii\authclient\clients\GoogleOAuth]] - [Google](https://www.google.com/) OAuth2 client
 - [[yii\authclient\clients\GoogleOpenId]] - [Google](https://www.google.com/) OpenID client
 - [[yii\authclient\clients\LinkedIn]] - [LinkedIn](http://www.linkedin.com/) OAuth2 client
 - [[yii\authclient\clients\LinkedIn]] - [LinkedIn](http://www.linkedin.com/) OAuth2 client
 - [[yii\authclient\clients\Twitter]] - [Twitter](https://twitter.com/) OAuth1 client
 - [[yii\authclient\clients\YandexOAuth]] - [Yandex](http://www.yandex.ru/) OAuth2 client
 - [[yii\authclient\clients\YandexOpenId]] - [Yandex](http://www.yandex.ru/) OpenID client

Please, refer to the particular client class documentation for its actual usage.


Creating your own auth clients
------------------------------

You may create your own auth client for any external auth provider, which supports
OpenId or OAuth protocol. To do so, first of all, you need to find out which protocol is
supported by the external auth provider, this will give you the name of the base class
for your extension:
 - for OAuth 2 use [[yii\authclient\OAuth2]]
 - for OAuth 1/1.0a use [[yii\authclient\OAuth1]]
 - for OpenID use [[yii\authclient\OpenId]]

At this stage you can determine auth client default name, title and view options, declaring
corresponding methods:

```
use yii\authclient\OAuth2;

class MyAuthClient extends OAuth2
{
	protected function defaultName()
	{
		return 'my_auth_client';
	}

	protected function defaultTitle()
	{
		return 'My Auth Client';
	}

	protected function defaultViewOptions()
	{
		return [
			'popupWidth' => 800,
			'popupHeight' => 500,
		];
	}
}
```

Depending on actual base class, you will need to redeclare different fields and methods.

1) [[yii\authclient\OpenId]]

All you need is specify auth URL, by redeclaring "authUrl" field.
You may also setup default required and/or optional attributes.
For example:

```
use yii\authclient\OpenId;

class MyAuthClient extends OpenId
{
	public $authUrl = 'https://www.my.com/openid/';

	public $requiredAttributes = [
		'contact/email',
	];

	public $optionalAttributes = [
		'namePerson/first',
		'namePerson/last',
	];
}
```

2) [[yii\authclient\OAuth2]]

You will need to specify:
- authorize URL - redeclare "authUrl" field
- token request URL - redeclare "tokenUrl" field
- API base URL - redeclare "apiBaseUrl" field
- User attribute fetching strategy - redeclare "initUserAttributes" method

For example:

```
use yii\authclient\OAuth2;

class MyAuthClient extends OAuth2
{
	public $authUrl = 'https://www.my.com/oauth2/auth';

	public $tokenUrl = 'https://www.my.com/oauth2/token';

	public $apiBaseUrl = 'https://www.my.com/apis/oauth2/v1';

	protected function initUserAttributes()
	{
		return $this->api('userinfo', 'GET');
	}
}
```

You may also specify default auth scopes.

Note: some OAuth providers may not follow OAuth standards clearly, introducing
some differences, which may require additional efforts to apply.

3) [[yii\authclient\OAuth1]]

You will need to specify:
- authorize URL - redeclare "authUrl" field
- request token URL - redeclare "requestTokenUrl" field
- access token URL - redeclare "accessTokenUrl" field
- API base URL - redeclare "apiBaseUrl" field
- User attribute fetching strategy - redeclare "initUserAttributes" method

For example:

```
use yii\authclient\OAuth1;

class MyAuthClient extends OAuth1
{
	public $authUrl = 'https://www.my.com/oauth/auth';

	public $requestTokenUrl = 'https://www.my.com/oauth/request_token';

	public $accessTokenUrl = 'https://www.my.com/oauth/access_token';

	public $apiBaseUrl = 'https://www.my.com/apis/oauth/v1';

	protected function initUserAttributes()
	{
		return $this->api('userinfo', 'GET');
	}
}
```

You may also specify default auth scopes.

Note: some OAuth providers may not follow OAuth standards clearly, introducing
some differences, which may require additional efforts to apply.

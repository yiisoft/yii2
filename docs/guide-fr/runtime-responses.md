Réponses
=========

Quand une application a terminé la prise en charge d'une [requête](runtime-requests.md), elle génère un objet [[yii\web\Response|response]] et l'envoie à l'utilisateur final. L'objet `response` contient des informations telles que le code d'état HTTP, les entêtes HTTP et le corps. Le but ultime du développement d'applications Web est essentiellement du construire de tels objets `response` pour des requêtes variées. 

Dans la plupart des cas, vous devez travailler avec le [composant d'application](structure-application-components.md) `response` qui, par défaut, est une instance de [[yii\web\Response]]. Néanmoins, Yii vous permet également de créer vos propres objets `response` et de les envoyer à l'utilisateur final comme nous l'exliquons dans ce qui suit.

Dans cette section, nous décrivons comment composer et enovoyer des réponses à l'utilisateur final. 


## Code d'état <span id="status-code"></span>

Une de première chose que vous devez faire lorsque vous construisez une réponse est de déclarer si la requête a été correctement prise en charge ou pas. Cela se fait en définissant la propriété  [[yii\web\Response::statusCode (code d'état)]] qui peut prendre un des [code d'état HTTP](https://tools.ietf.org/html/rfc2616#section-10) valides. Par exemple, pour indiquer que la requête a été prise en charge avec succès, vous pouvez définir le code à 200, comme ceci :

```php
Yii::$app->response->statusCode = 200;
```

Néanmoins, dans la plupart des cas, vous n'avez pas besoin de définir ce code explicitement. Cela tient au fait que la valeur par défaut de [[yii\web\Response::statusCode]] est 200. Et, si vous voulez indiquer que la prise en charge de la requête a échoué vous pouvez lever une exception appropriée comme ceci :

```php
throw new \yii\web\NotFoundHttpException;
```

Lorsque le  [gestionnaire d'erreurs](runtime-handling-errors.md) intercepte l'exception, il extraie le code d'état de l'exception et l'assigne à la réponse. Concernant l'exception [[yii\web\NotFoundHttpException]] ci-dessus, elle est associée au code d'état HTTP 404. Les exception HTTP suivantes sont prédéfinies dans Yii :

* [[yii\web\BadRequestHttpException]]: code d'état 400.
* [[yii\web\ConflictHttpException]]: code d'état 409.
* [[yii\web\ForbiddenHttpException]]: code d'état 403.
* [[yii\web\GoneHttpException]]: code d'état 410.
* [[yii\web\MethodNotAllowedHttpException]]: code d'état 405.
* [[yii\web\NotAcceptableHttpException]]: code d'état 406. 
* [[yii\web\NotFoundHttpException]]: code d'état 404.
* [[yii\web\ServerErrorHttpException]]: code d'état 500.
* [[yii\web\TooManyRequestsHttpException]]: code d'état 429.
* [[yii\web\UnauthorizedHttpException]]: code d'état 401.
* [[yii\web\UnsupportedMediaTypeHttpException]]: code d'état 415.

Si l'exception que vous voulez lever ne fait pas partie de cette liste, vous pouvez en créer une en étendant la classe [[yii\web\HttpException]], ou en en levant une à laquelle vous passez directement le code d'état. Par exemple :
 
```php
throw new \yii\web\HttpException(402);
```


## Entêtes HTTP  <span id="http-headers"></span> 

Vous pouvez envoyer les entêtes HTTP en manipulant la [[yii\web\Response::headers|collection d'entêtes]] dans le composant `response`. Par exemple :

```php
$headers = Yii::$app->response->headers;

// ajoute un entête  Pragma . L'entête Pragma existant n'est PAS écrasé.
$headers->add('Pragma', 'no-cache');

// définit un entête Pragma. Tout entête Pragma existant est supprimé.
$headers->set('Pragma', 'no-cache');

// retire un (des) entêtes Pragma et retourne les valeurs de l'entête Pragma retiré dans un tableau
$values = $headers->remove('Pragma');
```

> Info: les noms d'entête ne sont pas sensibles à la casse. Les nouveaux entêtes enregistrés ne sont pas envoyés à l'utilisateur tant que la méthode [[yii\web\Response::send()]] n'est pas appelée.


## Corps de la réponse <span id="response-body"></span>

La plupart des réponses doivent avoir un corps qui transporte le contenu que vous voulez montrer à l'utilisateur final. 

Si vous disposez déjà d'une chaîne de caractères formatée pour le corps, vous pouvez l'assigner à la propriété [[yii\web\Response::content]] de la réponse. Par exemple :

```php
Yii::$app->response->content = 'hello world!';
```

Si vos données doivent être formatées avant l'envoi à l'utilisateur final, vous devez définir les propriétés [[yii\web\Response::format|format]] et [[yii\web\Response::data|data]]. La propriété [[yii\web\Response::format|format]] spécifie dans quel format les  [[yii\web\Response::data|données]] doivent être formatées. Par exemple :

```php
$response = Yii::$app->response;
$response->format = \yii\web\Response::FORMAT_JSON;
$response->data = ['message' => 'hello world'];
```

De base, Yii prend en charge les formats suivants, chacun mis en œuvre par une classe [[yii\web\ResponseFormatterInterface|formatter]]. Vous pouvez personnaliser les formateurs ou en ajouter de nouveaux en configurant la propriété [[yii\web\Response::formatters]].

* [[yii\web\Response::FORMAT_HTML|HTML]]: mise en œuvre par [[yii\web\HtmlResponseFormatter]].
* [[yii\web\Response::FORMAT_XML|XML]]: mise en œuvre par [[yii\web\XmlResponseFormatter]].
* [[yii\web\Response::FORMAT_JSON|JSON]]: mise en œuvre par [[yii\web\JsonResponseFormatter]].
* [[yii\web\Response::FORMAT_JSONP|JSONP]]: mise en œuvre par [[yii\web\JsonResponseFormatter]].
* [[yii\web\Response::FORMAT_RAW|RAW]]: utilisez ce format si vous voulez envoyer la réponse directement sans lui appliquer aucun formatage. 

Bien que le corps de la réponse puisse être défini explicitement comme montré ci-dessus, dans la plupart des cas, vous pouvez le définir implicitement en utilisant la valeur retournée par les méthodes d'[action](structure-controllers.md). Un cas d'usage courant ressemble à ceci :
 
```php
public function actionIndex()
{
    return $this->render('index');
}
```

L'action `index` ci-dessus retourne le résultat du rendu de la vue `index`. La valeur de retour est interceptée par le composant  `response`, formatée et envoyée à l'utilisateur final.

Parce que le format par défaut de la réponse est [[yii\web\Response::FORMAT_HTML|HTML]], vous devez seulement retourner un chaîne de caractères dans une méthode d'action. Si vous utilisez un format de réponse différent, vous devez le définir avant de retourner les donnés. Par exemple :

```php
public function actionInfo()
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return [
        'message' => 'hello world',
        'code' => 100,
    ];
}
```

Comme mentionné plus haut, en plus d'utiliser le composant d'application `response`, vous pouvez également créer vos propres objets `response` et les envoyer à l'utilisateur final. Vous pouvez faire cela en retournant un tel objet dans une méthode d'action, comme le montre l'exemple suivant :

```php
public function actionInfo()
{
    return \Yii::createObject([
        'class' => 'yii\web\Response',
        'format' => \yii\web\Response::FORMAT_JSON,
        'data' => [
            'message' => 'hello world',
            'code' => 100,
        ],
    ]);
}
```

> Note : si vous êtes en train de créer vos propres objets `response`, vous ne pourrez par bénéficier des configurations que vous avez établies pour le composant `response` dans la configuration de l'application. Vous pouvez néanmoins, utiliser l'[injection de dépendances](concept-di-container.md) pour appliquer une configuration commune à vos nouveaux objets `response`. 


## Redirection du navigateur <span id="browser-redirection"></span>

La redirection du navigateur s'appuie sur l'envoi d'un entête HTTP `Location`. Comme cette fonctionnalité est couramment utilisée, Yii fournit une prise en charge spéciale pour cela.

Vous pouvez rediriger le navigateur sur une URL en appelant la méthode [[yii\web\Response::redirect()]]. Cette méthode définit l'entête `Location` approprié avec l'URL donnée et retourne l'objet `response` lui-même. Dans une méthode d'action vous pouvez appeler sa version abrégée [[yii\web\Controller::redirect()]]. Par exemple :

```php
public function actionOld()
{
    return $this->redirect('http://example.com/new', 301);
}
```

Dans le code précédent, la méthode d'action retourne le résultat de la méthode `redirect()`. Comme expliqué ci-dessus, l'objet `response` retourné par une méthode d'action est utilisé en tant que réponse à envoyer à l'utilisateur final.

Dans des endroits autres que les méthodes d'action, vous devez appeler la méthode [[yii\web\Response::redirect()]] directement, suivi d'un appel chaîné à la méthode [[yii\web\Response::send()]] pour garantir qu'aucun contenu supplémentaire ne sera ajouté à la réponse. 

```php
\Yii::$app->response->redirect('http://example.com/new', 301)->send();
```

> Info: par défaut la méthode [[yii\web\Response::redirect()]] définit le code d'état à 302 pour indiquer au navigateur que la ressource requise est *temporairement* située sous un URI différent. Vous pouvez passer un code 301 pour dire au navigateur que la ressource a été déplacée *de manière permanente*.

Lorsque la requête courante est une requête AJAX, l'envoi d'un entête `Location` ne provoque pas automatiquement une redirection du navigateur. Pour pallier ce problème, la méthode [[yii\web\Response::redirect()]] définit un entête  `X-Redirect` avec l'URL de redirection comme valeur. Du côté client, vous pouvez écrire un code JavaScript pour lire l'entête et rediriger le navigateur sur l'URL transmise. 

> Info: Yii est fourni avec un fichier JavaScript `yii.js` qui fournit un jeu d'utilitaires JavaScript, y compris l'utilitaire de redirection basé sur l'entête `X-Redirect`. Par conséquent, si vous utilisez ce fichier JavaScript (en enregistrant le paquet de ressources [[yii\web\YiiAsset]] ), vous n'avez rien à écrire pour prendre en charge la redirection AJAX. 

## Envoi de fichiers <span id="sending-files"></span>

Comme la redirection du navigateur, l'envoi de fichiers est une autre fonctionnalité qui s'appuie sur les entêtes HTTP spécifiques. Yii fournit un jeu de méthodes pour prendre en charge différents besoins d'envoi de fichiers. Elles assurent toutes la prise en charge de la plage d'entêtes HTTP. 

* [[yii\web\Response::sendFile()]]: envoie un fichier existant à un client.
* [[yii\web\Response::sendContentAsFile()]]: envoie un chaîne de caractères en tant que fichier à un client.
* [[yii\web\Response::sendStreamAsFile()]]: envoie un flux de fichier existant en tant que fichier à un client. 

Ces méthodes ont la même signature avec l'objet `response` comme valeur de retour. Si le fichier à envoyer est trop gros, vous devez envisager d'utiliser [[yii\web\Response::sendStreamAsFile()]] parce qu'elle fait un usage plus efficace de la mémoire. L'exemple qui suit montre comment envoyer un fichier dans une action de contrôleur. 

```php
public function actionDownload()
{
    return \Yii::$app->response->sendFile('path/to/file.txt');
}
```

Si vous appelez la méthode d'envoi de fichiers dans des endroits autres qu'une méthode d'action, vous devez aussi appeler la méthode  [[yii\web\Response::send()]] immédiatement après pour garantir qu'aucun contenu supplémentaire ne sera ajouté à la réponse. 

```php
\Yii::$app->response->sendFile('path/to/file.txt')->send();
```

Quelques serveurs Web assurent une prise en charge spéciale de l'envoi de fichiers appelée *X-Sendfile*. L'idée est de rediriger la requête d'un fichier sur le serveur Web qui sert directement le fichier. En conséquence, l'application Web peut terminer plus rapidement tandis que le serveur Web est en train d'envoyer le fichier. Pour utiliser cette fonctionnalité, vous pouvez appeler la méthode [[yii\web\Response::xSendFile()]]. La liste suivante résume, comment activer la fonctionnalité `X-Sendfile` pour quelques serveurs Web populaires :

- Apache: [X-Sendfile](http://tn123.org/mod_xsendfile)
- Lighttpd v1.4: [X-LIGHTTPD-send-file](http://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
- Lighttpd v1.5: [X-Sendfile](http://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
- Nginx: [X-Accel-Redirect](http://wiki.nginx.org/XSendfile)
- Cherokee: [X-Sendfile and X-Accel-Redirect](http://www.cherokee-project.com/doc/other_goodies.html#x-sendfile)


## Envoi de la réponse <span id="sending-response"></span>

Le contenu d'une réponse n'est pas envoyé à l'utilisateur tant que la méthode [[yii\web\Response::send()]] n'est pas appelée. Par défaut, cette méthode est appelée automatiquement à la fin de [[yii\base\Application::run()]]. Vous pouvez néanmoins appeler cette méthode explicitement pour forcer l'envoi de la réponse immédiatement. 

La méthode [[yii\web\Response::send()]] entreprend les étapes suivantes pour envoyer la réponse :

1. Elle déclenche l'événement  [[yii\web\Response::EVENT_BEFORE_SEND]].
2. Elle appelle [[yii\web\Response::prepare()]] pour formater [[yii\web\Response::data|les données de la réponse]] du [[yii\web\Response::content|contenu de la réponse]].
3. Elle déclenche l'événement  [[yii\web\Response::EVENT_AFTER_PREPARE]].
4. Elle appelle la méthode [[yii\web\Response::sendHeaders()]] pour envoyer les entêtes HTTP enregistrés. 
5. Elle appelle la méthode [[yii\web\Response::sendContent()]] pour envoyer le corps de la réponse.
6. Elle déclenche l'événement [[yii\web\Response::EVENT_AFTER_SEND]].

Après que la méthode [[yii\web\Response::send()]] est appelée une fois, tout appel suivant de cette méthode est ignoré. Cela signifie qu'une fois la réponse expédiée, vous ne pouvez lui ajouter aucun contenu. 

Comme vous pouvez le voir, la méthode [[yii\web\Response::send()]] déclenche plusieurs événements utiles. En répondant à ces événements, il est possible d'ajuster ou d'enjoliver la réponse. 

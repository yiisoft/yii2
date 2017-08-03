Mise en cache HTTP
============

En plus de la mise en cache côté serveur que nous avons décrite dans les sections précédentes, les applications Web peuvent aussi exploiter la mise en cache côté client pour économiser le temps de génération et de transfert d'un contenu de page inchangé.

Pour utiliser la mise en cache côté client, vous pouvez configurer [[yii\filters\HttpCache]] comme un filtre pour des actions de contrôleur dont le résultat rendu peut être mis en cache du côté du client. [[yii\filters\HttpCache|HttpCache]]
ne fonctionne que pour les requêtes `GET` et `HEAD`. Il peut gérer trois sortes d'entêtes HTTP relatifs à la mise en cache pour ces requêtes :

* [[yii\filters\HttpCache::lastModified|Last-Modified]]
* [[yii\filters\HttpCache::etagSeed|Etag]]
* [[yii\filters\HttpCache::cacheControlHeader|Cache-Control]]


## Entête `Last-Modified` <span id="last-modified"></span>

L'entête `Last-Modified` (dernière modification) utilise un horodatage pour indiquer si la page a été modifiée depuis sa mise en cache par le client.

Vous pouvez configurer la propriété [[yii\filters\HttpCache::lastModified]] pour activer l'envoi de l'entête `Last-modified`. La propriété doit être une fonction de rappel PHP qui retourne un horodatage UNIX concernant la modification de la page. La signature de la fonction de rappel PHP doit être comme suit :

```php
/**
 * @param Action $action l'objet action qui est actuellement géré
 * @param array $params la valeur de la propriété "params"
 * @return int un horodatage UNIX représentant l'instant de modification de la page
 */
function ($action, $params)
```

Ce qui suit est un exemple d'utilisation de l'entête `Last-Modified` :

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['index'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('post')->max('updated_at');
            },
        ],
    ];
}
```

Le code précédent établit que la mise en cache HTTP doit être activée pour l'action `index` seulement. Il doit générer un entête HTTP `Last-Modified` basé sur l'instant de la dernière mise à jour d'articles (posts). Lorsque le navigateur visite la page `index` pour la première fois, la page est générée par le serveur et envoyée au navigateur. Si le navigateur visite à nouveau la même page, et qu'aucun article n'a été modifié, le serveur ne régénère par la page, et le navigateur utilise la version mise en cache du côté du client. En conséquence, le rendu côté serveur et la transmission de la page sont tous deux évités. 


## Entête `ETag` <span id="etag"></span>

L'entête "Entity Tag" (or `ETag` en raccourci) utilise une valeur de hachage pour représenter le contenu d'une page. Si la page est modifiée, la valeur de hachage change également. En comparant la valeur de hachage conservée sur le client avec la valeur de hachage générée côté serveur, le cache peut déterminer si la page a été modifiée et doit être retransmise.

Vous pouvez configurer la propriété [[yii\filters\HttpCache::etagSeed]] pour activer l'envoi de l'entête `ETag`. La propriété doit être une fonction de rappel PHP qui retourne un nonce (sel) pour la génération de la valeur de hachage Etag. La signature de la fonction de rappel PHP doit être comme suit :

```php
/**
 * @param Action $action l'objet action qui est actuellement géré
 * @param array $params la valeur de la propriété "params"
 * @return string une chaîne de caractères à utiliser comme nonce (sel) pour la génération d'une valeur de hachage ETag 
 */
function ($action, $params)
```

Ce qui suit est un exemple d'utilisation de l'entête `ETag` :

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['view'],
            'etagSeed' => function ($action, $params) {
                $post = $this->findModel(\Yii::$app->request->get('id'));
                return serialize([$post->title, $post->content]);
            },
        ],
    ];
}
```

Le code ci-dessus établit que la mise en cache HTTP doit être activée pour l'action `view` seulement. Il doit générer un entête HTTP `ETag` basé sur le titre et le contenu de l'article demandé. Lorsque le navigateur visite la page pour la première fois, la page est générée par le serveur et envoyée au navigateur. Si le navigateur visite à nouveau la même page et que ni le titre, ni le contenu de l'article n'ont changé, le serveur ne régénère pas la page et le navigateur utilise la version mise en cache côté client. En conséquence, le rendu par le serveur et la transmission de la page sont tous deux évités. 

ETags vous autorise des stratégies de mises en cache plus complexes et/ou plus précises que l'entête `Last-Modified`. Par exemple, un ETag peut être invalidé si on a commuté le site sur un nouveau thème. 

Des génération coûteuses d'ETag peuvent contrecarrer l'objectif poursuivi en utilisant `HttpCache` et introduire une surcharge inutile, car il faut les réévaluer à chacune des requêtes. Essayez de trouver une expression simple qui invalide le cache si le contenu de la page a été modifié. 

> Note : en conformité avec la norme [RFC 7232](http://tools.ietf.org/html/rfc7232#section-2.4),
  `HttpCache` envoie les entêtes `ETag` et `Last-Modified` à la fois si ils sont tous deux configurés. Et si le client envoie les entêtes `If-None-Match` et `If-Modified-Since` à la fois, seul le premier est respecté. 


## Entête `Cache-Control` <span id="cache-control"></span>

L'entête `Cache-Control` spécifie la politique de mise en cache générale pour les pages. Vous pouvez l'envoyer en configurant la propriété [[yii\filters\HttpCache::cacheControlHeader]] avec la valeur de l'entête. Par défaut, l'entête suivant est envoyé :

```
Cache-Control: public, max-age=3600
```

## Propriété "Session Cache Limiter" <span id="session-cache-limiter"></span>

Lorsqu'une page utilise une session, PHP envoie automatiquement quelques entêtes HTTP relatifs à la mise en cache comme spécifié dans la propriété `session.cache_limiter` de PHP INI. Ces entêtes peuvent interférer ou désactiver la mise en cache que vous voulez obtenir de `HttpCache`. Pour éviter ce problème, par défaut, `HttpCache` désactive l'envoi de ces entêtes automatiquement. Si vous désirez modifier ce comportement, vous devez configurer la propriété [[yii\filters\HttpCache::sessionCacheLimiter]]. Cette propriété accepte une chaîne de caractères parmi `public`, `private`, `private_no_expire` et `nocache`. Reportez-vous au manuel de PHP à propos de [session_cache_limiter()](http://www.php.net/manual/en/function.session-cache-limiter.php) pour des explications sur ces valeurs.


## Implications SEO <span id="seo-implications"></span>

Les robots moteurs de recherche ont tendance à respecter les entêtes de mise en cache. Comme certains moteurs d'indexation du Web sont limités quant aux nombre de pages par domaine qu'ils sont à même de traiter dans un certain laps de temps, l'introduction d'entêtes de mise en cache peut aider à l'indexation de votre site car ils limitent le nombre de pages qui ont besoin d'être traitées.

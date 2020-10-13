Mise en cache de fragments
==========================

La mise en cache de fragments fait référence à la mise en cache de fragments de pages Web. Par exemple, si une page affiche un résumé des ventes annuelles dans un tableau, vous pouvez stocker ce tableau en cache pour éliminer le temps nécessaire à sa génération à chacune des requêtes. La mise en cache de fragments est construite au-dessus de la [mise en cache de données](caching-data.md).

Pour utiliser la mise en cache de fragments, utilisez la construction qui suit dans une [vue](structure-views.md):

```php
if ($this->beginCache($id)) {

    // ... générez le contenu ici ...

    $this->endCache();
}
```

C'est à dire, insérez la logique de génération du contenu entre les appels [[yii\base\View::beginCache()|beginCache()]] et
[[yii\base\View::endCache()|endCache()]]. Si le contenu est trouvé dans le cache, [[yii\base\View::beginCache()|beginCache()]]
rendra le contenu en cache et retournera `false` (faux), ignorant la logique de génération de contenu.
Autrement, votre logique de génération de contenu sera appelée, et quand [[yii\base\View::endCache()|endCache()]] sera appelée, le contenu généré sera capturé et stocké dans le cache.

Comme pour la [mise en cache de données](caching-data.md), un `$id` (identifiant) unique est nécessaire pour identifier un cache de contenu.


## Options de mise en cache <span id="caching-options"></span>

Vous pouvez spécifier des options additionnelles sur la mise en cache de fragments en passant le tableau d'options comme second paramètre à la méthode [[yii\base\View::beginCache()|beginCache()]]. En arrière plan, ce tableau d'options est utilisé pour configurer un composant graphique [[yii\widgets\FragmentCache]] qui met en œuvre la fonctionnalité réelle de mise en cache de fragments.

### Durée <span id="duration"></span>

L'option [[yii\widgets\FragmentCache::duration|duration]] (durée) est peut-être l'option de la mise en cache de fragments la plus couramment utilisée. Elle spécifie pour combien de secondes le contenu peut demeurer valide dans le cache. Le code qui suit met le fragment de contenu en cache pour au maximum une heure :

```php
if ($this->beginCache($id, ['duration' => 3600])) {

    // ... générez le contenu ici...

    $this->endCache();
}
```

Si cette option n'est pas définie, la valeur utilisée par défaut est 60, ce qui veut dire que le contenu mise en cache expirera au bout de 60 secondes.


### Dépendances <span id="dependencies"></span>

Comme pour la [mise en cache de données](caching-data.md#cache-dependencies), le fragment de contenu mis en cache peut aussi avoir des dépendances. Par exemple, le contenu d'un article affiché dépend du fait que l'article a été modifié ou pas.

Pour spécifier une dépendance, définissez l'option [[yii\widgets\FragmentCache::dependency|dependency]], soit sous forme d'objet [[yii\caching\Dependency]], soit sous forme d'un tableau de configuration pour créer un objet [[yii\caching\Dependency]]. Le code qui suit spécifie que le fragment de contenu dépend du changement de la valeur de la colonne `updated_at` (mis à jour le) : 

```php
$dependency = [
    'class' => 'yii\caching\DbDependency',
    'sql' => 'SELECT MAX(updated_at) FROM post',
];

if ($this->beginCache($id, ['dependency' => $dependency])) {

    // ... générez le contenu ici ...

    $this->endCache();
}
```


### Variations <span id="variations"></span>

Le contenu mise en cache peut connaître quelques variations selon certains paramètres. Par exemple, pour une application Web prenant en charge plusieurs langues, le même morceau de code d'une vue peut générer le contenu dans différentes langues. Par conséquent, vous pouvez souhaitez que le contenu mis en cache varie selon la langue courante de l'application.

Pour spécifier des variations de mise en cache, définissez l'option [[yii\widgets\FragmentCache::variations|variations]], qui doit être un tableau de valeurs scalaires, représentant chacune un facteur de variation particulier. Par exemple, pour que le contenu mis en cache varie selon la langue, vous pouvez utiliser le code suivant :

```php
if ($this->beginCache($id, ['variations' => [Yii::$app->language]])) {

    // ... générez le contenu ici ...

    $this->endCache();
}
```


### Activation désactivation de la mise en cache <span id="toggling-caching"></span>

Parfois, vous désirez activer la mise en cache de fragments seulement lorsque certaines conditions sont rencontrées. Par exemple, pour une page qui affiche un formulaire, vous désirez seulement mettre le formulaire en cache lorsqu'il est initialement demandé (via une requête GET). Tout affichage subséquent du formulaire (via des requêtes POST) ne devrait pas être mise en cache car il contient des données entrées par l'utilisateur. Pour mettre en œuvre ce mécanisme, vous pouvez définir l'option [[yii\widgets\FragmentCache::enabled|enabled]], comme suit :

```php
if ($this->beginCache($id, ['enabled' => Yii::$app->request->isGet])) {

    // ... générez le contenu ici ...

    $this->endCache();
}
```


## Mises en cache imbriquées <span id="nested-caching"></span>

La mise en cache de fragments peut être imbriquée. C'est à dire qu'un fragment mis en cache peut être contenu dans un autre fragment lui aussi mis en cache.
Par exemple, les commentaires sont mis en cache dans un cache de fragment interne, et sont mis en cache en même temps et avec le contenu de l'article dans un cache de fragment externe. Le code qui suit montre comment deux caches de fragment peuvent être imbriqués :

```php
if ($this->beginCache($id1)) {

    // ...logique de génération du contenu ...

    if ($this->beginCache($id2, $options2)) {

        // ...logique de génération du contenu...

        $this->endCache();
    }

    // ... logique de génération de contenu ...

    $this->endCache();
}
```

Différentes options de mise en cache peuvent être définies pour les caches imbriqués. Par exemple, les caches internes et les caches externes peuvent utiliser des valeurs de durée différentes. Même lorsque les données mises en cache dans le cache externe sont invalidées, le cache interne peut continuer à fournir un fragment interne valide. Néanmoins, le réciproque n'est pas vraie ; si le cache externe est évalué comme valide, il continue à fournir la même copie mise en cache après que le contenu du cache interne a été invalidé. Par conséquent, vous devez être prudent lors de la définition des durées ou des dépendances des caches imbriqués, autrement des fragments internes périmés peuvent subsister dans le fragment externe.


## Contenu dynamique <span id="dynamic-content"></span>

Lors de l'utilisation de la mise en cache de fragments, vous pouvez rencontrer une situation dans laquelle un gros fragment de contenu est relativement statique en dehors de quelques endroits particuliers. Par exemple, l'entête d'une page peut afficher le menu principal avec le nom de l'utilisateur courant. Un autre problème se rencontre lorsque le contenu mis en cache, contient du code PHP qui doit être exécuté à chacune des requêtes (p. ex. le code pour enregistrer un paquet de ressources). Ces deux problèmes peuvent être résolus par une fonctionnalité qu'on appelle *contenu dynamique*.

Un contenu dynamique signifie un fragment de sortie qui ne doit jamais être mis en cache même s'il est contenu dans un  fragment mis en cache. Pour faire en sorte que le contenu soit dynamique en permanence, il doit être généré en exécutant un code PHP à chaque requête, même si le contenu l'englobant est servi à partir du cache.

Vous pouvez appeler la fonction [[yii\base\View::renderDynamic()]] dans un fragment mis en cache pour y insérer un contenu dynamique à l'endroit désiré, comme ceci :

```php
if ($this->beginCache($id1)) {

    // ... logique de génération de contenu ...

    echo $this->renderDynamic('return Yii::$app->user->identity->name;');

    // ... logique de génération de contenu ...

    $this->endCache();
}
```

La méthode [[yii\base\View::renderDynamic()|renderDynamic()]] accepte un morceau de code PHP en paramètre. La valeur retournée est traitée comme un contenu dynamique. Le même code PHP est exécuté à chacune des requêtes, peu importe que le fragment englobant soit servi à partir du cache ou pas. 

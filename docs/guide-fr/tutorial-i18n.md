Internationalisation
====================

Le terme *Internationalisation* (I18N) fait référence au processus de conception d'une application logicielle qui permet son adaptation à diverses langues et régions sans intervenir dans le code. Pour des applications Web, la chose est particulièrement importante puisque celle-ci peut concerner des utilisateurs potentiels répartis sur toute la surface de la terre. Yii met à votre disposition tout un arsenal de fonctionnalités qui prennent en charge la traduction des messages et des vues, ainsi que le formatage des nombres et des dates. 


## Locale et Langue <span id="locale-language"></span>

Une *locale* est un jeu de paramètres qui définissent la langue de l'utilisateur, son pays et des préférences spéciales que celui-ci désire voir dans l'interface utilisateur. 

Elle est généralement identifiée par un identifiant (ID), lui-même constitué par un identifiant de langue et un identifiant de région. Par exemple, l'identifiant `en-US` représente la locale *anglais* pour la langue et   *États-Unis* pour la région. 

Pour assurer la cohérence, tous les identifiants utilisés par les application Yii doivent être présentés sous leur forme canonique `ll-CC`, où `ll` est un code à 2 ou 3 lettres pour la langue conforme à la norme [ISO-639](https://www.loc.gov/standards/iso639-2/) et `CC` est un code à deux lettres pour le pays conforme à la norme [ISO-3166](https://www.iso.org/iso/en/prods-services/iso3166ma/02iso-3166-code-lists/list-en1.html).
Pour plus de détails sur les locales, reportez-vous à la [documentation du projet ICU](https://unicode-org.github.io/icu/userguide/locale/#the-locale-concept).

Dans Yii, nous utilisons souvent le mot « langue » pour faire référence à la locale. 

Une application Yii utilise deux langues :  la [[yii\base\Application::$sourceLanguage|langue source ]] et la [[yii\base\Application::$language|langue cible]]. La première fait référence à la langue dans laquelle les messages sont rédigés dans le code source, tandis que la deuxième est celle qui est utilisée pour présenter les textes à l'utilisateur final.
Pour l'essentiel, le service appelé *message translation service*(service de traduction des messages) assure la traduction d'un message textuel de la langue source vers la langue cible. 

Vous pouvez configurer les langues de l'application dans la configuration de la manière suivante :


```php
return [
    // définit la langue cible comme étant le français-France
    'language' => 'fr-FR',
    
    // définit la langue source comme étant l'anglais États-Unis
    'sourceLanguage' => 'en-US',
    
    ......
];
```

La valeur par défaut pour la [[yii\base\Application::$sourceLanguage|langue source]] est `en-US`, qui signifie « anglais États-Unis ». Il est recommandé de conserver cette valeur sans la changer car il est généralement plus facile de trouver des gens capables de traduire de l'anglais vers d'autres langues que d'une langue non anglaise vers une autre langue. 

Il est souvent nécessaire de définir la [[yii\base\Application::$language|langue cible]] de façon dynamique en se basant sur différents facteurs tels que, par exemple, les préférences linguistiques de l'utilisateur final. Au lieu de la définir dans la configuration de l'application  vous pouvez utiliser l'instruction suivante pour changer la langue cible :

```php
// modifier la langue cible pour qu'elle soit français-FRANCE 
\Yii::$app->language = 'fr-FR';
```

> Tip: si votre langue source change selon les différentes parties de votre code, vous pouvez modifier la valeur de la langue source localement comme c'est expliqué dans la section suivante.

## Traduction des messages <span id="message-translation"></span>

Le service de traduction des messages traduit un message textuel d'une langue (généralement la [[yii\base\Application::$sourceLanguage|langue source]]) vers une autre langue  (généralement la [[yii\base\Application::$language|langue cible]]). Il effectue la traduction en recherchant le message à traduire dans une source de messages qui stocke les messages originaux et les messages traduits. 
Si le message est trouvé, le message traduit correspondant est renvoyé ; dans le cas contraire, le message original est renvoyé sans traduction. 

Pour utiliser le service de traduction des messages, vous devez principalement effectuer les opérations suivantes :

* Envelopper le message textuel à traduire dans un appel à la méthode [[Yii::t()]] ;
* Configurer une ou plusieurs sources de messages dans lesquelles le service de traduction des messages peut rechercher des traductions ; 
* Confier aux traducteurs le soin de traduire les messages et de les stocker dans les sources de messages. 

La méthode [[Yii::t()]] peut être utilisée comme le montre l'exemple suivant :

```php
echo \Yii::t('app', 'This is a string to translate!');
```

où le deuxième paramètre fait référence au message textuel à traduire, tandis que le premier paramètre fait référence au nom de la catégorie à laquelle le message appartient. 

La méthode [[Yii::t()]] appelle la méthode `translate` du [composant d'application](structure-application-components.md) `i18n` pour assurer le travail réel de traduction. Le composant peut être configuré dans la configuration de l'application de la manière suivante :

```php
'components' => [
    // ...
    'i18n' => [
        'translations' => [
            'app*' => [
                'class' => 'yii\i18n\PhpMessageSource',
                //'basePath' => '@app/messages',
                //'sourceLanguage' => 'en-US',
                'fileMap' => [
                    'app' => 'app.php',
                    'app/error' => 'error.php',
                ],
            ],
        ],
    ],
],
```

Dans le code qui précède, une source de  messages prise en charge par  [[yii\i18n\PhpMessageSource]] est configurée. Le motif `app*` indique que toutes les catégories de messages dont les noms commencent par `app` doivent être traduites en utilisant cette source de messages. La classe [[yii\i18n\PhpMessageSource]] utilise des fichiers PHP pour stocker les traductions de messages. Chacun des fichiers PHP correspond aux messages d'une même catégorie. Par défaut, le nom du fichier doit être celui de la catégorie. Néanmoins, vous pouvez configurer  [[yii\i18n\PhpMessageSource::fileMap|fileMap (table de mise en correspondance de fichiers)]] pour faire correspondre une catégorie à un fichier PHP dont le nom obéit à une autre approche de nommage. Dans l'exemple qui précède, la catégorie  `app/error` correspond au fichier PHP `@app/messages/fr-FR/error.php` (en supposant que `fr-FR` est la langue cible). Sans cette configuration, la catégorie correspondrait à  `@app/messages/fr-FR/app/error.php`.

En plus de la possibilité de stocker les messages dans des fichiers PHP, vous pouvez aussi utiliser les sources de messages suivantes pour stocker vos traductions sous une autre forme :

- [[yii\i18n\GettextMessageSource]] utilise des fichiers GNU Gettext, MO ou PO pour maintenir les messages traduits.
- [[yii\i18n\DbMessageSource]] utilise une base de donnée pour stocker les messages traduits. 


## Format des messages <span id="message-formatting"></span>

Lorsque vous traduisez un message, vous pouvez inclure dans le messages des « valeurs à remplacer » qui seront remplacées dynamiquement en fonction de la valeur d'un paramètre. Vous pouvez même utiliser une syntaxe spéciale des « valeurs à remplacer » pour que les valeurs de remplacement soient formatées en fonction de la langue cible.
Dans cette sous-section, nous allons décrire différentes manières de formater un message.

### Valeurs à remplacer des message <span id="message-parameters"></span>

Dans un message à traduire, vous pouvez inclure une ou plusieurs « valeurs à remplacer » pour qu'elles puissent être remplacées par les valeurs données. En spécifiant différents jeux de valeurs, vous pouvez faire varier le message dynamiquement. Dans l'exemple qui suit, la valeur à remplacer `{username}` du message `'Hello, {username}!'` sera remplacée par  `'Alexander'` et `'Qiang'`, respectivement.

```php
$username = 'Alexander';
// affiche un message traduit en remplaçant {username} par "Alexander"
echo \Yii::t('app', 'Hello, {username}!', [
    'username' => $username,
]);

$username = 'Qiang';
// affiche un message traduit en remplaçant {username} par "Qiang"
echo \Yii::t('app', 'Hello, {username}!', [
    'username' => $username,
]);
```

Lorsque le traducteur traduit un message contenant une valeur à remplacer, il doit laisser la valeur à remplacer telle quelle. Cela tient au fait que les valeurs à remplacer seront remplacées par les valeurs réelles au moment de l'appel de  `Yii::t()` pour traduire le message.

Dans un même message, vous pouvez utiliser, soit des « valeurs à remplacer nommées », soit des « valeurs à remplacer positionnelles », mais pas les deux types. 
 
L'exemple précédent montre comment utiliser des valeurs à remplacer nommées, c'est à dire, des valeurs à remplacer écrites sous la forme `{nom}`, et pour lesquelles vous fournissez un tableau associatif dont les clés sont les noms des valeurs à remplacer (sans les accolades) et les valeurs, les valeurs de remplacement.

Les valeurs à remplacer positionnelles utilisent une suite d'entiers démarrant de zéro en tant que noms de valeurs à remplacer qui seront remplacées par les valeurs de remplacement, fournies sous forme d'un tableau, en fonction de leur position dans le tableau lors de l'appel de la méthode  `Yii::t()`. Dans l'exemple suivant, les valeurs à remplacer positionnelles `{0}`, `{1}` et `{2}` seront remplacées respectivement par les valeurs de  `$price`, `$count` et `$subtotal`.

```php
$price = 100;
$count = 2;
$subtotal = 200;
echo \Yii::t('app', 'Price: {0}, Count: {1}, Subtotal: {2}', [$price, $count, $subtotal]);
```

Dans le cas d'une seule valeur à remplacer, la valeur de remplacement peut être donnée sans la placer dans un tableau :

```php
echo \Yii::t('app', 'Price: {0}', $price);
```

> Tip: dans la plupart des cas, vous devriez utiliser des valeurs à remplacer nommées, parce que les noms permettent aux traducteurs de
> mieux comprendre le sens des messages qu'ils doivent traduire. 


### Formatage des valeurs de remplacement <span id="parameter-formatting"></span>

Vous pouvez spécifier des règles de formatage additionnelles dans les valeurs à remplacer qui seront appliquées aux valeurs de remplacement. Dans l'exemple suivant, la valeur de remplacement *price* est traitée comme un nombre et formatée comme une valeur monétaire :

```php
$price = 100;
echo \Yii::t('app', 'Price: {0,number,currency}', $price);
```

> Note: le formatage des valeurs de remplacement nécessite l'installation de [extension intl de PHP](https://www.php.net/manual/fr/intro.intl.php).

Vous pouvez utiliser, soit la forme raccourcie, soit la forme complète pour spécifier une valeur à remplacer avec un format :
```
forme raccourcie : {name,type}
forme complète : {name,type,style}
```

> Note: si vous avez besoin des caractères spéciaux tels que  `{`, `}`, `'`, `#`, entourez-les de `'`:
> 
```php
echo Yii::t('app', "Example of string with ''-escaped characters'': '{' '}' '{test}' {count,plural,other{''count'' value is # '#{}'}}", ['count' => 3]);
```

Le format complet est décrit dans la [documentation ICU](https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classMessageFormat.html).

Dans ce qui suit, nous allons présenter quelques usages courants.



#### Nombres <span id="number"></span>

La valeur de remplacement est traitée comme un nombre. Par exemple,

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0,number}', $sum);
```

Vous pouvez spécifier un style facultatif pour la valeur de remplacement `integer` (entier), `currency` (valeur monétaire), ou `percent` (pourcentage) :

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0,number,currency}', $sum);
```

Vous pouvez aussi spécifier un motif personnalisé pour formater le nombre. Par exemple,

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0,number,,000,000000}', $sum);
```

Les caractères à utiliser dans les formats personnalisés sont présentés dans le document [ICU API reference](https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classDecimalFormat.html) à la section "Special Pattern Characters" (Caractères pour motifs spéciaux).
 
 
La valeur de remplacement est toujours formatée en fonction de la locale cible c'est à dire que vous ne pouvez pas modifier les séparateurs de milliers et de décimales, les symboles monétaires, etc. sans modifier la locale de traduction. Si vous devez personnaliser ces éléments vous pouvez utiliser [[yii\i18n\Formatter::asDecimal()]] et [[yii\i18n\Formatter::asCurrency()]].

#### Date <span id="date"></span>

La valeur de remplacement doit être formatée comme une date. Par exemple,

```php
echo \Yii::t('app', 'Today is {0,date}', time());
```

Vous pouvez spécifier des styles facultatifs pour la valeur de remplacement comme  `short` (court), `medium` (moyen), `long` (long) ou `full` (complet) :

```php
echo \Yii::t('app', 'Today is {0,date,short}', time());
```

Vous pouvez aussi spécifier un motif personnalisé pour formater la date :

```php
echo \Yii::t('app', 'Today is {0,date,yyyy-MM-dd}', time());
```

Voir [Formatting reference](https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classicu_1_1SimpleDateFormat.html#details).


#### Heure <span id="time"></span>

La valeur de remplacement doit être formatée comme une heure (au sens large heure minute seconde). Par exemple, 

```php
echo \Yii::t('app', 'It is {0,time}', time());
```

Vous pouvez spécifier des styles facultatifs pour la valeur de remplacement comme `short` (court), `medium` (moyen), `long` (long) ou `full` (complet) :

```php
echo \Yii::t('app', 'It is {0,time,short}', time());
```

Vous pouvez aussi spécifier un motif personnalisé pour formater l'heure :

```php
echo \Yii::t('app', 'It is {0,date,HH:mm}', time());
```

Voir [Formatting reference](https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classicu_1_1SimpleDateFormat.html#details).


#### Prononciation <span id="spellout"></span>

La valeur de remplacement doit être traitée comme un nombre et formatée comme une prononciation. Par exemple,

```php
//  produit "42 is spelled as forty-two" 
echo \Yii::t('app', '{n,number} is spelled as {n,spellout}', ['n' => 42]);
```

Par défaut le nombre est épelé en tant que cardinal. Cela peut être modifié :

```php
// produit  "I am forty-seventh agent" 
echo \Yii::t('app', 'I am {n,spellout,%spellout-ordinal} agent', ['n' => 47]);
```

Notez qu'il ne doit pas y avoir d'espace après `spellout,` et avant `%`.

Pour trouver une liste des options disponibles pour votre locale, reportez-vous à 
"Numbering schemas, Spellout" à [https://intl.rmcreative.ru/](https://intl.rmcreative.ru/).

#### Nombre ordinal <span id="ordinal"></span>

La valeur de remplacement doit être traitée comme un nombre et formatée comme un nombre ordinal. Par exemple,

```php
// produit "You are the 42nd visitor here!" (vous êtes le 42e visiteur ici !)
echo \Yii::t('app', 'You are the {n,ordinal} visitor here!', ['n' => 42]);
```

Les nombres ordinaux acceptent plus de formats pour des langues telles que l'espagnol : 

```php
// produit 471ª
echo \Yii::t('app', '{n,ordinal,%digits-ordinal-feminine}', ['n' => 471]);
```

Notez qu'il ne doit pas y avoir d'espace après `ordinal,` et avant `%`.

Pour trouver une liste des options disponibles pour votre locale, reportez-vous à 
"Numbering schemas, Ordinal" à  [https://intl.rmcreative.ru/](https://intl.rmcreative.ru/).

#### Durée <span id="duration"></span>

La valeur de remplacement doit être traitée comme un nombre de secondes et formatée comme une durée. Par exemple, 

```php
// produit "You are here for 47 sec. already!" (Vous êtes ici depuis 47sec. déjà !)
echo \Yii::t('app', 'You are here for {n,duration} already!', ['n' => 47]);
```

La durée accepte d'autres formats :

```php
// produit  130:53:47
echo \Yii::t('app', '{n,duration,%in-numerals}', ['n' => 471227]);
```

Notez qu'il ne doit pas y avoir d'espace après `duration,` et avant `%`.

Pour trouver une liste des options disponibles pour votre locale, reportez-vous à 
"Numbering schemas, Duration" à [https://intl.rmcreative.ru/](https://intl.rmcreative.ru/).

#### Pluriel <span id="plural"></span>

Les langues diffèrent dans leur manière de marquer le pluriel. Yii fournit un moyen pratique pour traduire les messages dans différentes formes de pluriel qui fonctionne même pour des règles très complexes. Au lieu de s'occuper des règles d'inflexion directement, il est suffisant de fournir la traductions des mots infléchis dans certaines situations seulement. Par exemple,

```php
// Lorsque  $n = 0, produit "There are no cats!" 
// Losque $n = 1, produit "There is one cat!" 
// Lorsque $n = 42, produit  "There are 42 cats!" 
echo \Yii::t('app', 'There {n,plural,=0{are no cats} =1{is one cat} other{are # cats}}!', ['n' => $n]);
```

Dans les arguments des règles de pluralisation ci-dessus,  `=` signifie valeur exacte. Ainsi `=0` signifie exactement zéro, `=1` signifie exactement un.  `other` signifie n'importe quelle autre valeur. `#` est remplacé par la valeur de  `n` formatée selon la langue cible. 

Les formes du pluriel peuvent être très compliquées dans certaines langues. Dans l'exemple ci-après en russe, `=1` correspond exactement à `n = 1` 
tandis que  `one` correspond à  `21` ou `101`:

```
Здесь {n,plural,=0{котов нет} =1{есть один кот} one{# кот} few{# кота} many{# котов} other{# кота}}!
```

Ces noms d'arguments spéciaux tels que  `other`, `few`, `many` et autres varient en fonction de la langue. Pour savoir lesquels utiliser pour une locale particulière, reportez-vous aux "Plural Rules, Cardinal" à [https://intl.rmcreative.ru/](https://intl.rmcreative.ru/). 
En alternative, vous pouvez vous reporter aux  [rules reference at unicode.org](https://cldr.unicode.org/index/cldr-spec/plural-rules).

> Note: le message en russe ci-dessus est principalement utilisé comme message traduit, pas comme message source, sauf si vous définissez la [[yii\base\Application::$sourceLanguage|langue source]] de votre application comme étant `ru-RU` et traduisez à partir du russe.
>
> Lorsqu'une traduction n'est pas trouvée pour un message source spécifié dans un appel de  `Yii::t()`, les règles du pluriel pour la 
> [[yii\base\Application::$sourceLanguage|langue source]] seront appliquées au message source.
Il existe un paramètre  `offset` dans le cas où la chaîne est de la forme suivante :

```php
$likeCount = 2;
echo Yii::t('app', 'You {likeCount,plural,
    offset: 1
    =0{did not like this}
    =1{liked this}
    one{and one other person liked this}
    other{and # others liked this}
}', [
    'likeCount' => $likeCount
]);

// You and one other person liked this
```

#### Sélection ordinale <span id="ordinal-selection"></span>

L'argument `selectordinal` pour une valeur à remplacer  numérique a pour but de choisir une chaîne de caractères basée sur les règles linguistiques de la locale cible pour les ordinaux. Ainsi, 

```php
$n = 3;
echo Yii::t('app', 'You are the {n,selectordinal,one{#st} two{#nd} few{#rd} other{#th}} visitor', ['n' => $n]);
```
//Produit pour l'anglais : 
//You are the 3rd visitor

//Traduction  en russe, 
'You are the {n,selectordinal,one{#st} two{#nd} few{#rd} other{#th}} visitor' => 'Вы {n,selectordinal,other{#-й}} посетитель',

//Traduit en russe produit :
//Вы 3-й посетитель

//Traduction en français
'You are the {n,selectordinal,one{#st} two{#nd} few{#rd} other{#th}} visitor' =>  'Vous êtes le {n,selectordinal,one{#er} other{#e}} visiteur'

//Traduit en français produit :
//Vous êtes le 3e visiteur
```

Le format est assez proche de celui utilisé pour le pluriel. Pour connaître quels arguments utiliser pour une locale particulière, reportez-vous aux "Plural Rules, Ordinal" à [https://intl.rmcreative.ru/](https://intl.rmcreative.ru/). 
En alternative, vous pouvez vous reporter aux  [rules reference at unicode.org](https://unicode-org.github.io/cldr-staging/charts/37/supplemental/language_plural_rules.html).

#### Sélection <span id="selection"></span>

Vous pouvez utiliser l'argument `select` dans une valeur à remplacer pour choisir une phrase en fonction de la valeur de remplacement. Par exemple, 

```php
// Peut produire "Snoopy is a dog and it loves Yii!"
echo \Yii::t('app', '{name} is a {gender} and {gender,select,female{she} male{he} other{it}} loves Yii!', [
    'name' => 'Snoopy',
    'gender' => 'dog',
]);
```
Dans l'expression qui précède, `female` et `male` sont des valeurs possibles de l'argument, tandis que `other` prend en compte les valeurs qui ne sont ni l'une ni l'autre des ces valeurs. Derrière chacune des valeurs possibles de l'argument, vous devez spécifier un segment de phrase en l'entourant d'accolades. 



### Spécification des sources de messages par défaut<span id="default-translation"></span>

Vous pouvez spécifier les sources de messages par défaut qui seront utilisées comme solution de repli pour les catégories qui ne correspondent à aucune des catégories configurées. 
Cette source de message doit être marquée par un caractère générique `*`. Pour cela ajoutez les lignes suivantes dans la configuration de l'application :

```php
//configure i18n component

'i18n' => [
    'translations' => [
        '*' => [
            'class' => 'yii\i18n\PhpMessageSource'
        ],
    ],
],
```
Désormais, vous pouvez utiliser une catégorie sans la configurer, ce qui est un comportement identique à celui de Yii 1.1. Les messages pour cette catégorie proviendront d'une source de messages par défaut située dans le dossier `basePath` c.-à-d. `@app/messages`:


```php
echo Yii::t('not_specified_category', 'message from unspecified category');
```

Le message sera chargé depuis `@app/messages/<LanguageCode>/not_specified_category.php`.

### Traduction des messages d'un module <span id="module-translation"></span>

Si vous voulez traduire les messages d'un module et éviter d'avoir un unique fichier de traduction pour tous les messages, vous pouvez procéder comme suit :

```php
<?php

namespace app\modules\users;

use Yii;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\users\controllers';

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['modules/users/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@app/modules/users/messages',
            'fileMap' => [
                'modules/users/validation' => 'validation.php',
                'modules/users/form' => 'form.php',
                ...
            ],
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('modules/users/' . $category, $message, $params, $language);
    }

}
```

Dans l'exemple précédent, nous utilisons le caractère générique pour la correspondance puis nous filtrons chacune des catégories par fichier requis. Au lieu d'utiliser `fileMap`, vous pouvez utiliser la convention de mise en correspondance du fichier de même nom. 

Désormais, vous pouvez utiliser  `Module::t('validation', 'your custom validation message')` ou `Module::t('form', 'some form label')` directement.

### Traduction des messages d'objets graphiques <span id="widget-translation"></span>

La règle applicable aux modules présentée ci-dessus s'applique également aux objets graphiques, par exemple :

```php
<?php

namespace app\widgets\menu;

use yii\base\Widget;
use Yii;

class Menu extends Widget
{

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        $i18n = Yii::$app->i18n;
        $i18n->translations['widgets/menu/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@app/widgets/menu/messages',
            'fileMap' => [
                'widgets/menu/messages' => 'messages.php',
            ],
        ];
    }

    public function run()
    {
        echo $this->render('index');
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('widgets/menu/' . $category, $message, $params, $language);
    }

}
```

Au lieu d'utiliser `fileMap`, vous pouvez utiliser la convention de mise en correspondance du fichier de même nom. 
Désormais, vous pouvez utiliser `Menu::t('messages', 'new messages {messages}', ['{messages}' => 10])` directement.

> Note: pour les objets graphiques, vous pouvez aussi utiliser les vues i18n, en y appliquant les mêmes règles que celles applicables aux contrôleurs.


### Traduction des messages du framework <span id="framework-translation"></span>

Yii est fourni avec les traductions par défaut des messages d'erreurs de validation et quelques autres chaînes. Ces messages sont tous dans la catégorie `yii`. Parfois, vous souhaitez corriger la traduction par défaut des messages du framework pour votre application. Pour le faire, configurez le [composant d'application](structure-application-components.md) `i18n` comme indiqué ci-après : 

```php
'i18n' => [
    'translations' => [
        'yii' => [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@app/messages'
        ],
    ],
],
```

Vous pouvez désormais placer vos traductions corrigées dans `@app/messages/<language>/yii.php`.

### Gestion des traductions manquantes <span id="missing-translations"></span>

Même si la traduction n'est pas trouvée dans la source de traductions, Yii affiche le contenu du message demandé. Un tel comportement est très pratique tant que le message est une phrase valide. Néanmoins, quelques fois, cela ne suffit pas. Vous pouvez désirer faire quelque traitement de la situation, au moment où le message apparaît manquant. Vous pouvez utiliser pour cela l'événement [[yii\i18n\MessageSource::EVENT_MISSING_TRANSLATION|missingTranslation (traduction manquante)]] de [[yii\i18n\MessageSource]].

Par exemple, vous désirez peut-être marquer toutes les traductions manquantes par quelque chose de voyant, de manière à les repérer facilement dans la page. Vous devez d'abord configurer un gestionnaire d'événement. Cela peut se faire dans la configuration de l'application :

```php
'components' => [
    // ...
    'i18n' => [
        'translations' => [
            'app*' => [
                'class' => 'yii\i18n\PhpMessageSource',
                'fileMap' => [
                    'app' => 'app.php',
                    'app/error' => 'error.php',
                ],
                'on missingTranslation' => ['app\components\TranslationEventHandler', 'handleMissingTranslation']
            ],
        ],
    ],
],
```

Vous devez ensuite implémenter votre gestionnaire d'événement :

```php
<?php

namespace app\components;

use yii\i18n\MissingTranslationEvent;

class TranslationEventHandler
{
    public static function handleMissingTranslation(MissingTranslationEvent $event)
    {
        $event->translatedMessage = "@MISSING: {$event->category}.{$event->message} FOR LANGUAGE {$event->language} @";
    }
}
```

Si [[yii\i18n\MissingTranslationEvent::translatedMessage]] est défini par le gestionnaire d'événement, il sera affiché en tant que résultat de la traduction.

> Note: chacune des sources de messages gère ses traductions manquantes séparément. Si vous avez recours à plusieurs sources de messages et que vous voulez qu'elles gèrent les messages manquants de la même manière, vous devez assigner le gestionnaire d'événement correspondant à chacune d'entre-elles. 


### Utilisation de la commande `message`<span id="message-command"></span>

Les traductions peuvent être stockées dans des [[yii\i18n\PhpMessageSource|fichiers php]], des [[yii\i18n\GettextMessageSource|fichiers .po ]] ou dans une [[yii\i18n\DbMessageSource|bases de données]]. Reportez-vous aux classes spécifiques pour connaître les options supplémentaires. 

En premier lieu, vous devez créer un fichier de configuration. Décidez de son emplacement et exécutez la commande suivante :


```bash
./yii message/config-template path/to/config.php
```

Ouvrez le fichier ainsi créé et ajustez-en les paramètres pour qu'ils répondent à vos besoins. Portez-une attention particulière à :

* `languages`: un tableau des langues dans lesquelles votre application doit être traduite  ;
* `messagePath`: le chemin du dossier où doivent être placés les fichiers de messages, qui doit correspondre le paramètre `basePath` de `i18n` dans la configuration de l'application. 

Vous pouvez également utiliser la commande './yii message/config' pour générer dynamiquement le fichier de configuration avec les options spécifiées via le ligne de commande. Par exemple, vous pouvez définir `languages` et `messagePath` comme indiqué ci-dessous :


```bash
./yii message/config --languages=de,ja --messagePath=messages path/to/config.php
```

Pour connaître toutes les options utilisables, exécutez la commande :

```bash
./yii help message/config
```

Une fois que vous en avez terminé avec la configuration, vous pouvez finalement extraire vos messages par la commande :

```bash
./yii message path/to/config.php
```

Vous pouvez aussi utiliser des options pour changer dynamiquement les paramètres d'extraction.

Vous trouverez alors vos fichiers de traduction (si vous avez choisi les traductions basées sur des fichiers) dans votre dossier  `messagePath`.


## Traduction des vues<span id="view-translation"></span>

Plutôt que de traduire des textes de messages individuels, vous pouvez parfois désirer traduire le script d'une vue tout entier. Pour cela, contentez-vous de traduire la vue et de la sauvegarder dans un sous-dossier dont le nom est le code de la langue cible. Par exemple, si vous avez traduit le script de la vue `views/site/index.php` et que la langue cible est  `fr-FR`, vous devez sauvegarder la traduction dans `views/site/fr-FR/index.php`. Désormais, à chaque fois que vous appellerez  [[yii\base\View::renderFile()]] ou toute méthode qui invoque cette méthode (p. ex. [[yii\base\Controller::render()]]) pour rendre la vue, `views/site/index.php`, ce sera la vue `views/site/fr-FR/index.php` qui sera rendue à sa place.

> Note: si la  [[yii\base\Application::$language|langue cible]] est identique à la  [[yii\base\Application::$sourceLanguage|langue source]]
> la vue originale sera rendue sans tenir compte de l'existence de la vue traduite. 


## Formatage des dates et des nombres<span id="date-number"></span>

Reportez-vous à la section [Formatage des données](output-formatting.md) pour les détails.


## Configuration de l'environnement PHP <span id="setup-environment"></span>

Yii utilise l'[extension intl de PHP](https://www.php.net/manual/fr/book.intl.php) pour fournir la plupart de ses fonctionnalités d'internationalisation, telles que le formatage des dates et des nombres de la classe [[yii\i18n\Formatter]] et le formatage des messages de la classe [[yii\i18n\MessageFormatter]].
Les deux classes fournissent un mécanisme de remplacement lorsque l'extension `intl` n'est pas installée. Néanmoins, l'implémentation du mécanisme de remplacement ne fonctionne bien que quand la langue cible est l'anglais. C'est pourquoi, il est fortement recommandé d'installer `intl` quand c'est nécessaire.
L'[extension intl de PHP](https://www.php.net/manual/fr/book.intl.php) est basée sur la [bibliothèque ICU](https://icu.unicode.org/) qui fournit la base de connaissances et les règles de formatage pour les différentes locales. Des versions différentes d'ICU peuvent conduire à des formatages différents des dates et des nombres. Pour être sûr que votre site Web donne les même résultats dans tous les environnements, il  est recommandé d'installer la même version de l'extension `intl` (et par conséquent la même version d'ICU) dans tous les environnements. 

Pour savoir quelle version d'ICU est utilisée par PHP, vous pouvez exécuter le script suivant, qui vous restitue la version de PHP et d'ICU en cours d'utilisation. 

```php
<?php
echo "PHP: " . PHP_VERSION . "\n";
echo "ICU: " . INTL_ICU_VERSION . "\n";
echo "ICU Data: " . INTL_ICU_DATA_VERSION . "\n";
```

Il est également recommandé d'utiliser une version d'ICU supérieure ou égale à 48. Cela garantit que toutes les fonctionnalités décrites dans ce document sont utilisables. Par exemple, une version d'ICU inférieure à 49 ne prend pas en charge la valeur à remplacer `#` dans les règles de pluralisation. Reportez-vous à  <https://icu.unicode.org/download> pour obtenir une liste complète des versions d'ICU disponibles. Notez que le numérotage des versions a changé après la version 4.8  (p. ex., ICU 4.8, ICU 49, ICU 50, etc.)

En outre, les informations dans la base de donnée des fuseaux horaires fournie par la bibliothèque ICU peuvent être surannées. Reportez-vous au [manuel d'ICU](https://unicode-org.github.io/icu/userguide/datetime/timezone/#updating-the-time-zone-data) pour les détails sur la manière de mettre la base de données des fuseaux horaires à jour. Bien que la base de données des fuseaux horaires d'ICU soit utilisée pour le formatage, celle de PHP peut aussi être d'actualité. Vous pouvez la mettre à jour en installant la dernière version du [paquet `timezonedb` de pecl](https://pecl.php.net/package/timezonedb).

Comportements
=============

Les comportements (*behaviors* sont des instances de la classe [[yii\base\Behavior]], ou de ses classes filles. Les comportements, aussi connus sous le nom de [mixins](https://fr.wikipedia.org/wiki/Mixin), vous permettent d'améliorer les fonctionnalités d'une classe de [[yii\base\Component|composant]] existante sans avoir à modifier les héritages de cette classe. Le fait d'attacher un comportement à un composant injecte les méthodes et les propriétés de ce comportement dans le composant, rendant ces méthodes et ces propriétés accessibles comme si elles avaient été définies dans la classe du composant lui-même. En outre, un comportement peut répondre aux [événements](concept-events.md) déclenchés par le composant, ce qui permet aux comportements de personnaliser l'exécution normale du code du composant.


Définition des comportements <span id="defining-behaviors"></span>
---------------------------

Pour définir un comportement, vous devez créer une classe qui étend la classe  [[yii\base\Behavior]], ou une des ses classes filles. Par exemple :

```php
namespace app\components;

use yii\base\Behavior;

class MyBehavior extends Behavior
{
    public $prop1;

    private $_prop2;

    public function getProp2()
    {
        return $this->_prop2;
    }

    public function setProp2($value)
    {
        $this->_prop2 = $value;
    }

    public function foo()
    {
        // ...
    }
}
```

Le code ci-dessus définit la classe de comportement `app\components\MyBehavior` avec deux propriété — `prop1` et `prop2` — et une méthode `foo()`. Notez que la propriété `prop2` est définie via la méthode d'obtention `getProp2` et la méthode d'assignation `setProp2`. Cela est le cas parce que la classe  [[yii\base\Behavior]] étend la classe [[yii\base\BaseObject]] et, par conséquent, prend en charge la définition des [propriétés](concept-properties.md) via les méthodes d'obtention et d'assignation. 

Comme cette classe est un comportement, lorsqu'elle est attachée à un composant, ce composant acquiert alors les propriétés  `prop1` et `prop2`, ainsi que la méthode `foo()`.

> Tip: depuis l'intérieur d'un comportement, vous avez accès au composant auquel le comportement est attaché via la propriété [[yii\base\Behavior::owner]].

> Note: dans le cas où les méthodes  [[yii\base\Behavior::__get()]] et/ou [[yii\base\Behavior::__set()]] du comportement sont redéfinies, vous devez redéfinir les méthodes [[yii\base\Behavior::canGetProperty()]] et/ou [[yii\base\Behavior::canSetProperty()]] également.

Gestion des événements du composant
-----------------------------------

Si un comportement a besoin de répondre aux événements déclenchés par le composant auquel il est attaché, il doit redéfinir la méthode [[yii\base\Behavior::events()]]. Par exemple:

```php
namespace app\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class MyBehavior extends Behavior
{
    // ...

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event)
    {
        // ...
    }
}
```

La méthode [[yii\base\Behavior::events()|events()]] doit retourner une liste d'événements avec leur gestionnaire correspondant. L'exemple ci-dessus déclare que l'événement [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] existe et définit son gestionnaire `beforeValidate()`. En spécifiant un gestionnaire d'événement, vous pouvez utiliser un des formats suivants :
 
* une chaîne de caractères qui fait référence au nom d'une méthode de la classe du comportement, comme dans l'exemple ci-dessus ;
* un tableau constitué d'un nom d'objet ou de classe et d'un nom de méthode sous forme de chaîne de caractères (sans les parenthèses), p. ex. `[$object, 'methodName']`;
* une fonction anonyme.

La signature d'un gestionnaire d'événement doit être similaire à ce qui suit, où `event` fait référence au paramètre événement. Reportez-vous à la section [Événements](concept-events.md) pour plus de détail sur les événements.

```php
function ($event) {
}
```

Attacher des comportements <span id="attaching-behaviors"></span>
-----------------------------

Vous pouvez attacher un comportement à un [[yii\base\Component|composant]] soit de manière statique, soit de manière dynamique. Le première manière est une pratique plus habituelle.

Pour attacher un comportement de manière statique, redéfinissez la méthode [[yii\base\Component::behaviors()|behaviors()]] de la classe du composant auquel le comportement va être attaché. La méthode [[yii\base\Component::behaviors()|behaviors()]] doit retourner une liste de [configurations](concept-configurations.md) de comportements. Chaque comportement peut être soit un nom de classe de comportement, soit un tableau de configuration :

```php
namespace app\models;

use yii\db\ActiveRecord;
use app\components\MyBehavior;

class User extends ActiveRecord
{
    public function behaviors()
    {
        return [
            // comportement anonyme, nom de la classe de comportement seulement
            MyBehavior::class,

            // comportement nommé, nom de classe de comportement seulement
            'myBehavior2' => MyBehavior::class,

            // comportement anonyme, tableau de configuration
            [
                'class' => MyBehavior::class,
                'prop1' => 'value1',
                'prop2' => 'value2',
            ],

            // comportement nommé, tableau de configuration
            'myBehavior4' => [
                'class' => MyBehavior::class,
                'prop1' => 'value1',
                'prop2' => 'value2',
            ]
        ];
    }
}
```

Vous pouvez associer un nom au comportement en spécifiant la clé de tableau correspondant à la configuration du comportement. Dans ce cas, le comportement est appelé *comportement nommé*. Dans l'exemple ci-dessus, il y a deux comportements nommés : `myBehavior2` et `myBehavior4`. Si un comportement n'est pas associé à un nom, il est appelé *comportement anonyme*.


Pour attacher un comportement de manière dynamique, appelez la méthode [[yii\base\Component::attachBehavior()]] du composant auquel le comportement va être attaché : 

```php
use app\components\MyBehavior;

// attache un objet comportement 
$component->attachBehavior('myBehavior1', new MyBehavior());

// attache un classe de comportement
$component->attachBehavior('myBehavior2', MyBehavior::class);

// attache un tableau de configuration 
$component->attachBehavior('myBehavior3', [
    'class' => MyBehavior::class,
    'prop1' => 'value1',
    'prop2' => 'value2',
]);
```

Vous pouvez attacher plusieurs comportements à la fois en utilisant la méthode  [[yii\base\Component::attachBehaviors()]] :

```php
$component->attachBehaviors([
    'myBehavior1' => new MyBehavior(), // un comportement nommé
    MyBehavior::class,                 // un comportement anonyme
]);
```

Vous pouvez aussi attacher des comportements via les [configurations](concept-configurations.md) comme ceci :

```php
[
    'as myBehavior2' => MyBehavior::class,

    'as myBehavior3' => [
        'class' => MyBehavior::class,
        'prop1' => 'value1',
        'prop2' => 'value2',
    ],
]
```

Pour plus de détails, reportez-vous à la section  [Configurations](concept-configurations.md#configuration-format).

Utilisation des comportements <span id="using-behaviors"></span>
-----------------------------

Pour utiliser un comportement, commencez par l'attacher à un [[yii\base\Component|composant]] en suivant les instructions données ci-dessus. Une fois le comportement attaché au composant, son utilisation est évidente.

Vous pouvez accéder à une variable membre *publique*, ou à une  [propriété](concept-properties.md) définie par une méthode d'obtention et/ou une méthode d'assignation (*getter* et *setter*), du comportement, via le composant auquel ce comportement est attaché : 

```php
// "prop1" est une propriété définie dans la classe du comportement
echo $component->prop1;
$component->prop1 = $value;
```

Vous pouvez aussi appeler une méthode *publique* du comportement de façon similaire :

```php
// foo() est une méthode publique définie dans la classe du comportement
$component->foo();
```

Comme vous pouvez le voir, bien que le composant `$component` ne définissent pas `prop1` et`foo()`, elles peuvent être utilisées comme si elles faisaient partie de la définition du composant grâce au comportement attaché. 

Si deux comportement définissent la même propriété ou la même méthode, et que ces deux comportement sont attachés au même composant, le comportement qui a été attaché le *premier* prévaut lorsque la propriété ou la méthode est accédée.

Un comportement peut être associé à un nom lorsqu'il est attaché à un composant. Dans un tel cas, vous pouvez accéder à l'objet comportement en utilisant ce nom :

```php
$behavior = $component->getBehavior('myBehavior');
```

Vous pouvez aussi obtenir tous les comportements attachés au composant :

```php
$behaviors = $component->getBehaviors();
```


Détacher des comportements <span id="detaching-behaviors"></span>
--------------------------

Pour détacher un comportement, appelez [[yii\base\Component::detachBehavior()]] avec le nom associé au comportement :

```php
$component->detachBehavior('myBehavior1');
```

Vous pouvez aussi détacher *tous les* comportements : 

```php
$component->detachBehaviors();
```


Utilisation de  `TimestampBehavior` <span id="using-timestamp-behavior"></span>
-------------------------

Pour aller à l'essentiel, jetons un coup d'œil à [[yii\behaviors\TimestampBehavior]]. Ce comportement prend automatiquement en charge la mise à jour de l'attribut *timestamp* (horodate) d'un modèle [[yii\db\ActiveRecord|enregistrement actif]] à chaque fois qu'il est sauvegardé via les méthodes `insert()`, `update()` ou `save()`.

Tout d'abord, attachez ce comportement à la classe [[yii\db\ActiveRecord|Active Record (enregistrement actif)]] que vous envisagez d'utiliser :

```php
namespace app\models\User;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class User extends ActiveRecord
{
    // ...

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                // si vous utilisez datetime au lieur de l'UNIX timestamp:
                // 'value' => new Expression('NOW()'),
            ],
        ];
    }
}
```

Le comportement ci-dessus spécifie que lorsque l'enregistrement est : 

* inséré, le comportement doit assigner l'horodate UNIX courante aux attributs `created_at` (créé le)  et `updated_at` (mis à jour le) ;
* mis à jour, le comportement doit assigner l'horodate UNIX courante à l'attribut `updated_at` ;

> Note: pour que la mise en œuvre ci-dessus fonctionne avec une base de données MySQL, vous devez déclarer les colonnes (`created_at`, `updated_at`) en tant que `int(11)` pour qu'elles puissent représenter des horodates UNIX. 

Avec ce code en place, si vous avez un objet `User` (utilisateur) et que vous essayez de le sauvegarder, il verra ses attributs `created_at` et `updated_at` automatiquement remplis avec l'horodate UNIX :

```php
$user = new User;
$user->email = 'test@example.com';
$user->save();
echo $user->created_at;  // affiche l'horodate courante
```

Le comportement [[yii\behaviors\TimestampBehavior|TimestampBehavior]] offre également une méthode utile [[yii\behaviors\TimestampBehavior::touch()|touch()]], qui assigne l'horodate courante à un attribut spécifié et le sauvegarde dans la base de données : 

```php
$user->touch('login_time');
```

Autres comportements
--------------------

Il existe plusieurs comportements pré-inclus et extérieurs disponibles :

- [[yii\behaviors\BlameableBehavior]] – remplit automatiquement les attributs spécifiés avec l'identifiant de l'utilisateur courant. 
- [[yii\behaviors\SluggableBehavior]] – remplit automatiquement l'attribut spécifié avec une valeur utilisable en tant que chaîne purement ASCII (*slug*) dans une URL. 
- [[yii\behaviors\AttributeBehavior]] – assigne automatiquement une valeur spécifiée à un ou plusieurs attributs d'un objet enregistrement actif lorsque certains événements se produisent. 
- [yii2tech\ar\softdelete\SoftDeleteBehavior](https://github.com/yii2tech/ar-softdelete) – fournit des méthodes pour une suppression douce et une restauration douce d'un enregistrement actif c.-à-d. positionne un drapeau ou un état qui marque l'enregistrement comme étant effacé.
- [yii2tech\ar\position\PositionBehavior](https://github.com/yii2tech/ar-position) – permet la gestion de l'ordre des enregistrements dans un champ entier (*integer*) en fournissant les méthodes de remise dans l'ordre.

Comparaison des comportement et des traits <span id="comparison-with-traits"></span>
------------------------------------------

Bien que les comportements  soient similaires aux [traits](https://www.php.net/manual/fr/language.oop5.traits.php) par le fait qu'ils *injectent* tous deux leurs propriétés et leurs méthodes dans la classe primaire, ils diffèrent par de nombreux aspects. Comme nous l'expliquons ci-dessous, ils ont chacun leurs avantages et leurs inconvénients. Ils sont plus des compléments l'un envers l'autre, que des alternatives. 


### Raisons d'utiliser des comportements <span id="pros-for-behaviors"></span>

Les classes de comportement, comme les classes normales, prennent en charge l'héritage. Les traits, par contre, peuvent être considérés comme des copier coller pris en charge par le langage. Ils ne prennent pas en charge l'héritage. 

Les comportements peuvent être attachés et détachés à un composant de manière dynamique sans qu'une modification de la classe du composant soit nécessaire. Pour utiliser un trait, vous devez modifier le code de la classe qui l'utilise. 

Les comportements sont configurables mais les traits ne le sont pas. 

Les comportement peuvent personnaliser l'exécution du code d'un composant en répondant à ses événements.

Lorsqu'il se produit des conflits de noms entre les différents comportements attachés à un même composant, les conflits sont automatiquement résolus  en donnant priorité au comportement attaché le premier. Les conflits de noms causés par différents traits nécessitent une résolution manuelle en renommant les propriétés et méthodes concernées. 


### Raisons d'utiliser des traits <span id="pros-for-traits"></span>

Les traits sont beaucoup plus efficaces que les comportements car les comportements sont des objets qui requièrent plus de temps du processeur et plus de mémoire. 

Les environnement de développement intégrés (EDI) sont plus conviviaux avec les traits car ces derniers sont des constructions natives du langage. 

Propriétés
==========

En PHP, les variables membres des classes sont aussi appelées *propriétés*. Ces variables font partie de la définition de la classe  et sont utilisées pour représenter l'état d'une instance de cette classe (c.-à-d. à différentier une instance de la classe d'une autre). En pratique, vous désirez souvent gérer la lecture et l'écriture de ces propriété d'une manière particulière. Par exemple, vous pouvez désirer qu'une chaîne de caractères soit toujours nettoyée avant de l'assigner à une propriété `label`. Vous *pouvez* utiliser le code suivant pour arriver à cette fin :

```php
$object->label = trim($label);
```

Le revers du code ci-dessus est que vous devez appeler `trim()` partout ou vous voulez définir la propriété `label`. Si, plus tard, la propriété `label` devient sujette à de nouvelles exigences, telles que la première lettre doit être une capitale, vous auriez à modifier toutes les parties de code  qui assigne une valeur à la propriété `label`. La répétition de code conduit à des bogues, et c'est une pratique courant de l'éviter autant que faire se peut.

Pour résoudre ce problème, Yii introduit une classe de base nommée [[yii\base\BaseObject]] qui prend en charge la définition de propriétés sur la base de méthodes d'obtention (*getter*) et de méthode d'assignation (*setters*). Si une classe a besoin de cette fonctionnalité, il suffit qu'elle étende la classe[[yii\base\BaseObject]], ou une de ses classes filles.

> Info: presque toutes les classes du noyau du framework Yii étendent la classe [[yii\base\BaseObject]] ou une de ses classes filles. Cela veut dire, que chaque fois que vous trouvez une méthode d'obtention ou d'assignation dans une classe du noyau, vous pouvez l'utiliser comme une propriété. 
 
Une méthode d'obtention est une méthode dont le nom commence par le mot `get` (obtenir) et une méthode d'assignation est une méthode dont le nom commence par le mot `set` (assigner, définir).  Le nom après les mots préfixes `get` ou `set` définit le nom d'une propriété. Par exemple, une méthode d'obtention `getLabel` et/ou une méthode d'assignation `setLabel` obtient et assigne, respectivement, une propriété nommée `label`, comme le montre le code suivant :

```php
namespace app\components;

use yii\base\BaseObject;

class Foo extends BaseObject
{
    private $_label;

    public function getLabel()
    {
        return $this->_label;
    }

    public function setLabel($value)
    {
        $this->_label = trim($value);
    }
}
```

Pour être tout à fait exact, les méthodes d'obtention et d'assignation créent la propriété `label`, qui dans ce cas fait référence en interne à une propriété privée nommée `_label`.

Les propriétés définies par les méthodes d'obtention et d'assignation peuvent être utilisées comme des variables membres de la classe. La différence principale est que, lorsqu'une telle propriété est lue, la méthode d'obtention correspondante est appelée ; lorsqu'une valeur est assignée à la propriété, la méthode d'assignation correspondante est appelée. Par exemple :

```php
// équivalent à $label = $object->getLabel();
$label = $object->label;

// équivalent à $object->setLabel('abc');
$object->label = 'abc';
```

Une propriété définie par une méthode d'obtention (*getter*) sans méthode d'assignation (*setter*) est une propriété *en lecture seule*. Essayer d'assigner une valeur à une telle propriété provoque une exception [[yii\base\InvalidCallException|InvalidCallException]]. De façon similaire, une propriété définie par une méthode d'assignation sans méthode d'obtention est *en écriture seule*. Essayer de lire une telle propriété provoque une exception. Il n'est pas courant d'avoir des propriétés *en écriture seule*. 

Il existe plusieurs règles spéciales pour les propriétés définies via des méthodes d'obtention et d'assignation, ainsi que certaines limitations sur elles.

* Le nom de telles propriétés sont *insensibles à la casse*. Par exempe,  `$object->label` et `$object->Label` sont identiques. Cela est dû au fait que le nom des méthodes dans PHP est insensible à la casse.
* Si le nom d'uen telle propriété est identique à celui d'une variable membre de la classe, la dernier prévaut. Par exemple, si la classe ci-dessus `Foo` possède une variable mommée `label`, alors l'assignation `$object->label = 'abc'` affecte la *variable membre* `label` ; cette ligne ne fait pas appel à la méthode d'assignation `setLabel()`.
* Ces propriétés en prennent pas en charge la visibilité. Cela ne fait aucune différence pour les méthodes d'obtention et d'assignation qui définissent une propriété, que cette propriété soit publique, protégée ou privée.
* Les propriétés peuvent uniquement être définies par des méthodes d'obtention et d'assignation *non-statiques*. Les méthodes statiques ne sont pas traitées de la même manière. 
* Un appel normal à la méthode `property_exists()` ne fonctionne pas pour déterminer des propriétés magiques. Vous devez appeler  [[yii\base\BaseObject::canGetProperty()|canGetProperty()]] ou [[yii\base\BaseObject::canSetProperty()|canSetProperty()]] respectivement.

En revenant au problème évoqué au début de ce guide, au lieu d'appeler `trim()` partout où une valeur est assignée à `label`, vous pouvez vous contenter d'appeler `trim()` dans la méthode d'assignation `setLabel()`. Et si une nouvelle exigence apparaît – comme celle de mettre la première lettre en capitale – la méthode  `setLabel()` peut être rapidement modifiée sans avoir à toucher à d'autres parties du code. Cet unique modification affecte l'ensemble des assignation de `label`.

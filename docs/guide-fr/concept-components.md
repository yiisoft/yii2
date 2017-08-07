Composants
==========

Les composants sont les blocs de constructions principaux de vos applications Yii. Les composants sont des instances de la classe [[yii\base\Component]],
ou de ses classes filles. Les trois fonctionnalités principales fournies par les composants aux autres classes sont :

* [Les propriétés](concept-properties.md) ;
* [Les événements](concept-events.md) ;
* [Les comportements](concept-behaviors.md).
 
Séparément et en combinaisons, ces fonctionnalités rendent les classes de Yii beaucoup plus personnalisables et faciles à utiliser. Par exemple, l'[[yii\jui\DatePicker|objet graphique de sélection de date]] inclus, un composant d'interface utilisateur, peut être utilisé dans une [vue](structure-view.md) pour générer un sélecteur de date interactif :

```php
use yii\jui\DatePicker;

echo DatePicker::widget([
    'language' => 'ru',
    'name'  => 'country',
    'clientOptions' => [
        'dateFormat' => 'yy-mm-dd',
    ],
]);
```
Les propriétés de l'objet graphique sont faciles à écrire car la classe étend [[yii\base\Component]].

Tandis que les composants sont très puissants, ils sont un peu plus lourds que les objets normaux. Cela est dû au fait que, en particulier,  la prise en charge des fonctionnalités [event](concept-events.md) et [behavior](concept-behaviors.md) requiert un peu plus de mémoire et de temps du processeur. Si vos composants n'ont pas besoin de ces deux fonctionnalités, vous devriez envisager d'étendre la classe [[yii\base\BaseObject]] au lieu de la classe [[yii\base\Component]]. Ce faisant, votre composant sera aussi efficace que les objets PHP normaux, mais avec la prise en charge des [propriétés](concept-properties.md).

Lorsque votre classe étend la classe [[yii\base\Component]] ou [[yii\base\BaseObject]], il est recommandé que suiviez ces conventions :

- Si vous redéfinissez le constructeur, spécifiez un paramètre `$config` en tant que *dernier* paramètre du constructeur est passez le au constructeur du parent. 
- Appelez toujours le constructeur du parent *à la fin* de votre constructeur redéfini.
- Si vous redéfinissez la méthode [[yii\base\BaseObject::init()]], assurez-vous que vous appelez la méthode `init()` mise en œuvre par le parent *au début* de votre méthodes `init()`.

Par exemple :

```php
<?php

namespace yii\components\MyClass;

use yii\base\BaseObject;

class MyClass extends BaseObject
{
    public $prop1;
    public $prop2;

    public function __construct($param1, $param2, $config = [])
    {
        // ... initialisation avant l'application de la configuration

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... initialization après l'application de la configuration
    }
}
```

Le respect de ces conseils rend vos composants  [configurables](concept-configurations.md) lors de leur création. Par exemple :

```php
$component = new MyClass(1, 2, ['prop1' => 3, 'prop2' => 4]);
// alternatively
$component = \Yii::createObject([
    'class' => MyClass::class,
    'prop1' => 3,
    'prop2' => 4,
], [1, 2]);
```

> Info: bien que l'approche qui consiste à appeler la méthode [[Yii::createObject()]] semble plus compliquée, elle est plus puissante car elle est mise en œuvre sur un [conteneur d'injection de dépendances](concept-di-container.md).
  

La classe [[yii\base\BaseObject]] fait appliquer le cycle de vie suivant de l'objet :

1. Pré-initialisation dans le constructeur. Vous pouvez définir les propriétés par défaut à cet endroit.
2. Configuration de l'objet via `$config`. La configuration peut écraser les valeurs par défaut définies dans le constructeur.
3. Post-initialisation dans la méthode [[yii\base\BaseObject::init()|init()]]. Vous pouvez redéfinir cette méthode pour effectuer des tests sanitaires et normaliser les propriétés.
4. Appel des méthodes de l'objet.

Les trois premières étapes arrivent toutes durant la construction de l'objet. Cela signifie qu'une fois que vous avez obtenu une instance de la classe (c.-à-d. un objet), cet objet a déjà été initialisé dans un état propre et fiable. 

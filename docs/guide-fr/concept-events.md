Événements
==========

Les événement vous permettent d'injecter du code personnalisé dans le code existant à des points précis de son exécution. Vous pouvez attacher du code personnalisé à un événement de façon à ce que, lorsque l'événement est déclenché, le code s'exécute automatiquement. Par exemple, un objet serveur de courriel  peut déclencher un événement `messageSent` (message envoyé) quand il réussit à envoyer un message. Si vous voulez conserver une trace des messages dont l'envoi a réussi, vous pouvez simplement attacher le code de conservation de la trace à l'événement  `messageSent`.

Yii introduit une classe de base appelée [[yii\base\Component]] pour prendre en charge les événements. Si une classe a besoin de déclencher des événements, elle doit êtendre la classe [[yii\base\Component]], ou une de ses classes filles. 


Gestionnaires d'événements <span id="event-handlers"></span>
--------------------------

Un gestionnaire d'événement est une  [fonction de rappel PHP](http://www.php.net/manual/en/language.types.callable.php) qui est exécutée lorsque l'événement à laquelle elle est attachée est déclenché. Vous pouvez utiliser n'importe laquelle des fonctions de rappel suivantes :

- une fonction PHP globale spécifiée sous forme de chaîne de caractères (sans les parenthèses) p. ex., `'trim'` ;
- une méthode d'objet spécifiée sous forme de tableau constitué d'un nom d'objet et d'un nom de méthode sous forme de chaîne de caractères (sans les parentèses), p. ex., `[$object, 'methodName']`;
- une méthode d'une classe statique spécifiée sous forme de tableau constitué d'un nom de classe et d'un nom de méthode sous forme de chaîne de caractères (sans les parenthèses), p. ex., `['ClassName', 'methodName']`;
- une fonction anonyme p. ex., `function ($event) { ... }`.

La signature d'un gestionnaire d'événement est :

```php
function ($event) {
    // $event est un objet de la classe  yii\base\Event ou des ses classes filles
}
```

Via le paramètre `$event`, un gestionnaire d'événement peut obtenir l'information suivante sur l'événement qui vient de se produire : 

- le [[yii\base\Event::name|nom de l'événement]];
- l'[[yii\base\Event::sender|émetteur de l'événement]]: l'objet dont la méthode  `trigger()` a été appelée ;
- les [[yii\base\Event::data|données personnalisées]]: les données fournies lorsque le gestionnaire d'événement est attaché (les explications arrivent bientôt).


Attacher des gestionnaires d'événements <span id="attaching-event-handlers"></span>
---------------------------------------

Vous pouvez attacher un gestionnaire d'événement en appelant la méthode [[yii\base\Component::on()]] du composant. Par exemple :

```php
$foo = new Foo;

// le gestionnaire est une fonction globale
$foo->on(Foo::EVENT_HELLO, 'function_name');

// le gestionnaire est une méthode d'objet
$foo->on(Foo::EVENT_HELLO, [$object, 'methodName']);

// le gestionnaire est une méthode d'une classe statique
$foo->on(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);

// le gestionnaire est un fonction anonyme
$foo->on(Foo::EVENT_HELLO, function ($event) {
    // event handling logic
});
```

Vous pouvez aussi attacher des gestionnaires d'événements via les [configurations](concept-configurations.md). Pour plus de détails, reportez-vous à la section [Configurations](concept-configurations.md#configuration-format).


Losque vous attachez un gestionnaire d'événement, vous pouvez fournir des données additionnelles telles que le troisième paramètre de [[yii\base\Component::on()]]. Les données sont rendues disponibles au gestionnaire lorsque l'événement est déclenché et que le gestionnaire est appelé. Par exemple :

```php
// Le code suivant affiche  "abc" lorsque l'événement est déclenché
// parce que  $event->data contient les données passées en tant que troisième argument à la méthode "on"
$foo->on(Foo::EVENT_HELLO, 'function_name', 'abc');

function function_name($event) {
    echo $event->data;
}
```

Ordre des gestionnaires d'événements
------------------------------------

Vous pouvez attacher un ou plusieurs gestionnaires à un seul événement. Lorsqu'un événement est déclenché, les gestionnaires attachés sont appelés dans l'ordre dans lequel ils ont été attachés à l'événement. Si un gestionnaire a besoin d'arrêter l'appel des gestionnaires qui viennent après lui, il doit définir la propriété [[yii\base\Event::handled (géré)]] du paramètre `$event` parameter à `true`:

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    $event->handled = true;
});
```

Par défaut, un gestionnaire nouvellement attaché est ajouté à la file des gestionnaires de l'événement. En conséquence, le gestionnaire est appelé en dernier lorsque l'événement est déclenché. Pour insérer un événement nouvellement attaché en tête de file pour qu'il soit appelé le premier, vous devez appeler [[yii\base\Component::on()]], en lui passant `false` pour le quatrième paramètre `$append`:

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    // ...
}, $data, false);
```

Déclenchement des événements <span id="triggering-events"></span>
----------------------------

Les événements sont déclenchés en appelant la méthode [[yii\base\Component::trigger()]]. La méthode requiert un  *nom d'événement* et, en option, un objet événement qui décrit les paramètres à passer aux gestionnaires de cet événement. Par exemple :

```php
namespace app\components;

use yii\base\Component;
use yii\base\Event;

class Foo extends Component
{
    const EVENT_HELLO = 'hello';

    public function bar()
    {
        $this->trigger(self::EVENT_HELLO);
    }
}
```
Avec le code précédent, tout appel à  `bar()` déclenche un événement nommé `hello`.

> Tip: il est recommandé d'utiliser des constantes de classe pour représenter les noms d'événement. Dans l'exemple qui précède, la constante `EVENT_HELLO` représente l'événement `hello`. Cette approche procure trois avantages. Primo, elle évite les erreurs de frappe. Secondo, elle permet aux événements d'être reconnus par le mécanisme d'auto-complètement des EDI. Tertio, vous pouvez dire quels événements sont pris en charge par une classe en vérifiant la déclaration de ses constantes. 

Parfois, lors du déclenchement d'un événement, vous désirez passer des informations additionnelles aux gestionnaires de cet événement. Par exemple, un serveur de courriels peut souhaiter passer les informations sur le message aux gestionnaires de l'événement `messageSent` pour que ces derniers soient informés de certaines particularités des messages envoyés. Pour ce faire, vous pouvez fournir un objet événement comme deuxième paramètre de la méthode [[yii\base\Component::trigger()]]. L'objet événement doit simplement être une instance de la classe [[yii\base\Event]] ou d'une de ses classes filles. Par exemple :

```php
namespace app\components;

use yii\base\Component;
use yii\base\Event;

class MessageEvent extends Event
{
    public $message;
}

class Mailer extends Component
{
    const EVENT_MESSAGE_SENT = 'messageSent';

    public function send($message)
    {
        // ...sending $message...

        $event = new MessageEvent;
        $event->message = $message;
        $this->trigger(self::EVENT_MESSAGE_SENT, $event);
    }
}
```

Lorsque la méthode [[yii\base\Component::trigger()]] est appelée, elle appelle tous les gestionnaires attachés à l'événement nommé. 


Détacher des gestionnaires d'événements <span id="detaching-event-handlers"></span>
---------------------------------------

Pour détacher un gestionnaire d'un événement, appelez la méthode [[yii\base\Component::off()]]. Par exemple :

```php
// le gestionnaire est une fonction globale
$foo->off(Foo::EVENT_HELLO, 'function_name');

// le gestionnaire est une méthode d'objet
$foo->off(Foo::EVENT_HELLO, [$object, 'methodName']);

// le gestionnaire est une méthode d'une classe statique 
$foo->off(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);

// le gestionnaire est une fonction anonyme
$foo->off(Foo::EVENT_HELLO, $anonymousFunction);
```

Notez qu'en général, vous ne devez pas essayer de détacher une fonction anonyme sauf si vous l'avez stokée quelque part lorsque vous l'avez attachée à un événement. Dans l'exemple ci-dessus, on suppose que la fonctions anonyme est stockée dans une variable nommée  `$anonymousFunction`.

Pour détacher *tous* les gestionnaires d'un événement, appelez simplement la méthode [[yii\base\Component::off()]] sans le deuxième paramètre :

```php
$foo->off(Foo::EVENT_HELLO);
```


Gestionnaire d'événement au niveau de la classe <span id="class-level-event-handlers"></span>
-----------------------------------------------

Les sections précédent décrivent comment attacher un gestionnaire à un événement au *niveau d'une instance*. Parfois, vous désirez répondre à un événement déclenché par *chacune des* instances d'une classe plutôt que par une instance spécifique. Au lieu d'attacher l'événement à chacune des instances, vous pouvez attacher le gestionnaire au *niveau de la classe* en appelant la méthode statique [[yii\base\Event::on()]].

Par exemple, un objet [Active Record](db-active-record.md) déclenche un événement  [[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]]
à chaque fois qu'il insère un nouvel enregistrement dans la base de données. Afin de suivre les insertions faites par tous les objets [Active Record](db-active-record.md), vous pouvez utiliser le code suivant :

```php
use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;

Event::on(ActiveRecord::class, ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
    Yii::trace(get_class($event->sender) . ' is inserted');
});
```

Le gestionnaire d'événement est invoqué à chaque fois qu'une instance de la classe [[yii\db\ActiveRecord|ActiveRecord]], ou d'une de ses classes filles, déclenche l'événement [[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]]. Dans le gestionnaire, vous pouvez obtenir l'objet qui a déclenché l'événement via `$event->sender`.

Losqu'un objet déclenche un événement, il commence par appeler les gestionnaires attachés au niveau de l'instance, puis les gestionnaires attachés au niveau de la classe. 

Vous pouvez déclencher un événement au *niveau de la classe* en appelant la méthode statique [[yii\base\Event::trigger()]]. Un événement déclenché au niveau de la classe n'est associé à aucun objet en particulier. En conséquence, il provoque l'appel des gestionnaires attachés au niveau de la classe seulement. Par exemple :

```php
use yii\base\Event;

Event::on(Foo::class, Foo::EVENT_HELLO, function ($event) {
    var_dump($event->sender);  // displays "null"
});

Event::trigger(Foo::class, Foo::EVENT_HELLO);
```

Notez que, dans ce cas, `$event->sender` fait référence au nom de la classe qui a déclenché l'événement plutôt qu'à une instance de classe.

> Note: comme les gestionnaires attachés au niveau de la classe répondent aux événements déclenchés par n'importe quelle instance de cette classe, ou de ses classes filles, vous devez utiliser cette fonctionnalité avec précaution, en particulier si la classe est une classe de bas niveau comme la classe [[yii\base\BaseObject]].

Pour détacher un gestionnaire attaché au niveau de la classe, appelez  [[yii\base\Event::off()]]. Par exemple :

```php
// détache $handler
Event::off(Foo::class, Foo::EVENT_HELLO, $handler);

// détache tous les gestionnaires de Foo::EVENT_HELLO
Event::off(Foo::class, Foo::EVENT_HELLO);
```


Événement utilisant des interfaces <span id="interface-level-event-handlers"></span>
----------------------------------

Il y a encore une manière plus abstraite d'utiliser les événements. Vous pouvez créer une interface séparée pour un événement particulier et l'implémenter dans des classes dans lesquelles vous en avez besoin. 

Par exemple, vous pouvez créer l'interface suivante :

```php
namespace app\interfaces;

interface DanceEventInterface
{
    const EVENT_DANCE = 'dance';
}
```

Et ajouter deux classes qui l'implémente :

```php
class Dog extends Component implements DanceEventInterface
{
    public function meetBuddy()
    {
        echo "Woof!";
        $this->trigger(DanceEventInterface::EVENT_DANCE);
    }
}

class Developer extends Component implements DanceEventInterface
{
    public function testsPassed()
    {
        echo "Yay!";
        $this->trigger(DanceEventInterface::EVENT_DANCE);
    }
}
```

Pour gérer l'évenement `EVENT_DANCE` déclenché par n'importe laquelle de ces classes, appelez [[yii\base\Event::on()|Event::on()]] et passez-lui le nom de l'interface comme premier argument :

```php
Event::on(DanceEventInterface::class, DanceEventInterface::EVENT_DANCE, function ($event) {
    Yii::debug(get_class($event->sender) . ' danse'); // enregistrer le message disant que le chien ou le développeur danse.
})
```

Vous pouvez déclencher l'événement de ces classes :

```php
// trigger event for Dog class
Event::trigger(Dog::class, DanceEventInterface::EVENT_DANCE);

// trigger event for Developer class
Event::trigger(Developer::class, DanceEventInterface::EVENT_DANCE);
```

Notez bien que vous ne pouvez pas déclencher l'événement de toutes les classes qui implémentent l'interface :,

```php
// NE FONCTIONNE PAS
Event::trigger(DanceEventInterface::class, DanceEventInterface::EVENT_DANCE); // error
```

Pour détacher le gestionnaire d'événement, appelez [[yii\base\Event::off()|Event::off()]]. Par exemple :

```php
// détache $handler
Event::off(DanceEventInterface::class, DanceEventInterface::EVENT_DANCE, $handler);

// détache tous les gestionnaires de DanceEventInterface::EVENT_DANCE
Event::off(DanceEventInterface::class, DanceEventInterface::EVENT_DANCE);
```


Événements globaux <span id="global-events"></span>
------------------

Yii prend en charge ce qu'on appelle les *événements globaux*, qui est une astuce basée sur le mécanisme des événements décrit ci-dessus. L'événement global requiert un singleton accessible globalement tel que l'instance de l'[application](structure-applications.md) elle-même.

Pour créer l'événement global, un émetteur d'événement appelle la méthode `trigger()`  du singleton pour déclencher l'événement au lieu d'appeler la méthode `trigger()` propre à l'émetteur. De façon similaire, les gestionnaires d'événement sont attachés à l'événement sur le singleton. Par exemple :

```php
use Yii;
use yii\base\Event;
use app\components\Foo;

Yii::$app->on('bar', function ($event) {
    echo get_class($event->sender);  // affiche "app\components\Foo"
});

Yii::$app->trigger('bar', new Event(['sender' => new Foo]));
```

Un avantage de l'utilisation d'événement globaux est que vous n'avez pas besoin d'un objet lorsque vous attachez un gestionnaire à l'événement qui est déclenché par l'objet. Au lieu de cela, vous attachez le gestionnaire et déclenchez l'événement via le singleton (p. ex. l'instance d'application). 

Néanmoins, parce que l'espace de noms des événements globaux est partagé par toutes les parties, vous devez nommer les événements globaux avec prudence, par exemple en introduisant une sorte d'espace de noms (p. ex. "frontend.mail.sent", "backend.mail.sent").

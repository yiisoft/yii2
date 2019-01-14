События
======

События - это механизм, внедряющий элементы собственного кода в существующий код в определенные моменты его исполнения. К событию можно присоединить собственный код, который будет выполняться автоматически при срабатывании события. Например, объект, отвечающий за почту, может инициировать событие `messageSent` при успешной отправке сообщения. При этом если нужно отслеживать успешно отправленные сообщения, достаточно присоединить соответствующий код к событию `messageSent`.

Для работы с событиями Yii использует базовый класс [[yii\base\Component]]. Если класс должен инициировать события, его нужно унаследовать от [[yii\base\Component]] или потомка этого класса.


Обработчики событий <span id="event-handlers"></span>
--------------

Обработчик события - это [callback-функция PHP](http://www.php.net/manual/ru/language.types.callable.php), которая выполняется при срабатывании события, к которому она присоединена. Можно использовать следующие callback-функции:

- глобальную функцию PHP, указав строку с именем функции (без скобок), например, `'trim'`;
- метод объекта, указав массив, содержащий строки с именами объекта и метода (без скобок), например, `[$object, 'methodName']`;
- статический метод класса, указав массив, содержащий строки с именами класса и метода (без скобок), например, `['ClassName', 'methodName']`;
- анонимную функцию, например, `function ($event) { ... }`.

Сигнатура обработчика события выглядит следующим образом:

```php
function ($event) {
    // $event - это объект класса yii\base\Event или его потомка
}
```

Через параметр `$event` обработчик события может получить следующую информацию о возникшем событии:

- [[yii\base\Event::name|event name]]
- [[yii\base\Event::sender|event sender]]: объект, метод `trigger()` которого был вызван
- [[yii\base\Event::data|custom data]]: данные, которые были предоставлены во время присоединения обработчика события (будет описано ниже)


Присоединение обработчиков событий <span id="attaching-event-handlers"></span>
------------------------

Обработчики события присоединяются с помощью метода [[yii\base\Component::on()]]. Например:

```php
$foo = new Foo;

// обработчик - глобальная функция
$foo->on(Foo::EVENT_HELLO, 'function_name');

// обработчик - метод объекта
$foo->on(Foo::EVENT_HELLO, [$object, 'methodName']);

// обработчик - статический метод класса
$foo->on(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);

// обработчик - анонимная функция
$foo->on(Foo::EVENT_HELLO, function ($event) {
    // логика обработки события
});
```

Также обработчики событий можно присоединять с помощью [конфигураций](concept-configurations.md). Дополнительную информацию см. в разделе [Конфигурации](concept-configurations.md#configuration-format).


Присоединяя обработчик события, можно передать дополнительные данные с помощью третьего параметра метода [[yii\base\Component::on()]]. Эти данные будут доступны в обработчике, когда сработает событие и он будет вызван. Например:

```php
// Следующий код выводит "abc" при срабатывании события
// так как в $event->data содержатся данные, которые переданы в качестве третьего аргумента метода "on"
$foo->on(Foo::EVENT_HELLO, 'function_name', 'abc');

function function_name($event) {
    echo $event->data;
}
```

Порядок обработки событий
-------------------

К одному событию можно присоединить несколько обработчиков. При срабатывании события обработчики будут вызываться в том порядке, в котором они присоединялись к событию. Чтобы запретить в обработчике вызов всех следующих за ним обработчиков, необходимо установить свойство [[yii\base\Event::handled]] параметра `$event` в `true`:

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    $event->handled = true;
});
```

По умолчанию, новые обработчики присоединяются к концу очереди обработчиков, уже существующей у события.
В результате при срабатывании события обработчик выполнится последним.
Чтобы обработчик присоединился к началу очереди и запускался первым, при вызове [[yii\base\Component::on()]] в качестве четвертого параметра `$append` следует передать `false`:

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    // ...
}, $data, false);
```

Инициирование событий <span id="triggering-events"></span>
-----------------

События инициируются при вызове метода [[yii\base\Component::trigger()]]. Методу нужно передать *имя события*, а при необходимости - объект события, в котором описываются параметры, передаваемые обработчикам событий. Например:

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

Показанный выше код инициирует событие `hello` при каждом вызове метода `bar()`.

> Tip: Желательно для обозначения имен событий использовать константы класса. В предыдущем примере константа `EVENT_HELLO` обозначает событие `hello`. У такого подхода три преимущества. Во-первых, исключаются опечатки. Во-вторых, для событий работает автозавершение в различных средах разработки. В-третьих, чтобы узнать, какие события поддерживаются классом, достаточно проверить константы, объявленные в нем.

Иногда при инициировании события может понадобиться передать его обработчику дополнительную информацию. Например, объекту, отвечающему за почту, может понадобиться передать обработчику события `messageSent` определенные данные, раскрывающие смысл отправленных почтовых сообщений. Для этого в качестве второго параметра методу [[yii\base\Component::trigger()]] передается объект события. Объект события должен быть экземпляром класса [[yii\base\Event]] или его потомка. Например:

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
        // ...отправка $message...

        $event = new MessageEvent;
        $event->message = $message;
        $this->trigger(self::EVENT_MESSAGE_SENT, $event);
    }
}
```

При вызове метода [[yii\base\Component::trigger()]] будут вызваны все обработчики, присоединенные к указанному событию.


Отсоединение обработчиков событий <span id="detaching-event-handlers"></span>
------------------------

Для отсоединения обработчика от события используется метод [[yii\base\Component::off()]]. Например:

```php
// обработчик - глобальная функция
$foo->off(Foo::EVENT_HELLO, 'function_name');

// обработчик - метод объекта
$foo->off(Foo::EVENT_HELLO, [$object, 'methodName']);

// обработчик - статический метод класса
$foo->off(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);

// обработчик - анонимная функция
$foo->off(Foo::EVENT_HELLO, $anonymousFunction);
```

Учтите, что в общем случае отсоединять обработчики - анонимные функции можно только если они где-то сохраняются в момент присоединения к событию. В предыдущем примере предполагается, что анонимная функция сохранена в переменной `$anonymousFunction`.

Чтобы отсоединить ВСЕ обработчики от события, достаточно вызвать [[yii\base\Component::off()]] без второго параметра:

```php
$foo->off(Foo::EVENT_HELLO);
```


Обработчики событий на уровне класса <span id="class-level-event-handlers"></span>
--------------------------

Во всех предыдущих примерах мы рассматривали присоединение событий *на уровне экземпляров*. Есть случаи, когда необходимо обрабатывать события, которые инициируются *любым* экземпляром класса, а не только конкретным экземпляром. В таком случае присоединять обработчик события к каждому экземпляру класса не нужно. Достаточно присоединить обработчик *на уровне класса*, вызвав статический метод [[yii\base\Event::on()]].

Например, объект [Active Record](db-active-record.md) инициирует событие [[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] после добавления в базу данных новой записи. Чтобы отслеживать записи, добавленные в базу данных *каждым* объектом [Active Record](db-active-record.md), можно использовать следующий код:

```php
use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;

Event::on(ActiveRecord::className(), ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
    Yii::debug(get_class($event->sender) . ' добавлен');
});
```

Обработчик будет вызван при срабатывании события [[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] в экземплярах класса [[yii\db\ActiveRecord|ActiveRecord]] или его потомков. В обработчике можно получить доступ к объекту, который инициировал событие, с помощью свойства `$event->sender`.

При срабатывании события будут в первую очередь вызваны обработчики на уровне экземпляра, а затем - обработчики на уровне класса.

Инициировать событие *на уровне класса* можно с помощью статического метода [[yii\base\Event::trigger()]]. Событие на уровне класса не связано ни с одним конкретным объектом. В таком случае будут вызваны только обработчики события на уровне класса. Например:

```php
use yii\base\Event;

Event::on(Foo::className(), Foo::EVENT_HELLO, function ($event) {
    var_dump($event->sender);  // выводит "null"
});

Event::trigger(Foo::className(), Foo::EVENT_HELLO);
```

Обратите внимание, что в данном случае `$event->sender` имеет значение `null` вместо экзепляра класса, который инициировал событие.

> Note: Поскольку обработчики на уровне класса отвечают на события, инициируемые всеми экземплярами этого класса и всех его потомков, их следует использовать с осторожностью, особенно в случае базовых классов низкого уровня, таких как [[yii\base\BaseObject]].

Отсоединить обработчик события на уровне класса можно с помощью метода [[yii\base\Event::off()]]. Например:

```php
// отсоединение $handler
Event::off(Foo::className(), Foo::EVENT_HELLO, $handler);

// отсоединяются все обработчики Foo::EVENT_HELLO
Event::off(Foo::className(), Foo::EVENT_HELLO);
```

Обработчики событий на уровне интерфейсов <span id="interface-level-event-handlers"></span>
-------------

Существует еще более абстрактный способ обработки событий.
Вы можете создать отдельный интерфейс для общего события и реализовать его в классах, где это необходимо.

Например, создадим следующий интерфейс:

```php
namespace app\interfaces;

interface DanceEventInterface
{
    const EVENT_DANCE = 'dance';
}
```

И два класса, которые его реализуют:

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

Для обработки события `EVENT_DANCE`, инициализированного любым из этих классов,
вызовите [[yii\base\Event::on()|Event:on()]], передав ему в качестве первого параметра имя интерфейса.

```php
Event::on('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE, function ($event) {
    Yii::debug(get_class($event->sender) . ' just danced'); // Оставит запись в журнале о том, что кто-то танцевал
});
```

Вы можете также инициализировать эти события:

```php
// trigger event for Dog class
Event::trigger(Dog::className(), DanceEventInterface::EVENT_DANCE);

// trigger event for Developer class
Event::trigger(Developer::className(), DanceEventInterface::EVENT_DANCE);
```

Однако, невозможно инициализировать событие во всех классах, которые реализуют интерфейс:

```php
// НЕ БУДЕТ РАБОТАТЬ
Event::trigger('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE);
```

Отсоединить обработчик события можно с помощью метода [[yii\base\Event::off()|Event::off()]]. Например:

```php
// отсоединяет $handler
Event::off('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE, $handler);

// отсоединяются все обработчики DanceEventInterface::EVENT_DANCE
Event::off('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE);
```

Глобальные события <span id="global-events"></span>
-------------

Yii поддерживает так называемые *глобальные события*, которые на самом деле основаны на нестандартном использовании описанного выше механизма событий. Для глобальных событий нужен глобально доступный объект-синглтон, например, экземпляр приложения - [application](structure-applications.md).

Чтобы создать глобальное событие, отправитель сообщения вызывает метод `trigger()` синглтона, а не свой собственный метод `trigger()`. Аналогичным образом обработчики события также присоединяются к событиям синглтона. Например:

```php
use Yii;
use yii\base\Event;
use app\components\Foo;

Yii::$app->on('bar', function ($event) {
    echo get_class($event->sender);  // выводит "app\components\Foo"
});

Yii::$app->trigger('bar', new Event(['sender' => new Foo]));
```

Преимущество глобальных событий в том, что им не нужен объект, к событию которого бы присоединялся обработчик и объект, с помощью которого бы это событие инициировалось. Вместо этого и для присоединения обработчика, и для инициирования события используется синглтон (например, экземпляр приложения).

Тем не менее, так как пространство имен глобальных событий едино для всего приложения, их имена нельзя назначать бездумно. Например, полезными могут быть искусственные пространства имен ("frontend.mail.sent", "backend.mail.sent").

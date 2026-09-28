Eventos
=======

Eventos permitem que você injete código personalizado dentro de outo código existente em determinados pontos de execução. Você pode anexar o código personalizado a um evento de modo que ao acionar o evento, o código é executado automaticamente. Por exemplo, um objeto de e-mail pode disparar um evento `messageSent` quando o envio da mensagem for bem sucedida. Se você quiser acompanhar as mensagens que são enviadas com sucesso, você poderia então simplesmente anexar o código de acompanhamento ao evento `messageSent`.
O Yii disponibiliza uma classe base chamada [[yii\base\Component]] para dar suporte aos eventos.
Se sua classe precisar disparar eventos, ela deverá estender de [[yii\base\Component]], ou de uma classe-filha.


Manipuladores de Evento <span id="event-handlers"></span>
--------------

Um manipulador de evento é uma função [Callback do PHP] (https://www.php.net/manual/pt_BR/language.types.callable.php) que é executada quando o evento é disparado. Você pode usar qualquer um dos seguintes callbacks:
- uma função global do PHP especificada como uma string (sem parênteses), por exemplo, `'trim'`;
- Um método do objeto especificado como um array, informando o objeto e um nome do método como uma string (sem parênteses), por exemplo `[$object, 'methodName']`;
- Um método estático da classe especificado como um array informando o nome da classe e nome do método como string (sem parênteses), por exemplo, `['ClassName', 'methodName']`; 
- Uma função anônima, por exemplo, `function ($event) { ... }`.

A assinatura de um manipulador de eventos é a seguinte:

```php
function ($event) {
   // $event is an object of yii\base\Event or a child class
}
```

Através do parâmetro `$event`, um manipulador de evento pode receber as seguintes informações sobre o evento que ocorreu:

- [[yii\base\Event::name|nome do evento]]
- [[yii\base\Event::sender|objeto chamador]]: o objeto cujo método `trigger()` foi chamado
- [[yii\base\Event::data|dados personalizados]]: os dados que são fornecidos ao anexar o manipulador de eventos (a ser explicado a seguir)


Anexando manipuladores de eventos <span id="attaching-event-handlers"></span>
------------------------

Você pode anexar um manipulador para um evento, chamando o método [[yii\base\Component::on()]]. Por exemplo:

```php
$foo = new Foo;

// esse manipulador é uma função global
$foo->on(Foo::EVENT_HELLO, 'function_name');

// esse manipulador é um método de objeto
$foo->on(Foo::EVENT_HELLO, [$object, 'methodName']);

// esse manipulador é um método estático da classe
$foo->on(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);

// esse manipulador é uma função anônima
$foo->on(Foo::EVENT_HELLO, function ($event) {
   // Código ...
});
```

Você também pode anexar manipuladores de eventos por meio de [configurações](concept-configurations.md). Para mais detalhes, consulte a seção [Configurações](concept-configurations.md#configuration-format).

Ao anexar um manipulador de eventos, você pode fornecer dados adicionais no terceiro parâmetro do método [[yii\base\Component::on()]].
Os dados serão disponibilizados para o manipulador quando o evento for disparado e o manipulador chamado. Por exemplo:

```php
// O código a seguir mostrará "abc" quando o evento for disparado
// porque $event->data contêm os dados passados no terceiro parâmetro do "on"
$foo->on(Foo::EVENT_HELLO, 'function_name', 'abc');

function function_name($event) {
   echo $event->data;
}
```


Ordem dos Manipuladores de Eventos
-------------------

Você pode anexar um ou mais manipuladores para um único evento. Quando o evento é disparado, os manipuladores anexados serão chamados na ordem em que eles foram anexados ao evento. Se o manipulador precisar interromper os eventos subsequentes, pode definir a propriedade [[yii\base\Event::handled]] do parâmetro `$event` para *true*:

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
   $event->handled = true;
});
```

Por padrão, um novo manipulador é anexado a fila de manipuladores existente para o evento.

Como resultado, o manipulador será chamado por último quando o evento for disparado.

Para inserir um novo manipulador de evento no início da fila de modo a ser chamado primeiro, você pode chamar o método [[yii\base\Component::on()]], passando *false* para o quarto parâmetro `$append`:

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
   // ...
}, $data, false);
```


Disparando Eventos <span id="triggering-events"></span>
-----------------

Os eventos são disparados chamando o método [[yii\base\Component::trigger()]]. Este método requer um *nome de evento*, e, opcionalmente, um objeto de evento que descreve os parâmetros a serem passados para os manipuladores de eventos. Por exemplo:

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

Com o código acima, todas as chamadas para `bar ()` irão disparar um evento chamado `hello`.

> Dica: Recomenda-se usar constantes de classe para representar nomes de eventos. No exemplo acima, a constante `EVENT_HELLO` representa o evento `hello`. Esta abordagem tem três benefícios. Primeiro, previne erros de digitação. Segundo, pode fazer o evento se tornar reconhecível para recursos de *auto-complete* de IDEs. Terceiro, você pode especificar quais eventos são suportados em uma classe, basta verificar suas declarações de constantes.

Às vezes, quando um evento é disparado você pode querer passar junto informações adicionais para os manipuladores de eventos. Por exemplo, um objeto de e-mail pode querer passar uma informação para o manipulador do evento `messageSent` de modo que os manipuladores podem conhecer os detalhes das mensagens enviadas. Para fazer isso, você pode fornecer um objeto de evento como o segundo parâmetro para o método  [[yii\base\Component::trigger()]]. Este objeto precisa ser uma instância da classe  [[yii\base\Event]] ou de uma classe filha. Por exemplo:

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

Quando o método [[yii\base\Component::trigger()]] é chamado, ele chamará todos os manipuladores ligados ao evento passado.


Desvinculando manipuladores de eventos <span id="detaching-event-handlers"></span>
------------------------

Para retirar um manipulador de um evento, chame o método [[yii\base\Component::off()]]. Por Exemplo:

```php
// o manipulador é uma função global
$foo->off(Foo::EVENT_HELLO, 'function_name');

// o manipulador é um método de objeto
$foo->off(Foo::EVENT_HELLO, [$object, 'methodName']);

// o manipulador é um método de estático da Classe
$foo->off(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);

// o manipulador é uma função anônima
$foo->off(Foo::EVENT_HELLO, $anonymousFunction);
```

Note que, em geral, você não deve tentar desvincular uma função anônima, a menos que você guarde em algum lugar quando ela for ligada ao evento. No exemplo acima, é assumido que a função anónima é armazenada em uma variável `$anonymousFunction`.

Para desvincular todos os manipuladores de um evento, simplesmente chame [[yii\base\Component::off()]] sem o segundo parâmetro:

```php
$foo->off(Foo::EVENT_HELLO);
```


Manipuladores de Eventos de Classe <span id="class-level-event-handlers"></span>
--------------------------

As subseções acima descreveram como anexar um manipulador para um evento a *nível de instância* (objeto).
Às vezes, você pode querer responder a um evento acionado por *todas* as instâncias da classe em vez de apenas uma instância específica. Em vez de anexar um manipulador de evento em todas as instâncias, você pode anexar o manipulador a *nível da classe* chamando o método estático [[yii\base\Event::on()]].

Por exemplo, um objeto [Active Record](db-active-record.md) irá disparar um evento [[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] sempre que inserir um novo registro no banco de dados. A fim de acompanhar as inserções feitas por *cada* objeto [Active Record](db-active-record.md), você pode usar o seguinte código:

```php
use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;

Event::on(ActiveRecord::class, ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
   Yii::debug(get_class($event->sender) . ' is inserted');
});
```

O manipulador de evento será invocado sempre que uma instância de [[yii\db\ActiveRecord|ActiveRecord]], ou uma de suas classes filhas, disparar o evento [[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]]. No manipulador, você pode obter o objeto que disparou o evento através de `$event->sender`.

Quando um objecto dispara um evento, ele irá primeiro chamar manipuladores de nível de instância, seguido pelos manipuladores de nível de classe.

Você pode disparar um evento de *nível de classe* chamando o método estático [[yii\base\Event::trigger()]]. Um evento de nível de classe não está associado com um objeto particular. Como resultado, ele fará a chamada dos manipuladores de eventos apenas a nível da classe. Por exemplo:

```php
use yii\base\Event;

Event::on(Foo::class, Foo::EVENT_HELLO, function ($event) {
   var_dump($event->sender);  // displays "null"
});

Event::trigger(Foo::class, Foo::EVENT_HELLO);
```

Note que, neste caso, `$event->sender` refere-se ao nome da classe acionando o evento em vez de uma instância do objeto.

> Observação: Já que um manipulador de nível de classe vai responder a um evento acionado por qualquer instância dessa classe, ou qualquer classe filha, você deve usá-lo com cuidado, especialmente se a classe é uma classe base de baixo nível, tal como [[yii\base\BaseObject]].

Para desvincular um manipulador de evento de nível de classe, chame [[yii\base\Event::off()]]. Por exemplo:

```php
// desvincula $handler
Event::off(Foo::class, Foo::EVENT_HELLO, $handler);

// Desvincula todos os manipuladores de Foo::EVENT_HELLO
Event::off(Foo::class, Foo::EVENT_HELLO);
```


Eventos Globais <span id="global-events"></span>
-------------

O Yii suporta o assim chamado *evento global*, que na verdade é um truque com base no mecanismo de eventos descrito acima.
O evento global requer um *singleton* acessível globalmente, tal como a própria instância da [aplicação](structure-applications.md).

Para criar o evento global, um evento *remetente* chama o método singleton `trigger()` para disparar o evento, em vez de chamar o método `trigger()` do *remetente* . Da mesma forma, os manipuladores de eventos são anexados ao evento no *singleton* . Por exemplo:

```php
use Yii;
use yii\base\Event;
use app\components\Foo;

Yii::$app->on('bar', function ($event) {
   echo get_class($event->sender);  // Mostra na tela "app\components\Foo"
});

Yii::$app->trigger('bar', new Event(['sender' => new Foo]));
```

A vantagem de usar eventos globais é que você não precisa de um objeto ao anexar um manipulador para o evento que será acionado pelo objeto. Em vez disso, a inclusão do manipulador e o evento acionado são ambos feitos através do *singleton*. (Por exemplo, uma instância da aplicação). Contudo, já que o namespace dos eventos globais é compartilhado com todos, você deve nomear os eventos globais sabiamente, tais como a introdução de algum tipo de namespace (por exemplo. "frontend.mail.sent", "backend.mail.sent").

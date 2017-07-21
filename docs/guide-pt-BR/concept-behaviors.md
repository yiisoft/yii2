Behaviors (Comportamentos)
=========

Behaviors são instâncias de  [[yii\base\Behavior]], ou de uma classe-filha, também conhecido como [mixins](http://en.wikipedia.org/wiki/Mixin), permite melhorar a funcionalidade de uma classe [[yii\base\Component|componente]]  existente sem a necessidade de mudar a herança dela.
Anexar um behavior a um componente "introduz" os métodos e propriedades do behavior dentro do componente, tornando esses métodos e propriedades acessíveis como se estes fossem definidos na própria classe do componente. Além disso, um behavior pode responder a um [evento](concept-events.md) disparado pelo componente, o que permite a customização do código normal.


Definindo Behaviors <span id="defining-behaviors"></span>
------------------

Para definir um behavior, crie uma classe estendendo [[yii\base\Behavior]], ou de uma classe-filha. Por exemplo:

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

O código acima define a classe behavior `app\components\MyBehavior`, com duas propriedades --`prop1` e `prop2`-- e um método `foo()`. Note que a propriedade `prop2`
É definida através do método getter `getProp2()` e setter `setProp2()`. Isto é possível porque [[yii\base\Behavior]] estende de [[yii\base\BaseObject]] e portanto suporta definição de propriedades através de [propriedades](concept-properties.md) getters e setters.

Como essa classe é um behavior, quando ela está anexada a um componente, então este componente terá as propriedades `prop1` e `prop2` e o método `foo()`.

> Dica: Em um behavior, você pode acessar o componente que o behavior está anexado através da propriedade [[yii\base\Behavior::owner]].


Manuseando Eventos de Componente
------------------

Se um behavior precisar responder a eventos disparados pelo componente ao qual está ligado, este deve sobrescrever o método [[yii\base\Behavior::events()]]. Por exemplo:

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

O método [[yii\base\Behavior::events()|events()]] deve retornar uma lista de eventos e seus manipuladores correspondentes.
O exemplo acima declara o evento [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] existente e define seu manipulador, `beforeValidate()`. Ao especificar um manipulador de evento, você pode utilizar um dos seguintes formatos:

* uma string que refere-se ao nome do método da classe behavior, como o exemplo acima
* um array com o nome do objeto ou classe, e um nome de método como string (sem parênteses), por exemplo, `[$object, 'methodName']`;
* uma função anônima

A assinatura de um manipulador de eventos deve ser como o exemplo abaixo, onde `$event` refere-se ao parâmetro do evento. Por favor, consulte a seção [Eventos](concept-events.md) para mais detalhes sobre eventos.

```php
function ($event) {
}
```


Anexando Behaviors (Comportamentos) <span id="attaching-behaviors"></span>
-------------------

Você pode anexar um behavior a um [[yii\base\Component|componente]] de forma estática ou dinâmica. Na prática a forma estática é a mais comum.

Para anexar um behavior de forma estática, sobrescreva o método [[yii\base\Component::behaviors()|behaviors()]] da classe componente
para o behavior que está sendo anexado. O método [[yii\base\Component::behaviors()|behaviors()]] deve retornar uma lista de [configurações](concept-configurations.md) de behaviors.
Cada configuração de behavior pode ser tanto um nome da classe behavior ou um array de configuração:

```php
namespace app\models;

use yii\db\ActiveRecord;
use app\components\MyBehavior;

class User extends ActiveRecord
{
   public function behaviors()
   {
       return [
           // behavior anônimo, somente o nome da classe
           MyBehavior::class,

           // behavior nomeado, somente o nome da classe
           'myBehavior2' => MyBehavior::class,

           // behavior anônimo, array de configuração
           [
               'class' => MyBehavior::class,
               'prop1' => 'value1',
               'prop2' => 'value2',
           ],

           // behavior nomeado, array de configuração
           'myBehavior4' => [
               'class' => MyBehavior::class,
               'prop1' => 'value1',
               'prop2' => 'value2',
           ]
       ];
   }
}
```

Você pode associar um nome com um behavior especificando a chave do array correspondente à configuração do behavior. Neste caso o behavior é chamado *behavior nomeado*. No exemplo acima existem dois behaviors nomeados: `myBehavior2` e `myBehavior4`. Se um behavior não está associado a um nome, ele é chamado de *behavior anônimo*.

Para anexar um behavior dinamicamente, execute o método [[yii\base\Component::attachBehavior()]] do componente para o behavior que está sendo anexado:

```php
use app\components\MyBehavior;

// anexando um objeto behavior 
$component->attachBehavior('myBehavior1', new MyBehavior);

// anexando uma classe behavior 
$component->attachBehavior('myBehavior2', MyBehavior::class);

// anexando através de um array de configuração
$component->attachBehavior('myBehavior3', [
   'class' => MyBehavior::class,
   'prop1' => 'value1',
   'prop2' => 'value2',
]);
```

Você pode anexar vários behaviors de uma só vez usando o método [[yii\base\Component::attachBehaviors()]]:

```php
$component->attachBehaviors([
   'myBehavior1' => new MyBehavior,  // um behavior nomeado
   MyBehavior::class,          // um behavior anônimo 
]);
```

Você também pode anexar behaviors através de [configurações](concept-configurations.md) conforme o exemplo a seguir: 

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

Para mais detalhes, por favor, consulte a seção [Configurações](concept-configurations.md#configuration-format).


Usando Behaviors <span id="using-behaviors"></span>
---------------

Para usar um behavior, primeiro este deve ser anexado à um [[yii\base\Component|componente]] conforme as instruções mencionadas anteriormente. Uma vez que o behavior está anexado ao componente, seu uso é simples.

Você pode acessar uma variável *pública* ou uma [propriedade](concept-properties.md) definida por um getter e/ou um setter do behavior através do componente ao qual ele está anexado:

```php
// "prop1" é uma propriedade definida na classe behavior 
echo $component->prop1;
$component->prop1 = $value;
```

Você também pode executar um método *público* do behavior de forma parecida:

```php
// foo() é um método público definido na classe behavior 
$component->foo();
```

Como você pode ver, embora `$component` não defina `prop1` e nem `foo()`, eles podem ser utilizados como se eles fizessem parte  da definição do componente, isto se deve ao behavior anexado.

Se dois behaviors definem a mesma propriedade ou método e ambos são anexados ao mesmo componente, o behavior que for anexado primeiramente ao componente terá precedência quando a propriedade ou método for acessada.

Um behavior pode estar associado a um nome quando ele for anexado a um componente. Sendo esse o caso, você pode acessar o objeto behavior usando o seu nome:

```php
$behavior = $component->getBehavior('myBehavior');
```

Você também pode pegar todos os behaviors anexados a um componente:

```php
$behaviors = $component->getBehaviors();
```


Desvinculando Behaviors (Comportamentos)<span id="detaching-behaviors"></span>
-------------------

Para desvincular um behavior, execute [[yii\base\Component::detachBehavior()]] com o nome associado ao behavior:

```php
$component->detachBehavior('myBehavior1');
```

Você também pode desvincular *todos* os behaviors:

```php
$component->detachBehaviors();
```


Usando `TimestampBehavior` <span id="using-timestamp-behavior"></span>
-------------------------

Para encerrar, vamos dar uma olhada no [[yii\behaviors\TimestampBehavior]]. Este behavior suporta atualização automática dos atributos timestamp de um [[yii\db\ActiveRecord|Active Record]] toda vez que o model (modelo) for salvo (por exemplo, na inserção ou na alteração).

Primeiro, anexe este behavior na classe [[yii\db\ActiveRecord|Active Record]] que você planeja usar:

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
           ],
       ];
   }
}
```

A configuração do behavior acima especifica que o registro ao ser:

* inserido, o behavior deve atribuir o timestamp atual para os atributos `created_at` e `updated_at` 
* atualizado, o behavior deve atribuir o timestamp atual para o atributo `updated_at` 

Com esse código no lugar, se você tem um objeto `User` e tenta salvá-lo, você encontrará seus `created_at` e `updated_at` automaticamente preenchidos com a data e hora atual:

```php
$user = new User;
$user->email = 'test@example.com';
$user->save();
echo $user->created_at;  // mostra a data atual
```

O [[yii\behaviors\TimestampBehavior|TimestampBehavior]] também oferece um método útil
[[yii\behaviors\TimestampBehavior::touch()|touch()]], que irá atribuir a data e hora atual para um atributo específico e o salva no banco de dados:

```php
$user->touch('login_time');
```


Comparando Behaviors com Traits <span id="comparison-with-traits"></span>
----------------------

Apesar de behaviors serem semelhantes a [traits](http://www.php.net/traits) em que ambos "injetam" suas propriedades e métodos para a classe principal, eles diferem em muitos aspectos. Tal como explicado abaixo, ambos têm prós e contras. Eles funcionam mais como complemento um do outro.


### Razões para usar Behaviors <span id="pros-for-behaviors"></span>

Classes Behavior, como classes normais, suportam herança. Traits, por outro lado, pode ser só suporta a programação “copia e cola”. Eles não suportam herança.

Behaviors podem ser anexados e desvinculados a um componente dinamicamente sem necessidade de modificação da classe componente.
Para usar um trait, você deve modificar o código da classe.

Behaviors são configuráveis enquanto traits não são.

Behaviors podem customizar a execução do código do componente respondendo aos seus eventos.
Quando houver nomes conflitantes entre diferentes behaviors anexados ao mesmo componente, o conflito é automaticamente resolvido priorizando o behavior anexado primeiramente ao componente. Nomes conflitantes causados por diferentes traits requer resolução manual renomeando as propriedades ou métodos afetados.


### Razões para usar Traits <span id="pros-for-traits"></span>

Traits são muito mais eficientes do que behaviors, estes são objetos e requerem mais tempo e memória.

IDEs são mais amigáveis com traits por serem nativos do PHP.


Behaviors (Comportamentos)
=========

Behaviors são instâncias de  [[yii\base\Behavior]], ou de uma classe-filha, também conhecido como [mixins](http://en.wikipedia.org/wiki/Mixin), permite melhorar a funcionalidade de uma classe [[yii\base\Component|componente]]  existente sem a necessidade de mudar a herança dela.
Anexar um behavior a um componente "introduz" os métodos e propriedades do behavior dentro do componente, tornando esses métodos e propriedades acessíveis como se estes fossem definidos na própria classe do componente. Além disso, um behavior pode responder a um [evento](concept-events.md) disparado pelo componente, o que permite a customização do código normal.

Definindo Behaviors <span id="defining-behaviors"></span>
------------------

Para definir um behavior , Crie uma classe extendendo de [[yii\base\Behavior]], ou de uma classe-filha. Por exemplo:

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
É definida através do método getter `getProp2()` e setter `setProp2()`. Isto é possível porque [[yii\base\Behavior]] extende de [[yii\base\Object]] e portanto suporta definição de propriedades através de getters e setters [properties](concept-properties.md).

Como essa classe é um behavior , quando ela está anexada a um componente, então este componente terá as propriedades `prop1` e `prop2` e o método `foo()`.

> Dica: Em um behavior , você pode acessar o componente que o behavior está anexado através da propriedade [[yii\base\Behavior::owner]].

Manuseando Eventos de Componente
------------------

Se um behavior precisa responder a eventos disparados pelo componente ao qual está ligado, este deve sobrescrever o método [[yii\base\Behavior::events()]]. Por exemplo:

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
O exemplo acima declara que o evento [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] existe e define seu manipulador, `beforeValidate()`. Ao especificar um manipulador de evento, você pode utilizar um dos seguintes formatos:

* uma string que refere-se ao nome do método da classe behavior , como o exemplo acima
* um array com o nome do objeto ou classe, e um nome de método como string (sem parênteses), por exemplo, `[$object, 'methodName']`;
* uma função anônima

A assinatura de um manipulador de eventos deve ser como o exemplo abaixo, onde `$event` refere-se ao parâmetro do evento. Por favor, consulte a seção [Events](concept-events.md) para mais detalhes sobre eventos.

```php
function ($event) {
}
```

Anexando Behaviors (Comportamentos) <span id="attaching-behaviors"></span>
-------------------

Você pode anexar um behavior a um [[yii\base\Component|component]] de forma estática ou dinâmica. Na prática a forma estática é a mais comum.

Para anexar um behavior de forma estática, sobrescreva o método [[yii\base\Component::behaviors()|behaviors()]] da classe componente
para o behavior que está sendo anexado. O método [[yii\base\Component::behaviors()|behaviors()]] deveria retornar uma lista de behavior [configurations](concept-configurations.md).
Cada configuração de behavior pode ser tanto um nome de classe behavior ou um array de configuração:

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
           MyBehavior::className(),

           // behavior nomeado, somente o nome da classe
           'myBehavior2' => MyBehavior::className(),

           // behavior anônimo, array de configuração
           [
               'class' => MyBehavior::className(),
               'prop1' => 'value1',
               'prop2' => 'value2',
           ],

           // behavior nomeado, array de configuração
           'myBehavior4' => [
               'class' => MyBehavior::className(),
               'prop1' => 'value1',
               'prop2' => 'value2',
           ]
       ];
   }
}
```

Você pode associar um nome com um behavior especificando a chave do array correspondente à configuração do behavior. Neste caso o behavior é chamado *behavior nomeado*. No exemplo acima existem dois behaviors nomeados: `myBehavior2` e `myBehavior4`. Se um behavior não está associado a um nome, ele é chamado de *behavior anônimo*.

Para anexar um behavior dinâmicamente, execute o método [[yii\base\Component::attachBehavior()]] do component para o behavior que está sendo anexado:

```php
use app\components\MyBehavior;

// anexando um objeto behavior 
$component->attachBehavior('myBehavior1', new MyBehavior);

// anexando uma classe behavior 
$component->attachBehavior('myBehavior2', MyBehavior::className());

// anexando através de um array de configuração
$component->attachBehavior('myBehavior3', [
   'class' => MyBehavior::className(),
   'prop1' => 'value1',
   'prop2' => 'value2',
]);
```

Você pode anexar vários behaviors de uma só vez usando o método [[yii\base\Component::attachBehaviors()]]:

```php
$component->attachBehaviors([
   'myBehavior1' => new MyBehavior,  // a named behavior
   MyBehavior::className(),          // an anonymous behavior
]);
```

Você também pode anexar behaviors através de [configurations](concept-configurations.md) conforme exemplo abaixo: 

```php
[
   'as myBehavior2' => MyBehavior::className(),

   'as myBehavior3' => [
       'class' => MyBehavior::className(),
       'prop1' => 'value1',
       'prop2' => 'value2',
   ],
]
```
Para mais detalhes, por favor consulte a seção [Configurations](concept-configurations.md#configuration-format).

Usando Behaviors <span id="using-behaviors"></span>
---------------

Para usar um behavior, primeiro este deve ser anexado à um [[yii\base\Component|component]] conforme as instruções acima. Uma vez que o behavior está anexado ao componente, seu uso é simples.

Você pode acessar uma variável *pública* ou uma propriedade [property](concept-properties.md) definida por um getter e/ou um setter do behavior através do componente ao qual ele está anexado:

```php
// "prop1" é uma propriedade defined na classe behavior 
echo $component->prop1;
$component->prop1 = $value;
```

Você também pode executar um método *público* do behavior de forma parecida:

```php
// foo() é um método publico definido na classe behavior 
$component->foo();
```

Como você pode ver, embora `$component` não define`prop1` e nem `foo()`, eles podem ser utilizados como se eles fizessem parte  da definição do componente, isto se deve ao behavior anexado.

Se dois behaviors definem a mesma propriedade ou método e ambos são anexados ao mesmo componente, o behavior que for anexado primeiramente ao componente terá precedência quando a propriedade ou método for acessada.

Um behavior pode estar associado a um nome quando ele for anexado a um componente. Sendo esse o caso, você pode acessar o objeto behavior usando o name:

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

Para encerrar, vamos dar uma olhada [[yii\behaviors\TimestampBehavior]]. Este behavior suporta atualização automática dos atributos timestamp de um [[yii\db\ActiveRecord|Active Record]] toda vez que o model (modelo) for salvo (por exemplo, Inserte ou update).

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
               'class' => TimestampBehavior::className(),
               'attributes' => [
                   ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                   ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
               ],
           ],
       ];
   }
}
```

A configuração do behavior acima especifica que quando o registro está sendo:

* inserido, o behavior deve atribuir o timestamp atual para os atributos `created_at` e `updated_at` 
* atualizado, o behavior deve atribuir o timestamp atual para o atributo `updated_at` 

Com esse código no lugar, se você tem um objeto `User` e tenta salvá-lo, você encontrará seus `created_at` e `updated_at` automáticamente preenchidos com a data e hora atual:

```php
$user = new User;
$user->email = 'test@example.com';
$user->save();
echo $user->created_at;  // shows the current timestamp
```

O [[yii\behaviors\TimestampBehavior|TimestampBehavior]] também oferece um método útil
[[yii\behaviors\TimestampBehavior::touch()|touch()]], que irá atribuir a data e hora atual para um atributo específico e salvar o database:

```php
$user->touch('login_time');
```

Comparando Behaviors com Traits <span id="comparison-with-traits"></span>
----------------------

Apesar de behaviors serem semelhantes a [traits](http://www.php.net/traits) em que ambos "injetam" suas propriedades e métodos para a classe principal, eles diferem em muitos aspectos. Tal como explicado abaixo, ambos têm prós e contras. Eles funcionam mais como complemento um do outro.

### Razões para usar Behaviors <span id="pros-for-behaviors"></span>

Classes Behavior, como classes normais, suportam herança. Traits, por outro lado, pode ser só suporta a programação “copia e cola”. Eles não suportam herança.

Behaviors podem ser anexados e desvinculados a um componente dinamicamente sem necessidade de modificação da classe componente.
Para usar um trait, você deve modificar o código da classe de usá-lo.

Behaviors são configuráveis enquanto traits não são.

Behaviors podem customizar a execução do código do componente respondendo aos seus eventos.
Quando houver nomes conflitantes entre diferentes behaviors anexados ao mesmo componente, o conflito é automaticamente resolvido priorizando o behavior anexado primeiramente ao componente. Nomes conflitantes causados por diferentes traits requer resolução manual renomeando as propriedades ou métodos afetados.

### Razões para usar Traits <span id="pros-for-traits"></span>

Traits são muito mais eficientes do que behaviors, estes são objetos e requerem mais tempo e memória.

IDEs são mais amigáveis com traits por serem nativos do PHP.


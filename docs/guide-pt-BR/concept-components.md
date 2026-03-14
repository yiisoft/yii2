Componentes
===========

Componente é a parte principal na construção de aplicações Yii. Componentes são instâncias de [[yii\base\Component]], ou uma classe estendida. As três características principais que os componentes fornecem a outras classes são:

* [Propriedades](concept-properties.md)
* [Eventos](concept-events.md)
* [Behaviors (Comportamentos)](concept-behaviors.md)

Separadamente e combinadas, essas características fazem com que as classes no Yii sejam muito mais customizáveis e fáceis de usar. Por exemplo, o [[yii\jui\DatePicker|widget date picker]], um componente de interface do usuário, pode ser usado na [view (visão)](structure-view.md) para gerar um `date picker` interativo:

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

Os widgets são facilmente escritos porque a classe estende de [[yii\base\Component]].

Enquanto os componentes são muito poderosos, eles são um pouco mais pesados do que um objeto normal, devido ao fato de que é preciso memória e CPU extra para dar suporte às funcionalidades de [evento](concept-events.md) e de [behavior](concept-behaviors.md) em particular.
Se o seu componente não precisa dessas duas características, você pode considerar estender a sua classe de componente de [[yii\base\BaseObject]] em vez de [[yii\base\Component]]. Se o fizer, fará com que seus componentes sejam tão eficientes como objetos normais do PHP, mas com um suporte adicional para [propriedades](concept-properties.md).

Ao estender a classe de [[yii\base\Component]] ou [[yii\base\BaseObject]], é recomendado que você siga as seguintes convenções:

- Se você sobrescrever o construtor, declare um parâmetro `$config` como último parâmetro do construtor, e em seguida passe este parâmetro para o construtor pai.
- Sempre chame o construtor pai *no final* do seu construtor reescrito.
- Se você sobrescrever o método [[yii\base\BaseObject::init()]], certifique-se de chamar a implementação pai do `init` *no início* do seu método `init`.

Por Exemplo:

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
       // ... initialization before configuration is applied

       parent::__construct($config);
   }

   public function init()
   {
       parent::init();

       // ... initialization after configuration is applied
   }
}
```

Seguindo essas orientações fará com que seus componentes sejam [configuráveis](concept-configurations.md) quando forem criados. Por Exemplo:

```php
$component = new MyClass(1, 2, ['prop1' => 3, 'prop2' => 4]);
// alternatively
$component = \Yii::createObject([
   'class' => MyClass::class,
   'prop1' => 3,
   'prop2' => 4,
], [1, 2]);
```

> Informação: Embora a forma de chamar [[Yii::createObject()]] pareça ser mais complicada, ela é mais poderosa porque ela é aplicada no topo de um [container 
de injeção de dependência](concept-di-container.md).
 

A classe [[yii\base\BaseObject]] impõe o seguinte ciclo de vida do objeto:

1. Pré-inicialização dentro do construtor. Você pode definir valores de propriedade padrão aqui.
2. Configuração de objeto via `$config`. A configuração pode sobrescrever o valor padrão configurado dentro do construtor.
3. Pós-inicialização dentro do [[yii\base\BaseObject::init()|init()]]. Você pode sobrescrever este método para executar checagens e normalização das propriedades.
4. Chamadas de método de objeto.

Os três primeiros passos acontecem dentro do construtor do objeto. Isto significa que uma vez que você instanciar a classe (isto é, um objeto), esse objeto será inicializado adequadamente. 


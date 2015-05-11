Componentes
===========

Componente é a parte principal na construção de aplicações Yii. Componentes são instâncias de [[yii\base\Component]],
Ou uma classe extendida. As três características principais que os Componentes fornecem   a outras classes são:

* [Propriedades](concept-properties.md)
* [Eventos](concept-events.md)
* [Behaviors (Comportamentos)](concept-behaviors.md)
 
Separadamente e combinadas, essas características fazem com que as classes no Yii sejam muito mais customizáveis e fáceis de usar. Por Exemplo,
O include [[yii\jui\DatePicker|date picker widget]], um componete de interface do usuário, pode ser usado na [view (visão)](structure-view.md)
Para gerar um `date picker` interativo:

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

Enquanto componenetese são muito poderosos, eles são um pouco mais pesados do que um objeto normal, devido ao fato de que é preciso
Memória e CPU extra para dar suporte a [evento](concept-events.md) e [comportamento](concept-behaviors.md) funcionalidade em particular.
Se o seu componente não precisa dessas duas características, você pode considerar extender a sua classe de componente de [[yii\base\Object]] em vez de [[yii\base\Component]]. Se o fizer, fará com que seus componentes sejam tão eficientes como objetos normais do PHP,
Mas com um suporte adcional para [propriedades](concept-properties.md).

Ao extender sua classe de [[yii\base\Component]] ou [[yii\base\Object]], é recomendado que você siga estas convenções:

- Se você sobrescrever o construtor, declare um parâmetro `$config` como último parâmetro do construtor, e em seguida passe este parâmetro para o construtor pai.
- Sempre chame o construtor pai *no final* do seu construtor reescrito.
- Se você sobrescrever o método [[yii\base\Object::init()]],certifique-se de chamar a implementação pai do `init` *no início* do seu método `init`.

Por Exemplo:

```php
<?php

namespace yii\components\MyClass;

use yii\base\Object;

class MyClass extends Object
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

Seguindo essas orientações fará com que seus componentes sejam [configuraveis](concept-configurations.md) quando forem criados. Por Exemplo:

```php
$component = new MyClass(1, 2, ['prop1' => 3, 'prop2' => 4]);
// alternatively
$component = \Yii::createObject([
    'class' => MyClass::className(),
    'prop1' => 3,
    'prop2' => 4,
], [1, 2]);
```

> informação: Embora a forma de chamar [[Yii::createObject()]] pareça ser mais complicada, ela é mais poderosa porque ela é
> aplicada no topo de um [recipiente 
de injeção de dependência](concept-di-container.md).
  

A Classe [[yii\base\Object]] inpõe o seguinte cilo de vida do objeto:

1. Pré-inicialização dentro do construtor. Você pode definir valores de propriedade padrão aqui.
2. Configuração de objeto via `$config`. A configuração pode sobrescrever o valor padrão configurado dentro do construtor.
3. Pós-inicialização dentro do [[yii\base\Object::init()|init()]]. Você pode sobrescrever este método para executar checagens e normalização das propriedades.
4. Chamadas de método de objeto.

Os três primeiros passos acontecem dentro do construtor do objeto. Isto significa que uma vez que você instancia a classe (Isto é, um objeto), esse objeto foi inicializado adequadamente. 

Propriedades
===========

No PHP, atributos da classe são sempre chamadas de *propriedades*. Esses atributos fazem parte da definição da classe e são usadas para representar o estado de uma instância da classe (para diferenciar uma instância da classe de outra). Na prática, muitas vezes você pode querer lidar com a leitura ou a escrita de propriedades de maneiras especiais. Por exemplo, você pode querer trimar uma string sempre que for atribuído um valor para a propriedade `label`. Você *poderia* usar o código a seguir para realizar esta tarefa:

```php
$object->label = trim($label);
```

A desvantagem do código acima é que você teria que chamar `trim ()` em todos os lugares onde você definir a propriedade `label` no seu código. Se, no futuro, a propriedade `label` receber um novo requisito, tais como a primeira letra deve ser capitalizado, você teria que modificar novamente todos os pedaços de código que atribui um valor para a propriedade `label`. A repetição de código leva a erros, e é uma prática que você deve evitar sempre que possível.

Para resolver este problema, o Yii introduz uma classe base chamada [[yii\base\BaseObject]] que suporta definições de propriedades baseadas nos métodos *getter* e *setter* da classe. Se uma classe precisar dessa funcionalidade, ela deve estender a classe [[yii\base\BaseObject]], ou de uma classe-filha.

> Informação: Quase todas as classes nativas (core) do framework Yii estendem de [[yii\base\BaseObject]] ou de uma classe-filha. Isto significa que sempre que você vê um método *getter* ou *setter* em uma classe nativa (core), você pode usá-lo como uma propriedade.

Um método getter é um método cujo nome inicia com a palavra `get`; um método setter inicia com `set`.
O nome depois do prefixo `get` ou `set` define o nome da propriedade. Por exemplo, um getter `getLabel()` e/ou um setter `setLabel()` define a propriedade chamada `label`, como mostrado no código a seguir:

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

(Para ser claro, os métodos getter e setter criam a propriedade `label`, que neste caso internamente refere-se ao atributo privado chamado `_label`.)

Propriedades definidas por getters e setters podem ser usados como atributos da classe. A principal diferença é que quando tal propriedade é iniciada para leitura, o método getter correspondente será chamado; quando a propriedade é iniciada atribuindo um valor, o método setter correspondente será chamado. Por exemplo:

```php
// equivalent to $label = $object->getLabel();
$label = $object->label;

// equivalent to $object->setLabel('abc');
$object->label = 'abc';
```

A propriedade definida por um método getter sem um método setter é *somente de leitura*. Tentando atribuir um valor a tal propriedade causará uma exceção [[yii\base\InvalidCallException|InvalidCallException]]. Semelhantemente, uma propriedade definida por um método setter sem um método getter é *somente de gravação *, e tentar ler tal propriedade também causará uma exceção. Não é comum ter propriedade *somente de gravação*.

Existem várias regras especiais para, e limitações sobre, as propriedades definidas via getters e setters:

* Os nomes dessas propriedades são *case-insensitive*. Por exemplo, `$object->label` e `$object->Label` são a mesma coisa.  Isso ocorre porque nomes de métodos no PHP são case-insensitive.
* Se o nome de uma tal propriedade é o mesmo que um atributo da classe, esta última terá precedência. Por exemplo, se a classe `Foo` descrita acima tiver um atributo `label`, então a atribuição `$object->label = 'abc'` afetará o *atributo* 'label'; esta linha não executaria   `setLabel()` método setter.
* Essas propriedades não suportam visibilidade. Não faz nenhuma diferença para a definição dos métodos getter ou setter se a propriedade é pública, protegida ou privada.
* As propriedades somente podem ser definidas por getters e/ou setters *não estáticos*. Os métodos estáticos não serão tratados da mesma maneira.

Voltando para o problema descrito no início deste guia, em vez de chamar `trim()` em todos os lugares que um valor for atribuído a `label`, agora o `trim()` só precisa ser invocado dentro do  setter `setLabel()`. E se uma nova exigência faz com que seja necessário que o 'label' seja inicializado capitalizado, o método `setLabel()` pode rapidamente ser modificado sem tocar em nenhum outro código. Esta única mudança afetará de forma global cada atribuição à propriedade `label`.

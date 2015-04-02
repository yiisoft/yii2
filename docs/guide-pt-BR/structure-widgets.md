Widgets
=======

Os widgets são blocos de construção reutilizáveis usados nas 
[views (visões)](structure-views.md) para criar e configurar complexos elementos 
de interface do usuário sob uma modelagem orientada a objetos. Por exemplo, um 
widget datapicker pode gerar um calendário que permite aos usuários selecionarem 
uma data que desejam inserir em um formulário. Tudo o que você precisa fazer é 
apenas inserir um código na view (visão) conforme o seguinte:

```php
<?php
use yii\jui\DatePicker;
?>
<?= DatePicker::widget(['name' => 'date']) ?>
```

Existe uma quantidade considerável de widgets empacotados no Yii, como o 
[[yii\widgets\ActiveForm|active form]], o [[yii\widgets\Menu|menu]], o 
[jQuery UI widgets](widget-jui.md), o [Twitter Bootstrap widgets](widget-bootstrap.md), etc.
A seguir, iremos introduzir os conhecimentos básicos sobre os widgets. Por favor,
consulte a documentação de classes da API se você quiser saber mais sobre o uso de um determinado widget.


## Usando Widgets <span id="using-widgets"></span>

Os widgets são usados principalmente nas [views (visões)](structure-views.md). 
Você pode chamar o método [[yii\base\Widget::widget()]] para usar um widget em 
uma view (visão). O método possui um array de [configuração](concept-configurations.md) 
para inicializar o widget e retornar o resultado da renderização do widget. Por 
exemplo, o código a seguir insere um widget datapicker configurado para usar o 
idioma Russo e manter a data selecionada no atributo `from_date` do `$model`.

```php
<?php
use yii\jui\DatePicker;
?>
<?= DatePicker::widget([
    'model' => $model,
    'attribute' => 'from_date',
    'language' => 'ru',
    'clientOptions' => [
        'dateFormat' => 'yy-mm-dd',
    ],
]) ?>
```

Alguns widgets podem ter um bloco de conteúdo que deve ser colocado entre as 
chamadas dos métodos [[yii\base\Widget::begin()]] e [[yii\base\Widget::end()]]. 
Por exemplo, o código a seguir usa o widget [[yii\widgets\ActiveForm]] para gerar
um formulário de login. O widget irá gerar as tags de abertura e de fechamento 
do `<form>` respectivamente nos lugares onde os métodos `begin()` e `end()` foram 
chamados. Qualquer conteúdo entre estes métodos serão renderizados entre as tags 
de abertura e de fechamento do `<form>`.

```php
<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>

<?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

    <?= $form->field($model, 'username') ?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Login') ?>
    </div>

<?php ActiveForm::end(); ?>
```

Observe que ao contrário do [[yii\base\Widget::widget()]] que retorna a 
renderização de um widget, o método [[yii\base\Widget::begin()]] retorna uma 
instância do widget que pode ser usado para construir o seu conteúdo.


## Criando Widgets <span id="creating-widgets"></span>

Para criar um widget, estenda a classe [[yii\base\Widget]] e sobrescreva os 
métodos [[yii\base\Widget::init()]] e/ou [[yii\base\Widget::run()]]. Normalmente, 
o método `init()` deve conter os códigos que normalizam as propriedade do widget, 
enquanto o método `run()` deve conter o código que gera o resultado da renderização 
do widget. O resultado da renderização pode ser feito diretamente dando "echo" ou 
pelo retorno de uma string no método `run()`.

No exemplo a seguir, o `HelloWidget` codifica o HTML e exibe o conteúdo atribuído 
à sua propriedade `message`. Se a propriedade não for definida, será exibido 
"Hello World" como padrão.

```php
namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class HelloWidget extends Widget
{
    public $message;

    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = 'Hello World';
        }
    }

    public function run()
    {
        return Html::encode($this->message);
    }
}
```

Para usar este widget, simplesmente insira o código a seguir em uma view (visão):

```php
<?php
use app\components\HelloWidget;
?>
<?= HelloWidget::widget(['message' => 'Good morning']) ?>
```

O `HelloWidget` abaixo é uma variante que pega o conteúdo entre as chamadas de 
`begin()` e `end()`, codifica o HTML e em seguida os exibe.

```php
namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class HelloWidget extends Widget
{
    public function init()
    {
        parent::init();
        ob_start();
    }

    public function run()
    {
        $content = ob_get_clean();
        return Html::encode($content);
    }
}
```

Como você pode ver, o buffer de saída do PHP é iniciado no método `init()` para 
que qualquer conteúdo entre as chamadas de `init()` e `run()` possam ser capturadas, 
processadas e retornadas em `run()`.

> Informação: Ao chamar o [[yii\base\Widget::begin()]], uma nova instância do 
  widget será criada e o método `init()` será chamado logo ao final de seu construtor.
  Ao chamar o [[yii\base\Widget::end()]], o método `run()` será chamado cujo o 
  resultado da renderização será dado *echo* pelo `end()`.

O código a seguir mostra como você pode usar esta nova variante do `HelloWidget`:

```php
<?php
use app\components\HelloWidget;
?>
<?php HelloWidget::begin(); ?>

    um conteúdo qualquer...

<?php HelloWidget::end(); ?>
```

Algumas vezes, um widget pode precisar renderizar um grande conteúdo. Enquanto 
você pode inserir todo este conteúdo no método `run()`, uma boa prática é 
colocá-lo em uma [view (visão)](structure-views.md) e chamar o 
[[yii\base\Widget::render()]] para renderizá-lo. Por exemplo,

```php
public function run()
{
    return $this->render('hello');
}
```

Por padrão, as views (visões) para um widget devem ser armazenadas em arquivos 
sob o diretório `WidgetPath/views`, onde o `WidgetPath` significa o diretório 
que contém os arquivo da classe do widget. Portanto, o exemplo anterior irá 
renderizar o arquivo de view (visão) `@app/components/views/hello.php`, assumindo 
que a classe widget está localizada sob o diretório `@app/components`. Você pode 
sobrescrever o método [[yii\base\Widget::getViewPath()]] para personalizar o 
diretório que conterá os arquivos de views (visões) do widget.


## Boas Práticas <span id="best-practices"></span>

Os widgets são uma maneira orientada a objetos de reutilizar códigos de view (visão).

Ao criar os widgets, você ainda deve seguir o padrão MVC. Em geral, você deve 
manter a lógica nas classes widgets e manter as apresentações nas 
[views (visões)](structure-views.md).

Os widgets devem ser projetados para serem autossuficientes. Isto é, ao utilizar 
um widget, você deverá ser capaz de removê-lo de uma view (visão) sem fazer 
qualquer outra coisa. Isto pode ser complicado se um widget requerer recursos 
externos, tais como CSS, JavaScript, imagens, etc. Felizmente, o Yii fornece o 
suporte para [asset bundles](structure-assets.md), que pode ser utilizado para 
resolver este problema.

Quando um widget contiver somente código de view (visão), será bem semelhante a 
uma [view (visão)](structure-views.md). Na verdade, neste caso, a única diferença 
é que um widget é uma classe para ser redistribuída, enquanto uma view é apenas 
um simples script PHP que você preferirá manter em sua aplicação

Widgets
=======

Los _widgets_ son bloques de código reutilizables que se usan en las [vistas](structure-views.md)
para crear elementos de interfaz de usuario complejos y configurables, de forma orientada a objetos.
Por ejemplo, un _widget_ de selección de fecha puede generar un selector de fechas bonito que
permita a los usuarios seleccionar una fecha.  Todo lo que hay que hacer es insertar el siguiente
código en una vista:

```php
<?php
use yii\jui\DatePicker;
?>
<?= DatePicker::widget(['name' => 'date']) ?>
```

Yii incluye un buen número de _widgets_, tales como
[[yii\widgets\ActiveForm|formulario activo]],
[[yii\widgets\Menu|menú]],
[_widgets_ de jQuery UI](https://www.yiiframework.com/extension/yiisoft/yii2-jui), y
[_widgets_ de Twitter Bootstrap](https://www.yiiframework.com/extension/yiisoft/yii2-bootstrap).
A continuación presentaremos las nociones básicas de de los _widgets_.  Por favor, refiérase a la
documentación de la API de clases si quiere aprender más acerca del uso de un _widget_ en particular.


## Uso de los _widgets_ <span id="using-widgets"></span>

Los _widgets_ se usan principalmente en las [vistas](structure-views.md).  Se puede llamar al método
[[yii\base\Widget::widget()]] para usar un _widget_ en una vista.  El método toma un _array_ de
[configuración](concept-configurations.md) para inicializar el _widget_ y devuelve la representación
resultante del _widget_.  Por ejemplo, el siguiente código inserta un _widget_ de selección de fecha
configurado para usar el idioma ruso y guardar la selección en el atributo `from_date` de `$model`.

```php
<?php
use yii\jui\DatePicker;
?>
<?= DatePicker::widget([
    'model' => $model,
    'attribute' => 'from_date',
    'language' => 'ru',
    'dateFormat' => 'php:Y-m-d',
]) ?>
```

Algunos _widgets_ pueden coger un bloque de contenido que debería encontrarse entre la invocación de
[[yii\base\Widget::begin()]] y [[yii\base\Widget::end()]].  Por ejemplo, el siguiente código usa el
_widget_ [[yii\widgets\ActiveForm]] para generar un formulario de inicio de sesión.  El _widget_
generará las etiquetas `<form>` de apertura y cierre donde se llame a `begin()` y `end()`
respectivamente. Cualquier cosa que este en medio se representará tal cual.

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

Hay que tener en cuenta que, a diferencia de [[yii\base\Widget::widget()]] que devuelve la
representación resultante del _widget_, el método [[yii\base\Widget::begin()]] devuelve una
instancia del _widget_, que se puede usar para generar el contenido del _widget_.

> Nota: Algunos _widgets_ utilizan un [búfer de salida](https://www.php.net/manual/es/book.outcontrol.php)
> para ajustar el contenido rodeado al invocar [[yii\base\Widget::end()]].  Por este motivo se espera
> que las llamadas a [[yii\base\Widget::begin()]] y [[yii\base\Widget::end()]] tengan lugar en el
> mismo fichero de vista.
> No seguir esta regla puede desembocar en una salida distinta a la esperada.


### Configuración de las variables globales predefinidas

Las variables globales predefinidas de un _widget_ se pueden configurar por medio del contenedor
de inyección de dependencias:

```php
\Yii::$container->set('yii\widgets\LinkPager', ['maxButtonCount' => 5]);
```

Consulte la [sección "Uso práctico" de la Guía del contenedor de inyección de dependencias](concept-di-container.md#practical-usage) para más detalles.


## Creación de _widgets_ <span id="creating-widgets"></span>

Para crear un _widget_, extienda la clase [[yii\base\Widget]] y sobrescriba los métodos
[[yii\base\Widget::init()]] y/o [[yii\base\Widget::run()]].  Normalmente el método `init()` debería
contener el código que inicializa las propiedades del _widget_, mientras que el método `run()`
debería contener el código que genera la representación resultante del _widget_.  La representación
resultante del método `run()` puede pasarse directamente a `echo` o devolverse como una cadena.

En el siguiente ejemplo, `HelloWidget` codifica en HTML y muestra el contenido asignado a su
propiedad `message`.  Si la propiedad no está establecida, mostrará «Hello World» por omisión.

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

Para usar este _widget_, simplemente inserte el siguiente código en una vista:

```php
<?php
use app\components\HelloWidget;
?>
<?= HelloWidget::widget(['message' => 'Good morning']) ?>
```

Abajo se muestra una variante de `HelloWidget` que toma el contenido insertado entre las llamadas a
`begin()` y `end()`, lo codifica en HTML y posteriormente lo muestra.

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

Como se puede observar, el búfer de salida de PHP es iniciado en `init()` para que toda salida
entre las llamadas de `init()` y `run()` puede ser capturada, procesada y devuelta en `run()`.

> Info: Cuando llame a [[yii\base\Widget::begin()]], se creará una nueva instancia del _widget_ y se
> llamará a su método `init()` al final del constructor del _widget_.  Cuando llame a
> [[yii\base\Widget::end()]], se invocará el método `run()` y el resultado que devuelva será pasado
> a `echo` por `end()`.

El siguiente código muestra cómo usar esta nueva variante de `HelloWidget`:

```php
<?php
use app\components\HelloWidget;
?>
<?php HelloWidget::begin(); ?>

    contenido que puede contener <etiqueta>s

<?php HelloWidget::end(); ?>
```

A veces, un _widget_ puede necesitar representar un gran bloque de contenido.  Aunque que se
podría incrustar el contenido dentro del método `run()`, es preferible ponerlo dentro de una
[vista](structure-views.md) y llamar al método [[yii\base\Widget::render()]] para representarlo.
Por ejemplo:

```php
public function run()
{
    return $this->render('hello');
}
```

Por omisión, las vistas para un _widget_ deberían encontrarse en ficheros dentro del directorio
`WidgetPath/views`, donde `WidgetPath` representa el directorio que contiene el fichero de clase
del _widget_.  Por lo tanto, el ejemplo anterior representará el fichero de vista
`@app/components/views/hello.php`, suponiendo que la clase del _widget_ se encuentre en
`@app/components`.  Se puede sobrescribir el método [[yii\base\Widget::getViewPath()]] para
personalizar el directorio que contiene los ficheros de vista del _widget_.


## Buenas prácticas <span id="best-practices"></span>

Los _widgets_ son una manera orientada a objetos de reutilizar código de las vistas.

Al crear _widgets_, debería continuar suguiendo el patrón MVC.  En general, se debería mantener la
lógica en las clases del widget y la presentación en las [vistas](structure-views.md).

Los _widgets_ deberían diseñarse para ser autosuficientes.  Es decir, cuando se use un _widget_, se
debería poder ponerlo en una vista sin hacer nada más.  Esto puede resultar complicado si un
_widget_ requiere recursos externos, tales como CSS, JavaScript, imágenes, etc.  Afortunadamente
Yii proporciona soporte para [paquetes de recursos](structure-asset-bundles.md) (_asset bundles_)
que se pueden utilizar para resolver este problema.

Cuando un _widget_ sólo contiene código de vista, es muy similar a una [vista](structure-views.md).
De hecho, en este caso, su única diferencia es que un _widget_ es una clase redistribuible, mientras
que una vista es sólo un simple script PHP que prefiere mantener dentro de su aplicación.

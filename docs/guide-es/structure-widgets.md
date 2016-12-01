Widgets
=======

Los Widgets son bloques de código reutilizables utilizados en las [vistas](structure-views.md) para crear elementos de 
interfaz de usuario complejos y configurables de forma orientada a objetos. Por ejemplo, widget DatePicker puede 
generar un DatePicker de lujo que permita a los usuarios seleccionar una fecha. Todo lo que se tiene que hacer es 
insertar el siguiente código en una vista.

```php
<?php
use yii\jui\DatePicker;
?>
<?= DatePicker::widget(['name' => 'date']) ?>
```

Hay un buen número de widgets incluidos en Yii, tales como [[yii\widgets\ActiveForm|active form]], 
[[yii\widgets\Menu|menu]], [Widgets de jQuery UI](widget-jui.md), [widgets de Twitter Bootstrap](widget-bootstrap.md). 
En adelante, introduciremos las nociones básicas acerca de los widgets. Por favor, refiérase a la documentación de la 
API de clases si quiere aprender más acerca de el uso de un widget en particular.

## Uso de los Widgets <span id="using-widgets"></span>

Los Widgets son usados principalmente en las [vistas](structure-views.md). Se puede llamar al método 
[[yii\base\Widget::widget()]] para usar un widget en una vista. El método obtiene un array de 
[configuración](concept-configurations.md) para inicializar el widget y retorna la representación resultante del 
widget. Por ejemplo, el siguiente código inserta un widget DatePicker que esta configurado para usar el idioma Ruso y 
mantener la entrada en atributo 'form_date' del '$model'.

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

Algunos widgets pueden coger un bloque de contenido que debería encontrarse entre la invocación de 
[[yii\base\Widget::begin()]] y [[yii\base\Widget::end()]]. Por ejemplo, el siguiente código usa el widget 
[[yii\widgets\ActiveForm]] para generar un formulario de inicio de sesión. El widget generará las etiquetas '<form>' 
de apertura y cierre donde sean llamados 'begin()' y 'end()', respectivamente. Cualquier cosa que este en medio será 
representado como tal.

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

Hay que tener en cuenta que a diferencia de [[yii\base\Widget::widget()]] que devuelve la representación resultante 
del widget, el método [[yii\base\Widget::begin()]] devuelve una instancia del widget que se puede usar para generar el 
contenido del widget.

## Creación Widgets <span id="creating-widgets"></span>

Para crear un widget, se debe extender a [[yii\base\Widget]] y sobrescribir los métodos [[yii\base\Widget::init()]] 
y/o [[yii\base\Widget::run()]]. Normalmente el método 'init()' debería contener el código que estandariza las 
propiedades del widget, mientras que el método 'run()' debería contener el código que genere la representación 
resultante del widget. La representación resultante puede ser "pintada" directamente o devuelta como una cadena por el 
método 'run()'.

En el siguiente ejemplo, 'HelloWidget' codifica en HTML y muestra el contenido asignado a su propiedad 'message'. Si 
la propiedad no está establecida, mostrará "Hello World" por defecto.

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

Para usar este widget, simplemente inserte el siguiente código en una vista:

```php
<?php
use app\components\HelloWidget;
?>
<?= HelloWidget::widget(['message' => 'Good morning']) ?>
```

Abajo se muestra una variante de 'HelloWidget' obtiene el contenido entre las llamadas 'begin()' y 'end()', lo 
codifica en HTML y posteriormente lo muestra.

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

Como se puede observar, el búfer de salida PHP es iniciado en 'init()' por tanto cualquier salida entre las llamadas 
de 'init()' y 'run()' puede ser capturada, procesada y devuelta en 'run()'.

> Info: Cuando se llama a [[yii\base\Widget::begin()]], se creará una nueva instancia del widget y el método 'init()' 
  será llamado al final del constructor del widget. Cuando se llama [[yii\base\Widget::end()]], el método 'run()' será 
  llamado el resultado que devuelva será escrito por 'end()'.

El siguiente código muestra como usar esta nueva variante de 'HelloWidget':

```php
<?php
use app\components\HelloWidget;
?>
<?php HelloWidget::begin(); ?>

    content that may contain <tag>'s

<?php HelloWidget::end(); ?>
```

A veces, un widget puede necesitar representar una gran cantidad de contenido. Mientras que se puede incrustar el 
contenido dentro del método 'run()', ponerlo dentro de una [vista](structure-views.md) y llamar 
[[yii\base\Widget::render()]] para representarla, es un mejor enfoque. Por ejemplo:

```php
public function run()
{
    return $this->render('hello');
}
```

Por defecto, las vistas para un widget deberían encontrarse en ficheros dentro del directorio 'WidgetPath/views', 
donde 'WidgetPath' representa el directorio que contiene el fichero de clase del widget. Por lo tanto, el anterior 
ejemplo representará el fichero de la vista `@app/components/views/hello.php`, asumiendo que la clase del widget se 
encuentre en `@app/components`. Se puede sobrescribir el método [[yii\base\Widget::getViewPath()]] para personalizar 
el directorio que contenga los ficheros de la vista del widget.

## Mejores Prácticas <span id="best-practices"></span>

Los widgets son una manera orientada a objetos de reutilizar código de las vistas.

Cuando se crean widgets, se debería continuar manteniendo el patrón MVC. En general, se debería mantener la lógica en 
las clases del widget y mantener la presentación en las [vistas](structure-views.md).

Los widgets deberían ser diseñados para ser autónomos. Es decir, cuando se usa un widget, se debería poder poner en 
una vista sin hacer nada más. Esto puede resultar complicado si un widget requiere recursos externos, tales como CSS, 
JavaScript, imágenes, etc. Afortunadamente Yii proporciona soporte para 
[asset bundles](structure-asset-bundles.md) que pueden ser utilizados para resolver el problema.

Cuando un widget sólo contiene código de vista, este es muy similar a una [vista](structure-views.md). De hecho, en 
este caso, su única diferencia es que un widget es una clase redistribuible, mientras que una vista es sólo un script 
PHP llano que prefiere mantenerse dentro de su aplicación.

Clase auxiliar Html (Html helper)
=================================

Todas las aplicaciones web generan grandes cantidades de marcado HTML (HTML markup). Si el marcado es estático, se
puede realizar de forma efectiva
[mezclando PHP y HTML en un mismo archivo](http://php.net/manual/es/language.basic-syntax.phpmode.php) pero cuando se
generan dinámicamente empieza a complicarse su gestión sin ayuda extra. Yii ofrece esta ayuda en forma de una clase auxiliar Html
que proporciona un conjunto de métodos estáticos para gestionar las etiquetas HTML más comúnmente usadas, sus opciones y contenidos.

> Note: Si el marcado es casi estático, es preferible usar HTML directamente. No es necesario encapsularlo todo con
llamadas a la clase auxiliar Html.

## Lo fundamental <span id="basics"></span>


Teniendo en cuenta que la construcción de HTML dinámico mediante la concatenación de cadenas de texto se complica
rápidamente, Yii proporciona un conjunto de métodos para manipular las opciones de etiquetas y la construcción de las
mismas basadas en estas opciones.

### Generación de etiquetas <span id="generating-tags"></span>

El código de generación de etiquetas es similar al siguiente:

```php
<?= Html::tag('p', Html::encode($user->name), ['class' => 'username']) ?>
```

El primer argumento es el nombre de la etiqueta. El segundo es el contenido que se ubicará entre la etiqueta de
apertura y la de cierre. Hay que tener en cuenta que estamos usando `Html::encode`. Esto es debido a que el contenido
no se codifica automáticamente para permitir usar HTML cuando se necesite. La tercera opción es un array de opciones
HTML o, en otras palabras, los atributos de las etiquetas. En este array la clave representa el nombre del atributo
como podría ser `class`, `href` o `target` y el valor es su valor.

El código anterior generará el siguiente HTML:

```html
<p class="username">samdark</p>
```

Si se necesita solo la apertura o el cierre de una etiqueta, se pueden usar los métodos `Html::beginTag()` y
`Html::endTag()`.

Las opciones se usan en muchos métodos de la clase auxiliar Html y en varios widgets. En todos estos casos hay cierta
gestión adicional que se debe conocer:

- Si un valor es `null`, el correspondiente atributo no se renderizará.
- Los atributos cuyos valores son de tipo booleano serán tratados como
  [atributos booleanos](http://www.w3.org/TR/html5/infrastructure.html#boolean-attributes).
- Los valores de los atributos se codificarán en HTML usando [[yii\helpers\Html::encode()|Html::encode()]].
- El atributo "data" puede recibir un array. En este caso, se "expandirá" y se renderizará una lista de atributos
  `data` ej. `'data' => ['id' => 1, 'name' => 'yii']` se convierte en `data-id="1" data-name="yii"`.
- El atributo "data" puede recibir un JSON. Se gestionará de la misma manera que un array ej.
  `'data' => ['params' => ['id' => 1, 'name' => 'yii'], 'status' => 'ok']` se convierte en
  `data-params='{"id":1,"name":"yii"}' data-status="ok"`.

### Formación de clases y estilos dinámicamente <span id="forming-css"></span>

Cuando se construyen opciones para etiquetas HTML, a menudo nos encontramos con valores predeterminados que hay que
modificar. Para añadir o eliminar clases CSS se puede usar el siguiente ejemplo:

```php
$options = ['class' => 'btn btn-default'];

if ($type === 'success') {
    Html::removeCssClass($options, 'btn-default');
    Html::addCssClass($options, 'btn-success');
}

echo Html::tag('div', 'Pwede na', $options);

// cuando $type sea 'success' se renderizará
// <div class="btn btn-success">Pwede na</div>
```

Para hacer lo mismo con los estilos para el atributo `style`:

```php
$options = ['style' => ['width' => '100px', 'height' => '100px']];

// devuelve style="width: 100px; height: 200px; position: absolute;"
Html::addCssStyle($options, 'height: 200px; positon: absolute;');

// devuelve style="position: absolute;"
Html::removeCssStyle($options, ['width', 'height']);
```

Cuando se usa [[yii\helpers\Html::addCssStyle()|addCssStyle()]] se puede especificar si un array de pares clave-valor
corresponde a nombres y valores de la propiedad CSS correspondiente o a una cadena de texto como por ejemplo
`width: 100px; height: 200px;`. Estos formatos se pueden "hacer" y "deshacer" usando
[[yii\helpers\Html::cssStyleFromArray()|cssStyleFromArray()]] y
[[yii\helpers\Html::cssStyleToArray()|cssStyleToArray()]]. El método
[[yii\helpers\Html::removeCssStyle()|removeCssStyle()]] acepta un array de propiedades que se eliminarán. Si sólo se
eliminara una propiedad, se puede especificar como una cadena de texto.

## Codificación y Decodificación del contenido <span id="encoding-and-decoding-content"></span>


Para que el contenido se muestre correctamente y de forma segura con caracteres especiales HTML el contenido debe ser
codificado. En PHP esto se hace con [htmlspecialchars](http://www.php.net/manual/es/function.htmlspecialchars.php) y
[htmlspecialchars_decode](http://www.php.net/manual/es/function.htmlspecialchars-decode.php). El problema con el uso
de estos métodos directamente es que se tiene que especificar la codificación y opciones extra cada vez. Ya que las
opciones siempre son las mismas y la codificación debe coincidir con la de la aplicación para prevenir problemas de
seguridad, Yii proporciona dos métodos simples y compactos:

```php
$userName = Html::encode($user->name);
echo $userName;

$decodedUserName = Html::decode($userName);
```

## Formularios <span id="forms"></span>


El trato con el marcado de formularios es una tarea repetitiva y propensa a errores. Por esto hay un grupo de métodos
para ayudar a gestionarlos.

> Note: hay que considerar la opción de usar [[yii\widgets\ActiveForm|ActiveForm]] en caso de que se gestionen
formularios que requieran validaciones.

### Creando formularios <span id="creating-forms"></span>

Se puede abrir un formulario con el método [[yii\helpers\Html::beginForm()|beginForm()]] como se muestra a
continuación:

```php
<?= Html::beginForm(['order/update', 'id' => $id], 'post', ['enctype' => 'multipart/form-data']) ?>
```

El primer argumento es la URL a la que se enviarán los datos del formulario. Se puede especificar en formato de ruta
de Yii con los parámetros aceptados por [[yii\helpers\Url::to()|Url::to()]]. El segundo es el método que se usará.
`post` es el método predeterminado. El tercero es un array de opciones para la etiqueta `form`. En este caso cambiamos
el método de codificación del formulario de `data` en una petición POST a `multipart/form-data`. Esto se requiere
cuando se quieren subir archivos.

El cierre de la etiqueta `form` es simple:

```php
<?= Html::endForm() ?>
```

### Botones <span id="buttons"></span>

Para generar botones se puede usar el siguiente código:

```php
<?= Html::button('Press me!', ['class' => 'teaser']) ?>
<?= Html::submitButton('Submit', ['class' => 'submit']) ?>
<?= Html::resetButton('Reset', ['class' => 'reset']) ?>
```

El primer argumento para los tres métodos es el título del botón y el segundo son las opciones. El título no está
codificado pero si se usan datos recibidos por el usuario, deben codificarse mediante
[[yii\helpers\Html::encode()|Html::encode()]].

### Inputs <span id="input-fields"></span>

Hay dos grupos en los métodos input. Unos empiezan con `active` y se llaman inputs activos y los otros no empiezan
así. Los inputs activos obtienen datos del modelo y del atributo especificado y los datos de los inputs normales se
especifica directamente.

Los métodos más genéricos son:

```php
type, input name, input value, options
<?= Html::input('text', 'username', $user->name, ['class' => $username]) ?>

type, model, model attribute name, options
<?= Html::activeInput('text', $user, 'name', ['class' => $username]) ?>
```

Si se conoce el tipo de input de antemano, es conveniente usar los atajos de los métodos:

- [[yii\helpers\Html::buttonInput()]]
- [[yii\helpers\Html::submitInput()]]
- [[yii\helpers\Html::resetInput()]]
- [[yii\helpers\Html::textInput()]], [[yii\helpers\Html::activeTextInput()]]
- [[yii\helpers\Html::hiddenInput()]], [[yii\helpers\Html::activeHiddenInput()]]
- [[yii\helpers\Html::passwordInput()]] / [[yii\helpers\Html::activePasswordInput()]]
- [[yii\helpers\Html::fileInput()]], [[yii\helpers\Html::activeFileInput()]]
- [[yii\helpers\Html::textarea()]], [[yii\helpers\Html::activeTextarea()]]

Los botones de opción (Radios) y las casillas de verificación (checkboxes) se especifican de forma un poco diferente:

```php
<?= Html::radio('agree', true, ['label' => 'I agree']);
<?= Html::activeRadio($model, 'agree', ['class' => 'agreement'])

<?= Html::checkbox('agree', true, ['label' => 'I agree']);
<?= Html::activeCheckbox($model, 'agree', ['class' => 'agreement'])
```

Las listas desplegables (dropdown list) se pueden renderizar como se muestra a continuación:

```php
<?= Html::dropDownList('list', $currentUserId, ArrayHelper::map($userModels, 'id', 'name')) ?>
<?= Html::activeDropDownList($users, 'id', ArrayHelper::map($userModels, 'id', 'name')) ?>

<?= Html::listBox('list', $currentUserId, ArrayHelper::map($userModels, 'id', 'name')) ?>
<?= Html::activeListBox($users, 'id', ArrayHelper::map($userModels, 'id', 'name')) ?>
```

El primer argumento es el nombre del input, el segundo es el valor seleccionado actualmente y el tercero es el array
de pares clave-valor donde la clave es la lista de valores y el valor del array es la lista a mostrar.

Si se quiere habilitar la selección múltiple, se puede usar la lista seleccionable (checkbox list):

```php
<?= Html::checkboxList('roles', [16, 42], ArrayHelper::map($roleModels, 'id', 'name')) ?>
<?= Html::activeCheckboxList($user, 'role', ArrayHelper::map($roleModels, 'id', 'name')) ?>
```

Si no, se puede usar la lista de opciones (radio list):

```php
<?= Html::radioList('roles', [16, 42], ArrayHelper::map($roleModels, 'id', 'name')) ?>
<?= Html::activeRadioList($user, 'role', ArrayHelper::map($roleModels, 'id', 'name')) ?>
```

### Etiquetas y Errores <span id="labels-and-errors"></span>

De forma parecida que en los inputs hay dos métodos para generar etiquetas. El activo que obtiene los datos del modelo y
el no-activo que acepta los datos directamente:

```php
<?= Html::label('User name', 'username', ['class' => 'label username']) ?>
<?= Html::activeLabel($user, 'username', ['class' => 'label username'])
```

Para mostrar los errores del formulario de un modelo o modelos a modo de resumen puedes usar:

```php
<?= Html::errorSummary($posts, ['class' => 'errors']) ?>
```

Para mostrar un error individual:

```php
<?= Html::error($post, 'title', ['class' => 'error']) ?>
```

### Input Names y Values <span id="input-names-and-values"></span>

Existen métodos para obtener names, IDs y values para los campos de entrada (inputs) basados en el modelo. Estos se
usan principalmente internamente pero a veces pueden resultar prácticos:

```php
// Post[title]
echo Html::getInputName($post, 'title');

// post-title
echo Html::getInputId($post, 'title');

// mi primer post
echo Html::getAttributeValue($post, 'title');

// $post->authors[0]
echo Html::getAttributeValue($post, '[0]authors[0]');
```

En el ejemplo anterior, el primer argumento es el modelo y el segundo es un atributo de expresión. En su forma más
simple es su nombre de atributo pero podría ser un nombre de atributo prefijado y/o añadido como sufijo con los
indices de un array, esto se usa principalmente para mostrar inputs en formatos de tablas:

- `[0]content` se usa en campos de entrada de datos en formato de tablas para representar el atributo "content" para
  el primer modelo del input en formato de tabla;
- `dates[0]` representa el primer elemento del array del atributo "dates";
- `[0]dates[0]` representa el primer elemento del array del atributo "dates" para el primer modelo en formato de tabla.

Para obtener el nombre de atributo sin sufijos o prefijos se puede usar el siguiente código:

```php
// dates
echo Html::getAttributeName('dates[0]');
```

## Estilos y scripts <span id="styles-and-scripts"></span>


Existen dos métodos para generar etiquetas que envuelvan estilos y scripts incrustados (embebbed):

```php
<?= Html::style('.danger { color: #f00; }') ?>

Genera

<style>.danger { color: #f00; }</style>

<?= Html::script('alert("Hello!");', ['defer' => true]);

Genera

<script defer>alert("Hello!");</script>
```

Si se quiere enlazar un estilo externo desde un archivo CSS:

```php
<?= Html::cssFile('@web/css/ie5.css', ['condition' => 'IE 5']) ?>

genera

<!--[if IE 5]>
    <link href="http://example.com/css/ie5.css" />
<![endif]-->
```

El primer argumento es la URL. El segundo es un array de opciones. Adicionalmente, para regular las opciones se puede
especificar:

- `condition` para envolver `<link` con los comentarios condicionales con condiciones especificas. Esperamos que sean
  necesarios los comentarios condicionales ;)
- `noscript` se puede establecer como `true` para envolver `<link` con la etiqueta `<noscript>` por lo que el sólo se
  incluirá si el navegador no soporta JavaScript o si lo ha deshabilitado el usuario.

Para enlazar un archivo JavaScript:

```php
<?= Html::jsFile('@web/js/main.js') ?>
```

Es igual que con las CSS, el primer argumento especifica el enlace al fichero que se quiere incluir. Las opciones se
pueden pasar como segundo argumento. En las opciones se puede especificar `condition` del mismo modo que se puede usar
para `cssFile`.

## Enlaces <span id="hyperlinks"></span>


Existe un método para generar hipervínculos a conveniencia:

```php
<?= Html::a('Profile', ['user/view', 'id' => $id], ['class' => 'profile-link']) ?>
```

El primer argumento es el título. No está codificado por lo que si se usan datos enviados por el usuario se tienen que
codificar usando `Html::encode()`. El segundo argumento es el que se introducirá en `href` de la etiqueta `<a`. Se
puede consultar [Url::to()](helper-url.md) para obtener más detalles de los valores que acepta. El tercer argumento es
un array de las propiedades de la etiqueta.

Si se requiere generar enlaces de tipo `mailto` se puede usar el siguiente código:

```php
<?= Html::mailto('Contact us', 'admin@example.com') ?>
```

## Imagenes <span id="images"></span>


Para generar una etiqueta de tipo imagen se puede usar el siguiente ejemplo:

```php
<?= Html::img('@web/images/logo.png', ['alt' => 'My logo']) ?>

genera

<img src="http://example.com/images/logo.png" alt="My logo" />
```

Aparte de los [alias](concept-aliases.md) el primer argumento puede aceptar rutas, parámetros y URLs. Del mismo modo
que [Url::to()](helper-url.md).

## Listas <span id="lists"></span>


Las listas desordenadas se puede generar como se muestra a continuación:

```php
<?= Html::ul($posts, ['item' => function($item, $index) {
    return Html::tag(
        'li',
        $this->render('post', ['item' => $item]),
        ['class' => 'post']
    );
}]) ?>
```

Para generar listas ordenadas se puede usar `Html::ol()` en su lugar.

Subir Archivos
==============

Subir archivos en Yii es normalmente realizado con la ayuda de [[yii\web\UploadedFile]], que encapsula cada archivo subido
en un objeto `UploadedFile`. Combinado con [[yii\widgets\ActiveForm]] y [modelos](structure-models.md),
puedes fácilmente implementar un mecanismo seguro de subida de archivos.


## Crear Modelos <span id="creating-models"></span>

Al igual que al trabajar con entradas de texto plano, para subir un archivo debes crear una clase de modelo y utilizar un atributo 
de dicho modelo para mantener la instancia del archivo subido. Debes también declarar una regla para validar la subida del archivo.
Por ejemplo,

```php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $imageFile;

    public function rules()
    {
        return [
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg'],
        ];
    }
    
    public function upload()
    {
        if ($this->validate()) {
            $this->imageFile->saveAs('uploads/' . $this->imageFile->baseName . '.' . $this->imageFile->extension);
            return true;
        } else {
            return false;
        }
    }
}
```

En el código anterior, el atributo `imageFile` es utilizado para mantener una instancia del archivo subido. Este está asociado con
una regla de validación `file`, que utiliza [[yii\validators\FileValidator]] para asegurarse que el archivo a subir tenga extensión `png` o `jpg`.
El método `upload()` realizará la validación y guardará el archivo subido en el servidor.

El validador `file` te permite chequear las extensiones, el tamaño, el tipo MIME, etc. Por favor consulta
la sección [Validadores del Framework](tutorial-core-validators.md#file) para más detalles.

> Tip: Si estás subiendo una imagen, podrías considerar el utilizar el validador `image`. El validador `image` es
  implementado a través de [[yii\validators\ImageValidator]], que verifica que un atributo haya recibido una imagen válida 
  que pueda ser tanto guardada como procesada utilizando la [Extensión Imagine](https://github.com/yiisoft/yii2-imagine).


## Renderizar Campos de Subida de Archivos <span id="rendering-file-input"></span>

A continuación, crea un campo de subida de archivo en la vista:

```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'imageFile')->fileInput() ?>

    <button>Enviar</button>

<?php ActiveForm::end() ?>
```

Es importante recordad que agregues la opción `enctype` al formulario para que el archivo pueda ser subido apropiadamente.
La llamada a `fileInput()` renderizará un tag `<input type="file">` que le permitirá al usuario seleccionar el archivo a subir.

> Tip: desde la versión 2.0.8, [[yii\widgets\ActiveField::fileInput|fileInput]] agrega la opción `enctype` al formulario
  automáticamente cuando se utiliza una campo de subida de archivo.

## Uniendo Todo <span id="wiring-up"></span>

Ahora, en una acción del controlador, escribe el código que una el modelo y la vista para implementar la subida de archivos:

```php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\UploadForm;
use yii\web\UploadedFile;

class SiteController extends Controller
{
    public function actionUpload()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            if ($model->upload()) {
                // el archivo se subió exitosamente
                return;
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
```

En el código anterior, cuando se envía el formulario, el método [[yii\web\UploadedFile::getInstance()]] es llamado
para representar el archivo subido como una instancia de `UploadedFile`. Entonces dependemos de la validación del modelo
para asegurarnos que el archivo subido es válido y entonces subirlo al servidor.


## Uploading Multiple Files <span id="uploading-multiple-files"></span>

También puedes subir varios archivos a la vez, con algunos ajustes en el código de las subsecciones previas.

Primero debes ajustar la clase del modelo, agregando la opción `maxFiles` en la regla de validación `file` para limitar
el número máximo de archivos a subir. Definir `maxFiles` como `0` significa que no hay límite en el número de archivos
a subir simultáneamente. El número máximo de archivos permitidos para subir simultáneamente está también limitado
por la directiva PHP [`max_file_uploads`](https://www.php.net/manual/es/ini.core.php#ini.max-file-uploads),
cuyo valor por defecto es 20. El método `upload()` debería también ser modificado para guardar los archivos uno a uno.

```php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile[]
     */
    public $imageFiles;

    public function rules()
    {
        return [
            [['imageFiles'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'maxFiles' => 4],
        ];
    }
    
    public function upload()
    {
        if ($this->validate()) { 
            foreach ($this->imageFiles as $file) {
                $file->saveAs('uploads/' . $file->baseName . '.' . $file->extension);
            }
            return true;
        } else {
            return false;
        }
    }
}
```

En el archivo de la vista, debes agregar la opción `multiple` en la llamada a `fileInput()` de manera que el campo
pueda recibir varios archivos:
 
```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'imageFiles[]')->fileInput(['multiple' => true, 'accept' => 'image/*']) ?>

    <button>Enviar</button>

<?php ActiveForm::end() ?>
```

Y finalmente en la acción del controlador, debes llamar `UploadedFile::getInstances()` en vez de
`UploadedFile::getInstance()` para asignar un array de instancias `UploadedFile` a `UploadForm::imageFiles`. 

```php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\UploadForm;
use yii\web\UploadedFile;

class SiteController extends Controller
{
    public function actionUpload()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->imageFiles = UploadedFile::getInstances($model, 'imageFiles');
            if ($model->upload()) {
                // el archivo fue subido exitosamente
                return;
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
```

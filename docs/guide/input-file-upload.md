Uploading Files
===============

Uploading files in Yii is usually done with the help of [[yii\web\UploadedFile]] which encapsulates each uploaded
file as an `UploadedFile` object. Combined with [[yii\widgets\ActiveForm]] and [models](structure-models.md),
you can easily implement a secure file uploading mechanism.

 
## Creating Models <span id="creating-models"></span>

Like working with plain text inputs, to upload a single file you would create a model class and use an attribute
of the model to keep the uploaded file instance. You should also declare a validation rule to validate the file upload.
For example,

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
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'fileExtension' => 'png, jpg'],
        ];
    }
    
    public function upload()
    {
        if ($this->validate()) {                
            $this->imageFile->saveAs('uploads/' . $model->imageFile->baseName . '.' . $model->imageFile->extension);
            return true;
        } else {
            return false;
        }
    }
}
```

In the code above, the `imageFile` attribute is used to keep the uploaded file instance. It is associated with
a `file` validation rule which uses [[yii\validators\FileValidator]] to ensure a file with extension name `png` or `jpg`
is uploaded. The `upload()` method will perform the validation and save the uploaded file on the server.

The `file` validator allows you to check file extensions, size, MIME type, etc. For more details, please refer to 
the [Core Validators](tutorial-core-validators.md#file) section for more details.

> Tip: If you are uploading an image, you may consider using the `image` validator instead. The `image` validator is
  implemented via [[yii\validators\ImageValidator]] which verifies if an attribute has received a valid image 
  that can be then either saved or processed using the [Imagine Extension](https://github.com/yiisoft/yii2-imagine).
  

## Rendering File Input <span id="rendering-file-input"></span>

Next, create a file input in a view:

```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'imageFile')->fileInput() ?>

    <button>Submit</button>

<?php ActiveForm::end() ?>
```

It is important to remember that you add the `enctype` option to the form so that the file can be properly uploaded.
The `fileInput()` call will render a `<input type="file">` tag which will allow users to select a file to upload.


## Wiring Up <span id="wiring-up"></span>

Now in a controller action, write the code to wire up the model and the view to implement file uploading:

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
                // file is uploaded successfully
                return;
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
```

In the above code, when the form is submitted, the [[yii\web\UploadedFile::getInstance()]] method is called
to represent the uploaded file as an `UploadedFile` instance. We then rely on the model validation to make sure
the uploaded file is valid and save the file on the server.


## Uploading Multiple Files <span id="uploading-multiple-files"></span>

If you need to upload multiple files at once, some adjustments are required.
 
Model:

```php
class UploadForm extends Model
{
    /**
     * @var UploadedFile|Null file attribute
     */
    public $file;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['file'], 'file', 'maxFiles' => 10], // <--- here!
        ];
    }
}
```

View:

```php
<?php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);
?>

<?= $form->field($model, 'file[]')->fileInput(['multiple' => true]) ?>

    <button>Submit</button>

<?php ActiveForm::end(); ?>
```

The difference is the following line:

```php
<?= $form->field($model, 'file[]')->fileInput(['multiple' => true]) ?>
```

Controller:

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
            $model->file = UploadedFile::getInstances($model, 'file');
            
            if ($model->file && $model->validate()) {
                foreach ($model->file as $file) {
                    $file->saveAs('uploads/' . $file->baseName . '.' . $file->extension);
                }
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
```

There are two differences from single file upload. First is that `UploadedFile::getInstances($model, 'file');` is used
instead of `UploadedFile::getInstance($model, 'file');`. The former returns instances for **all** uploaded files while
the latter gives you only a single instance. The second difference is that we're doing `foreach` and saving each file.

Uploading Files
===============

> Note: This section is under development.

Uploading files in Yii is done via form model, its validation rules and some controller code. Let's review what's needed
to handle uploads properly.

Form model
----------

First of all, you need to create a model that will handle file upload. Create `models/UploadForm.php` with the following
content:

```php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

/**
 * UploadForm is the model behind the upload form.
 */
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
            [['file'], 'file'],
        ];
    }
}
```

In the code above, we created a model `UploadForm` with an attribute `$file` that will become `<input type="file">` in
the HTML form. The attribute has the validation rule named `file` that uses [[yii\validators\FileValidator|FileValidator]].

Form view
---------

Next create a view that will render the form.

```php
<?php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<?= $form->field($model, 'file')->fileInput() ?>

<button>Submit</button>

<?php ActiveForm::end(); ?>
```

The `'enctype' => 'multipart/form-data'` is important since it allows file uploads. `fileInput()` represents a form
input field.

Controller
----------

Now create the controller that connects form and model together:

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
            $model->file = UploadedFile::getInstance($model, 'file');

            if ($model->validate()) {                
                $model->file->saveAs('uploads/' . $model->file->baseName . '.' . $model->file->extension);
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
```

Instead of `model->load(...)` we are using `UploadedFile::getInstance(...)`. [[\yii\web\UploadedFile|UploadedFile]] 
does not run the model validation. It only provides information about the uploaded file. Therefore, you need to run
validation manually via `$model->validate()`. This triggers the [[yii\validators\FileValidator|FileValidator]] that
expects a file:

```php
$file instanceof UploadedFile || $file->error == UPLOAD_ERR_NO_FILE //in code framework
```

If validation is successful, then we're saving the file: 

```php
$model->file->saveAs('uploads/' . $model->file->baseName . '.' . $model->file->extension);
```

If you're using "basic" application template then folder `uploads` should be created under `web`.

That's it. Load the page and try uploading. Uplaods should end up in `basic/web/uploads`.

Additional information
----------------------

### Required rule

If you need to make file upload mandatory use `skipOnEmpty` like the following:

```php
public function rules()
{
    return [
        [['file'], 'file', 'skipOnEmpty' => false],
    ];
}
```

### MIME type

It is wise to validate type of the file uploaded. FileValidator has property `$extensions` for the purpose:

```php
public function rules()
{
    return [
        [['file'], 'file', 'extensions' => 'gif, jpg',],
    ];
}
```

The thing is that it validates only file extension and not the file content. In order to validate content as well use
`mimeTypes` property of `FileValidator`:

```php
public function rules()
{
    return [
        [['file'], 'file', 'extensions' => 'jpg, png', 'mimeTypes' => 'image/jpeg, image/png',],
    ];
}
```

[List of common media types](http://en.wikipedia.org/wiki/Internet_media_type#List_of_common_media_types)

### Validating uploaded image

If you upload an image, [[yii\validators\ImageValidator|ImageValidator]] may come in handy. It verifies if an attribute
received a valid image that can be then either saved or processed using [Imagine Extension](https://github.com/yiisoft/yii2/tree/master/extensions/imagine).

### Uploading multiple files

If you need download multiple files at once some adjustments are required. View:

```php
<?php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);

if ($model->hasErrors()) { //it is necessary to see all the errors for all the files.
    echo '<pre>';
    print_r($model->getErrors());
    echo '</pre>';
}
?>

<?= $form->field($model, 'file[]')->fileInput(['multiple' => '']) ?>

    <button>Submit</button>

<?php ActiveForm::end(); ?>
```

The difference is the following line:

```php
<?= $form->field($model, 'file[]')->fileInput(['multiple' => '']) ?>
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

            $files = UploadedFile::getInstances($model, 'file');

            foreach ($files as $file) {

                $_model = new UploadForm();

                $_model->file = $file;

                if ($_model->validate()) {
                    $_model->file->saveAs('uploads/' . $_model->file->baseName . '.' . $_model->file->extension);
                } else {
                    foreach ($_model->getErrors('file') as $error) {
                        $model->addError('file', $error);
                    }
                }
            }

            if ($model->hasErrors('file')){
                $model->addError(
                    'file',
                    count($model->getErrors('file')) . ' of ' . count($files) . ' files not uploaded'
                );
            }

        }

        return $this->render('upload', ['model' => $model]);
    }
}
```

The difference is `UploadedFile::getInstances($model, 'file');` instead of `UploadedFile::getInstance($model, 'file');`.
Former returns instances for **all** uploaded files while the latter gives you only a single instance.

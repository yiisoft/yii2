Uploading Files
===============

Uploading files in Yii is done via a form model, its validation rules and some controller code. Let's review what's
required to handle uploads properly.


Uploading single file
---------------------

First of all, you need to create a model that will handle file uploads. Create `models/UploadForm.php` with the following
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
     * @var UploadedFile file attribute
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

In the code above, we've created a model `UploadForm` with an attribute `file` that will become `<input type="file">` in
the HTML form. The attribute has the validation rule named `file` that uses [[yii\validators\FileValidator|FileValidator]].

### Form view

Next, create a view that will render the form:

```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

<?= $form->field($model, 'file')->fileInput() ?>

<button>Submit</button>

<?php ActiveForm::end() ?>
```

The `'enctype' => 'multipart/form-data'` is necessary because it allows file uploads. `fileInput()` represents a form
input field.

### Controller

Now create the controller that connects the form and the model together:

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

            if ($model->file && $model->validate()) {                
                $model->file->saveAs('uploads/' . $model->file->baseName . '.' . $model->file->extension);
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
```

Instead of `model->load(...)`, we are using `UploadedFile::getInstance(...)`. [[\yii\web\UploadedFile|UploadedFile]] 
does not run the model validation, rather it only provides information about the uploaded file. Therefore, you need to run the validation manually via `$model->validate()` to trigger the [[yii\validators\FileValidator|FileValidator]]. The validator expects that
the attribute is an uploaded file, as you see in the core framework code:

```php
if (!$file instanceof UploadedFile || $file->error == UPLOAD_ERR_NO_FILE) {
    return [$this->uploadRequired, []];
}
```

If the validation is successful, then we're saving the file: 

```php
$model->file->saveAs('uploads/' . $model->file->baseName . '.' . $model->file->extension);
```

If you're using the "basic" project template, then folder `uploads` should be created under `web`.

That's it. Load the page and try uploading. Uploads should end up in `basic/web/uploads`.

Validation
----------

It's often required to adjust validation rules to accept certain files only or require uploading. Below we'll review
some common rule configurations.

### Required

If you need to make the file upload mandatory, use `skipOnEmpty` like the following:

```php
public function rules()
{
    return [
        [['file'], 'file', 'skipOnEmpty' => false],
    ];
}
```

### MIME type

It is wise to validate the type of file uploaded. FileValidator has the property `$extensions` for this purpose:

```php
public function rules()
{
    return [
        [['file'], 'file', 'extensions' => 'gif, jpg'],
    ];
}
```

By default it will validate against file content mime type corresponding to extension specified. For gif it will be
`image/gif`, for `jpg` it will be `image/jpeg`.


Note that some mime types can't be detected properly by PHP's fileinfo extension that is used by `file` validator. For
example, `csv` files are detected as `text/plain` instead of `text/csv`. You can turn off such behavior by setting
`checkExtensionByMimeType` to `false` and specifying mime types manually:

```php
public function rules()
{
    return [
        [['file'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'csv', 'mimeTypes' => 'text/plain'],
    ];
}
```

[List of common media types](http://en.wikipedia.org/wiki/Internet_media_type#List_of_common_media_types)

### Image properties

If you upload an image, [[yii\validators\ImageValidator|ImageValidator]] may come in handy. It verifies if an attribute
received a valid image that can be then either saved or processed using the [Imagine Extension](https://github.com/yiisoft/yii2/tree/master/extensions/imagine).

Uploading multiple files
------------------------

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

# Upload File with Yii2

### First you need to create a model that will handle the form of download the file.

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
In this code, we created a model ```UploadForm``` with an attribute ```$file``` that will be is ```<input type="file">``` in upload form and pointed out to him validation rule ```file```. This rule is [[yii\validators\FileValidator|FileValidator]]

### Secondly create a view for our model.
```php
<?php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<?= $form->field($model, 'file')->fileInput() ?>

<button>Submit</button>

<?php ActiveForm::end(); ?>
```

It is different attribute ```'enctype' => 'multipart/form-data'``` from the standard form. This value is required when you are using forms that have a file upload control. ```fileInput()``` represents a form input field.

### Thirdly, that create the controller that will connect our form and model.
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
The difference here from the standard crud action, so use ```UploadedFile::getInstance(...)``` instead ```model->load(...)```. [[\yii\web\UploadedFile|UploadedFile]] does not run the model validation, it only provides information about the uploaded file. Therefore, you need to run validation manually ```$model->validate()```. This triggers the [[yii\validators\FileValidator|FileValidator]] that expects a file
```php
$file instanceof UploadedFile || $file->error == UPLOAD_ERR_NO_FILE //in code framework
```

If validation done without errors, then save the file 
```php
$model->file->saveAs('uploads/' . $model->file->baseName . '.' . $model->file->extension);
```
If you use "basic" application then forlder ```uploads``` should be create inside ```web``` folder.

Everything is ready, now run the page and download the file. Check the folder ```basic/web/uploads``` to make sure that you have downloaded.

## Additional information.

***

### Required rule

If you need to check the mandatory download the file, then use ```skipOnEmpty```.
```php
public function rules()
{
    return [
        [['file'], 'file', 'skipOnEmpty' => false],
    ];
}
```

***

### Path upload folder

Folder to download the file can be installed using ```Yii::getAlias('@app/uploads')```. This base path of currently running application and folder ```uploads``

***

### MIME type

FileValidator have property ```$types```
```php
public function rules()
{
    return [
        [['file'], 'file', 'types' => 'gif, jpg',],
    ];
}
```
it pulls
```php
in_array(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)), $this->types, true))
```
As you can see, the name of the expansion may be one and the file type - other, actually.

``UploadedFile::getInstance()->type``` also do not take this value for granted.
Instead, use [[\yii\helpers\BaseFileHelper|FileHelper]] and his [[FileHelper::getMimeType()]] to determine the exact MIME type. 

If allowed to **load only the images**, using [[\yii\validators\ImageValidator|ImageValidator]] instead  [[yii\validators\FileValidator|FileValidator]].

```php
public function rules()
{
    return [
        [['file'], 'image', 'mimeTypes' => 'image/jpeg, image/png',],
    ];
}
```
```ImageValidator``` use use ```yii\helpers\FileHelper;``` for check mime types. 
[List Mime types](http://en.wikipedia.org/wiki/Internet_media_type#List_of_common_media_types)

***

### Multiple files uploader

If you need download multiple files, you will need to alter slightly the controller and view.
At first view:

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

In fact the only difference is in the one row.
```php
<?= $form->field($model, 'file[]')->fileInput(['multiple' => '']) ?>
```
instead
```php
<?= $form->field($model, 'file')->fileInput() ?>
```

* ```['multiple' => '']``` - HTML <input> multiple Attribute
* ```file[]``` vs ```file`` - need, otherwise UploadedFile sees only one file

We now turn to the controller
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
Here the differences in:
* ``` UploadedFile::getInstances($model, 'file');``` instead ``` UploadedFile::getInstance($model, 'file');```. First returns **all** uploaded files for the given model attribute, second - one.
* All other differences follow from the first.

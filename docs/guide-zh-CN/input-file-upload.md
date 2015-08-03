上传文件 //Translated By Dyingmars@github
============

在Yii里上传文件常常使用[[yii\web\UploadedFile]]。
这将会把每个上传的文件封装成UploadedFile对象。
结合[[yii\widgets\ActiveForm]]和[models](structure-models.md),你可以轻松实现安全上传文件机制。


##创建模型 <span id="creating-models"></span>

和普通的文本输入框相同，当要上传一个文件时，你需要创建一个模型类并且使用其中的某个属性来接收上传的文件实例。
你还需要声明一条验证规则以验证上传的文件。
举例来讲，

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

在以上代码里，`imageFile`属性用于接收上传的文件实例。它依赖于一条`file`验证规则，
该规则使用[[yii\validators\FileValidator]]以限制上传的文件扩展名只能为`png`或`jpg`。
`upload()`方法会执行验证并且把上传的文件保存在服务器上.

通过`file`验证器,你可以检查文件的扩展,大小,MIME类型,等等.具体请查阅
[Core Validatators](tutorial-core-validators.md#file) 章节.

>Tip:如果要上传一张图片,你可以考虑使用`image`验证器.
`image`验证器通过[[yii\validators\ImageValidator]]调用执行验证,
一旦验证有属性接受了有效的图片文件,就可将该文件保存或者使用扩展类[Imagine Extension](https://github.com/yiisoft/yii2-imagine)进行处理.


##渲染输入的文件 <span id="rendering-file-input"></span>

接下来,在视图里创建一个文件输入控件

```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'imageFile')->fileInput() ?>

    <button>Submit</button>

<?php ActiveForm::end() ?>
```

需要注意的是要记得在form选项里加入`enctype`属性以确保文件能被正常上传.
`fileInput()`方法会渲染一个`<input type="file">`标签,让用户可以选择一个文件上传.


## 连接 <span id="wiring-up"></span>

现在,在控制器方法里编写连接模型和视图的代码以执行文件上传.

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

在上面的代码里,当提交表单的时候,[[yii\web\UploadedFile::getInstance()]]方法就被调用,上传的文件被一个`UploadedFile`实例替代.
然后，我们依靠模型验证确保上传的文件是有效的，
并将文件保存在服务器上。


## 上传多个文件 <span id="uploading-multiple-files"></span>

将前面所述的代码做一些调整,也可以利用之一次上传多个文件.

首先你得调整模型类,在`file`验证规则里增加一个`maxFiles`选项,约定一次上传文件的最大数量.
`upload()`方法也得修改,
把上传的多个文件一个一个地保存.

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

在视图文件里,你需要把`multiple`选项添加到`fileInput()`,
这样上传控件就可以接收多个文件.

```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'imageFiles[]')->fileInput(['multiple' => true, 'accept' => 'image/*']) ?>

    <button>Submit</button>

<?php ActiveForm::end() ?>
```

And finally in the controller action, you should call `UploadedFile::getInstances()` instead of 
`UploadedFile::getInstance()` to assign an array of `UploadedFile` instances to `UploadForm::imageFiles`. 

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
                // file is uploaded successfully
                return;
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
```

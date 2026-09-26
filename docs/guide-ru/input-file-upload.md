Загрузка файлов
===============

Загрузка файлов в Yii, обычно, выполняется при помощи класса [[yii\web\UploadedFile]], который представляет каждый
загруженный файл в виде объекта `UploadedFile`. Используя [[yii\widgets\ActiveForm]] и [модели](structure-models.md)
можно легко создать безопасный механизм загрузки файлов.


## Создание моделей <span id="creating-models"></span>

Как и в случае с обработкой текстового ввода, для загрузки файла можно создать класс модели и использовать его атрибут
для хранения экземпляра объекта `UploadedFile`, содержащего параметры загруженного файла. Так же, возможно
использование правил валидации модели для проверки загруженного файла. Например,

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

В примере выше атрибут `imageFile` используется для хранения экземпляра  загруженного файла. Правило валидации `file`,
которое, при помощи валидатора [[yii\validators\FileValidator]], проверяет расширение загруженного файла на
соответствие с `png` или `jpg`. Метод `upload()` выполняет валидацию и сохраняет загруженный файл на сервере.

Валидатор `file` позволяет проверять расширение, размер, тип MIME и другие параметры загруженного файла.
Подробности в разделе [Встроенные валидаторы](tutorial-core-validators.md#file).

> Tip: При загрузке изображений лучше использовать соответствующий валидатор `image`. Данный валидатор
реализован классом [[yii\validators\ImageValidator]] и позволяет проверить корректность загруженного
изображения при помощи [расширения Imagine](https://github.com/yiisoft/yii2-imagine).


## Представление <span id="rendering-file-input"></span>

Теперь можно создать представление, отображающее поле загрузки файла:

```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'imageFile')->fileInput() ?>

    <button>Submit</button>

<?php ActiveForm::end() ?>
```

Важно помнить, что для корректной загрузки файла необходим параметр формы `enctype`. Метод `fileInput()`
выведет тег `<input type="file">`, позволяющий пользователю выбрать файл для загрузки.

> Tip: начиная с версии 2.0.8, [[yii\widgets\ActiveField::fileInput|fileInput]] автоматически добавляет
  к форме свойство `enctype`, если в ней есть поле для загрузки файла.

## Загрузка <span id="wiring-up"></span>

Теперь напишем код действия контроллера, который объединит модель и представление.

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

При получении данных, отправленных из формы, для создания из загруженного файла экземпляра объекта `UploadedFile`,
вызывается метод [[yii\web\UploadedFile::getInstance()]]. Далее всю работу по валидации и сохранению загруженного
файла на сервере берет на себя модель.


## Загрузка нескольких файлов <span id="uploading-multiple-files"></span>

Для загрузки нескольких файлов достаточно внести в предыдущий код несколько небольших изменений.

Сначала нужно добавить в правило валидации `file` параметр `maxFiles` для ограничения максимального количества
загружаемых одновременно файлов. Установка `maxFiles` равным `0` означает снятие ограничений на количество файлов,
которые могут быть загружены одновременно. Максимально разрешенное количество одновременно закачиваемых файлов
также ограничивается директивой PHP [`max_file_uploads`](https://www.php.net/manual/ru/ini.core.php#ini.max-file-uploads),
и по умолчанию равно 20. Метод `upload()` нужно изменить для сохранения загруженных файлов по одному.

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

В представлении, в вызов метода `fileInput()`, нужно добавить параметр `multiple` для того, чтобы поле *input* позволяло выбирать несколько файлов одновременно. Необходимо изменить `imageFiles` на `imageFiles[]` чтобы атрибут передавался в виде массива:
 
```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'imageFiles[]')->fileInput(['multiple' => true, 'accept' => 'image/*']) ?>

    <button>Submit</button>

<?php ActiveForm::end() ?>
```

В действии контроллера нужно заменить вызов `UploadedFile::getInstance()` на `UploadedFile::getInstances()` для присвоения атрибуту модели `imageFiles` массива объектов `UploadedFile`.

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

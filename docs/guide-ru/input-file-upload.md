Загрузка файлов
===============

<<<<<<< HEAD
Загрузка файлов в Yii выполняется с помощью модели-формы, её правил валидации и некоторого кода в контроллере. Давайте
посмотрим подробнее, что необходимо для загрузки файлов.

Загрузка одного файла
---------------------

Для начала требуется создать модель, которая будет обрабатывать загрузку файлов. Создайте `models/UploadForm.php` со
следующим содержанием:
=======
Загрузка файлов в Yii, обычно, выполняется при помощи класса [[yii\web\UploadedFile]], который представляет каждый
загруженный файл в виде объекта `UploadedFile`. Используя [[yii\widgets\ActiveForm]] и [модели](structure-models.md)
можно легко создать безопасный механизм загрузки файлов.


## Создание моделей <span id="creating-models"></span>

Как и в случае с обработкой текстового ввода, для загрузки файла можно создать класс модели и использовать его атрибут
для хранения экземпляра объекта `UploadedFile`, содержащего параметры загруженного файла. Так же, возможно
использование правил валидации модели для проверки загруженного файла. Например,
>>>>>>> master

```php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

<<<<<<< HEAD
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

В коде что выше, мы создали модель `UploadForm` с свойством `file`, которое станет в HTML форме  `<input type="file">`.
В правилах валидации это свойство описывается правилом `file`, которое используется [[yii\validators\FileValidator|FileValidator]].

### Вид формы

Далее создайте вид, который будет выводить форму:
=======
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
>>>>>>> master

```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

<<<<<<< HEAD
<?= $form->field($model, 'file')->fileInput() ?>

<button>Отправить</button>
=======
    <?= $form->field($model, 'imageFile')->fileInput() ?>

    <button>Submit</button>
>>>>>>> master

<?php ActiveForm::end() ?>
```

<<<<<<< HEAD
`'enctype' => 'multipart/form-data'` необходимо для того, чтобы форма поддерживала отправку файлов. `fileInput()` 
формирует файловый элемент формы.

### Контроллер

Теперь создайте контроллер, который соединит вид формы и модель:
=======
Важно помнить, что для корректной загрузки файла, необходим параметр формы `enctype`. Метод `fileInput()`
выведет тег `<input type="file">`, позволяющий пользователю выбрать файл для загрузки.


## Загрузка <span id="wiring-up"></span>

Теперь напишем код действия контроллера, который объединит модель и представление.
>>>>>>> master

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
<<<<<<< HEAD
            $model->file = UploadedFile::getInstance($model, 'file');

            if ($model->file && $model->validate()) {                
                $model->file->saveAs('uploads/' . $model->file->baseName . '.' . $model->file->extension);
=======
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            if ($model->upload()) {
                // file is uploaded successfully
                return;
>>>>>>> master
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
```

<<<<<<< HEAD
Вместо  `model->load(...)` мы используем `UploadedFile::getInstance(...)`. Так как [[\yii\web\UploadedFile|UploadedFile]] 
не запускает процесс валидации модели, а только предоставляет информацию о загруженном файле. Поэтому необходимо
произвести валидацию самостоятельно, через `$model->validate()`, которая вызовет [[yii\validators\FileValidator|FileValidator]].
Валидатор ожидает свойство, которое содержит информацию о загружаемом файле - вы это можете увидеть во внутреннем коде этого
валидатора:

```php
if (!$file instanceof UploadedFile || $file->error == UPLOAD_ERR_NO_FILE) {
    return [$this->uploadRequired, []];
}
```

Если валидация пройдена успешна, то сохраняем файл:

```php
$model->file->saveAs('uploads/' . $model->file->baseName . '.' . $model->file->extension);
```

Если вы используете "basic" шаблона приложения, то директория `uploads` должна быть создана внутри `web`.

Всё. Откройте форму и попробуйте загрузить файл. Он должен загрузиться в `basic/web/uploads`.

Валидация
----------

Очень часто требуется разрешить загрузку только определённых файлов или установить обязательную загрузку. Ниже мы 
рассмотрим некоторые общие настройки правила `file`.

### Обязательная загрузка файла

Если требуется обязательная загрузка файла, используйте `skipOnEmpty`:

```php
public function rules()
{
    return [
        [['file'], 'file', 'skipOnEmpty' => false],
    ];
}
```

### тип MIME

Разумно проверять тип загружаемого файла. Для этого FileValidator имеет свойство `$extensions`:

```php
public function rules()
{
    return [
        [['file'], 'file', 'extensions' => 'gif, jpg'],
    ];
}
```

По-умолчанию, эта проверка будет также проверять MIME-тип данных, в соответствии с расширением. Для gif это будет
`image/gif`, для `jpg` - `image/jpeg`.

Обратите внимание, что некоторые MIME-типы могут быть определены неверно через fileinfo, расширение PHP, которое 
использует FileValidator. Например, `csv` файлы будут определены как `text/plain` вместо корректного `text/csv`.
Вы можете отключить такое поведение через установку свойства `checkExtensionByMimeType` в `false` и указать
корректный MIME-тип вручную:

```php
public function rules()
{
    return [
        [['file'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'csv', 'mimeTypes' => 'text/plain'],
    ];
}
```

[Список MIME-типов](https://ru.wikipedia.org/wiki/%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA_MIME-%D1%82%D0%B8%D0%BF%D0%BE%D0%B2#List_of_common_media_types)

### Свойства изображений

Для загрузки изображений вам может пригодиться валидатор [[yii\validators\ImageValidator|ImageValidator]]. Он проверяет
является ли файл изображением, которое затем может быть обработано или сохранено с помощью [Imagine Extension](https://github.com/yiisoft/yii2-imagine).

Загрузка нескольких файлов
------------------------

Если вам нужно загрузить несколько файлов одновременно, то необходимо внести некоторые корректировки.
 
В модель:

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
            [['file'], 'file', 'maxFiles' => 10], // <--- здесь!
        ];
    }
}
```

В виде:

```php
<?php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);
?>

<?= $form->field($model, 'file[]')->fileInput(['multiple' => true]) ?>

    <button>Отправить</button>

<?php ActiveForm::end(); ?>
```

Изменения внесены в строку

```php
<?= $form->field($model, 'file[]')->fileInput(['multiple' => true]) ?>
```

В контроллере:
=======
При получении данных, отправленных из формы, для создания из загруженного файла экземпляра объекта `UploadedFile`,
вызывается метод [[yii\web\UploadedFile::getInstance()]]. Далее всю работу по валидации и сохранению загруженного
файла на сервере берет на себя модель.


## Загрузка нескольких файлов <span id="uploading-multiple-files"></span>

Для загрузки нескольких файлов достаточно внести в предыдущий код несколько небольших изменений.

Сначала нужно добавить в правило валидации `file` параметр `maxFiles` для ограничения максимального количества
загружаемых одновременно файлов. Установка `maxFiles` равным `0` означает снятие ограничений на количество файлов,
которые могут быть загружены одновременно. Максимально разрешенное количество одновременно закачиваемых файлов
также ограничивается директивой PHP [`max_file_uploads`](http://php.net/manual/ru/ini.core.php#ini.max-file-uploads),
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

В представлении, в вызов метода `fileInput()`, нужно добавить параметр `multiple` для того, чтобы поле *input* позволяло выбирать несколько файлов одновременно:
 
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
>>>>>>> master

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
<<<<<<< HEAD
            $model->file = UploadedFile::getInstances($model, 'file');
            
            if ($model->file && $model->validate()) {
                foreach ($model->file as $file) {
                    $file->saveAs('uploads/' . $file->baseName . '.' . $file->extension);
                }
=======
            $model->imageFiles = UploadedFile::getInstances($model, 'imageFiles');
            if ($model->upload()) {
                // file is uploaded successfully
                return;
>>>>>>> master
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
```
<<<<<<< HEAD

Есть два отличия в контроллере от загрузки одного файла. Во-первых используется `UploadedFile::getInstances($model, 'file');`
вместо `UploadedFile::getInstance($model, 'file');`. Первый возвратит информацию обо **всех** загруженных файлах, в то
время как, как второй вернёт информацию только об одном загруженном файле. Во-вторых отличие в том, что применяется
`foreach` для сохранения каждого файла. 
=======
>>>>>>> master

Wysyłanie plików
===============

Przesyłanie plików w Yii jest zazwyczaj wykonywane przy użyciu klasy [[yii\http\UploadedFile|UploadedFile]], która hermetyzuje każdy przesłany plik jako obiekt `UploadedFile`.
W połączeniu z [[yii\widgets\ActiveForm|ActiveForm]] oraz [modelem](structure-models.md), możesz w łatwy sposób zaimplementować bezpieczny mechanizm przesyłania plików.

## Tworzenie modeli <span id="creating-models"></span>

Tak jak przy zwykłych polach tekstowych, aby przesłać pojedyńczy plik musisz utworzyć klasę modelu oraz użyć atrybutu tego modelu do przechowania instancji przesłanego pliku.
Powinieneś również zadeklarować zasadę walidacji do zwalidowania przesłanego pliku.
Dla przykładu:

```php
namespace app\models;

use yii\base\Model;
use yii\http\UploadedFile;

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

W powyższym kodzie, atrybut `imageFile` zostanie użyty do przechowania instancji przesłanego pliku. Jest połączony z zasadą walidacji `file`, która korzysta z 
walidatora [[yii\validators\FileValidator|FileValidator]], aby upewnić się, że przesłany plik posiada rozszerzenie `png` lub `jpg`.
Metoda `upload()` wywoła walidację oraz zapis przesłanego pliku na serwerze.

Walidator `file` pozwala na sprawdzenie rozszerzenia, wielkości, typu MIME, itp. 
Po więcej szczegółów zajrzyj do sekcji [Podstawowe walidatory](tutorial-core-validators.md#file)

> Tip: Jeśli przesyłasz obrazek, możesz rozważyć użycie walidatora `image`. 
> Walidator ten jest implementowany przez [[yii\validators\ImageValidator|ImageValidator]], który weryfikuje czy atrybut otrzymał prawidłowy obrazek który może być 
> zapisany i przetworzony przez [rozszerzenie Imagine](https://github.com/yiisoft/yii2-imagine).

## Renderowanie pola wyboru pliku <span id="rendering-file-input"></span>

Po zapisaniu modelu, utwórz pole wyboru pliku w widoku:

```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'imageFile')->fileInput() ?>

    <button>Wyślij</button>

<?php ActiveForm::end() ?>
```

Należy pamiętać, aby dodać opcję `enctype` do formularza, przez co plik będzie mógł być prawidłowo przesłany.
Wywołanie `fileInput()` spowoduje wyrenderowanie tagu `<input type="file">`, który pozwala użytkownikowi na wybranie oraz przesłanie pliku.

> Tip: od wersji 2.0.8, [[yii\widgets\ActiveField::fileInput|fileInput]] dodaje automatycznie opcję `enctype` do formularza, kiedy pole typu 'file input' jest używane.

## Implementacja kontrolera <span id="wiring-up"></span>

W akcji kontrolera musimy połączyć model oraz widok aby zaimplementować przesyłanie plików:

```php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\UploadForm;
use yii\http\UploadedFile;

class SiteController extends Controller
{
    public function actionUpload()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            if ($model->upload()) {
                // plik został przesłany
                return;
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
```

W powyższym kodzie, kiedy formularz jest wysłany, metoda [[yii\http\UploadedFile::getInstance()|getInstance()]] wywoływana jest do reprezentowania pliku jako instancji `UploadedFile`.
Następnie przystępujemy do walidacji modelu, aby upewnić się, że przesłany plik jest prawidłowy, po czym zapisujemy go na serwerze.

## Przesyłanie wielu plików <span id="uploading-multiple-files"></span>

Możesz przesyłać wiele plików za jednym razem, modyfikując odrobinę kod wylistowany w powyższych sekcjach.

Najpierw powinieneś dostosować klasę modelu dodając opcję `maxFiles` do zasady walidacji `file`, aby określić dozwoloną maksymalną liczbę przesyłanych plików.
Metoda `upload()` powinna również zostać zaktualizowana, aby zapisywać pliki jeden po drugim.

```php
namespace app\models;

use yii\base\Model;
use yii\http\UploadedFile;

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

W pliku widoku, powinieneś dodać opcję `multiple` do wywołania `fileInput()`, aby pole wyboru pliku pozwalało na wybór wielu plików na raz:
 
```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'imageFiles[]')->fileInput(['multiple' => true, 'accept' => 'image/*']) ?>

    <button>Submit</button>

<?php ActiveForm::end() ?>
```

Na koniec, w akcji kontrolera musimy zmienić wywołanie `UploadedFile::getInstance()` na `UploadedFile::getInstances()`, aby przypisać tablicę instancji `UploadedFile` 
do `UploadForm::imageFiles`. 

```php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\UploadForm;
use yii\http\UploadedFile;

class SiteController extends Controller
{
    public function actionUpload()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->imageFiles = UploadedFile::getInstances($model, 'imageFiles');
            if ($model->upload()) {
                // plik został przesłany
                return;
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
```

ファイルをアップロードする
==========================

Yii におけるファイルのアップロードは、通常、アップロードされる個々のファイルを `UploadedFile` としてカプセル化する [[yii\web\UploadedFile]] の助けを借りて実行されます。
これを [[yii\widgets\ActiveForm]] および [モデル](structure-models.md) と組み合わせることで、安全なファイルアップロードメカニズムを簡単に実装することが出来ます。


## モデルを作成する <span id="creating-models"></span>

プレーンなテキストインプットを扱うのと同じように、一つのファイルをアップロードするためには、モデルクラスを作成して、そのモデルの一つの属性を使ってアップロードされるファイルのインスタンスを保持します。
また、ファイルのアップロードを検証するために、検証規則も宣言しなければなりません。
例えば、


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

上記のコードにおいては、`imageFile` 属性がアップロードされたファイルのインスタンスを保持するのに使われます。
この属性が関連付けられている `file` 検証規則は、[[yii\validators\FileValidator]] を使って、`png` または `jpg` の拡張子を持つファイルがアップロードされることを保証しています。
`upload()` メソッドは検証を実行して、アップロードされたファイルをサーバに保存します。

`file` バリデータによって、ファイル拡張子、サイズ、MIME タイプなどをチェックすることが出来ます。
詳細については、[コアバリデータ](tutorial-core-validators.md#file) の節を参照してください。

> Tip|ヒント: 画像をアップロードしようとする場合は、`image` バリデータを代りに使うことを考慮しても構いません。
`image` バリデータは [[yii\validators\ImageValidator]] によって実装されており、属性が有効な画像、すなわち、保存したり [Imagine エクステンション](https://github.com/yiisoft/yii2-imagine) を使って処理したりすることが可能な有効な画像を、受け取ったかどうかを検証します。


上記のコードにおいて作成した `UploadForm` というモデルは、HTML フォームで `<input type="file">` となる `$file` という属性を持ちます。
この属性は [[yii\validators\FileValidator|FileValidator]] を使用する `file` という検証規則を持ちます。

## ファイルインプットをレンダリングする <span id="rendering-file-input"></span>

次に、ビューでファイルインプットを作成します。

```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'imageFile')->fileInput() ?>

    <button>送信</button>

<?php ActiveForm::end() ?>
```

ファイルが正しくアップロードされるように、フォームに `enctype` オプションを追加することを憶えておくのは重要なことです。
`fileInput()` を呼ぶと `<input type="file">` のタグがレンダリングされて、ユーザがアップロードするファイルを選ぶことが出来るようになります。


## 繋ぎ合せる <span id="wiring-up"></span>

そして、コントローラアクションの中で、モデルとビューを繋ぎ合せるコードを書いて、ファイルのアップロードを実装します。

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
                // ファイルのアップロードが成功
                return;
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
```

上記のコードでは、フォームが送信されると [[yii\web\UploadedFile::getInstance()]] メソッドが呼ばれて、アップロードされたファイルが `UploadedFile` のインスタンスとして表現されます。
そして、次に、モデルの検証によってアップロードされたファイルが有効なものであることを確かめ、サーバにファイルを保存します。


## 複数のファイルをアップロードする <span id="uploading-multiple-files"></span>

ここまでの項で示したコードに若干の修正を加えれば、複数のファイルを一度にアップロードすることも出来ます。

最初に、モデルクラスを修正して、`file` 検証規則に `maxFiles` オプションを追加して、アップロードを許可されるファイルの最大数を制限しなければなりません。
`upload()` メソッドも、アップロードされた複数のファイルを一つずつ保存するように修正しなければなりません。

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

ビューファイルでは、`fileInput()` の呼び出しに `multiple` オプションを追加して、ファイルアップロードのフィールドが複数のファイルを受け取ることが出来るようにしなければなりません。

```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'imageFiles[]')->fileInput(['multiple' => true, 'accept' => 'image/*']) ?>

    <button>送信</button>

<?php ActiveForm::end() ?>
```

そして、最後に、コントローラアクションの中では、`UploadedFile::getInstance()` の代りに `UploadedFile::getInstances()` を呼んで、`UploadedFile` インスタンスの配列を `UploadForm::imageFiles` に代入しなければなりません。

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
                // ファイルのアップロードが成功
                return;
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
```

ファイルをアップロードする
==========================

Yii におけるファイルのアップロードは、フォームモデル、その検証規則、そして、いくらかのコントローラコードによって行われます。
アップロードを適切に処理するために何が必要とされるのか、見ていきましよう。


一つのファイルをアップロードする
--------------------------------

まず最初に、ファイルのアップロードを処理するモデルを作成する必要があります。
次の内容を持つ `models/UploadForm.php` を作成してください。

```php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

/**
 * UploadForm : アップロードのフォームの背後にあるモデル
 */
class UploadForm extends Model
{
    /**
     * @var UploadedFile file 属性
     */
    public $file;

    /**
     * @return array 検証規則
     */
    public function rules()
    {
        return [
            [['file'], 'file'],
        ];
    }
}
```

上記のコードにおいて作成した `UploadForm` というモデルは、HTML フォームで `<input type="file">` となる `$file` という属性を持ちます。
この属性は [[yii\validators\FileValidator|FileValidator]] を使用する `file` という検証規則を持ちます。

### フォームのビュー

次に、フォームを表示するビューを作成します。

```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

<?= $form->field($model, 'file')->fileInput() ?>

<button>送信</button>

<?php ActiveForm::end() ?>
```

ファイルのアップロードを可能にする `'enctype' => 'multipart/form-data'` は不可欠です。
`fileInput()` がフォームの入力フィールドを表します。

### コントローラ

そして、フォームとモデルを結び付けるコントローラを作成します。

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

`model->load(...)` の代りに `UploadedFile::getInstance(...)` を使っています。
[[\yii\web\UploadedFile|UploadedFile]] はモデルの検証を実行せず、アップロードされたファイルに関する情報を提供するだけです。
そのため、`$model->validate()` を手作業で実行して、[[yii\validators\FileValidator|FileValidator]] を起動する必要があります。
[[yii\validators\FileValidator|FileValidator]] は、下記のコアコードが示しているように、属性がファイルであることを要求します。

```php
if (!$file instanceof UploadedFile || $file->error == UPLOAD_ERR_NO_FILE) {
    return [$this->uploadRequired, []];  // "ファイルをアップロードしてください。" というエラーメッセージ
}
```

検証が成功したら、ファイルを保存します。

```php
$model->file->saveAs('uploads/' . $model->file->baseName . '.' . $model->file->extension);
```

「ベーシック」プロジェクトテンプレートを使っている場合は、`uploads` フォルダを `web` の下に作成しなければなりません。

以上です。ページをロードして、アップロードを試して見てください。ファイルは `basic/web/uploads` にアップロードされます。

検証
----

たいていの場合、検証規則を調整して、特定のファイルだけを受け取るようにしたり、アップロードを必須としたりする必要があります。
下記で、よく使われる規則の構成を見てみましよう。

### Required

ファイルのアップロードを必須とする必要がある場合は、次のように `skipOnEmpty` を `false` に設定します。

```php
public function rules()
{
    return [
        [['file'], 'file', 'skipOnEmpty' => false],
    ];
}
```

### MIME タイプ

アップロードされるファイルのタイプを検証することは賢明なことです。
`FileValidator` はこの目的のための `extensions` プロパティを持っています。

```php
public function rules()
{
    return [
        [['file'], 'file', 'extensions' => 'gif, jpg'],
    ];
}
```

デフォルトでは、ファイルのコンテントの MIME タイプが指定された拡張子に対応するものであるかどうかが検証されます。
例えば、`gif` に対しては `image/gif`、`jpg` に対しては `image/jpeg` であるかどうかが検証されます。

MIME タイプの中には、`file` バリデータによって使われている PHP fileinfo 拡張では適切に検知することが出来ないものがあることに注意してください。
例えば、`csv` ファイルは `text/csv` ではなく `text/plain` として検知されます。
このような振る舞いを避けるために、`checkExtensionByMimeType` を `false` に設定して、MIME タイプを手動で指定することが出来ます。

```php
public function rules()
{
    return [
        [['file'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'csv', 'mimeTypes' => 'text/plain'],
    ];
}
```

[一般的なメディアタイプの一覧表](http://en.wikipedia.org/wiki/Internet_media_type#List_of_common_media_types)

### 画像のプロパティ

画像をアップロードするときは、[[yii\validators\ImageValidator|ImageValidator]] が重宝するでしょう。
このバリデータは、属性が有効な画像を受け取ったか否かを検証します。
画像は、保存するか、または、[Imagine エクステンション](https://github.com/yiisoft/yii2/tree/master/extensions/imagine) によって処理することが出来ます。

複数のファイルをアップロードする
--------------------------------

複数のファイルを一度にアップロードする必要がある場合は、少し修正が必要になります。
 
モデル:

```php
class UploadForm extends Model
{
    /**
     * @var UploadedFile|Null ファイル属性
     */
    public $file;

    /**
     * @return array 検証規則
     */
    public function rules()
    {
        return [
            [['file'], 'file', 'maxFiles' => 10], // <--- ここ !
        ];
    }
}
```

ビュー:

```php
<?php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);
?>

<?= $form->field($model, 'file[]')->fileInput(['multiple' => true]) ?>

    <button>送信</button>

<?php ActiveForm::end(); ?>
```

違いがあるのは、次の行です。

```php
<?= $form->field($model, 'file[]')->fileInput(['multiple' => true]) ?>
```

コントローラ:

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

単一のファイルのアップロードとは、二つの点で異なります。
最初の違いは、`UploadedFile::getInstance($model, 'file');` の代りに `UploadedFile::getInstances($model, 'file');` が使用されることです。
前者が一つのインスタンスを返すだけなのに対して、後者はアップロードされた **全ての** ファイルのインスタンスを返します。
第二の違いは、`foreach` によって、全てのファイルをそれぞれ保存している点です。

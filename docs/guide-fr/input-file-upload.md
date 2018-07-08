Chargement de fichiers sur le serveur
=====================================

Le chargement de fichiers sur le serveur dans Yii est ordinairement effectué avec l'aide de [[yii\http\UploadedFile]] qui encapsule chaque fichier chargé dans un objet `UploadedFile`. Combiné avec les [[yii\widgets\ActiveForm]] et les [modèles](structure-models.md), vous pouvez aisément mettre en œuvre un mécanisme sûr de chargement de fichiers sur le serveur.


## Création de modèles <span id="creating-models"></span>

Comme on le ferait avec des entrées de texte simple, pour charger un unique fichier sur le serveur, vous devez créer une classe de modèle et utliser un attribut du modèle pour conserver un instance du fichier chargé. Vous devez également déclarer une règle de validation pour valider le fichier chargé. Par exemple : 

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

Dans le code ci-dessus, l'attribut `imageFile` est utilisé pur conserver une instance du fichier chargé. Il est associé à une règle de validation de fichier (`file`) qui utilise [[yii\validators\FileValidator]] pour garantir que l'extension du nom de fichier chargé est `png` ou `jpg`. La méthode `upload()` effectue l'examen de validation et sauvegarde le fichier sur le serveur.

Le validateur `file` vous permet de vérifier l'extension du fichier, sa taille, son type MIME, etc. Reportez-vous à la section [Validateurs de noyau](tutorial-core-validators.md#file) pour plus de détails.

> Tip: si vous chargez une image sur le serveur, vous pouvez envisager l'utilisation du validateur `image` au lieu de `file`. Le validateur `image` est mis en œuvre via [[yii\validators\ImageValidator]] qui vérifie si un attribut a reçu une image valide qui peut être, soit sauvegardée, soit traitée en utilisant l'[extension Imagine](https://github.com/yiisoft/yii2-imagine).


## Rendu d'une entrée de fichier <span id="rendering-file-input"></span>

Ensuite, créez une entrée de fichier dans une vue :

```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'imageFile')->fileInput() ?>

    <button>Submit</button>

<?php ActiveForm::end() ?>
```

Il est important de se rappeler que vous devez ajouter l'option `enctype` au formulaire afin que le fichier soit proprement chargé sur le serveur. L'appel de `fileInput()` rend une balise `<input type="file">` qui permet à l'utilisateur de sélectionner un fichier à charger sur le serveur.

> Tip: depuis la version 2.0.8, [[yii\widgets\ActiveField::fileInput|fileInput]] ajoute l'option `enctype` au formulaire automatiquement lorsqu'un champ d'entrée de fichier est utilisé.

## Câblage <span id="wiring-up"></span>

Maintenant dans une action de contrôleur, écrivez le code de câblage entre le modèle et la vue pour mettre en œuvre le chargement sur le serveur :

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
                // le fichier a été chargé avec succès sur le serveur
                return;
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
```

Dans le code ci-dessus, lorsque le formulaire est soumis, la méthode [[yii\http\UploadedFile::getInstance()]] est appelée pour représenter le fichier chargé sous forme d'instance de `UploadedFile`. Nous comptons ensuite sur la validation du modèle pour garantir que le fichier chargé est valide et le sauvegarder sur le serveur.


## Chargement sur le serveur de plusieurs fichiers  <span id="uploading-multiple-files"></span>

Vous pouvez également charger sur le serveur plusieurs fichiers à la fois, avec quelques ajustements au code présenté dans les sous-sections précédentes.

Tout d'abord, vous devez ajuster la classe du modèle en ajoutant l'option `maxFiles` dans la règle de validation de `file` pour limiter le nombre maximum de fichiers à charger simultanément. Définir `maxFiles` à `0` signifie que ce nombre n'est pas limité. Le nombre maximal de fichiers que l'on peut charger simultanément est aussi limité par la directive PHP [`max_file_uploads`](http://php.net/manual/en/ini.core.php#ini.max-file-uploads), dont la valeur par défaut est 20. La méthode `upload()` doit aussi être modifiée pour permettre la sauvegarde des fichiers un à un.

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

Dans le fichier de vue, vous devez ajouter l'option `multiple` à l'appel de `fileInput()` afin que le champ d'entrée puisse recevoir plusieurs fichiers :
 
```php
<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'imageFiles[]')->fileInput(['multiple' => true, 'accept' => 'image/*']) ?>

    <button>Submit</button>

<?php ActiveForm::end() ?>
```

Pour finir, dans l'action du contrôleur, vous devez appeler `UploadedFile::getInstances()` au lieu de `UploadedFile::getInstance()` pour assigner un tableau d'instances de `UploadedFile` à `UploadForm::imageFiles`. 

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
                // file is uploaded successfully
                return;
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
```

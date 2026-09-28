Validation des entrées utilisateur
==================================

En général, vous ne devriez jamais faire confiance aux données entrées par l'utilisateur et devriez toujours les valider avant de les utiliser.,

Étant donné un [modèle](structure-models.md) rempli par les données entrées par l'utilisateur, il est possible de valider ces entrées en appelant la méthode [[yii\base\Model::validate()]]. La méthode retourne une valeur booléenne qui indique si la validation a réussi ou pas. Si ce n'est pas le cas, vous pouvez obtenir les messages d'erreur depuis la propriété [[yii\base\Model::errors]]. Par exemple :

```php
$model = new \app\models\ContactForm();

// remplit les attributs du modèle avec les entrées de l'utilisateur
$model->load(\Yii::$app->request->post());
// ce qui est équivalent à :
// $model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // toutes les entrées sont valides
} else {
    // la validation a échoué: $errors est un tableau contenant les messages d'erreur
    $errors = $model->errors;
}
```


## Déclaration de règles <span id="declaring-rules"></span>

Pour que `validate()` fonctionne réellement, vous devez déclarer des règles de validation pour les attributs que vous envisagez de valider. Cela peut être réalisé en redéfinissant la méthode [[yii\base\Model::rules()]]. L'exemple suivant montre comment les règles de validation pour le modèle `ContactForm` sont déclarées :

```php
public function rules()
{
    return [
        // les attributs  name, email, subject et  body sont à saisir obligatoirement
        [['name', 'email', 'subject', 'body'], 'required'],

        // l'attribut email doit être une adresse de courriel valide
        ['email', 'email'],
    ];
}
```

La méthode [[yii\base\Model::rules()|rules()]] doit retourner un tableau de règles, dont chacune est un tableau dans le format suivant :


```php
[
    // obligatoire, spécifie quels attributs doivent être validés par cette règle.
    // Pour un attribut unique, vous pouvez utiliser le nom de l'attribut directement
    // sans le mettre dans un tableau
    ['attribute1', 'attribute2', ...],

    // obligatoire, spécifier le type de cette règle.
    // Il peut s'agir d'un nom de classe, d'un alias de validateur ou du nom d'une méthode de validation
    'validator',

    // facultatif, spécifie dans quel(s) scénario(s) cette règle doit être appliquée
    // si absent, cela signifie que la règle s'applique à tous les scénarios
    // Vous pouvez aussi configurer l'option "except" si vous voulez que la règle
    // s'applique à tous les scénarios sauf à ceux qui sont listés
    'on' => ['scenario1', 'scenario2', ...],

    // facultatif, spécifie des configurations additionnelles pour l'objet validateur
    'property1' => 'value1', 'property2' => 'value2', ...
]
```

Pour chacune des règles vous devez spécifier au moins à quels attributs la règle s'applique et quel est le type de cette règle. Vous pouvez spécifier le type de la règle sous l'une des formes suivantes :

* l'alias d'un validateur du noyau, comme `required`, `in`, `date`, etc. Reportez-vous à la sous-section [Validateurs du noyau](tutorial-core-validators.md) pour une liste complète des validateurs du noyau.
* le nom d'une méthode de validation dans la classe du modèle, ou une fonction anonyme. Reportez-vous à la sous-section [Inline Validators](#inline-validators) pour plus de détails.
* un nom de classe de validateur pleinement qualifié. Reportez-vous à la sous-section [Validateurs autonomes](#standalone-validators) pour plus de détails.

Une règle peut être utilisée pour valider un ou plusieurs attributs, et un attribut peut être validé par une ou plusieurs règles. Une règle peut s'appliquer dans certains [scenarios](structure-models.md#scenarios) seulement en spécifiant l'option `on`. Si vous ne spécifiez pas l'option `on`, la règle s'applique à tous les scénarios.

Quand la méthode `validate()` est appelée, elle suit les étapes suivantes pour effectuer l'examen de validation :

1. Détermine quels attributs doivent être validés en obtenant la liste des attributs de [[yii\base\Model::scenarios()]] en utilisant le [[yii\base\Model::scenario|scenario]] courant. Ces attributs sont appelés *attributs actifs*.
2. Détermine quelles règles de validation doivent être appliquées en obtenant la liste des règles de [[yii\base\Model::rules()]] en utilisant le [[yii\base\Model::scenario|scenario]] courant. Ces règles sont appelées *règles actives*.
3. Utilise chacune des règles actives pour valider chacun des attributs qui sont associés à cette règle. Les règles sont évaluées dans l'ordre dans lequel elles sont listées.

Selon les étapes de validation décrites ci-dessus, un attribut est validé si, et seulement si, il est un attribut actif déclaré dans `scenarios()` et est associé à une ou plusieurs règles actives déclarées dans `rules()`.

> Note: il est pratique le nommer les règles, c.-à-d.
>
> ```php
> public function rules()
> {
>     return [
>         // ...
>         'password' => [['password'], 'string', 'max' => 60],
>     ];
> }
> ```
>
> Vous pouvez l'utiliser dans un modèle enfant :
>
> ```php
> public function rules()
> {
>     $rules = parent::rules();
>     unset($rules['password']);
>     return $rules;
> }


### Personnalisation des messages d'erreur <span id="customizing-error-messages"></span>

La plupart des validateurs possèdent des messages d'erreurs qui sont ajoutés au modèle en cours de validation lorsque ses attributs ne passent pas la validation. Par exemple, le validateur [[yii\validators\RequiredValidator|required]] ajoute le message "Username cannot be blank." (Le nom d'utilisateur ne peut être vide.) au modèle lorsque l'attribut `username` ne passe pas la règle de validation utilisant ce validateur.

Vous pouvez personnaliser le message d'erreur d'une règle en spécifiant la propriété `message` lors de la déclaration de la règle, comme ceci :

```php
public function rules()
{
    return [
        ['username', 'required', 'message' => 'Please choose a username.'],
    ];
}
```

Quelques validateurs peuvent prendre en charge des messages d'erreur additionnels pour décrire précisément les différentes causes de non validation. Par exemple, le validateur [[yii\validators\NumberValidator|number]] prend en charge[[yii\validators\NumberValidator::tooBig|tooBig (trop grand)]] et [[yii\validators\NumberValidator::tooSmall|tooSmall (trop petit)]] pour décrire la cause de non validation lorsque la valeur à valider est trop grande ou trop petite, respectivement. Vous pouvez configurer ces messages d'erreur comme vous configureriez d'autres propriétés de validateurs dans une règle de validation.


### Événement de validation <span id="validation-events"></span>

Losque la méthode [[yii\base\Model::validate()]] est appelée, elle appelle deux méthodes que vous pouvez redéfinir pour personnaliser le processus de validation :

* [[yii\base\Model::beforeValidate()]]: la mise en œuvre par défaut déclenche un événement [[yii\base\Model::EVENT_BEFORE_VALIDATE]]. Vous pouvez, soit redéfinir cette méthode, soit répondre à cet événement pour accomplir un travail de pré-traitement (p. ex. normaliser les données entrées) avant que l'examen de validation n'ait lieu. La méthode retourne une valeur booléenne indiquant si l'examen de validation doit avoir lieu ou pas.

* [[yii\base\Model::afterValidate()]]: la mise en œuvre par défaut déclenche un événement [[yii\base\Model::EVENT_AFTER_VALIDATE]]. Vous pouvez, soit redéfinir cette méthode, soit répondre à cet événement pour accomplir un travail de post-traitement après que l'examen de validation a eu lieu.

### Validation conditionnelle <span id="conditional-validation"></span>

Pour valider des attributs seulement lorsque certaines conditions sont réalisées, p. ex. la validation d'un attribut dépend de la valeur d'un autre attribut, vous pouvez utiliser la propriété [[yii\validators\Validator::when|when]] pour définir de telles conditions. Par exemple :

```php
    ['state', 'required', 'when' => function($model) {
        return $model->country == 'USA';
    }]
```

La propriété [[yii\validators\Validator::when|when]] accepte une fonction de rappel PHP avec la signature suivante :

```php
/**
 * @param Model $model le modèle en cours de validation
 * @param string $attribute l'attribut en cours de validation
 * @return bool `true` si la règle doit être appliqué, `false` si non
 */
function ($model, $attribute)
```

Si vous avez aussi besoin de la prise en charge côté client de la validation conditionnelle, vous devez configurer la propriété [[yii\validators\Validator::whenClient|whenClient]] qui accepte une chaîne de caractères représentant une fonction JavaScript dont la valeur de retour détermine si la règles doit être appliquée ou pas. Par exemple :

```php
    ['state', 'required', 'when' => function ($model) {
        return $model->country == 'USA';
    }, 'whenClient' => "function (attribute, value) {
        return $('#country').val() == 'USA';
    }"]
```


### Filtrage des données <span id="data-filtering"></span>

Les entrées utilisateur nécessitent souvent d'être filtrées ou pré-traitées. Par exemple, vous désirez peut-être vous débarrasser des espaces devant et derrière l'entrée `username`. Vous pouvez utiliser les règles de validation pour le faire.

Les exemples suivants montrent comment se débarrasser des espaces dans les entrées et transformer des entrées vides en `nulls` en utilisant les validateurs du noyau [trim](tutorial-core-validators.md#trim) et [default](tutorial-core-validators.md#default) :

```php
return [
    [['username', 'email'], 'trim'],
    [['username', 'email'], 'default'],
];
```

Vous pouvez également utiliser le validateur plus général [filter](tutorial-core-validators.md#filter) pour accomplir un filtrage plus complexe des données.

Comme vous le voyez, ces règles de validation ne pratiquent pas un examen de validation proprement dit. Plus exactement, elles traitent les valeurs et les sauvegardent dans les attributs en cours de validation.


### Gestion des entrées vides <span id="handling-empty-inputs"></span>

Lorsque les entrées sont soumises par des formulaires HTML, vous devez souvent assigner des valeurs par défaut aux entrées si elles restent vides. Vous pouvez le faire en utilisant le validateur [default](tutorial-core-validators.md#default). Par exemple :

```php
return [
    // définit "username" et "email" comme *null* si elles sont vides
    [['username', 'email'], 'default'],

    // définit "level" à 1 si elle est vide
    ['level', 'default', 'value' => 1],
];
```

Par défaut, une entrée est considérée vide si sa valeur est une chaîne de caractères vide, un tableau vide ou un `null`. Vous pouvez personnaliser la logique de détection de vide en configurant la propriété [[yii\validators\Validator::isEmpty]] avec une fonction de rappel PHP. Par exemple :

```php
    ['agree', 'required', 'isEmpty' => function ($value) {
        return empty($value);
    }]
```

> Note: la plupart des validateurs ne traitent pas les entrées vides si leur propriété [[yii\validators\Validator::skipOnEmpty]] prend la valeur par défaut `true` (vrai). Ils sont simplement sautés lors de l'examen de validation si leurs attributs associés reçoivent des entrées vides. Parmi les [validateurs de noyau](tutorial-core-validators.md), seuls les validateurs `captcha`, `default`, `filter`, `required`, et `trim` traitent les entrées vides.


## Validation ad hoc <span id="ad-hoc-validation"></span>

Parfois vous avez besoin de faire une *validation ad hoc* pour des valeurs qui ne sont pas liées à un modèle.

Si vous n'avez besoin d'effectuer qu'un seul type de validation (p. ex. valider une adresse de courriel), vous pouvez appeler la méthode [[yii\validators\Validator::validate()|validate()]] du validateur désiré, comme ceci :

```php
$email = 'test@example.com';
$validator = new yii\validators\EmailValidator();

if ($validator->validate($email, $error)) {
    echo 'Email is valid.';
} else {
    echo $error;
}
```

> Note: tous les validateurs ne prennent pas en charge ce type de validation. Le validateur du noyau [unique](tutorial-core-validators.md#unique), qui est conçu pour travailler avec un modèle uniquement, en est un exemple.

Si vous avez besoin de validations multiples pour plusieurs valeurs, vous pouvez utiliser [[yii\base\DynamicModel]] qui prend en charge, à la fois les attributs et les règles à la volée. Son utilisation ressemble à ce qui suit :

```php
public function actionSearch($name, $email)
{
    $model = DynamicModel::validateData(compact('name', 'email'), [
        [['name', 'email'], 'string', 'max' => 128],
        ['email', 'email'],
    ]);

    if ($model->hasErrors()) {
        // validation fails
    } else {
        // validation succeeds
    }
}
```

La méthode [[yii\base\DynamicModel::validateData()]] crée une instance de `DynamicModel`, définit les attributs utilisant les données fournies (`name` et `email` dans cet exemple), puis appelle [[yii\base\Model::validate()]] avec les règles données.

En alternative, vous pouvez utiliser la syntaxe plus *classique* suivante pour effectuer la validation ad hoc :

```php
public function actionSearch($name, $email)
{
    $model = new DynamicModel(compact('name', 'email'));
    $model->addRule(['name', 'email'], 'string', ['max' => 128])
        ->addRule('email', 'email')
        ->validate();

    if ($model->hasErrors()) {
        // la validation a échoué
    } else {
        // la validation a réussi
    }
}
```

Après l'examen de validation, vous pouvez vérifier si la validation a réussi ou pas en appelant la méthode [[yii\base\DynamicModel::hasErrors()|hasErrors()]] et obtenir les erreurs de validation de la propriété [[yii\base\DynamicModel::errors|errors]], comme vous le feriez avec un modèle normal. Vous pouvez aussi accéder aux attributs dynamiques définis via l'instance de modèle, p. ex. `$model->name` et `$model->email`.


## Création de validateurs <span id="creating-validators"></span>

En plus de pouvoir utiliser les [validateurs du noyau](tutorial-core-validators.md) inclus dans les versions publiées de Yii, vous pouvez également créer vos propres validateurs. Vous pouvez créer des validateurs en ligne et des validateurs autonomes.


### Validateurs en ligne <span id="inline-validators"></span>

Un validateur en ligne est un validateur défini sous forme de méthode de modèle ou de fonction anonyme. La signature de la méthode/fonction est :

```php
/**
 * @param string $attribute l'attribut en cours de validation
 * @param mixed $params la valeur des *paramètres* donnés dans la règle
 */
function ($attribute, $params)
```

Si un attribut ne réussit pas l'examen de validation, la méthode/fonction doit appeler [[yii\base\Model::addError()]] pour sauvegarder le message d'erreur dans le modèle de manière à ce qu'il puisse être retrouvé plus tard pour être présenté à l'utilisateur.

Voici quelques exemples :

```php
use yii\base\Model;

class MyForm extends Model
{
    public $country;
    public $token;

    public function rules()
    {
        return [
            // un validateur en ligne défini sous forme de méthode de modèle validateCountry()
            ['country', 'validateCountry'],

            // un validateur en ligne défini sous forme de fonction anonyme
            ['token', function ($attribute, $params) {
                if (!ctype_alnum($this->$attribute)) {
                    $this->addError($attribute, 'The token must contain letters or digits.');
                }
            }],
        ];
    }

    public function validateCountry($attribute, $params)
    {
        if (!in_array($this->$attribute, ['USA', 'Web'])) {
            $this->addError($attribute, 'The country must be either "USA" or "Web".');
        }
    }
}
```

> Note: Par défaut, les validateurs en ligne ne sont pas appliqués si leurs attributs associés reçoivent des entrées vides ou s'ils ont déjà échoué à des examen de validation selon certaines règles. Si vous voulez être sûr qu'une règle sera toujours appliquée, vous devez configurer les propriétés [[yii\validators\Validator::skipOnEmpty|skipOnEmpty]] et/ou [[yii\validators\Validator::skipOnError|skipOnError]] à `false` (faux) dans les déclarations des règles. Par exemple :
>
> ```php
> [
>     ['country', 'validateCountry', 'skipOnEmpty' => false, 'skipOnError' => false],
> ]
> ```


### Validateurs autonomes <span id="standalone-validators"></span>

Un validateur autonome est une classe qui étend la classe [[yii\validators\Validator]] ou une de ses classe filles. Vous pouvez mettre en œuvre sa logique de validation en redéfinissant la méthode [[yii\validators\Validator::validateAttribute()]]. Si un attribut ne réussit pas l'exament de validation, appellez [[yii\base\Model::addError()]] pour sauvegarder le message d'erreur dans le modèle, comme vous le feriez avec des [validateurs en ligne](#inline-validators).


Par exemple, le validateur en ligne ci-dessus peut être transformé en une nouvelle classe [[components/validators/CountryValidator]].

```php
namespace app\components;

use yii\validators\Validator;

class CountryValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        if (!in_array($model->$attribute, ['USA', 'Web'])) {
            $this->addError($model, $attribute, 'The country must be either "USA" or "Web".');
        }
    }
}
```

Si vous voulez que votre validateur prennent en charge la validation d'une valeur sans modèle, vous devez redéfinir la méthode [[yii\validators\Validator::validate()]]. Vous pouvez aussi redéfinir [[yii\validators\Validator::validateValue()]] au lieu de `validateAttribute()` et `validate()`, parce que, par défaut, les deux dernières méthodes sont appelées en appelant `validateValue()`.

Ci-dessous, nous présentons un exemple de comment utiliser la classe de validateur précédente dans votre modèle.

```php
namespace app\models;

use Yii;
use yii\base\Model;
use app\components\validators\CountryValidator;

class EntryForm extends Model
{
    public $name;
    public $email;
    public $country;

    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            ['country', CountryValidator::class],
            ['email', 'email'],
        ];
    }
}
```


## Validation côté client <span id="client-side-validation"></span>

La validation côté client basée sur JavaScript est souhaitable lorsque l'utilisateur fournit les entrées via des formulaires HTML, parce que cela permet à l'utilisateur de détecter plus vite les erreurs et lui apporte ainsi un meilleur ressenti. Vous pouvez utiliser ou implémenter un validateur qui prend en charge la validation côté client *en plus* de la validation côté serveur.

> Info: bien que la validation côté client soit souhaitable, ce n'est pas une obligation. Son but principal est d'apporter un meilleur ressenti à l'utilisateur. Comme pour les données venant de l'utilisateur, vous ne devriez jamais faire confiance à la validation côté client. Pour cette raison, vous devez toujours effectuer la validation côté serveur en appelant [[yii\base\Model::validate()]], comme nous l'avons décrit dans les sous-sections précédentes.

### Utilisation de la validation côté client <span id="using-client-side-validation"></span>

Beaucoup de  [validateurs du noyau](tutorial-core-validators.md) prennent en charge la validation côté client directement. Tout ce que vous avez à faire c'est utiliser [[yii\widgets\ActiveForm]] pour construire vos formulaires HTML. Par exemple, `LoginForm` ci-dessous déclare deux règles : l'une utilise le validateur du noyau [required](tutorial-core-validators.md#required) qui est pris en charge à la fois côté serveur et côté client ; l'autre utilise le validateur en ligne `validatePassword` qui ne prend pas en charge la validation côté client.

```php
namespace app\models;

use yii\base\Model;
use app\models\User;

class LoginForm extends Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            // username et password sont tous deux obligatoires
            [['username', 'password'], 'required'],

            // password est validé par validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword()
    {
        $user = User::findByUsername($this->username);

        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError('password', 'Incorrect username or password.');
        }
    }
}
```

Le formulaire HTML construit par le code suivant contient deux champs de saisie `username` et `password`. Si vous soumettez le formulaire sans rien saisir, vous recevrez directement les messages d'erreur vous demandant d'entrer quelque chose sans qu'aucune communication avec le serveur n'ait lieu.

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('Login') ?>
<?php yii\widgets\ActiveForm::end(); ?>
```

En arrière plan, [[yii\widgets\ActiveForm]] lit les règles de validation déclarées dans le modèle et génère le code JavaScript approprié pour la prise en charge de la validation côté client. Lorsqu'un utilisateur modifie la valeur d'un champ de saisie ou soumet le formulaire, le code JavaScript est appelé.

Si vous désirez inhiber la validation côté client complètement, vous pouvez configurer la propriété [[yii\widgets\ActiveForm::enableClientValidation]] à `false` (faux). Vous pouvez aussi inhiber la validation côté client pour des champs de saisie individuels en configurant leur propriété [[yii\widgets\ActiveField::enableClientValidation]] à `false`. Lorsque `enableClientValidation` est configurée à la fois au niveau du champ et au niveau du formulaire, c'est la première configuration qui prévaut.


### Mise en œuvre de la validation côté client <span id="implementing-client-side-validation"></span>

Pour créer un validateur qui prend en charge la validation côté client, vous devez implémenter la méthode [[yii\validators\Validator::clientValidateAttribute()]] qui retourne un morceau de code JavaScript propre à effectuer l'examen de validation côté client. Dans ce code JavaScript, vous pouvez utiliser les variables prédéfinies suivantes :

- `attribute`: le nom de l'attribut en cours de validation ;
- `value`: la valeur en cours de validation ;
- `messages`: un tableau utilisé pour contenir les messages d'erreurs pour l'attribut ;
- `deferred`: un tableau dans lequel les objets différés peuvent être poussés (explication dans la prochaine sous-section).

Dans l'exemple suivant, nous créons un `StatusValidator` qui valide une entrée si elle représente l'identifiant d'une donnée existante ayant un état valide. Le validateur prend en charge à la fois la validation côté serveur et la validation côté client.

```php
namespace app\components;

use yii\validators\Validator;
use app\models\Status;

class StatusValidator extends Validator
{
    public function init()
    {
        parent::init();
        $this->message = 'Invalid status input.';
    }

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (!Status::find()->where(['id' => $value])->exists()) {
            $model->addError($attribute, $this->message);
        }
    }

    public function clientValidateAttribute($model, $attribute, $view)
    {
        $statuses = json_encode(Status::find()->select('id')->asArray()->column());
        $message = json_encode($this->message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return <<<JS
if ($.inArray(value, $statuses) === -1) {
    messages.push($message);
}
JS;
    }
}
```

> Tip: le code ci-dessus est donné essentiellement pour démontrer comment prendre en charge la validation côté client. En pratique, vous pouvez utiliser le validateur du noyau [in](tutorial-core-validators.md#in) pour arriver au même résultat. Vous pouvez écrire la règle de validation comme suit :
>
> ```php
> [
>     ['status', 'in', 'range' => Status::find()->select('id')->asArray()->column()],
> ]
> ```

> Tip: si vous avez besoin de travailler à la main avec la validation côté client, c.-à-d. ajouter des champs dynamiquement ou effectuer quelque logique d'interface utilisateur, reportez-vous à [Travail avec ActiveForm via JavaScript](https://github.com/samdark/yii2-cookbook/blob/master/book/forms-activeform-js.md) dans le *Cookbook* de Yii 2.0 .

### Validation différée <span id="deferred-validation"></span>

Si vous devez effectuer une validation asynchrone côté client, vous pouvez créer des [objets différés](https://api.jquery.com/category/deferred-object/). Par exemple, pour effectuer une validation AJAX personnalisée, vous pouvez utiliser le code suivant :

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        deferred.push($.get("/check", {value: value}).done(function(data) {
            if ('' !== data) {
                messages.push(data);
            }
        }));
JS;
}
```

Dans ce qui précède, la variable `deferred` est fournie par Yii, et représente un tableau de d'objets différés. La méthode `$.get()` crée un objet différé qui est poussé dans le tableau `deferred`.

Vous pouvez aussi créer explicitement un objet différé et appeler sa méthode `resolve()` lorsque la fonction de rappel asynchrone est activée . L'exemple suivant montre comment valider les dimensions d'une image à charger sur le serveur du côté client.

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        var def = $.Deferred();
        var img = new Image();
        img.onload = function() {
            if (this.width > 150) {
                messages.push('Image too wide!!');
            }
            def.resolve();
        }
        var reader = new FileReader();
        reader.onloadend = function() {
            img.src = reader.result;
        }
        reader.readAsDataURL(file);

        deferred.push(def);
JS;
}
```

> Note: La méthode `resolve()` doit être appelée après que l'attribut a été validé. Autrement la validation principale du formulaire ne se terminera pas.

Pour faire simple, le tableau `deferred` est doté d'une méthode raccourci `add()` qui crée automatiquement un objet différé et l'ajoute au tableau `deferred`. En utilisant cette méthode, vous pouvez simplifier l'exemple ci-dessus comme suit :

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        deferred.add(function(def) {
            var img = new Image();
            img.onload = function() {
                if (this.width > 150) {
                    messages.push('Image too wide!!');
                }
                def.resolve();
            }
            var reader = new FileReader();
            reader.onloadend = function() {
                img.src = reader.result;
            }
            reader.readAsDataURL(file);
        });
JS;
}
```


### Validation AJAX <span id="ajax-validation"></span>

Quelques validations ne peuvent avoir lieu que côté serveur, parce que seul le serveur dispose des informations nécessaires. Par exemple, pour valider l'unicité d'un nom d'utilisateur, il est nécessaire de consulter la table des utilisateurs côté serveur. Vous pouvez utiliser la validation basée sur AJAX dans ce cas. Elle provoquera une requête AJAX en arrière plan pour exécuter l'examen de validation tout en laissant à l'utilisateur le même ressenti que lors d'une validation côté client normale.

Pour activer la validation AJAX pour un unique champ de saisie, configurez la propriété [[yii\widgets\ActiveField::enableAjaxValidation|enableAjaxValidation]] de ce champ à `true` et spécifiez un `identifiant` unique de formulaire :

```php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'registration-form',
]);

echo $form->field($model, 'username', ['enableAjaxValidation' => true]);

// ...

ActiveForm::end();
```

Pour étendre la validation AJAX à tout le formulaire, configurez la propriété [[yii\widgets\ActiveForm::enableAjaxValidation|enableAjaxValidation]] à `true` au niveau du formulaire :

```php
$form = ActiveForm::begin([
    'id' => 'contact-form',
    'enableAjaxValidation' => true,
]);
```

> Note: lorsque la propriété `enableAjaxValidation` est configurée à la fois au niveau du champ et au niveau du formulaire, la première configuration prévaut.

Vous devez aussi préparer le serveur de façon à ce qu'il puisse prendre en charge les requêtes de validation AJAX . Cela peut se faire à l'aide d'un fragment de code comme celui qui suit dans les actions de contrôleur :

```php
if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return ActiveForm::validate($model);
}
```

Le code ci-dessus vérifie si la requête courante est une requête AJAX. Si oui, il répond à la requête en exécutant l'examen de validation et en retournant les erreurs au format JSON.

> Info: vous pouvez aussi utiliser la [validation différée](#deferred-validation) pour effectuer une validation AJAX. Néanmoins, la fonctionnalité de validation AJAX décrite ici est plus systématique et nécessite moins de codage.

Quand, à la fois `enableClientValidation` et `enableAjaxValidation` sont définies à  `true`, la requête de validation AJAX est déclenchée seulement après une validation réussie côté client.

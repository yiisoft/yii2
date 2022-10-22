Modèles
=======

Les modèles font partie du modèle d'architecture [MVC](https://fr.wikipedia.org/wiki/Mod%C3%A8le-vue-contr%C3%B4leur) (Modèle Vue Contrôleur).
Ces objets représentent les données à traiter, les règles et la logique de traitement. 

Vous pouvez créer des classes de modèle en étendant la classe [[yii\base\Model]] ou ses classe filles. La classe de base [[yii\base\Model]] prend en charge des fonctionnalités nombreuses et utiles :

* Les [attributs](#attributes) : ils représentent les données à traiter et peuvent être accédés comme des propriétés habituelles d'objets ou des éléments de tableaux. 
* Les étiquettes d'[attribut](#attribute-labels) : elles spécifient les étiquettes pour l'affichage des attributs.
* L'[assignation massive](#massive-assignment) : elle permet l'assignation de multiples attributs en une seule étape.
* Les [règles de validation](#validation-rules) : elles garantissent la validité des données saisies en s'appuyant sur des règles de validation déclarées. 
* L'[exportation des données](#data-exporting) : elle permet au modèle de données d'être exporté sous forme de tableaux dans des formats personnalisables.

La classe `Model` est également la classe de base pour des modèles plus évolués, comme la classe class [Active Record (enregistrement actif)](db-active-record.md). Reportez-vous à la documentation ad hoc pour plus de détails sur ces modèles évolués.

> Info: vous n'êtes pas forcé de baser vos classes de modèle sur la classe [[yii\base\Model]]. Néanmoins, comme il y a de nombreux composants de Yii conçus pour prendre en charge la classe [[yii\base\Model]], il est généralement préférable de baser vos modèles sur cette classe.


## Attributs <span id="attributes"></span>

Les modèles représentent les données de l'application en termes  d'attributs. Chaque attribut est comme un propriété publiquement accessible d'un modèle. La méthode [[yii\base\Model::attributes()]] spécifie quels attributs une classe de modèle possède. 

Vous pouvez accéder à un attribut comme vous accédez à une propriété d'un objet ordinaire :

```php
$model = new \app\models\ContactForm;

// "name" is an attribute of ContactForm
$model->name = 'example';
echo $model->name;
```

Vous pouvez également accéder aux attributs comme aux éléments d'un tableau, grâce à la prise en charge de [ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php) et [ArrayIterator](https://www.php.net/manual/en/class.arrayiterator.php)
par la classe [[yii\base\Model]]:

```php
$model = new \app\models\ContactForm;

// accès aux attributs comme à des éléments de tableau
$model['name'] = 'example';
echo $model['name'];

// itération sur les attributs avec foreach
foreach ($model as $name => $value) {
    echo "$name: $value\n";
}
```


### Définition d'attributs <span id="defining-attributes"></span>

Par défaut, si votre classe de modèle étend directement la classe [[yii\base\Model]], toutes ses variables membres *non statiques et publiques* sont des attributs. Par exemple, la classe de modèle `ContactForm` ci-dessous possède quatre attributs : `name`, `email`,
`subject` et `body`. Le modèle `ContactForm` est utilisé pour représenter les données saisies dans un formulaire HTML. 

```php
namespace app\models;

use yii\base\Model;

class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;
}
```


Vous pouvez redéfinir la méthode [[yii\base\Model::attributes()]] pour spécifier les attributs d'une autre manière. La méthode devrait retourner le nom des attributs d'un modèle. Par exemple, [[yii\db\ActiveRecord]] fait cela en retournant le nom des colonnes de la base de données associée en tant que noms d'attribut. Notez que vous pouvez aussi avoir besoin de redéfinir les méthodes magiques telles que `__get()` et `__set()` afin que les attributs puissent être accédés comme les propriétés d'un objet ordinaire. 


### Étiquettes d'attribut <span id="attribute-labels"></span>

Lors de l'affichage de la valeur d'un attribut ou lors de la saisie d'une entrée pour une telle valeur, il est souvent nécessaire d'afficher une étiquette associée à l'attribut. Par exemple, étant donné l'attribut nommé `firstName` (prénom), vous pouvez utiliser une étiquette de la forme `First Name` qui est plus conviviale lorsqu'elle est présentée à l'utilisateur final dans un formulaire ou dans un message d'erreur. 

Vous pouvez obtenir l'étiquette d'un attribut en appelant la méthode [[yii\base\Model::getAttributeLabel()]]. Par exemple :

```php
$model = new \app\models\ContactForm;

// displays "Name"
echo $model->getAttributeLabel('name');
```

Par défaut, les étiquettes d'attribut sont automatiquement générées à partir des noms d'attribut. La génération est faite en appelant la méthode [[yii\base\Model::generateAttributeLabel()]]. Cette méthode transforme un nom de variable avec une casse en dos de chameau en de multiples mots, chacun commençant par une capitale. Par exemple, `username` donne `Username` et `firstName` donne `First Name`.

Si vous ne voulez pas utiliser les étiquettes à génération automatique, vous pouvez redéfinir la méthode [[yii\base\Model::attributeLabels()]] pour déclarer explicitement les étiquettes d'attribut. Par exemple :

```php
namespace app\models;

use yii\base\Model;

class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;

    public function attributeLabels()
    {
        return [
            'name' => 'Nom',
            'email' => 'Adresse de courriel',
            'subject' => 'Subjet',
            'body' => 'Contenu',
        ];
    }
}
```

Pour les application prenant en charge de multiples langues, vous désirez certainement traduire les étiquettes d'attribut. Cela peut être fait dans la méthode [[yii\base\Model::attributeLabels()|attributeLabels()]] également, en procédant comme ceci :

```php
public function attributeLabels()
{
    return [
        'name' => \Yii::t('app', 'Your name'),
        'email' => \Yii::t('app', 'Your email address'),
        'subject' => \Yii::t('app', 'Subject'),
        'body' => \Yii::t('app', 'Content'),
    ];
}
```

Vous pouvez même définir les étiquettes en fonction de conditions. Par exemple, en fonction du [scénario](#scenarios) dans lequel le modèle est utilisé, vous pouvez retourner des étiquettes différentes pour le même attribut.

> Info: strictement parlant, les étiquettes d'attribut font partie des [vues](structure-views.md). Mais la déclaration d'étiquettes dans les modèles est souvent très pratique et conduit à un code propre et réutilisable. 


## Scénarios <span id="scenarios"></span>

Un modèle peut être utilisé dans différents *scénarios*. Par exemple, un modèle `User` (utilisateur) peut être utilisé pour collecter les données d'un utilisateur, mais il peut aussi être utilisé à des fins d'enregistrement d'enregistrement de l'utilisateur. Dans différents scénarios, un modèle peut utiliser des règles de traitement et une logique différente. Par exemple, `email` peut être nécessaire lors de l'enregistrement de l'utilisateur mais pas utilisé lors de sa connexion. 

Un modèle utilise la propriété [[yii\base\Model::scenario]] pour conserver un trace du scénario dans lequel il est utilisé.

Par défaut, un modèle prend en charge un unique scénario nommé `default`. Le code qui suit montre deux manières de définir le scénario d'un modèle :

```php
// le scénario est défini comme une propriété
$model = new User;
$model->scenario = User::SCENARIO_LOGIN;

// le scénario est défini via une configuration
$model = new User(['scenario' => User::SCENARIO_LOGIN]);
```

Par défaut, les scénarios pris en charge par un modèle sont déterminés par les [règles de validation](#validation-rules) déclarées dans le modèle. Néanmoins, vous pouvez personnaliser ce comportement en redéfinissant la méthode [[yii\base\Model::scenarios()]], de la manière suivante : 

```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    const SCENARIO_LOGIN = 'login';
    const SCENARIO_REGISTER = 'register';

    public function scenarios()
    {
        return [
            self::SCENARIO_LOGIN => ['username', 'password'],
            self::SCENARIO_REGISTER => ['username', 'email', 'password'],
        ];
    }
}
```

> Info: dans ce qui précède et dans l'exemple qui suit, les classes de modèle étendent la classe [[yii\db\ActiveRecord]] parce que l'utilisation de multiples scénarios intervient ordinairement dans les classes [Active Record](db-active-record.md).

La méthode `scenarios()` retourne un tableau dont les clés sont les noms de scénario et les valeurs les *attributs actifs* correspondants. Les attributs actifs peuvent être [assignés massivement](#massive-assignment) et doivent respecter des règles de [validation](#validation-rules). Dans l'exemple ci-dessus, les attributs `username` et `password`  sont actifs dans le scénario `login`, tandis que dans le scénario `register` , `email` est, en plus de `username` et`password`, également actif.

La mise en œuvre par défaut des `scenarios()` retourne tous les scénarios trouvés dans la méthode de déclaration des règles de validation [[yii\base\Model::rules()]]. Lors de la redéfinition des `scenarios()`, si vous désirez introduire de nouveaux scénarios en plus des scénarios par défaut, vous pouvez utiliser un code similaire à celui qui suit :

```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    const SCENARIO_LOGIN = 'login';
    const SCENARIO_REGISTER = 'register';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_LOGIN] = ['username', 'password'];
        $scenarios[self::SCENARIO_REGISTER] = ['username', 'email', 'password'];
        return $scenarios;
    }
}
```
La fonctionnalité *scénarios* est d'abord utilisée pour la [validation](#validation-rules) et dans l'[assignation massive des attributs](#massive-assignment). Vous pouvez, cependant l'utiliser à d'autres fins. Par exemple, vous pouvez déclarer des [étiquettes d'attribut](#attribute-labels) différemment en vous basant sur le scénario courant.


## Règles de validation<span id="validation-rules"></span>

Losque les données pour un modèle sont reçues de l'utilisateur final, elles doivent être validées pour être sûr qu'elles respectent certaines règles (appelées *règles de validation*). Par exemple, étant donné un modèle pour un  formulaire de contact (`ContactForm`), vous voulez vous assurer qu'aucun des attributs n'est vide et que l'attribut `email` contient une adresse de courriel valide. Si les valeurs pour certains attributs ne respectent pas les règles de validation, les messages d'erreur appropriés doivent être affichés pour aider l'utilisateur à corriger les erreurs.

Vous pouvez faire appel à la méthode [[yii\base\Model::validate()]] pour valider les données reçues. La méthode utilise les règles de validation déclarées dans [[yii\base\Model::rules()]] pour valider chacun des attributs concernés. Si aucune erreur n'est trouvée, elle retourne `true` (vrai). Autrement, les erreurs sont stockées dans la propriété [[yii\base\Model::errors]] et la méthode retourne `false` (faux). Par exemple :

```php
$model = new \app\models\ContactForm;

// définit les attributs du modèle avec les entrées de l'utilisateur final
$model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // toutes les entrées sont valides
} else {
    // la validation a échoué : le tableau $errors contient les messages d'erreur
    $errors = $model->errors;
}
```


Pour déclarer des règles de validation associées à un modèle, redéfinissez la méthode [[yii\base\Model::rules()]] en retournant les règles que le modèle doit respecter. L'exemple suivant montre les règles de validation déclarées pour le modèle *formulaire de contact `ContactForm` :

```php
public function rules()
{
    return [
        // the name, email, subject and body attributes are required
        [['name', 'email', 'subject', 'body'], 'required'],

        // the email attribute should be a valid email address
        ['email', 'email'],
    ];
}
```

Une règle peut être utilisée pour valider un ou plusieurs attributs, et un attribut peut être validé par une ou plusieurs règles. Reportez-vous à la section [validation des entrées](input-validation.md) pour plus de détails sur la manière de déclarer les règles de validation.

Parfois, vous désirez qu'une règle ne soit applicable que dans certains [scénarios](#scenarios). Pour cela, vous pouvez spécifier la propriété `on` d'une règle, comme ci-dessous :

```php
public function rules()
{
    return [
        // username, email et  password sont tous requis dans le scénario "register"
        [['username', 'email', 'password'], 'required', 'on' => self::SCENARIO_REGISTER],

        // username et password sont requis dans le scénario "login"
        [['username', 'password'], 'required', 'on' => self::SCENARIO_LOGIN],
    ];
}
```

Si vous ne spécifiez pas la propriété `on`, la règle sera appliquée dans tous les scénarios. Une règle est dite *règle active* si elle s'applique au scénario courant [[yii\base\Model::scenario|scenario]].

Un attribut n'est validé que si, et seulement si, c'est un attribut actif déclaré dans `scenarios()` et s'il est associé à une ou plusieurs règles actives déclarées dans `rules()`.


## Assignation massive <span id="massive-assignment"></span>

L'assignation massive est une façon pratique de peupler un modèle avec les entrées de l'utilisateur final en utilisant une seule ligne de code . Elle peuple les attributs d'un modèle en assignant directement les données d'entrée à la propriété [[yii\base\Model::$attributes]]. Les deux extraits de code suivants sont équivalent. Ils tentent tous deux d'assigner les données du formulaire soumis par l'utilisateur final aux attributs du modèle `ContactForm`. En clair, le premier qui utilise l'assignation massive, est plus propre et moins sujet aux erreurs que le second :

```php
$model = new \app\models\ContactForm;
$model->attributes = \Yii::$app->request->post('ContactForm');
```

```php
$model = new \app\models\ContactForm;
$data = \Yii::$app->request->post('ContactForm', []);
$model->name = isset($data['name']) ? $data['name'] : null;
$model->email = isset($data['email']) ? $data['email'] : null;
$model->subject = isset($data['subject']) ? $data['subject'] : null;
$model->body = isset($data['body']) ? $data['body'] : null;
```


### Attributs sûr <span id="safe-attributes"></span>

L'assignation massive ne s'applique qu'aux attributs dits *attributs sûrs* qui sont les attributs listés dans la méthode [[yii\base\Model::scenarios()]] pour le [[yii\base\Model::scenario|scénario]] courant d'un modèle. 
Par exemple, si le modèle `User` contient la déclaration de scénarios suivante, alors, lorsque le scénario courant est  `login`, seuls les attributs `username` et `password` peuvent être massivement assignés. Tout autre attribut n'est pas touché par l'assignation massive. 

```php
public function scenarios()
{
    return [
        self::SCENARIO_LOGIN => ['username', 'password'],
        self::SCENARIO_REGISTER => ['username', 'email', 'password'],
    ];
}
```

> Info: la raison pour laquelle l'assignation massive ne s'applique qu'aux attributs sûrs est de vous permettre de contrôler quels attributs peuvent être modifiés par les données envoyées par l'utilisateur final. Par exemple, si le modèle `User` possède un attribut `permission` qui détermine les permissions accordées à l'utilisateur, vous préférez certainement que cet attribut ne puisse être modifié que par un administrateur via l'interface d'administration seulement.

Comme la mise en œuvre par défaut de la méthode [[yii\base\Model::scenarios()]] retourne tous les scénarios et tous les attributs trouvés dans la méthode [[yii\base\Model::rules()]], si vous ne redéfinissez pas cette méthode, cela signifie qu'un attribut est *sûr* tant qu'il apparaît dans une des règles de validation actives. 

Pour cette raison, un validateur spécial dont l'alias est `safe` est fourni pour vous permettre de déclarer un attribut *sûr* sans réellement le valider. Par exemple, les règles suivantes déclarent que  `title`
et `description` sont tous deux des attributs sûrs.

```php
public function rules()
{
    return [
        [['title', 'description'], 'safe'],
    ];
}
```


### Attributs non sûr <span id="unsafe-attributes"></span>

Comme c'est expliqué plus haut, la méthode  [[yii\base\Model::scenarios()]] remplit deux objectifs : déterminer quels attributs doivent être validés, et déterminer quels attributs sont *sûrs*. Dans certains cas, vous désirez valider un attribut sans le marquer comme *sûr*. Vous pouvez le faire en préfixant son nom par un point d'exclamation `!` lorsque vous le déclarez dans la méthode `scenarios()`, comme c'est fait pour l'attribut `secret` dans le code suivant :

```php
public function scenarios()
{
    return [
        self::SCENARIO_LOGIN => ['username', 'password', '!secret'],
    ];
}
```

Lorsque le modèle est dans le scénario `login`, les trois attributs sont validés. Néanmoins, seuls les attributs  `username`
et `password` peuvent être massivement assignés. Pour assigner une valeur d'entrée à l'attribut `secret`, vous devez le faire explicitement, comme montré ci-dessous :

```php
$model->secret = $secret;
```

La même chose peut être faite dans la méthode `rules()` :

```php
public function rules()
{
    return [
        [['username', 'password', '!secret'], 'required', 'on' => 'login']
    ];
}
```

Dans ce cas, les attributs `username`, `password` et `secret` sont requis, mais `secret` doit être assigné explicitement. 

## Exportation de données <span id="data-exporting"></span>

On a souvent besoin d'exporter les modèles dans différents formats. Par exemple, vous désirez peut-être convertir une collection de modèles dans le format JSON ou dans le format Excel. Le processus d'exportation peut être décomposé en deux étapes indépendantes :

- les modèles sont convertis en tableaux,
- les tableaux sont convertis dans les formats cibles. 

Vous pouvez vous concentrer uniquement sur la première étape, parce que la seconde peut être accomplie par des formateurs génériques de données, tels que [[yii\web\JsonResponseFormatter]].

La manière la plus simple de convertir un modèle en tableau est d'utiliser la propriété [[yii\base\Model::$attributes]]. Par exemple :

```php
$post = \app\models\Post::findOne(100);
$array = $post->attributes;
```

Par défaut, la propriété [[yii\base\Model::$attributes]] retourne les valeurs de *tous* les attributs déclarés dans la méthode [[yii\base\Model::attributes()]].

Une manière plus souple et plus puissante de convertir un modèle en tableau est d'utiliser la méthode [[yii\base\Model::toArray()]]. Son comportement par défaut est de retourner la même chose que la propriété [[yii\base\Model::$attributes]]. Néanmoins, elle vous permet de choisir quelles données, appelées *champs*, doivent être placées dans le tableau résultant et comment elles doivent être formatées. En fait, c'est la manière par défaut pour exporter les modèles dans le développement d'un service Web respectant totalement l'achitecture REST, telle que décrite à la section [Formatage de la réponse](rest-response-formatting.md).


### Champs <span id="fields"></span>

Un champ n'est rien d'autre qu'un élément nommé du tableau qui est obtenu en appelant la méthode [[yii\base\Model::toArray()]] d'un modèle.

Par défaut, les noms de champ sont équivalents aux noms d'attribut. Cependant, vous pouvez changer ce comportement en redéfinissant la méthode [[yii\base\Model::fields()|fields()]] et/ou la méthode [[yii\base\Model::extraFields()|extraFields()]]. Ces deux méthodes doivent retourner une liste de définitions de champ. Les champs définis par `fields()` sont des champs par défaut, ce qui signifie que `toArray()` retourne ces champs par défaut. La méthode `extraFields()` définit des champs additionnels disponibles qui peuvent également être retournés par `toArray()` du moment que vous les spécifiez via le paramètre `$expand`. Par exemple, le code suivant retourne tous les champs définis dans la méthode `fields()` ainsi que les champs `prettyName` et `fullAddress`, s'ils sont définis dans `extraFields()`.

```php
$array = $model->toArray([], ['prettyName', 'fullAddress']);
```

Vous pouvez redéfinir la méthode `fields()` pour ajouter, retirer, renommer ou redéfinir des champs. La valeur de retour de la méthode `fields()` doit être un tableau associatif. Les clés du tableau sont les noms des champs et les valeurs sont les définitions de champ correspondantes qui peuvent être, soit des noms d'attribut/propriété, soit des fonctions anonymes retournant les valeurs de champ correspondantes. Dans le cas particulier où un nom de champ est identique à celui du nom d'attribut qui le définit, vous pouvez omettre la clé du tableau. Par exemple :

```php
// liste explicitement chaque champ ; à utiliser de préférence quand vous voulez être sûr 
// que les changements dans votre table de base de données ou dans les attributs de votre modèle
// ne créent pas de changement dans vos champs (pour conserver la rétro-compatibilité de l'API). 
public function fields()

{
    return [
        // le nom du champ est identique à celui de l'attribut
        'id',

        // le nom du champ est "email", le nom d'attribut correspondant est  "email_address"
        'email' => 'email_address',

        // le nom du champ est  "name", sa valeur est définie par une fonction PHP de rappel
        'name' => function () {
            return $this->first_name . ' ' . $this->last_name;
        },
    ];
}

// filtre quelques champs ; à utiliser de préférence quand vous voulez hériter de l'implémentation du parent 
// et mettre quelques champs sensibles en liste noire.
public function fields()
{
    $fields = parent::fields();

    // retire les champs contenant des informations sensibles 
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

> Warning: étant donné que, par défaut, tous les attributs d'un modèle sont inclus dans le tableau exporté, 
> vous devez vous assurer que vos données ne contiennent pas d'information sensible. 
> Si de telles informations existent, vous devriez redéfinir la méthode `fields()` pour les filtrer. 
> Dans l'exemple ci-dessus, nous avons choisi d'exclure `auth_key`, `auth_key`, `password_hash` et `password_reset_token`.


## Meilleures pratiques <span id="best-practices"></span>

Les modèles sont les endroits centraux pour représenter les données de l'application, les règles et la logique. Ils doivent souvent être réutilisés à différents endroits. Dans une application bien conçue, les modèles sont généralement plus volumineux que les [contrôleurs](structure-controllers.md).

En résumé, voici les caractéristiques essentielles des modèles :

* Ils peuvent contenir des attributs pour représenter les données de l'application.
* Ils peuvent contenir des règles de validation pour garantir la validité et l'intégrité des données.
* Ils peuvent contenir des méthodes assurant le traitement logique des données de l'application. 
* Ils ne devraient PAS accéder directement à la requête, à la session ou à n'importe quelle autre donnée environnementale. Ces données doivent être injectées dans les modèles par les [contrôleurs](structure-controllers.md).
* Ils devraient éviter d'inclure du code HTML ou tout autre code relatif à la présentation — cela est fait de manière plus avantageuse dans les [vues](structure-views.md).
* Il faut éviter d'avoir trop de [scénarios](#scenarios) dans un même modèle.

Vous pouvez ordinairement considérer cette dernière recommandation lorsque vous développez des systèmes importants et complexes. Dans ces systèmes, les modèles pourraient être très volumineux parce que, étant utilisés dans de nombreux endroits, ils doivent contenir de nombreux jeux de règles et de traitement logiques. Cela se termine le plus souvent en cauchemar pour la maintenance du code du modèle parce que le moindre changement de code  est susceptible d'avoir de l'effet en de nombreux endroits. Pour rendre le modèle plus maintenable, vous pouvez adopter la stratégie suivante :

* Définir un jeu de classes de base du modèle qui sont partagées par différentes [applications](structure-applications.md) ou
  [modules](structure-modules.md). Ces classes de modèles devraient contenir un jeu minimal de règles et de logique qui sont communes à tous les usages. 
* Dans chacune des [applications](structure-applications.md) ou [modules](structure-modules.md) qui utilise un modèle, définir une classe de modèle concrète  en étendant la classe de base de modèle correspondante. Les classes de modèles concrètes devraient contenir certaines règles et logiques spécifiques à cette application ou à ce module.

Par exemple, dans le [Modèle avancé de projet](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md), vous pouvez définir une classe de modèle de base `common\models\Post`. Puis, pour l'application « interface utilisateur » (*frontend*) vous pouvez définir une classe de base concrète `frontend\models\Post` qui étend la classe `common\models\Post`. De manière similaire, pour l'application « interface d'administration » (*backend*) vous pouvez définir une classe `backend\models\Post`. Avec cette stratégie, vous êtes sûr que le code de `frontend\models\Post` est seulement spécifique à l'application « interface utilisateur », et si vous y faite un changement, vous n'avez à vous soucier de savoir si cela à une influence sur l'application « interface d'administration ».

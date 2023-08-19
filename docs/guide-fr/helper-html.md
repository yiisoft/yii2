Classe assistante Html
======================

Toutes les applications Web génèrent un grand nombre de balises HTML. Si le code HTML est statique, il peut être créé efficacement sous forme de [mélange de code PHP et de code HTML dans un seul fichier](https://www.php.net/manual/fr/language.basic-syntax.phpmode.php), mais lorsqu'il est généré dynamiquement, cela commence à être compliqué à gérer sans une aide supplémentaire. Yii fournit une telle aide sous la forme de la classe assistante Html, qui offre un jeu de méthodes statiques pour manipuler les balises Html les plus courantes, leurs options et leur contenu.

> Note: si votre code HTML est presque statique, il vaut mieux utiliser HTML directement. Il n'est pas nécessaire d'envelopper tout dans des appels aux méthodes de la classe assistante Html.


## Les bases <span id="basics"></span>

Comme la construction de code HTML dynamique en concaténant des chaînes de caractère peut très vite tourner à la confusion, Yii fournit un jeu de méthodes pour manipuler les options de balises et construire des balises s'appuyant sur ces options. 


### Génération de balises <span id="generating-tags"></span>

Le code pour générer une balise ressemble à ceci :

```php
<?= Html::tag('p', Html::encode($user->name), ['class' => 'username']) ?>
```

Le premier argument est le nom de la balise. Le deuxième est le contenu qui apparaît entre l'ouverture de la balise et sa fermeture. 
Notez que nous utilisons `Html::encode` – c'est parce que le contenu n'est pas encodé automatiquement pour permetre l'utilisation de HTML quand c'est nécessaire.
Le troisième est un tableau d'options HTML ou, en d'autres mots, les attributs de la balise.
Dans ce tableau, la clé est le nom de l'attribut (comme `class`, `href` ou `target`) et la valeur est sa valeur.

Le code ci-dessus génère le code HTML suivant : 

```html
<p class="username">samdark</p>
```

Dans le cas où vous avez simplement besoin d'ouvrir ou de fermer la balise, vous pouvez utiliser les méthodes `Html::beginTag()` et `Html::endTag()`.

Des options sont utilisées dans de nombreuses méthodes de la classe assistante Html et de nombreux composants graphiques (widgets). Dans tous ces cas, il y a quelques manipulations supplémentaires à connaître :

- Si une valeur est `null`, l'attribut correspondant n'est pas rendu.
- Les attributs du type booléen sont traités comme des 
  [attributs booléens ](https://html.spec.whatwg.org/multipage/common-microsyntaxes.html#boolean-attributes).
- Les valeurs des attributs sont encodés HTML à l'aide de la méthode [[yii\helpers\Html::encode()|Html::encode()]].
- Si la valeur d'un attribut est un tableau, il est géré comme suit :
 
   * Si l'attribut est un attribut de donnée tel que listé dans [[yii\helpers\Html::$dataAttributes]], tel que `data` ou `ng`,
     une liste d'attributs est rendue, un pour chacun des élément dans le tableau de valeurs. Par exemple, 
     `'data' => ['id' => 1, 'name' => 'yii']` génère `data-id="1" data-name="yii"`; et 
     `'data' => ['params' => ['id' => 1, 'name' => 'yii'], 'status' => 'ok']` génère
     `data-params='{"id":1,"name":"yii"}' data-status="ok"`. Notez que dans le dernier exemple le format JSON est utilisé pour rendre le sous-tableau.
   * Si l'attribut n'est PAS un attribut de donnée, la valeur est encodée JSON. Par exemple,
     `['params' => ['id' => 1, 'name' => 'yii']` génère `params='{"id":1,"name":"yii"}'`.


### Formation des classes et des styles CSS <span id="forming-css"></span>

Lors de la construction des options pour des balises HTML, nous démarrons souvent avec des valeurs par défaut qu'il faut modifier. Afin d'ajouter ou de retirer une classe, vous pouvez utiliser ce qui suit : 

```php
$options = ['class' => 'btn btn-default'];

if ($type === 'success') {
    Html::removeCssClass($options, 'btn-default');
    Html::addCssClass($options, 'btn-success');
}

echo Html::tag('div', 'Pwede na', $options);

// si la valeur de $type est 'success' le rendu sera
// <div class="btn btn-success">Pwede na</div>
```

Vous pouvez spécifier de multiples classe CSS en utilisant le tableau de styles également :

```php
$options = ['class' => ['btn', 'btn-default']];

echo Html::tag('div', 'Save', $options);
// rend '<div class="btn btn-default">Save</div>'
```

Vous pouvez aussi utiliser le tableau de styles pour ajouter ou retirer des classes :

```php
$options = ['class' => 'btn'];

if ($type === 'success') {
    Html::addCssClass($options, ['btn-success', 'btn-lg']);
}

echo Html::tag('div', 'Save', $options);
// rend '<div class="btn btn-success btn-lg">Save</div>'
```

`Html::addCssClass()` empêche la duplication, vous n'avez donc pas à vous préoccuper de savoir si une classe apparaît deux fois :

```php
$options = ['class' => 'btn btn-default'];

Html::addCssClass($options, 'btn-default'); // class 'btn-default' is already present

echo Html::tag('div', 'Save', $options);
// rend '<div class="btn btn-default">Save</div>'
```

Si l'option classe CSS est spécifiée en utilisant le tableau de styles, vous pouvez utiliser une clé nommée pour indiquer le but logique de la classe. Dans ce cas, une classe utilisant la même clé dans le tableau de styles passé à `Html::addClass()` est ignorée :

```php
$options = [
    'class' => [
        'btn',
        'theme' => 'btn-default',
    ]
];

Html::addCssClass($options, ['theme' => 'btn-success']); // la clé 'theme' est déjà utilisée

echo Html::tag('div', 'Save', $options);
// rend '<div class="btn btn-default">Save</div>'
```

Les styles CSS peuvent être définis d'une façon similaire en utilisant l'attribut `style` :

```php
$options = ['style' => ['width' => '100px', 'height' => '100px']];

// donne style="width: 100px; height: 200px; position: absolute;"
Html::addCssStyle($options, 'height: 200px; position: absolute;');

// gives style="position: absolute;"
Html::removeCssStyle($options, ['width', 'height']);
```

Lors de l'utilisation de  [[yii\helpers\Html::addCssStyle()|addCssStyle()]], vous pouvez spécifier soit un tableau de paires clé-valeur qui correspond aux propriétés CSS noms et valeurs, soit une chaîne de caractères telle que `width: 100px; height: 200px;`. Ces formats peuvent être convertis de l'un en l'autre en utilisant les méthodes [[yii\helpers\Html::cssStyleFromArray()|cssStyleFromArray()]] et
[[yii\helpers\Html::cssStyleToArray()|cssStyleToArray()]]. La méthode [[yii\helpers\Html::removeCssStyle()|removeCssStyle()]]
accepte un tableau de propriétés à retirer. S'il s'agit d'une propriété unique, elle peut être spécifiée sous forme de chaîne de caractères. 

### Encodage et décodage du contenu <span id="encoding-and-decoding-content"></span>

Pour que le contenu puisse être affiché en HTML de manière propre et en toute sécurité, les caractères spéciaux du contenu doivent être encodés. En PHP, cela s'obtient avec [htmlspecialchars](https://www.php.net/manual/fr/function.htmlspecialchars.php) et 
[htmlspecialchars_decode](https://www.php.net/manual/fr/function.htmlspecialchars-decode.php). Le problème rencontré en utilisant ces méthodes directement est que vous devez spécifier l'encodage et des options supplémentaires tout le temps. Comme ces options restent toujours les mêmes et que l'encodage doit correspondre à celui de l'application pour éviter les problèmes de sécurité, Yii fournit deux méthodes compactes et faciles à utiliser :

```php
$userName = Html::encode($user->name);
echo $userName;

$decodedUserName = Html::decode($userName);
```


## Formulaires <span id="forms"></span>

Manipuler des formulaires dans le code HTML est tout à fait répétitif et sujet à erreurs. À cause de cela, il existe un groupe de méthodes pour aider à les manipuler.

> Note : envisagez d'utiliser [[yii\widgets\ActiveForm|ActiveForm]] dans le cas où vous avez affaire à des modèles et que ces derniers doivent être validés. 

### Création de formulaires <span id="creating-forms"></span>

Les formulaires peut être ouverts avec la méthode [[yii\helpers\Html::beginForm()|beginForm()]] comme ceci :

```php
<?= Html::beginForm(['order/update', 'id' => $id], 'post', ['enctype' => 'multipart/form-data']) ?>
```

Le premier argument est l'URL à laquelle le formulaire sera soumis. Il peut être spécifié sous la forme d'une route Yii et de paramètres acceptés par [[yii\helpers\Url::to()|Url::to()]].
Le deuxième est la méthode à utiliser. `post` est la méthode par défaut. Le troisième est un tableau d'options pour la balise form. Dans ce cas, nous modifions l'encodage des données du formulaire dans la requête POST en `multipart/form-data`, ce qui est requis pour envoyer des fichiers.

La fermeture du formulaire se fait simplement par :

```php
<?= Html::endForm() ?>
```


### Boutons <span id="buttons"></span>

Pour générer des boutons, vous pouvez utiliser le code suivant :

```php
<?= Html::button('Pressez-mo!', ['class' => 'teaser']) ?>
<?= Html::submitButton('Envoyer', ['class' => 'submit']) ?>
<?= Html::resetButton('Ré-initialiser', ['class' => 'reset']) ?>
```

Le premier argument pour les trois méthodes est l'intitulé du bouton, le deuxième est un tableau d'options. 
L'intitulé n'est pas encodé, mais si vous affichez des données en provenance de l'utilisateur, encodez les avec [[yii\helpers\Html::encode()|Html::encode()]].


### Champs d'entrée <span id="input-fields"></span>

Il y a deux groupes de méthodes d'entrée de données. Celles qui commencent par `active`, est qui sont appelées entrées actives, et celles qui ne commencent pas par ce mot. Les entrées actives prennent leurs données dans le modèle à partir des attributs spécifiés, tandis que pour les entrées régulières, les données sont spécifiées directement.

Les méthodes les plus génériques sont :

```php
type, nom de l'entrée, valeur de l'entrée, options
<?= Html::input('text', 'username', $user->name, ['class' => $username]) ?>

type, modèle, nom de l'attribut du modèle, options
<?= Html::activeInput('text', $user, 'name', ['class' => $username]) ?>
```

Si vous connaissez le type de l'entrée à l'avance, il est plus commode d'utiliser les méthodes raccourcis :

- [[yii\helpers\Html::buttonInput()]]
- [[yii\helpers\Html::submitInput()]]
- [[yii\helpers\Html::resetInput()]]
- [[yii\helpers\Html::textInput()]], [[yii\helpers\Html::activeTextInput()]]
- [[yii\helpers\Html::hiddenInput()]], [[yii\helpers\Html::activeHiddenInput()]]
- [[yii\helpers\Html::passwordInput()]] / [[yii\helpers\Html::activePasswordInput()]]
- [[yii\helpers\Html::fileInput()]], [[yii\helpers\Html::activeFileInput()]]
- [[yii\helpers\Html::textarea()]], [[yii\helpers\Html::activeTextarea()]]

Les listes radio et les boîtes à cocher sont un peu différentes en matière de signature de méthode :

```php
<?= Html::radio('agree', true, ['label' => 'I agree']);
<?= Html::activeRadio($model, 'agree', ['class' => 'agreement'])

<?= Html::checkbox('agree', true, ['label' => 'I agree']);
<?= Html::activeCheckbox($model, 'agree', ['class' => 'agreement'])
```

Les listes déroulantes et les boîtes listes peuvent être rendues comme suit :

```php
<?= Html::dropDownList('list', $currentUserId, ArrayHelper::map($userModels, 'id', 'name')) ?>
<?= Html::activeDropDownList($users, 'id', ArrayHelper::map($userModels, 'id', 'name')) ?>

<?= Html::listBox('list', $currentUserId, ArrayHelper::map($userModels, 'id', 'name')) ?>
<?= Html::activeListBox($users, 'id', ArrayHelper::map($userModels, 'id', 'name')) ?>
```

Le premier argument est le nom de l'entrée, le deuxième est la valeur sélectionnée actuelle et le troisième est un tableau de paires clé-valeur, dans lequel la clé est la valeur d'entrée dans la liste et la valeur est l'étiquette qui correspond à cette valeur dans la liste.

Si vous désirez que des choix multiples soient sélectionnables, vous pouvez utiliser la liste à sélection multiples (checkbox list) :

```php
<?= Html::checkboxList('roles', [16, 42], ArrayHelper::map($roleModels, 'id', 'name')) ?>
<?= Html::activeCheckboxList($user, 'role', ArrayHelper::map($roleModels, 'id', 'name')) ?>
```

Sinon utilisez la liste radio :

```php
<?= Html::radioList('roles', [16, 42], ArrayHelper::map($roleModels, 'id', 'name')) ?>
<?= Html::activeRadioList($user, 'role', ArrayHelper::map($roleModels, 'id', 'name')) ?>
```


### Étiquettes et erreurs <span id="labels-and-errors"></span>

Comme pour les entrées, il existe deux méthodes pour générer les étiquettes de formulaire. Celles pour les entrées « actives » qui prennent leurs étiquettes dans le modèle, et celles « non actives » qui sont étiquetées directement :

```php
<?= Html::label('User name', 'username', ['class' => 'label username']) ?>
<?= Html::activeLabel($user, 'username', ['class' => 'label username']) ?>
```

Pour afficher les erreurs de formulaire à partir d'un modèle ou sous forme de résumé pour un modèle, vous pouvez utiliser :

```php
<?= Html::errorSummary($posts, ['class' => 'errors']) ?>
```

Pour afficher une erreur individuellement :

```php
<?= Html::error($post, 'title', ['class' => 'error']) ?>
```


### Nom et valeur des entrées  <span id="input-names-and-values"></span>

Il existe deux méthodes pour obtenir des noms, des identifiants et des valeurs pour des champs d'entrée basés sur un modèle. Elles sont essentiellement utilisées en interne, mais peuvent être pratiques quelques fois :

```php
// Post[title]
echo Html::getInputName($post, 'title');

// post-title
echo Html::getInputId($post, 'title');

// my first post
echo Html::getAttributeValue($post, 'title');

// $post->authors[0]
echo Html::getAttributeValue($post, '[0]authors[0]');
```

Dans ce qui précède, le premier argument est le modèle, tandis que le deuxième est l'expression d'attribut. Dans sa forme la plus simple, l'expression est juste un nom d'attribut, mais il peut aussi s'agir d'un nom d'attribut préfixé et-ou suffixé par des index de tableau, ce qui est essentiellement le cas pour des entrées tabulaires : 

- `[0]content` est utilisé dans des entrées de données tabulaires pour représenter l'attribut `content` pour le premier modèle des entrées tabulaires ;
- `dates[0]` représente le premier élément du tableau de l'attribut `dates` ; 
- `[0]dates[0]` représente le premier élément du tableau de l'attribut `dates` pour le premier modèle des entrées tabulaires.  

Afin d'obtenir le nom de l'attribut sans suffixe ou préfixe, vous pouvez utiliser ce qui suit :

```php
// dates
echo Html::getAttributeName('dates[0]');
```


## Styles et scripts <span id="styles-and-scripts"></span>

Il existe deux méthodes pour générer les balises enveloppes des styles et des scripts :

```php
<?= Html::style('.danger { color: #f00; }') ?>

Produit

<style>.danger { color: #f00; }</style>


<?= Html::script('alert("Hello!");', ['defer' => true]);

Produit

<script defer>alert("Hello!");</script>
```

Si vous désirez utiliser utiliser un style externe d'un fichier CSS :

```php
<?= Html::cssFile('@web/css/ie5.css', ['condition' => 'IE 5']) ?>

génère

<!--[if IE 5]>
    <link href="https://example.com/css/ie5.css" />
<![endif]-->
```

Le premier argument est l'URL. Le deuxième est un tableau d'options. En plus des options normales, vous pouvez spécifier :

- `condition` pour envelopper `<link` dans des commentaires conditionnels avec la condition spécifiée. Nous espérons que vous n'aurez jamais besoin de commentaires conditionnels ;
- `noscript` peut être défini à `true` pour envelopper `<link` dans une balise `<noscript>` de façon à ce qu'elle soit incluse seulement si le navigateur ne prend pas en charge JavaScript ou si l'utilisateur l'a désactivé. 

Pour lier un fichier JavaScript :

```php
<?= Html::jsFile('@web/js/main.js') ?>
```

Se passe comme avec CSS, le premier argument spécifie l'URL du fichier à inclure. Les options sont passées via le deuxième argument. Dans les options vous pouvez spécifier `condition` de la même manière que dans les options pour un fichier CSS (méthode `cssFile`). 


## Hyperliens <span id="hyperlinks"></span>

Il y a une méthode commode pour générer les hyperliens :

```php
<?= Html::a('Profile', ['user/view', 'id' => $id], ['class' => 'profile-link']) ?>
```

Le premier argument est le titre. Il n'est pas encodé, mais si vous utilisez des données entrées par l'utilisateur, vous devez les encoder avec `Html::encode()`. Le deuxième argument est ce qui se retrouvera dans l'attribut `href` de la balise `<a`.

Voir [Url::to()](helper-url.md) pour les détails sur les valeurs acceptées. 
Le troisième argument est un tableau pour les attributs de la balise.

Si vous devez générer des liens  `mailto`, vous pouvez utiliser le code suivant :

```php
<?= Html::mailto('Contact us', 'admin@example.com') ?>
```


## Images <span id="images"></span>

Pour générer une balise image, utilisez le code suivant :

```php
<?= Html::img('@web/images/logo.png', ['alt' => 'My logo']) ?>

qui génère

<img src="https://example.com/images/logo.png" alt="My logo" />
```

En plus des [alias](concept-aliases.md), le premier argument accepte les routes, les paramètres et les URL, tout comme [Url::to()](helper-url.md).


## Listes <span id="lists"></span>

Les listes non ordonnées peuvent être générées comme suit :

```php
<?= Html::ul($posts, ['item' => function($item, $index) {
    return Html::tag(
        'li',
        $this->render('post', ['item' => $item]),
        ['class' => 'post']
    );
}]) ?>
```

Pour une liste ordonnée, utilisez plutôt `Html::ol()`.

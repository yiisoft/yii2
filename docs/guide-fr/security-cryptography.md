Cryptographie
=============

Dans cette section nous allons passer en revue les aspects suivants relatifs à la sécurité :

- Génération de données aléatoires 
- Chiffrage et déchiffrage
- Confirmation de l'intégrité des données

Génération de données pseudo-aléatoires
---------------------------------------

Les données pseudo-aléatoires sont utiles dans de nombreuses situations. Par exemple, lors de la réinitialisation d'un mot de passe via courriel, vous devez générer un jeton, le sauvegarder dans la base de données, et l'envoyer à l'utilisateur afin qu'il puisse prouver qu'il est le détenteur du compte concerné. Il est très important que ce jeton soit unique et difficile à deviner, sinon il y aurait une possibilité que l'attaquant le devine et réinitialise le mot de passe de l'utilisateur.

Les fonctions d'aide à la sécurité de Yii facilite la création de données pseudo-aléatoires :


```php
$key = Yii::$app->getSecurity()->generateRandomString();
```

Chiffrage et déchiffrage
----------------------

Yii fournit des fonctions d'aide pratiques qui vous permettent de chiffrer/déchiffrer les données en utilisant une clé secrète. Les données sont passées à la fonction de chiffrage de façon à ce que, seule la personne qui possède la clé secrète soit en mesure de les déchiffrer.

Par exemple, nous avons besoin de stocker quelques informations dans notre base de données mais nous avons besoin de garantir que seul l'utilisateur qui dispose de la clé secrète soit en mesure des les visualiser (même si la base de données de l'application est compromise) :


```php
// $data et $secretKey sont obtenues du formulaire
$encryptedData = Yii::$app->getSecurity()->encryptByPassword($data, $secretKey);
// stocke  $encryptedData dans la base de données
```

Par la suite, lorsqu'un utilisateur désire lire les données :

```php
// $secretKey est obtenue de la saisie de l'utilisateur, $encryptedData provient de la base de données
$data = Yii::$app->getSecurity()->decryptByPassword($encryptedData, $secretKey);
```

Il est également possible d'utiliser une clé à la place d'un mot de passe via [[\yii\base\Security::encryptByKey()]] et
[[\yii\base\Security::decryptByKey()]].

Confirmation de l'intégrité des données
---------------------------------------

Il y a des situations dans lesquelles vous avez besoin de vérifier que vos données n'ont pas été trafiquées par une tierce partie ou corrompue. Yii vous offre un moyen facile de confirmer l'intégrité des données sous forme d'une fonction d'aide.

Préfixez les données par une valeur de hachage obtenue à l'aide de la clé secrète et des données. 


```php
// $secretKey notre clé secrèe pour l'application ou l'utilisateur, $genuineData les données authentiques obtenues d'une source fiable.
$data = Yii::$app->getSecurity()->hashData($genuineData, $secretKey);
```

Vérifiez si l'intégrité des données est compromise.

```php
// $secretKey notre clé secrèe pour l'application ou l'utilisateur, $data données obtenues d'une source peu sûre
$data = Yii::$app->getSecurity()->validateData($data, $secretKey);
```

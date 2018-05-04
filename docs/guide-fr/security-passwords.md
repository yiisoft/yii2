Utilisation de mots de passe
============================

La plupart des développeurs savent que les mots de passe ne peuvent pas être stockés « en clair », mais beaucoup d'entre-eux croient qu'il est toujours sûr des les hacher avec  `md5` ou `sha1`. Il fut un temps où utiliser ces algorithmes de hachage était suffisant, mais les matériels modernes font qu'il est désormais possible de casser de tels hachages – même les plus robustes – très rapidement en utilisant des attaques en force brute. 

Pour apporter une sécurité améliorée pour les mots de passe des utilisateurs, même dans le pire des scénario (une brèche est ouverte dans votre application), vous devez utiliser des algorithmes de hachage qui résistent aux attaques en force brute. Le choix le meilleur couramment utilisé est `bcrypt`.

En  PHP, vous pouvez créer une valeur de hachage `bcrypt` à l'aide de la  [fonction crypt](http://php.net/manual/en/function.crypt.php). Yii fournit deux fonctions d'aide qui facilitent l'utilisation de  `crypt` pour générer et vérifier des valeurs de hachage de manière sure. 

Quand un utilisateur fournit un mot de passe pour la première fois (p. ex. à l'enregistrement), le mot de passe doit être haché :


```php
$hash = Yii::$app->getSecurity()->generatePasswordHash($password);
```

La valeur de hachage peut ensuite être associée à l'attribut du modèle correspondant afin de pouvoir être stockée dans la base de données pour utilisation ultérieure.

Lorsqu'un utilisateur essaye ensuite de se connecter, le mot de passe soumis est comparé au mot de passe précédemment haché et stocké : 


```php
if (Yii::$app->getSecurity()->validatePassword($password, $hash)) {
    // tout va bien, nous connectons l'utilisateur
} else {
    // wrong password
}
```

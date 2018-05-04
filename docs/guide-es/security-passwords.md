Trabajar con Passwords
======================

La mayoría de los desarrolladores saben que los passwords no deben ser guardados en texto plano, pero muchos desarrolladores aún creen
que es seguro aplicar a los passowrds hash `md5` o `sha1`. Hubo un tiempo cuando utilizar esos algoritmos de hash mencionados era suficiente,
pero el hardware moderno hace posible que ese tipo de hash e incluso más fuertes, puedan revertirse rápidamente utilizando ataques de fuerza bruta.

Para poder proveer de una seguridad mayor para los passwords de los usuarios, incluso en el peor de los escenarios (tu aplicación sufre una brecha de seguridad),
necesitas utilizar un algoritmo que resista los ataques de fuerza bruta. La mejor elección actualmente es `bcrypt`.
En PHP, puedes generar un hash `bcrypt` utilizando la [función crypt](http://php.net/manual/en/function.crypt.php). Yii provee
dos funciones auxiliares que hacen que `crypt` genere y verifique los hash más fácilmente.

Cuando un usuario provee un password por primera vez (por ej., en la registración), dicho password necesita ser pasado por un hash:


```php
$hash = Yii::$app->getSecurity()->generatePasswordHash($password);
```

El hash puede estar asociado con el atributo del model correspondiente, de manera que pueda ser almacenado en la base de datos para uso posterior.

Cuando un usuario intenta ingresar al sistema, el password enviado debe ser verificado con el password con hash almacenado previamente:


```php
if (Yii::$app->getSecurity()->validatePassword($password, $hash)) {
    // todo en orden, dejar ingresar al usuario
} else {
    // password erróneo
}
```

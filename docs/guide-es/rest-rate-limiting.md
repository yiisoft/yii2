Limitando el rango (rate)
=========================

Para prevenir el abuso, puedes considerar añadir un *límitación del rango (rate limiting)* para tus APIs. Por ejemplo,
puedes querer limitar el uso del API de cada usuario a un máximo de 100 llamadas al API dentro de un periodo de 10 minutos.
Si se reciben demasiadas peticiones de un usuario dentro del periodo de tiempo declarado, deveríá devolverse una respuesta con código de estado 429 (que significa "Demasiadas peticiones").

Para activar la limitación de rango, la clase [[yii\web\User::identityClass|user identity class]] debe implementar [[yii\filters\RateLimitInterface]].
Este interface requiere la implementación de tres métodos:

* `getRateLimit()`: devuelve el número máximo de peticiones permitidas y el periodo de tiempo (p.e., `[100, 600]` significa que como mucho puede haber 100 llamadas al API dentro de 600 segundos).
* `loadAllowance()`: devuelve el número de peticiones que quedan permitidas y el tiempo (fecha/hora) UNIX
  con el último límite del rango que ha sido comprobado.
* `saveAllowance()`: guarda ambos, el número restante de peticiones permitidas y el tiempo actual (fecha/hora) UNIX .

Puedes usar dos columnas en la tabla de usuario para guardar la información de lo permitido y la fecha/hora (timestamp). Con ambas definidas,
entonces `loadAllowance()` y `saveAllowance()` pueden ser utilizados para leer y guardar los valores de las dos columnas correspondientes al actual usuario autenticado.
Para mejorar el desempeño, también puedes considerar almacenar esas piezas de información en caché o almacenamiento NoSQL.

Una vez que la clase de identidad implementa la interfaz requerida, Yii utilizará automáticamente [[yii\filters\RateLimiter]]
configurado como un filtro de acción para que [[yii\rest\Controller]] compruebe el límite de rango. El limitador de rango
lanzará una excepeción [[yii\web\TooManyRequestsHttpException]] cuando el límite del rango sea excedido.

Puedes configurar el limitador de rango
en tu clase controlador REST como sigue:

```php
public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['rateLimiter']['enableRateLimitHeaders'] = false;
    return $behaviors;
}
```

Cuando se activa el límite de rango, por defecto todas las respuestas serán enviadas con la siguiente cabecera HTTP conteniendo
información sobre el límite actual de rango:

* `X-Rate-Limit-Limit`, el máximo número de peticiones permitidas en un periodo de tiempo
* `X-Rate-Limit-Remaining`, el número de peticiones restantes en el periodo de tiempo actual
* `X-Rate-Limit-Reset`, el número de segundos a esperar para pedir el máximo número de peticiones permitidas

Puedes desactivar estas cabeceras configurando [[yii\filters\RateLimiter::enableRateLimitHeaders]] a false,
tal y como en el anterior ejemplo.

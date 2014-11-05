Limitando el ratio (rate)
=============

Para prevenir el abuso, puedes considerar añadir un *límitación del ratio* (*rate limiting*) para tus APIs. Por ejemplo, puedes querer limitar el uso del API de cada usuario que no sea como mucho 100 llamadas al API dentro de un periodo de 10 minutos. Si demasiadas peticiones son recibidas de un usuario dentro del periodo de tiempo declarado , una respuesta con código de estado 429 (significa "Demasiadas peticiones") puede ser devuelto.

Para activar la limitación de ratio, la clase [[yii\web\User::identityClass|user identity class]] debe implementar [[yii\filters\RateLimitInterface]].
Este interface requiere la implementación de tres métodos:

* `getRateLimit()`: devuelve el número máximo de peticiones permitidas y el periodo de tiempo (p.e., `[100, 600]` significa que como mucho puede haber 100 llamadas al API dentro de 600 segundos).
* `loadAllowance()`: devuelve el número de peticiones que quedan permitidas y el tiempo (fecha/hora) UNIX con el último límite del ratio que ha sido comprobado.
* `saveAllowance()`: guarda ambos, el número que quedan de peticiones permitidas y el actual tiempo (fecha/hora) UNIX .

Tu puedes usar dos columnas en la tabla de usuario para guardar la información de lo permitido y la fecha/hora (timestamp). Con ambas definidas, entonces `loadAllowance()` y `saveAllowance()` pueden ser implementadas para leer y guardar los valores de las dos columnas correspondientes al actual usuario autenticado. Para mejorar el desempeño, también puedes considerar almacenar esas piezas de información en caché o almacenamiento NoSQL.

Una vez que la clase de identidad implementa el requerido interface, Yii puede usar automáticamente [[yii\filters\RateLimiter]] configurado como una acción de filtrado para [[yii\rest\Controller]] y mejorar la comprobación del limitador de ratio. El limitador de ratio puede lanzar una [[yii\web\TooManyRequestsHttpException]] cuando el límite del ratio es excedido.

Puedes configurar el limitador de ratio en tu clase REST de la controladora como sigue:

```php
public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['rateLimiter']['enableRateLimitHeaders'] = false;
    return $behaviors;
}
```

Cuando la limitación de ratio está activada, por defecto cada respuesta puede ser enviada con las siguientes cabeceras de HTTP conteniendo la información actual del límite de ratio:

* `X-Rate-Limit-Limit`, el máximo número de peticiones permitidas en un periodo de tiempo
* `X-Rate-Limit-Remaining`, el número de peticiones restantes en el periodo de tiempo actual
* `X-Rate-Limit-Reset`, el número de segundos a esperar para pedir el máximo número de peticiones permitidas

Puedes desactivar estas cabeceras configurando [[yii\filters\RateLimiter::enableRateLimitHeaders]] a false, tal y como en el anterior ejemplo.

Limit użycia
============

W celu zapobiegnięcia nadużyciom, powinno się rozważyć wprowadzenie *limitu użycia* swojego API. Może to być na przykład ograniczenie 
do maksymalnie 100 zapytań do API dla każdego użytkownika w czasie 10 minut. Jeśli użytkownik przekroczy ten limit w zadanym czasie, 
należy zwrócić odpowiedź ze statusem 429 (oznaczającym "Zbyt dużo zapytań").

Aby ustalić limit użycia, [[yii\web\User::identityClass|klasa identyfikująca użytkownika]] powinna zaimplementować [[yii\filters\RateLimitInterface|RateLimitInterface]].
Interfejs ten wymaga dodania trzech metod:

* `getRateLimit()`: zwraca maksymalną liczbę zapytań i okres czasu (np. `[100, 600]` oznacza maksymalnie 100 zapytań do API w czasie 600 sekund).
* `loadAllowance()`: zwraca liczbę pozostałych dozwolonych zapytań z limitu i uniksowy znacznik czasu wskazujący datę ostatniego sprawdzenia limitu.
* `saveAllowance()`: zapisuje liczbę pozostałych dozwolonych zapytań i aktualny uniksowy znacznik czasu.

Do celów obsługi powyższych metod można wykorzystać dwie dodatkowe kolumny w bazie danych użytkowników dla liczby dokonanych połączeń i znacznika czasu. 
Po ustaleniu tych wartości, metody `loadAllowance()` i `saveAllowance()` mogą być poprawnie zaimplementowane do odczytu i zapisu tych wartości dla aktualnego 
zautoryzowanego użytkownika. Aby zwiększyć wydajność tego mechanizmu, należy rozważyć użycie pamięci podręcznej lub bazy typu NoSQL.

Po zaimplemetowaniu wymaganego interfejsu, Yii automatycznie użyje [[yii\filters\RateLimiter|RateLimiter]], skonfigurowanego jako filtr akcji dla [[yii\rest\Controller|Controller]], 
aby pilnować limitu użycia API. Mechanizm rzuci wyjątek [[yii\web\TooManyRequestsHttpException|TooManyRequestsHttpException]], kiedy limit zostanie przekroczony. 

Po dodaniu limitu, każda odpowiedź będzie domyślnie zawierała następujące nagłówki HTTP, zawierające informacje o aktualnym użyciu limitu:

* `X-Rate-Limit-Limit`, maksymalna liczba zapytań w zadanym okresie czasu,
* `X-Rate-Limit-Remaining`, liczba pozostałych dozwolonych zapytań z limitu w aktualnym okresie czasu,
* `X-Rate-Limit-Reset`, liczba sekund, którą należy odczekać, aby uzyskać ponownie maksymalną liczbę zapytań z limitu.

Wysyłanie powyższych nagłówków można wyłączyć konfigurując [[yii\filters\RateLimiter::enableRateLimitHeaders|enableRateLimitHeaders]] w klasie kontrolera REST jak w poniższym przykładzie.

```php
public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['rateLimiter']['enableRateLimitHeaders'] = false;
    return $behaviors;
}
```

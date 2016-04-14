Routing
=======

Po przygotowaniu klas zasobów i kontrolerów dostęp do nich można uzyskać w ten sam sposób, jak w przypadku zwykłej aplikacji, używając URL np. 
`http://localhost/index.php?r=user/create`.

W praktyce zwykle chcemy skorzystać z opcji "ładnych" URLi i metod HTTP.
Przykładowo żądanie `POST /users` może oznaczać wywołanie akcji `user/create`, co możemy uzyskać w łatwy sposób konfigurując 
[komponent aplikacji](structure-application-components.md) `urlManager` w pliku konfiguracyjnym jak poniżej:

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'enableStrictParsing' => true,
    'showScriptName' => false,
    'rules' => [
        ['class' => 'yii\rest\UrlRule', 'controller' => 'user'],
    ],
]
```

Porównując to z menadżerem URLi dla aplikacji Web, główną nowością tutaj jest użycie [[yii\rest\UrlRule|UrlRule]] do routingu RESTfulowych zasobów API. 
Ta specjalna klasa zasad URL stworzy cały zestaw potomnych zasad URL obsługujących routing i tworzenie URLi dla wyznaczonego kontrolera.
Dla przykładu, kod powyżej jest zgrubnym odpowiednikiem następujących zasad:

```php
[
    'PUT,PATCH users/<id>' => 'user/update',
    'DELETE users/<id>' => 'user/delete',
    'GET,HEAD users/<id>' => 'user/view',
    'POST users' => 'user/create',
    'GET,HEAD users' => 'user/index',
    'users/<id>' => 'user/options',
    'users' => 'user/options',
]
```

I poniższe punkty końcowe API są obsługiwane przez tę zasadę:

* `GET /users`: lista wszystkich użytkowników strona po stronie;
* `HEAD /users`: pokazuje streszczenie informacji listy użytkowników;
* `POST /users`: tworzy nowego użytkownika;
* `GET /users/123`: zwraca szczegóły na temat użytkownika 123;
* `HEAD /users/123`: zwraca streszczenie informacji o użytkowniku 123;
* `PATCH /users/123` i `PUT /users/123`: aktualizuje użytkownika 123;
* `DELETE /users/123`: usuwa użytkownika 123;
* `OPTIONS /users`: pokazuje obsługiwane metody dla punktu końcowego `/users`;
* `OPTIONS /users/123`: pokazuje obsługiwane metody dla punktu końcowego `/users/123`.

Możesz skonfigurować opcje `only` i `except`, aby wskazać listę akcji, które mają być odpowiednio: tylko obsługiwane lub pominięte.
Przykładowo,

```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => 'user',
    'except' => ['delete', 'create', 'update'],
],
```

Dodatkowo można dodać opcję `patterns` lub `extraPatterns`, aby zredefiniować istniejące wzorce lub dodać nowe obsługiwane przez tę zasadę.
Dla przykładu, aby dodać obsługę nowej akcji `search` dla punktu końcowego `GET /users/search`, skonfiguruj opcję `extraPatterns` jak następuje,

```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => 'user',
    'extraPatterns' => [
        'GET search' => 'search',
    ],
]
```

Na pewno zwróciłeś uwagę na to, że ID kontrolera `user` występuje tu w formie mnogiej jako `users` dla URLi punktu końcowego.
Dzieje się tak, ponieważ [[yii\rest\UrlRule|UrlRule]] automatycznie przechodzi na formę mnogą dla ID kontrolerów podczas tworzenia potomnych zasad URL.
Zachowanie to można wyłączyć ustawiając [[yii\rest\UrlRule::pluralize|pluralize]] na false. 

> Info: forma mnoga ID kontrolerów jest tworzona poprzez metodę [[yii\helpers\Inflector::pluralize()|pluralize()]]. Uwzględnia ona specjalne zasady tworzenia form mnogich. 
> Dla przykładu, od słowa `box` zostanie utworzona liczba mnoga `boxes` a nie `boxs`.

W przypadku, gdy mechanizm automatycznego tworzenia formy mnogiej nie spełnia Twoich oczekiwań, możesz również skonfigurować właściwość 
[[yii\rest\UrlRule::controller|controller]], aby bezpośrednio określić w jaki sposób nazwa użyta w punkcie końcowym URLi ma być zmapowana na ID kontrolera. 
Dla przykładu, poniższy kod mapuje nazwę `u` na ID kontrolera `user`.  
 
```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => ['u' => 'user'],
]
```

Obsługa błędów
==============

Podczas obsługi żądania RESTfulowego API, w przypadku wystąpienia błędu w zapytaniu użytkownika lub gdy stanie się coś nieprzewidywanego 
z serwerem, możesz po prostu rzucić wyjątkiem, aby powiadomić użytkownika, że coś poszło nieprawidłowo.
Jeśli możesz zidentyfikować przyczynę błędu (np. żądany zasób nie istnieje), powinieneś rozważyć rzucenie wyjątkiem razem z odpowiednim kodem statusu HTTP 
(np. [[yii\web\NotFoundHttpException|NotFoundHttpException]] odpowiada statusowi o kodzie 404). 
Yii wyśle odpowiedź razem z odpowiadającym jej kodem i treścią statusu HTTP. Yii dołączy również do samej odpowiedzi zserializowaną reprezentację 
wyjątku. Przykładowo:

```
HTTP/1.1 404 Not Found
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "name": "Not Found Exception",
    "message": "The requested resource was not found.",
    "code": 0,
    "status": 404
}
```

Poniższa lista zawiera kody statusów HTTP, które są używane przez Yii REST framework:

* `200`: OK. Wszystko działa w porządku.
* `201`: Zasób został poprawnie stworzony w odpowiedzi na żądanie `POST`. Nagłówek `Location` zawiera URL kierujący do nowoutworzonego zasobu.
* `204`: Żądanie zostało poprawnie przetworzone, ale odpowiedź nie zawiera treści (jak w przypadku żądania `DELETE`).
* `304`: Zasób nie został zmodyfikowany. Można użyć wersji przetrzymywanej w pamięci podręcznej.
* `400`: Nieprawidłowe żądanie. Może być spowodowane przez wiele czynników po stronie użytkownika, takich jak przekazanie nieprawidłowych danych JSON,
   nieprawidłowych parametrów akcji, itp.
* `401`: Nieudana autentykacja.
* `403`: Autoryzowany użytkownik nie ma uprawnień do danego punktu końcowego API.
* `404`: Żądany zasób nie istnieje.
* `405`: Niedozwolona metoda. Sprawdź nagłówek `Allow`, aby poznać dozwolone metody HTTP.
* `415`: Niewspierany typ mediów. Żądany typ zawartości lub numer wersji jest nieprawidłowy.
* `422`: Nieudana walidacja danych (dla przykładu w odpowiedzi na żądanie `POST`). Sprawdź treść odpowiedzi, aby poznać szczegóły błędu.
* `429`: Zbyt wiele żądań. Żądanie zostało odrzucone z powodu osiagnięcia limitu użycia.
* `500`: Wewnętrzny błąd serwera. To może być spowodowane wewnętrznymi błędami programu.


## Modyfikowanie błędnej odpowiedzi <span id="customizing-error-response"></span>

Czasem wymagane może być dostosowanie domyślnego formatu błędnej odpowiedzi. Dla przykładu, zamiast używać różnych statusów HTTP dla oznaczenia różnych błędów, 
można serwować zawsze status 200 i dodawać prawidłowy kod statusu HTTP jako część struktury JSON w odpowiedzi, jak pokazano to poniżej:

```
HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "success": false,
    "data": {
        "name": "Not Found Exception",
        "message": "The requested resource was not found.",
        "code": 0,
        "status": 404
    }
}
```

Aby osiągnąć powyższy efekt, należy skonfigurować odpowiedź na event `beforeSend` dla komponentu `response` w aplikacji:

```php
return [
    // ...
    'components' => [
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null && Yii::$app->request->get('suppress_response_code')) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'data' => $response->data,
                    ];
                    $response->statusCode = 200;
                }
            },
        ],
    ],
];
```

Powyższy kod zreformatuje odpowiedź (zarówno typu sukces jak i błąd), kiedy `suppress_response_code` zostanie przekazane jako parametr `GET`.

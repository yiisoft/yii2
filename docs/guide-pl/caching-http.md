Pamięć podręczna HTTP
=====================

Oprócz pamięci podręcznej tworzonej po stronie serwera, która została opisana w poprzednich rozdziałach, aplikacje mogą również
skorzystać z pamięci podręcznej tworzonej po stronie klienta, aby zaoszczędzić czas poświęcany na ponowne generowanie i przesyłanie
identycznej zawartości strony.

Aby skorzystać z tego mechanizmu, należy skonfigurować [[yii\filters\HttpCache]] jako filtr kontrolera akcji, których wyrenderowana
zwrotka może być zapisana w pamięci podręcznej po stronie klienta. [[yii\filters\HttpCache|HttpCache]] obsługuje tylko żądania typu
`GET` i `HEAD` i dla tych typów tylko trzy nagłówki HTTP związane z pamięcią podręczną:

* [[yii\filters\HttpCache::lastModified|Last-Modified]]
* [[yii\filters\HttpCache::etagSeed|Etag]]
* [[yii\filters\HttpCache::cacheControlHeader|Cache-Control]]


## Nagłówek `Last-Modified` <span id="last-modified"></span>

Nagłówek `Last-Modified` korzysta ze znacznika czasu, aby określić, czy strona została zmodyfikowana od momentu jej ostatniego zapisu
w pamięci podręcznej.

Możesz skonfigurować właściwość [[yii\filters\HttpCache::lastModified]], aby przesyłać nagłowek `Last-Modified`. Właściwość powinna być
typu PHP callable i zwracać uniksowy znacznik czasu informujący o czasie modyfikacji strony. Sygnatura metody jest następująca:

```php
/**
 * @param Action $action aktualnie przetwarzany obiekt akcji
 * @param array $params wartość właściwości "params"
 * @return int uniksowy znacznik czasu modyfikacji strony
 */
function ($action, $params)
```

Poniżej znajdziesz przykład wykorzystania nagłówka `Last-Modified`:

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['index'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('post')->max('updated_at');
            },
        ],
    ];
}
```

Kod ten uruchamia pamięć podręczną HTTP wyłącznie dla akcji `index`, która powinna wygenerować nagłówek HTTP `Last-Modified` oparty
o datę ostatniej aktualizacji postów. Przeglądarka, wyświetlając stronę `index` po raz pierwszy, otrzymuje jej zawartość wygenerowaną
przez serwer; każda kolejna wizyta, przy założeniu, że żaden post nie został zmodyfikowany w międzyczasie, skutkuje wyświetleniem
wersji strony przechowywanej w pamięci podręcznej po stronie klienta, zamiast generować ją ponownie przez serwer.
W rezultacie, renderowanie zawartości po stronie serwera i przesyłanie jej do klienta jest pomijane.


## Nagłowek `ETag` <span id="etag"></span>

Nagłowek "Entity Tag" (lub w skrócie `ETag`) wykorzystuje skrót hash jako reprezentację strony. W momencie, gdy strona się zmieni, jej
hash również automatycznie ulega zmianie. Porównując hash przechowywany po stronie klienta z hashem wygenerowanym przez serwer,
mechanizm pamięci podręcznej ustala, czy strona się zmieniła i powinna być ponownie przesłana.

Możesz skonfigurować właściwość [[yii\filters\HttpCache::etagSeed]], aby przesłać nagłowek `ETag`.
Właściwość powinna być typu PHP callable i zwracać ziarno do wygenerowania hasha ETag. Sygnatura metody jest następująca:

```php
/**
 * @param Action $action aktualnie przetwarzany obiekt akcji
 * @param array $params wartość właściwości "params"
 * @return string łańcuch znaków użyty do generowania hasha ETag
 */
function ($action, $params)
```

Poniżej znajdziesz przykład użycia nagłówka `ETag`:

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['view'],
            'etagSeed' => function ($action, $params) {
                $post = $this->findModel(\Yii::$app->request->get('id'));
                return serialize([$post->title, $post->content]);
            },
        ],
    ];
}
```

Kod ten uruchamia pamięć podręczną HTTP wyłącznie dla akcji `view`, która powinna wygenerować nagłówek HTTP `ETag` oparty o tytuł
i zawartość przeglądanego posta. Przeglądarka, wyświetlając stronę `view` po raz pierwszy, otrzymuje jej zawartość wygenerowaną
przez serwer; każda kolejna wizyta, przy założeniu, że ani tytuł, ani zawartość posta nie została zmodyfikowana w międzyczasie,
skutkuje wyświetleniem wersji strony przechowywanej w pamięci podręcznej po stronie klienta, zamiast generować ją ponownie przez
serwer.
W rezultacie, renderowanie zawartości po stronie serwera i przesyłanie jej do klienta jest pomijane.

ETagi pozwalają na bardziej skomplikowane i precyzyjne strategie przechowywania w pamięci podręcznej niż nagłówki `Last-Modified`.
Dla przykładu, ETag może być zmieniony dla strony w momencie, gdy użyty na niej będzie nowy szablon wyglądu.

Zasobożerne generowanie ETagów może przekreślić cały zysk z użycia `HttpCache` i wprowadzić niepotrzebny narzut, ponieważ muszą być one
określane przy każdym żądaniu. Z tego powodu należy używać jak najprostszych metod generujących.

> Note: Aby spełnić wymagania [RFC 7232](https://datatracker.ietf.org/doc/html/rfc7232#section-2.4),
  `HttpCache` przesyła zarówno nagłówek `ETag`, jak i `Last-Modified`, jeśli oba są skonfigurowane.
  Jeśli klient wysyła nagłówek `If-None-Match` razem z `If-Modified-Since`, tylko pierwszy z nich jest brany pod uwagę.


## Nagłówek `Cache-Control` <span id="cache-control"></span>

Nagłówek `Cache-Control` określa ogólną politykę obsługi pamięci podręcznej stron. Możesz go przesłać konfigurując właściwość
[[yii\filters\HttpCache::cacheControlHeader]] z wartością nagłówka. Domyślnie przesyłany jest następujący nagłówek:

```
Cache-Control: public, max-age=3600
```

## Ogranicznik pamięci podręcznej sesji <span id="session-cache-limiter"></span>

Kiedy strona używa mechanizmu sesji, PHP automatycznie wysyła związane z pamięcią podręczną nagłówki HTTP, określone
w `session.cache_limiter` w ustawieniach PHP INI. Mogą one kolidować z funkcjonalnością `HttpCache`, a nawet całkowicie ją wyłączyć -
aby temu zapobiec, `HttpCache` blokuje to automatyczne wysyłanie. Jeśli jednak chcesz zmienić to zachowanie, powinieneś skonfigurować
właściwość [[yii\filters\HttpCache::sessionCacheLimiter]]. Powinna ona przyjmować wartość zawierającą łańcuch znaków `public`,
`private`, `private_no_expire` i `nocache`. Szczegóły dotyczące tego zapisu znajdziesz w dokumentacji PHP dla
[session_cache_limiter()](https://www.php.net/manual/pl/function.session-cache-limiter.php).


## Korzyści dla SEO <span id="seo-implications"></span>

Boty silników wyszukiwarek zwykle respektują ustawienia nagłówków pamięci podręcznej. Niektóre automaty mają limit ilości stron
zaindeksowanych w pojedynczej domenie w danej jednostce czasu, dlatego też wprowadzenie nagłówków dla pamięci podręcznej może
w znaczącym stopniu przyspieszyć cały proces indeksacji, poprzez redukcję ilości stron, które trzeba przeanalizować.

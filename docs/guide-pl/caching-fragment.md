Pamięć podręczna fragmentów
===========================

Pamięć podręczna fragmentów dotyczy zapisywania w pamięci podręcznej części strony Web. Dla przykładu, jeśli strona wyświetla podsumowanie danych rocznej sprzedaży w postaci tabeli, 
można tę tabelę zapisać w pamięci podręcznej, aby wyeliminować konieczność generowania jej za każdym razem od nowa. Mechanizm pamięci podręcznej fragmentów zbudowany jest w oparciu 
o [pamięć podręczną danych](caching-data.md).

Aby wykorzystać pamięć podręczną fragmentów, należy użyć następującego kodu w [widoku](structure-views.md):

```php
if ($this->beginCache($id)) {

    // ... generowanie zawartości w tym miejscu ...

    $this->endCache();
}
```

Jak widać, chodzi tu o zamknięcie bloku generatora zawartości pomiędzy wywołaniem metod [[yii\base\View::beginCache()|beginCache()]] i [[yii\base\View::endCache()|endCache()]]. 
Jeśli wskazana zawartość zostanie odnaleziona w pamięci podręcznej, [[yii\base\View::beginCache()|beginCache()]] wyrenderuje zapisaną zawartość i zwróci `false`, przez co pominie 
blok jej generowania. W przeciwnym wypadku generowanie zostanie uruchomione, a w momencie wywołania [[yii\base\View::endCache()|endCache()]] wygenerowana zawartość zostanie zapisana 
w pamięci podręcznej.

Tak, jak w przypadku [pamięci podręcznej danych](caching-data.md), unikalne `$id` jest wymagane do identyfikacji zawartości.


## Opcje zapisu w pamięci podręcznej <span id="caching-options"></span>

Możesz określić dodatkowe opcje zapisu pamięci podręcznej fragmentów, przekazując tablicę opcji jako drugi parametr w metodzie [[yii\base\View::beginCache()|beginCache()]]. 
Opcje te będą użyte do skonfigurowania widżetu [[yii\widgets\FragmentCache|FragmentCache]], który implementuje właściwą funkcjonalność zapisu pamięci podręcznej.

### Czas życia <span id="duration"></span>

Prawdopodobnie najczęściej używaną opcją zapisu fragmentów jest [[yii\widgets\FragmentCache::duration|duration]].
Parametr ten określa, przez ile sekund zawartość może być przechowywana w pamięci podręcznej, zanim konieczne będzie wygenerowanie jej ponownie. Poniższy kod zapisuje fragment 
zawartości w pamięci podręcznej na maksymalnie godzinę:

```php
if ($this->beginCache($id, ['duration' => 3600])) {

    // ... generowanie zawartości w tym miejscu ...

    $this->endCache();
}
```

Jeśli ta opcja nie jest określona, przyjmuje domyślną wartość 60, co oznacza, że ważność zapisanej zawartości wygaśnie po upływie 60 sekund.


### Zależności <span id="dependencies"></span>

Tak, jak w przypadku [pamięci podręcznej danych](caching-data.md#cache-dependencies), zapis fragmentów może opierać się na zależnościach.
Dla przykładu, zawartość wyświetlanego posta zależy od tego, czy został on zmodyfikowany, bądź nie.

Aby określić zależność, należy ustawić opcję [[yii\widgets\FragmentCache::dependency|dependency]], która może przyjąć postać zarówno obiektu klasy [[yii\caching\Dependency|Dependency]], 
jak i tablicy konfiguracyjnej, służacej do utworzenia obiektu zależności. Poniższy kod określa pamięć podręczną fragmentu jako zależną od zmiany wartości kolumny `updated_at`:

```php
$dependency = [
    'class' => 'yii\caching\DbDependency',
    'sql' => 'SELECT MAX(updated_at) FROM post',
];

if ($this->beginCache($id, ['dependency' => $dependency])) {

    // ... generowanie zawartości w tym miejscu ...

    $this->endCache();
}
```


### Wariacje <span id="variations"></span>

Zapisana zawartość może mieć kilka wersji, zależnych od niektórych parametrów. Przykładowo, w aplikacji Web wspierającej kilka języków, ten sam fragment kodu w widoku może generować 
zawartość w różnych językach. Z tego powodu wymagana może być konieczność zapisu zawartości w wariacji zależnej od aktualnie wybranego języka aplikacji.

Aby określić wariacje pamięci podręcznej, ustaw opcję [[yii\widgets\FragmentCache::variations|variations]], która powinna mieć postać tablicy wartości skalarnych, z których każda 
będzie reprezentować odpowiedni czynnik modyfikujący wersję. Dla prykładu, aby zapisać zawartość w zależności od języka, możesz użyć następującego kodu:

```php
if ($this->beginCache($id, ['variations' => [Yii::$app->language]])) {

    // ... generowanie zawartości w tym miejscu ...

    $this->endCache();
}
```


### Warunkowe uruchamianie pamięci podręcznej <span id="toggling-caching"></span>

Czasem konieczne może być uruchamianie pamięci podręcznej fragmentów tylko w przypadku, gdy spełnione są określone warunki. Przykładowo, dla strony zawierającej formularz, 
pożądane może być zapisanie i wyświetlenie go z pamięci podręcznej tylko w momencie pierwszego pobrania jego treści (poprzez żądanie GET). Każde kolejne żądanie wyświetlenia formularza 
(już za pomocą metody POST) nie powinno być zapisane w pamięci, ponieważ może zawierać dane podane przez użytkownika.
Aby użyć takiego mechanizmu, należy ustawić opcję [[yii\widgets\FragmentCache::enabled|enabled]], jak w przykładzie poniżej:

```php
if ($this->beginCache($id, ['enabled' => Yii::$app->request->isGet])) {

    // ... generowanie zawartości w tym miejscu ...

    $this->endCache();
}
```


## Zagnieżdżony zapis w pamięci <span id="nested-caching"></span>

Fragmenty zapisane w pamięci podręcznej mogą być zagnieżdżane. Oznacza to, że zapisany fragment może być częścią innego, również zapisanego w pamięci podręcznej.
Przykładowo, komentarze mogą być zapisane jako fragmenty w pamięci podręcznej, które z kolei w całości również są zapisane jako większy fragment w pamięci. 
Poniższy kod pokazuje, w jaki sposób można zagnieździć dwa fragmenty w pamięci podręcznej:

```php
if ($this->beginCache($id1)) {

    // ... generowanie zawartości ...

    if ($this->beginCache($id2, $options2)) {

        // ... generowanie zawartości ...

        $this->endCache();
    }

    // ... generowanie zawartości ...

    $this->endCache();
}
```

Zagnieżdżone fragmenty mogą mieć różne opcje zapisu. Dla przykładu, wewnętrzny i zewnętrzy fragment może mieć inną wartość czasu życia. Nawet w przypadku, gdy zawartość zapisana 
w zewnętrznym fragmencie straci ważność, wewnętrzny fragment wciąż będzie pobierany z pamięci. Nie zadziała to jednak w przeciwnym przypadku - dopóki zewnętrzny fragment będzie ważny, 
będzie zwracał tą samą zawartość za każdym razem, niezależnie od tego, czy zawartość wewnętrznego fragmentu już wygasła, czy nie. Z tego powodu należy zwrócić szczególną ostrożność 
przy ustalaniu czasu życia lub zależności zagnieżdżonych fragmentów, ponieważ "stara" zawartość może być wciąż niezamierzenie przechowywana w zewnętrznym fragmencie.


## Dynamiczna zawartość <span id="dynamic-content"></span>

Używając pamięci podręcznej fragmentów, można napotkać na sytuację, kiedy duża część zawartości strony jest względnie statyczna z wyjątkiem kilku nielicznych miejsc. 
Przykładowo, nagłówek strony może wyświetlać pasek głównego menu razem z imieniem aktualnie zalogowanego użytkownika. Innym kłopotem może być to, że zapisywana w pamięci zawartość 
może zawierać kod PHP, który musi być wykonany dla każdego żądania (np. kod rejestrujący paczkę assetów). W obu tych przypadkach pomoże nam skorzystanie z funkcjonalności tzw. 
*dynamicznej zawartości*.

Dynamiczna zawartość oznacza fragment zwrotki, który nie powinien zostać zapisany w pamięci podręcznej, nawet jeśli znajduje się w bloku objętym zapisem. Aby określić zawartość jako 
dynamiczną, musi być ona generowana poprzez wykonanie jakiegoś kodu PHP dla każdego zapytania, nawet jeśli całość fragmentu serwowana jest z pamięci podręcznej.

Możesz wywołać metodę [[yii\base\View::renderDynamic()|renderDynamic()]] wewnątrz zapisywanego fragmentu, aby wstawić w danym miejscu zawartość dynamiczną, jak w przykładzie poniżej:

```php
if ($this->beginCache($id1)) {

    // ... generowanie zawartości ...

    echo $this->renderDynamic('return Yii::$app->user->identity->name;');

    // ... generowanie zawartości ...

    $this->endCache();
}
```

Metoda [[yii\base\View::renderDynamic()|renderDynamic()]] przyjmuje jako parametr kod PHP.
Wartość zwracana przez ten kod jest traktowana jako zawartość dynamiczna. Ten sam kod PHP będzie wykonany dla każdego zapytania, niezależnie od tego, czy obejmujący go fragment będzie 
pobierany z pamięci podręcznej czy też nie.

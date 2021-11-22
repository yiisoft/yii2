Aktualizacja z wersji 1.1
=========================

Pomiędzy wersjami 1.1 i 2.0 Yii jest ogrom różnic, ponieważ framework został całkowicie przepisany w 2.0.
Z tego też powodu aktualizacja z wersji 1.1 nie jest tak trywialnym procesem, jak w przypadku aktualizacji pomiędzy pomniejszymi wersjami. 
W tym przewodniku zapoznasz się z największymi różnicami dwóch głównych wersji.

Jeśli nie korzystałeś wcześniej z Yii 1.1, możesz pominąć tę sekcję i przejść bezpośrednio do "[Pierwszych kroków](start-installation.md)".

Zwróć uwagę na to, że Yii 2.0 wprowadza znacznie więcej nowych funkcjonalności, niż wymienionych jest w tym podsumowaniu. 
Wskazane jest zapoznanie się z treścią całego przewodnika, aby poznać je wszystkie. Jest bardzo prawdopodobne, że niektóre z mechanizmów, które 
poprzednio musiałeś stworzyć samemu, teraz są częścią podstawowego kodu.


Instalacja
----------

Yii 2.0 w pełni korzysta z udogodnień [Composera](https://getcomposer.org/), będącego de facto menadżerem projektów PHP. 
Z jego pomocą odbywa się zarówno instalacja podstawowego frameworka, jak i wszystkich rozszerzeń. Aby zapoznać się ze szczegółową 
instrukcją instalacji Yii 2.0, przejdź do sekcji [Instalacja Yii](start-installation.md). Jeśli chcesz stworzyć nowe rozszerzenie 
lub zmodyfikować istniejące w wersji 1.1, aby było kompatybilne z 2.0, przejdź do sekcji [Tworzenie rozszerzeń](structure-extensions.md#creating-extensions).


Wymagania PHP
-------------

Yii 2.0 wymaga PHP w wersji 5.4 lub nowszej, która została znacząco ulepszona w stosunku do wersji 5.2 (wymaganej przez Yii 1.1).
Z tego też powodu już na poziomie samego języka pojawiło się sporo różnic, na które należy zwrócić uwagę.
Poniżej znajdziesz krótkie podsumowanie głównych różnic dotyczących PHP:

- [Przestrzenie nazw](https://www.php.net/manual/pl/language.namespaces.php).
- [Funkcje anonimowe](https://www.php.net/manual/pl/functions.anonymous.php).
- Skrócona składnia zapisu tablic `[...elementy...]` używana zamiast `array(...elementy...)`.
- Krótkie tagi echo `<?=` używane w plikach widoków. Można ich używać bezpiecznie, począwszy od PHP 5.4.
- [Klasy i interfejsy SPL](https://www.php.net/manual/pl/book.spl.php).
- [Opóźnione statyczne wiązania](https://www.php.net/manual/pl/language.oop5.late-static-bindings.php).
- [Data i czas](https://www.php.net/manual/pl/book.datetime.php).
- [Traity](https://www.php.net/manual/pl/language.oop5.traits.php).
- [Rozszerzenie intl](https://www.php.net/manual/pl/book.intl.php). Yii 2.0 korzysta z rozszerzenia PHP `intl` do wsparcia obsługi internacjonalizacji.


Przestrzeń nazw
---------------

Najbardziej oczywista zmiana w Yii 2.0 dotyczy używania przestrzeni nazw. Praktycznie każda z podstawowych klas je wykorzystuje, np. `yii\web\Request`. 
Prefiks "C" nie jest już używany w nazwach, a sam schemat nazewnictwa odpowiada teraz strukturze folderów - dla przykładu `yii\web\Request` wskazuje, 
że plik klasy to `web/Request.php` znajdujący się w folderze frameworka Yii.

Dzięki mechanizmowi ładowania klas Yii możesz użyć dowolnej podstawowej klasy frameworka bez konieczności bezpośredniego dołączania jej kodu.


Komponent i obiekt
------------------

Yii 2.0 rozdzielił klasę `CComponent` z 1.1 na dwie: [[yii\base\BaseObject|BaseObject]] i [[yii\base\Component|Component]].
Lekka klasa [[yii\base\BaseObject|BaseObject]] pozwala na zdefiniowanie [właściwości obiektu](concept-properties.md) poprzez gettery i settery. 
Klasa [[yii\base\Component|Component]] dziedziczy po [[yii\base\BaseObject|BaseObject]] i dodatkowo wspiera obsługę [zdarzeń](concept-events.md) oraz [zachowań](concept-behaviors.md).

Jeśli Twoja klasa nie wymaga ww. wsparcia, rozważ użycie [[yii\base\BaseObject|BaseObject]] jako klasy podstawowej. Tak jest zazwyczaj w przypadku klas reprezentujących 
najbardziej podstawową strukturę danych.


Konfiguracja obiektu
--------------------

Klasa [[yii\base\BaseObject|BaseObject]] wprowadza ujednoliconą formę konfigurowania obiektów. Każda klasa dziedzicząca po [[yii\base\BaseObject|BaseObject]] powinna zadeklarować swój 
konstruktor (jeśli tego wymaga) w następujący sposób, dzięki czemu zostanie poprawnie skonfigurowana:

```php
class MyClass extends \yii\base\BaseObject
{
    public function __construct($param1, $param2, $config = [])
    {
        // ... inicjalizacja przed skonfigurowaniem

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... inicjalizacja po skonfigurowaniu
    }
}
```

W powyższym przykładzie ostatnim parametrem konstruktora musi być tablica konfiguracyjna, 
zawierająca pary nazwa-wartość służące do zainicjowania właściwości na końcu konstruktora.
Możesz nadpisać metodę [[yii\base\BaseObject::init()|init()]], aby wykonać dodatkowy proces inicjalizacyjny po 
zaaplikowaniu konfiguracji.

Dzięki tej konwencji możesz tworzyć i konfigurować nowe obiekty, używając 
tablicy konfiguracyjnej:

```php
$object = Yii::createObject([
    'class' => 'MyClass',
    'property1' => 'abc',
    'property2' => 'cde',
], [$param1, $param2]);
```

Więcej szczegółów na temat konfiguracji znajdziesz w sekcji [Konfiguracje](concept-configurations.md).


Zdarzenia (Events)
------------------

W Yii 1 zdarzenia były tworzone poprzez definiowanie `on`-metody (np., `onBeforeSave`). W Yii 2 możesz użyć dowolnej nazwy. 
Uruchomienie zdarzenia następuje poprzez wywołanie metody [[yii\base\Component::trigger()|trigger()]]:

```php
$event = new \yii\base\Event;
$component->trigger($eventName, $event);
```

Aby dołączyć uchwyt do zdarzenia, użyj metody [[yii\base\Component::on()|on()]]:

```php
$component->on($eventName, $handler);
// a aby odłączyć uchwyt użyj:
// $component->off($eventName, $handler);
```

Zdarzenia zostały wzbogacone w wiele udoskonaleń. Więcej szczegółów na ten temat znajdziesz w sekcji [Zdarzenia (Events)](concept-events.md).


Aliasy ścieżek
--------------

Yii 2.0 rozszerza funkcjonalność aliasów ścieżek zarówno na ścieżki plików oraz folderów, jak i adresy URL. Yii 2.0 wymaga teraz też, 
aby nazwa aliasu zaczynała się znakiem `@` w celu odróżnienia jej od zwyczajnych ścieżek plików/folderów lub URLi.
Dla przykładu: alias `@yii` odnosi się do folderu instalacji Yii. Aliasy ścieżek są wykorzystywane w większości miejsc w podstawowym 
kodzie Yii, choćby [[yii\caching\FileCache::cachePath|cachePath]] - można tu przekazać zarówno zwykłą ścieżkę, jak i alias.

Alias ścieżki jest mocno powiązany z przestrzenią nazw klasy. Zalecane jest, aby zdefiniować alias dla każdej podstawowej 
przestrzeni nazw, dzięki czemu mechanizm automatycznego ładowania klas Yii nie będzie wymagał dodatkowej konfiguracji. 
Dla przykładu: dzięki temu, że `@yii` odwołuje się do folderu instalacji Yii, klasa taka jak `yii\web\Request` może być automatycznie załadowana. 
Jeśli używasz zewnętrznych bibliotek, jak np. Zend Framework, możesz zdefiniować alias `@Zend` odnoszący się do folderu instalacji tego frameworka. 
Od tej pory Yii będzie również w stanie automatycznie załadować każdą klasę z tej biblioteki.

Więcej o aliasach ścieżek dostępne jest w sekcji [Aliasy](concept-aliases.md).


Widoki
------

Najbardziej znaczącą zmianą dotyczącą widoków w Yii 2 jest użycie specjalnej zmiennej `$this`. W widoku nie odnosi się ona już do 
aktualnego kontrolera lub widżetu, lecz do obiektu *widoku*, nowej koncepcji przedstawionej w 2.0. Obiekt *widoku* jest klasą typu [[yii\web\View|View]], która 
reprezentuje część wzorca MVC. Jeśli potrzebujesz odwołać się do kontrolera lub widżetu w widoku, możesz użyć `$this->context`.

Aby zrenderować częściowy widok wewnątrz innego widoku, możesz użyć `$this->render()` zamiast dotychczasowego `$this->renderPartial()`. 
Wywołanie `render` musi teraz też być bezpośrednio wyechowane, ponieważ metoda `render()` zwraca rezultat renderowania zamiast od razu go wyświetlać. 

```php
echo $this->render('_item', ['item' => $item]);
```

Oprócz wykorzystania PHP jako podstawowego języka szablonów, Yii 2.0 oficjalnie wspiera dwa popularne silniki szablonów: Smarty i Twig (The Prado nie jest już wspierany).
Aby użyć któregokolwiek z tych silników, musisz skonfigurować komponent aplikacji `view` poprzez ustawienie właściwości [[yii\base\View::$renderers|$renderers]]. 
Po więcej szczegółów przejdź do sekcji [Silniki szablonów](tutorial-template-engines.md).


Modele
------

Yii 2.0 korzysta z [[yii\base\Model|Model]] jako bazowego modelu, podobnie jak `CModel` w 1.1.
Klasa `CFormModel` została całkowicie usunięta, w Yii 2 należy rozszerzyć [[yii\base\Model|Model]], aby stworzyć klasę modelu formularza.

Yii 2.0 wprowadza nową metodę [[yii\base\Model::scenarios()|scenarios()]], służącą do deklarowania scenariuszy, jak i do oznaczania, w którym scenariuszu 
atrybut będzie wymagał walidacji, może być uznany za bezpieczny lub nie itp. Dla przykładu:

```php
public function scenarios()
{
    return [
        'backend' => ['email', 'role'],
        'frontend' => ['email', '!role'],
    ];
}
```

Widzimy tutaj dwa zadeklarowane scenariusze: `backend` i `frontend`. W scenariuszu `backend` obydwa atrybuty, 
`email` i `role`, są traktowane jako bezpieczne i mogą być przypisane zbiorczo. W przypadku scenariusza `frontend`, 
`email` może być przypisany zbiorczo, ale `role` już nie. Zarówno `email` jak i `role` powinny przejść proces walidacji.

Metoda [[yii\base\Model::rules()|rules()]] wciąż służy do zadeklarowania zasad walidacji. Zauważ, że z powodu wprowadzenia [[yii\base\Model::scenarios()|scenarios()]], 
nie ma już walidatora `unsafe`.

Jeśli metoda [[yii\base\Model::rules()|rules()]] deklaruje użycie wszystkich możliwych scenariuszy i jeśli nie masz potrzeby deklarowania atrybutów `unsafe` (niebezpiecznych), 
w większości przypadków nie potrzebujesz nadpisywać metody [[yii\base\Model::scenarios()|scenarios()]].

Aby dowiedzieć się więcej o modelach, przejdź do sekcji [Modele](structure-models.md).


Kontrolery
----------

Yii 2.0 używa [[yii\web\Controller|Controller]] jako bazowej klasy kontrolera, podobnie do `CController` w Yii 1.1.
[[yii\base\Action|Action]] jest bazową klasą dla akcji.

Najbardziej oczywistą implikacją tych zmian jest to, że akcja kontrolera powinna zwracać zawartość, którą chcesz wyświetlić, zamiast wyświetlać ją bezpośrednio:

```php
public function actionView($id)
{
    $model = \app\models\Post::findOne($id);
    if ($model) {
        return $this->render('view', ['model' => $model]);
    } else {
        throw new \yii\web\NotFoundHttpException;
    }
}
```

Przejdź do sekcji [Kontrolery](structure-controllers.md), aby poznać więcej szczegółów na ten temat.


Widżety
-------

Yii 2.0 korzysta z [[yii\base\Widget|Widget]] jako bazowej klasy widżetów, podobnie jak `CWidget` w Yii 1.1.

Dla lepszego wsparcia frameworka w aplikacjach IDE Yii 2.0 wprowadził nową składnię używania widżetów. Używane są teraz metody 
[[yii\base\Widget::begin()|begin()]], [[yii\base\Widget::end()|end()]] i [[yii\base\Widget::widget()|widget()]] w następujący sposób:

```php
use yii\widgets\Menu;
use yii\widgets\ActiveForm;

// Zwróć uwagę na konieczność użycia "echo", aby wyświetlić rezultat
echo Menu::widget(['items' => $items]);

// Przekazujemy tablicę, aby zainicjalizować właściwości obiektu
$form = ActiveForm::begin([
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => ['inputOptions' => ['class' => 'input-xlarge']],
]);
... pola formularza w tym miejscu ...
ActiveForm::end();
```

Więcej szczegółów na ten temat znajdziesz w sekcji [Widżety](structure-widgets.md).


Skórki i motywy (Theming)
-------------------------

Skórki działają zupełnie inaczej w 2.0. Oparte są teraz na mechanizmie mapowania ścieżki, który przekształca źródłowy plik widoku 
w plik widoku skórki. Dla przykładu, jeśli mapa ścieżki dla skórki to `['/web/views' => '/web/themes/basic']`, to skórkowa wersja pliku widoku 
`/web/views/site/index.php` to `/web/themes/basic/site/index.php`. Dzięki temu skórki mogą być użyte dla dowolnego pliku widoku, nawet w przypadku 
widoku wyrenderowanego poza kontekstem kontrolera lub widżetu.

Nie ma również już komponentu `CThemeManager`. Zamiast tego `theme` jest konfigurowalną właściwością komponentu aplikacji `view`.

Sekcja [Skórki i motywy (Theming)](output-theming.md) zawiera więcej szczegółów na ten temat.


Aplikacje konsolowe
-------------------

Aplikacje konsolowe używają teraz kontrolerów tak jak aplikacje webowe. Kontrolery konsolowe powinny rozszerzać klasę 
[[yii\console\Controller]], podobnie jak `CConsoleCommand` w 1.1.

Aby uruchomić polecenie konsoli, użyj `yii <route>`, gdzie `<route>` oznacza ścieżkę kontrolera (np. `sitemap/index`). 
Dodatkowe anonimowe argumenty są przekazywane jako parametry do odpowiedniej metody akcji kontrolera, natomiast nazwane 
argumenty są przetwarzane według deklaracji zawartych w [[yii\console\Controller::options()|options()]].

Yii 2.0 wspiera automatyczne generowanie informacji pomocy poprzez bloki komentarzy.

Aby dowiedzieć się więcej na ten temat, przejdź do sekcji [Komendy konsolowe](tutorial-console.md).


I18N
----

Yii 2.0 usunął wbudowany formater dat i liczb na rzecz [modułu PECL intl PHP](https://pecl.php.net/package/intl).

Tłumaczenia wiadomości odbywają się teraz poprzez komponent aplikacji `i18n`, w którym można ustalić zestaw źródeł treści, 
dzięki czemu możliwy jest ich wybór dla różnych kategorii wiadomości.

W sekcji [Internacjonalizacja](tutorial-i18n.md) znajdziesz więcej szczegółów na ten temat.


Filtry akcji
------------

Filtry akcji są implementowane od teraz za pomocą zachowań (behavior). Aby zdefiniować nowy filtr, należy rozszerzyć klasę [[yii\base\ActionFilter|ActionFilter]]. 
Użycie filtru odbywa się poprzez dołączenie go do kontrolera jako zachowanie. Dla przykładu: aby użyć filtra [[yii\filters\AccessControl]], dodaj poniższy kod w kontrolerze:

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => 'yii\filters\AccessControl',
            'rules' => [
                ['allow' => true, 'actions' => ['admin'], 'roles' => ['@']],
            ],
        ],
    ];
}
```

Więcej informacji na ten temat znajdziesz w sekcji [Filtry](structure-filters.md).


Zasoby (Assets)
---------------

Yii 2.0 wprowadza nowy mechanizm tzw. *pakietów zasobów*, który zastąpił koncepcję pakietów skryptowych z Yii 1.1.

Pakiet zasobów jest kolekcją plików zasobów (np. plików JavaScript, CSS, obrazków, itd.) zgromadzoną w folderze. 
Każdy pakiet jest reprezentowany przez klasę rozszerzającą [[yii\web\AssetBundle|AssetBundle]]. Zarejestrowanie pakietu poprzez 
metodę [[yii\web\AssetBundle::register()|register()]] pozwala na udostępnienie go publicznie. W przeciwieństwie do rozwiązania z Yii 1, 
strona rejestrująca pakiet będzie automatycznie zawierać referencje do plików JavaScript i CSS wymienionych na jego liście.

Sekcja [Zasoby (Assets)](structure-assets.md) zawiera szczegółowe informacje na ten temat.


Klasy pomocnicze
----------------

Yii 2.0 zawiera wiele powszechnie używanych statycznych klas pomocniczych (helperów), takich jak:

* [[yii\helpers\Html|Html]]
* [[yii\helpers\ArrayHelper|ArrayHelper]]
* [[yii\helpers\StringHelper|StringHelper]]
* [[yii\helpers\FileHelper|FileHelper]]
* [[yii\helpers\Json|Json]]

W sekcji [Klasy pomocnicze](helper-overview.md) znajdziesz więcej informacji na ten temat.


Formularze
----------

Yii 2.0 wprowadza koncepcję *pola* do budowy formularzy, korzystając z klasy [[yii\widgets\ActiveForm|ActiveForm]]. 
Pole jest kontenerem składającym się z etykiety, pola wprowadzenia danych formularza, informacji o błędzie i/lub tekstu podpowiedzi, reprezentowanym 
przez obiekt klasy [[yii\widgets\ActiveField|ActiveField]].
Używając pól, możesz stworzyć formularz w sposób o wiele prostszy i bardziej przejrzysty niż do tej pory:

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <div class="form-group">
        <?= Html::submitButton('Login') ?>
    </div>
<?php yii\widgets\ActiveForm::end(); ?>
```

Aby dowiedzieć się więcej na ten temat, przejdź do sekcji [Tworzenie formularzy](input-forms.md).


Konstruktor kwerend
-------------------

W 1.1 budowanie kwerend było rozrzucone pomiędzy kilka klas, tj. `CDbCommand`, `CDbCriteria` i `CDbCommandBuilder`. 
Yii 2.0 reprezentuje kwerendę bazodanową w postaci obiektu [[yii\db\Query|Query]], który może być zamieniony 
w komendę SQL za pomocą [[yii\db\QueryBuilder|QueryBuilder]].
Przykładowo:

```php
$query = new \yii\db\Query();
$query->select('id, name')
      ->from('user')
      ->limit(10);

$command = $query->createCommand();
$sql = $command->sql;
$rows = $command->queryAll();
```

Co najlepsze, taki sposób tworzenia kwerend może być również wykorzystany przy pracy z [Active Record](db-active-record.md).

Po więcej szczegółów udaj się do sekcji [Konstruktor kwerend](db-query-builder.md).


Active Record
-------------

Yii 2.0 wprowadza sporo zmian w mechanizmie [Active Record](db-active-record.md). Dwie najbardziej znaczące to konstruowanie kwerend i obsługa relacji.

Klasa `CDbCriteria` z 1.1 została zastąpiona przez [[yii\db\ActiveQuery|ActiveQuery]] w Yii 2. Klasa ta rozszerza [[yii\db\Query|Query]], dzięki czemu 
dziedziczy wszystkie metody konstruowania kwerend. Aby rozpocząć budowanie kwerendy, wywołaj metodę [[yii\db\ActiveRecord::find()|find()]]:

```php
// Pobranie wszystkich *aktywnych* klientów i posortowanie po ich ID:
$customers = Customer::find()
    ->where(['status' => $active])
    ->orderBy('id')
    ->all();
```

Deklaracja relacji polega na prostym zdefiniowaniu metody gettera, który zwróci obiekt [[yii\db\ActiveQuery|ActiveQuery]].
Nazwa właściwości określonej przez tego gettera reprezentuje nazwę stworzonej relacji. Dla przykładu: w poniższym kodzie deklarujemy 
relację `orders` (w 1.1 konieczne było zadeklarowanie relacji wewnątrz wydzielonej specjalnie metody `relations()`):

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany('Order', ['customer_id' => 'id']);
    }
}
```

Od tej pory można posługiwać się `$customer->orders`, aby uzyskać dostęp do tabeli zamówień klientów poprzez relację. Dodatkowo można również posłużyć się 
następującym kodem, aby wywołać relacyjną kwerendę dla zadanych warunków:

```php
$orders = $customer->getOrders()->andWhere('status=1')->all();
```

Przy "gorliwym" pobieraniu relacji ("eager", w przyciwieństwie do leniwego pobierania "lazy") Yii 2.0 działa inaczej niż w wersji 1.1. W 1.1 tworzono kwerendę JOIN, 
aby pobrać zarówno główne, jak i relacyjne rekordy. W Yii 2.0 wywoływane są dwie komendy SQL bez użycia JOIN - pierwsza pobiera główne rekordy, a druga relacyjne, filtrując je 
przy użyciu kluczy głównych rekordów.

Aby zmniejszyć zużycie CPU i pamięci, zamiast zwracać obiekty [[yii\db\ActiveRecord|ActiveRecord]], do kwerendy pobierającej dużą ilość rekordów możesz podpiąć metodę 
[[yii\db\ActiveQuery::asArray()|asArray()]], dzięki czemu zostaną one pobrane jako tablice. Przykładowo:

```php
$customers = Customer::find()->asArray()->all();
```

Inną istotną zmianą jest to, że nie można już definiować domyślnych wartości atrybutów poprzez publiczne właściwości. 
Jeśli potrzebujesz takich definicji, powinieneś przypisać je wewnątrz metody `init` w klasie rekordu.

```php
public function init()
{
    parent::init();
    $this->status = self::STATUS_NEW;
}
```

Nadpisywanie konstruktora klasy ActiveRecord w 1.1 wiązało się z pewnymi problemami, co nie występuje już w wersji 2.0. 
Zwróć jednak uwagę na to, że przy dodawaniu parametrów do konstruktora możesz potrzebować nadpisać metodę [[yii\db\ActiveRecord::instantiate()|instantiate()]].

W nowym rekordzie aktywnym znajdziesz wiele innych zmian i udogodnień. Aby zapoznać się z nimi, przejdź do sekcji [Rekord aktywny](db-active-record.md).


Zachowania Active Record
------------------------

W 2.0 zrezygnowaliśmy z bazowej klasy zachowania `CActiveRecordBehavior`. Jeśli chcesz stworzyć zachowanie dla rekordu aktywnego, musisz 
rozszerzyć bezpośrednio klasę [[yii\base\Behavior|Behavior]]. Jeśli klasa zachowania ma reagować na zdarzenia, powinna nadpisywać metodę [[yii\base\Behavior::events()|events()]], 
jak zaprezentowano poniżej:

```php
namespace app\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class MyBehavior extends Behavior
{
    // ...

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event)
    {
        // ...
    }
}
```


Klasa User i IdentityInterface
------------------------------

Klasa `CWebUser` z 1.1 została zastąpiona przez [[yii\web\User|User]] i nie ma już klasy `CUserIdentity`. 
Zamiast tego należy zaimplementować interfejs [[yii\web\IdentityInterface|IdentityInterface]], który jest znacznie bardziej wygodny i oczywisty w użyciu. 
Szablon zaawansowanego projektu zawiera przykład takiego właśnie użycia.

Po więcej szczegółów zajrzyj do sekcji [Uwierzytelnianie](security-authentication.md), [Autoryzacja](security-authorization.md) 
i [Szablon zaawansowanego projektu](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md).


Zarządzanie adresami URL
------------------------

Zarządzanie adresami URL w Yii 2 jest bardzo podobne do tego znanego z 1.1. Głównym ulepszeniem tego mechanizmu jest teraz wsparcie dla parametrów opcjonalnych. 
Dla przykładu: warunek dla adresu zadeklarowany poniżej obejmie zarówno `post/popular` jak i `post/1/popular`. W 1.1 konieczne byłoby napisanie dwóch warunków, aby 
osiągnąć to samo.

```php
[
    'pattern' => 'post/<page:\d+>/<tag>',
    'route' => 'post/index',
    'defaults' => ['page' => 1],
]
```

Przejdź do sekcji [Zarządzania adresami URL](runtime-routing.md) po więcej informacji.

Istotną zmianą konwencji nazw dla adresów jest to, że nazwy kontrolerów i akcji typu "camel case" są teraz 
konwertowane do małych liter, z każdym słowem oddzielonym za pomocą myślnika, np. ID kontrolera `CamelCaseController` zostanie 
przekształcone w `camel-case`.

Zapoznaj się z sekcją dotyczącą [ID kontrolerów](structure-controllers.md#controller-ids) i [ID akcji](structure-controllers.md#action-ids).


Korzystanie z Yii 1.1 i 2.x jednocześnie
----------------------------------------

Jeśli chciałbyś skorzystać z kodu napisanego dla Yii 1.1 w aplikacji Yii 2.0, 
prosimy o zapoznanie się z sekcją [Używanie Yii 1.1 i 2.0 razem](tutorial-yii-integration.md#using-both-yii2-yii1).

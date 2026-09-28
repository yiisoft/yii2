Witaj świecie
============

Ta sekcja opisuje jak utworzyć nową stronę "Witaj" w Twojej aplikacji.
Aby to osiągnąć, musisz utworzyć [akcję](structure-controllers.md#creating-actions) i [widok](structure-views.md):

* Aplikacja wyśle żądanie strony web do akcji
* Następnie akcja włączy widok, który pokazuje użytkownikowi słowo "Witaj".

Podczas tego poradnika nauczysz się trzech rzeczy:

1. Jak utworzyć [akcję](structure-controllers.md#creating-actions), która będzie odpowiadać na żądania,
2. Jak utworzyć [widok](structure-views.md), aby wyeksponować treść odpowiedzi,
3. Jak aplikacja wysyła żądania do [akcji](structure-controllers.md#creating-actions).

Tworzenie akcji <span id="creating-action"></span>
------------------

Do zadania "Witaj" utworzysz [akcję](structure-controllers.md#creating-actions) `say`, która odczytuje parametr `message` z żądania oraz wyświetla tą wiadomość użytkownikowi.
Jeśli żądanie nie dostarczy parametru `message`, akcja wyświetli domyślnie wiadomość "Witaj".

> Info: [Akcje](structure-controllers.md#creating-actions) są obiektami, do których użytkownik może bezpośrednio odnieść się, aby je wywołać.
> Akcje są pogrupowane w [kontrolery](structure-controllers.md). Wynikiem użycia akcji jest odpowiedź, którą otrzyma końcowy użytkownik.

Akcje muszą być deklarowane w [kontrolerach](structure-controllers.md). Dla uproszczenia, możesz zdeklarować akcję `say` w już istniejącym kontrolerze `SiteController`. 
Kontroler jest zdefiniowany w klasie `controllers/SiteController.php`. Oto początek nowej akcji:

```php
<?php

namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    // ...obecny kod...

    public function actionSay($message = 'Hello')
    {
        return $this->render('say', ['message' => $message]);
    }
}
```

W powyższym kodzie, akcja `say` jest zdefiniowana jako metoda o nazwie `actionSay` w klasie `SiteController`.
Yii używa prefixu `action` do rozróżnienia metod akcji od zwykłych metod w klasie kontrolera. Nazwa po prefixie `action` kieruje do ID akcji.

Podczas nazywania Twoich akcji powinieneś zrozumieć jak Yii traktuje ID akcji. Odwołanie do ID akcji zawsze występuje z małych liter. 
Jeśli ID akcji potrzebuje wielu słów, będą one łączone myślnikami (np. `create-comment`). Nazwy metod akcji są przypisywane do ID akcji przez usunięcie myślników z ID, przekształcenie 
piewszej litery w słowie na dużą literę oraz dodanie prefixu `action`. Dla przykładu akcja o ID `create-comment` odpowiada metodzie akcji o nazwie `actionCreateComment`.

Metoda akcji w naszym przykładzie przyjmuje parametr `$message`, którego wartość domyślna to `"Hello"` (w ten sam sposób ustawiasz domyślną wartość dla każdego argumentu funkcji lub 
metody w PHP).
Kiedy aplikacja otrzymuje żądanie i określa, że akcja `say` jest odpowiedzialna za jego obsługę, aplikacja uzupełni parametr znaleziony w żądaniu. 
Innymi słowy, jeśli żądanie zawiera parametr `message` z wartością `"Goodbye"` to do zmiennej `$message` w akcji będzie przypisana ta wartość.

W metodzie akcji wywołana jest funkcja [[yii\web\Controller::render()|render()]], która renderuje nam [widok](structure-views.md) pliku o nazwie `say`.
Parametr `message` jest również przekazywany do widoku, co sprawia, że może być w nim użyty. Metoda akcji zwraca wynik renderowania. Wynik ten będzie odebrany przez aplikację oraz 
wyświetlony końcowemu użytownikowi w przeglądarce (jako część kompletnej strony HTML).

Tworzenie widoku <span id="creating-view"></span>
---------------

[Widoki](structure-views.md) są skryptami, które tworzysz w celu wyświetlenia treści odpowiedzi.
Do zadania "Hello" utworzysz widok `say`, który wypisuje parametr `message` otrzymany z metody akcji.

```php
<?php
use yii\helpers\Html;
?>
<?= Html::encode($message) ?>
```

Widok `say` powinien być zapisany w pliku `views/site/say.php`. Kiedy wywołana jest metoda [[yii\web\Controller::render()|render()]] w akcji, będzie ona szukała pliku PHP nazwanego 
wg schematu `views/ControllerID/ViewName.php`.

Zauważ, że w powyższym kodzie parametr `message` jest przetworzony za pomocą metody [[yii\helpers\Html::encode()|encode()]] przed wyświetleniem go. Jest to konieczne w przypadku 
parametrów pochodzących od użytkownika, wrażliwych na ataki [XSS](https://en.wikipedia.org/wiki/Cross-site_scripting) przez podanie złośliwego kodu JavaScript.

Naturalnie możesz umieścić więcej zawartości w widoku `say`. Zawartość może zawierać tagi HTML, czysty tekst, a nawet kod PHP.
Tak naprawdę, widok `say` jest tylko skryptem PHP, który jest wywoływany przez metodę [[yii\web\Controller::render()|render()]].
Zawartość wyświetlana przez skrypt widoku będzie zwrócona do aplikacji jako wynik odpowiedzi. Aplikacja z kolei przedstawi ten wynik końcowemu użytkownikowi.

Próba <span id="trying-it-out"></span>
-------------

Po utworzeniu akcji oraz widoku możesz uzyskać dostęp do nowej strony przez przejście pod podany adres URL:

```
https://hostname/index.php?r=site%2Fsay&message=Hello+World
```

![Witaj świecie](images/start-hello-world.png)

Wynikiem wywołania tego adresu jest wyświetlenie napisu "Hello World". Strona dzieli ten sam nagłówek i stopkę z innymi stronami aplikacji. 

Jeśli pominiesz parametr `message` w adresie URL, zobaczysz na stronie tylko "Hello". `message` jest przekazywane jako parametr do metody `actionSay` i, jeśli zostanie pominięty, 
zostanie użyta domyślna wartość `"Hello"`.

> Info: Nowa strona dzieli ten sam nagłówek i stopkę z innymi stronami, ponieważ metoda [[yii\web\Controller::render()|render()]] automatycznie osadza wynik widoku `say` w tak zwanym 
[układzie strony](structure-views.md#layouts), który, w tym przypadku, znajduje się w `views/layouts/main.php`.

Parametr `r` w powyższym adresie URL wymaga głębszego objaśnienia. Oznacza on [route'a](runtime-routing.md), identyfikator akcji unikatowy w obrębie aplikacji.
Format route'a to `ControllerID/ActionID`. Kiedy aplikacja otrzymuje żądanie, sprawdza ten parametr, a następnie używa części `ControllerID`, aby ustalić, która klasa kontrolera 
powinna zostać zainstancjowana dla przetworzenia tego żądania.
Następnie, kontroler używa części `ActionID` do ustalenia, która akcja powinna zostać użyta. W tym przykładzie, route `site/say` będzie odczytany jako klasa kontrolera `SiteController` 
oraz akcja `say`.
W rezultacie zostanie wywołana metoda `SiteController::actionSay()`.

> Info: Tak jak i akcje, kontrolery również posiadają swoje ID, które jednoznacznie identyfikuje je w aplikacji.
> ID kontrolerów używają tych samych zasad nazewnictwa, co ID akcji. Nazwy klas kontrolerów uzyskiwane są z ID kontrolerów przez usunięcie myślników z ID, zamianę pierwszej litery na 
wielką w każdym słowie oraz dodanie przyrostka `Controller`.
Dla przykładu ID kontrolera `post-comment` odpowiada nazwie klasy kontrolera `PostCommentController`.

Podsumowanie <span id="summary"></span>
-------

W tej sekcji zobaczyłeś część kontrolerów oraz widoków wzorca architektonicznego MVC.
Utworzyłeś akcję jako część kontrolera do obsługi specyficznego żądania. Utworzyłeś też widok, który prezentuje zawartość odpowiedzi.
W tym prostym przykładzie nie został zaangażowany żaden model, ponieważ dane jakimi się posługiwaliśmy były zawarte w parametrze `message`.

Nauczyłeś się też czegoś o routingu w Yii, który działa jak most pomiędzy żądaniami użytkownika a akcjami kontrolerów. 

W następnej sekcji nauczysz się jak utworzyć model oraz dodać nową stronę zawierającą formularz HTML.

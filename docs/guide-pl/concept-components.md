Komponenty
==========

Komponenty są głównym budulcem aplikacji Yii. Komponenty to instancje klasy [[yii\base\Component|Component]] lub jej potomnych. 
Trzy główne funkcjonalności, które zapewniają komponenty innym klasom to:

* [Właściwości](concept-properties.md)
* [Eventy (zdarzenia)](concept-events.md)
* [Behaviory (zachowania)](concept-behaviors.md)
 
Wszystkie razem i każda z tych funkcjonalności osobno zapewnia klasom Yii o wiele większą elastyczność i łatwość użycia. Dla przykładu,
dołączony [[yii\jui\DatePicker|widżet wybierania daty]], komponent interfejsu użytkownika, może być użyty w [widoku](structure-views.md), 
aby wygenerować interaktywny kalendarz:

```php
use yii\jui\DatePicker;

echo DatePicker::widget([
    'language' => 'pl',
    'name'  => 'country',
    'clientOptions' => [
        'dateFormat' => 'yy-mm-dd',
    ],
]);
```

Właściwości widżetu są w łatwy sposób konfigurowalne ponieważ jego klasa rozszerza [[yii\base\Component|Component]].

Komponenty zapewniają duże możliwości, ale przez to są też bardziej zasobożerne od standardowych obiektów, ponieważ wymagają dodatkowej pamięci i czasu CPU dla wsparcia 
[eventów](concept-events.md) i [behaviorów](concept-behaviors.md) w szczególności.
Jeśli komponent nie wymaga tych dwóch funkcjonalności, należy rozważyć rozszerzenie jego klasy z [[yii\base\BaseObject|BaseObject]] zamiast [[yii\base\Component|Component]]. 
Dzięki temu komponent będzie tak samo wydajny jak standardowy obiekt PHP, ale z dodatkowym wsparciem [właściwości](concept-properties.md).

Rozszerzając klasę [[yii\base\Component|Component]] lub [[yii\base\BaseObject|BaseObject]], zalecane jest aby przestrzegać następującej konwencji:

- Przeciążając konstruktor, dodaj parametr `$config` jako *ostatni* na liście jego argumentów i przekaż go do konstruktora rodzica.
- Zawsze wywoływuj konstruktor rodzica *na końcu* przeciążanego konstruktora.
- Przeciążając metodę [[yii\base\BaseObject::init()|init()]], upewnij się, że wywołujesz metodę `init()` rodzica *na początku* własnej implementacji metody `init()`.

Przykład:

```php
<?php

namespace yii\components\MyClass;

use yii\base\BaseObject;

class MyClass extends BaseObject
{
    public $prop1;
    public $prop2;

    public function __construct($param1, $param2, $config = [])
    {
        // ... inicjalizacja przed zaaplikowaniem konfiguracji

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... inicjalizacja po zaaplikowaniu konfiguracji
    }
}
```

Postępowanie zgodnie z tymi zasadami zapewni [konfigurowalność](concept-configurations.md) Twojego komponentu, kiedy już zostanie utworzony. Dla przykładu:

```php
$component = new MyClass(1, 2, ['prop1' => 3, 'prop2' => 4]);
// lub alternatywnie
$component = \Yii::createObject([
    'class' => MyClass::class,
    'prop1' => 3,
    'prop2' => 4,
], [1, 2]);
```

> Info: Wersja z wywołaniem [[Yii::createObject()]] wygląda na bardziej skomplikowaną, ale jest o wiele wydajniejsza, ponieważ jej implementację zapewnia 
> [kontener wstrzykiwania zależności](concept-di-container.md).
  

Klasa [[yii\base\BaseObject|BaseObject]] wymusza następujący cykl życia obiektu:

1. Preinicjalizacja w konstruktorze. W tym miejscu można ustawić domyślne wartości właściwości.
2. Konfiguracja obiektu poprzez `$config`. Konfiguracja może nadpisać domyślne wartości ustawione w konstruktorze.
3. Postinicjalizacja w metodzie [[yii\base\BaseObject::init()|init()]]. Metoda może być przeciążona w celu normalizacji i sanityzacji właściwości.
4. Wywołanie metody obiektu.

Pierwsze trzy kroki są w całości wykonywane w konstruktorze obiektu, co oznacza, że uzyskana instancja klasy jest już poprawnie zainicjowana i stabilna.

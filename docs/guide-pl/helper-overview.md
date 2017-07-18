Klasy pomocnicze
================

> Note: Ta sekcja jest w trakcie tworzenia.

Yii jest wyposażone w wiele klas upraszczających pisanie często wykorzystywanych zadań w kodzie, takich jak manipulowanie ciągami znaków bądź tablicami, generowanie kodu HTML, itp.
Te pomocnicze klasy znajdują się w przestrzeni nazw `yii\helpers` i wszystkie są klasami statycznymi (czyli zawierają wyłącznie statyczne właściwości i nie powinny być tworzone ich 
instancje).

Aby skorzystać z klasy pomocnicznej, należy bezpośrednio wywołać jedną z jej statycznych metod, jak w przykładzie poniżej:

```php
use yii\helpers\Html;

echo Html::encode('Test > test');
```

> Note: W celu zapewnienia możliwości [dostosowania klas pomocniczych do własnych potrzeb](#customizing-helper-classes), Yii rozdziela każdą z ich wbudowanych wersji 
> na dwie klasy: podstawę (np. `BaseArrayHelper`) i klasę właściwą (np. `ArrayHelper`). Kiedy chcesz użyć klasy pomocnicznej, powinieneś korzystać wyłącznie z jej właściwej wersji 
> i nigdy nie używać bezpośrednio podstawy.


Wbudowane klasy pomocnicze
--------------------------

Poniższe wbudowane klasy pomocnicze dostępne są w każdym wydaniu Yii:

- [ArrayHelper](helper-array.md)
- Console
- FileHelper
- FormatConverter
- [Html](helper-html.md)
- HtmlPurifier
- Imagine (poprzez rozszerzenie yii2-imagine)
- Inflector
- Json
- Markdown
- StringHelper
- [Url](helper-url.md)
- VarDumper


Dostosowywanie klas pomocniczych do własnych potrzeb <span id="customizing-helper-classes"></span>
----------------------------------------------------

Aby zmodyfikować wbudowaną klasę pomocniczną (np. [[yii\helpers\ArrayHelper|ArrayHelper]]), należy stworzyć nową klasę rozszerzającą odpowiednią podstawę 
(np. [[yii\helpers\BaseArrayHelper|BaseArrayHelper]]) i nazwać ją identycznie jak jej wersja właściwa (np. [[yii\helpers\ArrayHelper|ArrayHelper]]), łącznie z zachowaniem jej 
przestrzeni nazw. Ta klasa może następnie zostać użyta do zastąpienia oryginalnej implementacji we frameworku.

Poniższy przykład ilustruje w jaki sposób zmodyfikować metodę [[yii\helpers\ArrayHelper::merge()|merge()]] klasy [[yii\helpers\ArrayHelper|ArrayHelper]]:

```php
<?php

namespace yii\helpers;

class ArrayHelper extends BaseArrayHelper
{
    public static function merge($a, $b)
    {
        // zmodyfikowana wersja metody
    }
}
```

Klasę należy zapisać w pliku o nazwie `ArrayHelper.php`, który może znajdować się w dowolnym odpowiednim folderze, np. `@app/components`.

Następnie dopisujemy poniższą linijkę kodu w [skrypcie wejściowym](structure-entry-scripts.md) aplikacji po fragmencie dołączającym plik `yii.php`, 
dzięki czemu [autoloader klas Yii](concept-autoloading.md) załaduje zmodyfikowaną wersję klasy pomocniczej zamiast oryginalnej:

```php
Yii::$classMap['yii\helpers\ArrayHelper'] = '@app/components/ArrayHelper.php';
```

Należy pamiętać o tym, że modyfikowanie klasy pomocniczej jest użyteczne tylko w przypadku, gdy chcemy zmienić domyślny sposób działania jej metody. 
W przypadku dodawania do aplikacji dodatkowych funkcjonalności, lepszym pomysłem jest stworzenie całkowicie nowej, osobnej klasy pomocniczej.
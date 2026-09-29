Praca ze skryptami
===========================

> Note: Ta sekcja nie została jeszcze ukończona.

### Rejestrowanie skryptów

Dzięki obiektowi [[yii\web\View|View]] możesz rejestrować skrypty w aplikacji. Przeznaczone są do tego dwie dedykowane metody: 
[[yii\web\View::registerJs()|registerJs()]] dla skryptów wbudowanych oraz [[yii\web\View::registerJsFile()|registerJsFile()]] dla skryptów zewnętrznych.
Skrypty wbudowane są przydatne przy konfiguracji oraz dynamicznym generowaniu kodu.
Możesz dodać je w następujący sposób:

```php
$this->registerJs("var options = " . json_encode($options) . ";", View::POS_END, 'my-options');
```

Pierwszy argument przekazywany do metody `registerJs` to kod JavaScript, który chcemy umieścić na stronie. Jako drugi argument wskazujemy miejsce, 
w którym skrypt ma zostać umieszczony na stronie. Możliwe wartości to:

- [[yii\web\View::POS_HEAD|POS_HEAD]] dla sekcji `head`.
- [[yii\web\View::POS_BEGIN|POS_BEGIN]] zaraz po otwarciu tagu `<body>`.
- [[yii\web\View::POS_END|POS_END]] zaraz przed zamknięciem tagu `</body>`.
- [[yii\web\View::POS_READY|POS_READY]] do wywołania kodu z użyciem zdarzenia `ready` na dokumencie. Ta opcja zarejestruje automatycznie [[yii\web\JqueryAsset|jQuery]]
- [[yii\web\View::POS_LOAD|POS_LOAD]] do wywołania kodu z użyciem zdarzenia `load` na dokumencie. Ta opcja zarejestruje automatycznie [[yii\web\JqueryAsset|jQuery]]

Ostatnim argumentem jest unikalne ID skryptu, które jest używane do zidentyfikowania bloku kodu i zastąpienia go, jeśli taki został już zarejestrowany. 
Jeśli ten argument nie zostanie podany, kod JavaScript zostanie użyty jako ID.

Skrypt zewnętrzny może zostać dodany następująco:

```php
$this->registerJsFile('https://example.com/js/main.js', ['depends' => [\yii\web\JqueryAsset::class]]);
```

Argumenty dla metod [[yii\web\View::registerCssFile()|registerCssFile()]] są podobne do [[yii\web\View::registerJsFile()|registerJsFile()]].
W powyższym przykładzie, zarejestrowaliśmy plik `main.js` z zależnością od `JqueryAsset`. Oznacza to, że plik `main.js` zostanie dodany PO pliku `jquery.js`. 
Bez określenia tej zależności, względny porządek pomiędzy `main.js` a `jquery.js` nie zostałby zachowany.

Tak jak i w przypadku [[yii\web\View::registerCssFile()|registerCssFile()]], mocno rekomendujemy, abyś użył [assetów](structure-assets.md) do zarejestrowania zewnętrznych plików JS 
zamiast używania [[yii\web\View::registerJsFile()|registerJsFile()]].


### Rejestracja assetów

Jak zostało wspomniane wcześniej, korzystniejsze jest stosowanie assetów, zamiast kodu CSS i JS bezpośrednio (po informacje na ten temat sięgnij do sekcji 
[menedżera assetów](structure-assets.md)). 
Korzystanie z już zdefiniowanych pakietów jest bardzo proste:

```php
\frontend\assets\AppAsset::register($this);
```


### Rejestrowanie kodu CSS

Możesz zarejestrować kod CSS przy użyciu metody [[yii\web\View::registerCss()|registerCss()]] lub [[yii\web\View::registerCssFile()|registerCssFile()]].
Pierwsza z nich rejestruje blok kodu CSS, natomiast druga zewnętrzny plik `.css`. Dla przykładu:

```php
$this->registerCss("body { background: #f00; }");
```

Powyższy kod doda kod CSS do sekcji `head` strony:

```html
<style>
body { background: #f00; }
</style>
```

Jeśli chcesz określić dodatkowe właściwości dla tagu `style`, przekaż tablicę `nazwa => wartość` jako drugi argument.
Jeśli chcesz się upewnić, że jest tylko jeden tag `style`, użyj trzeciego argumentu, tak jak zostało to opisane dla meta tagów.

```php
$this->registerCssFile("https://example.com/css/themes/black-and-white.css", [
    'depends' => [BootstrapAsset::class],
    'media' => 'print',
], 'css-print-theme');
```

Kod powyżej doda link w sekcji `head` strony do pliku CSS.

* Pierwszy argument określa, który plik ma zostać zarejestrowany,
* Drugi argument określa atrybuty tagu `<link>`. Opcja `depends` jest obsługiwana w specjalny sposób, od niej zależy położenie pliku CSS.
  W tym przypadku, plik link do pliku CSS zostanie umieszony ZA plikami CSS w [[yii\bootstrap\BootstrapAsset|BootstrapAsset]],
* Ostatni argument określa ID identyfikujące ten plik CSS. W przypadku jego braku, zostanie użyty do tego celu adres URL pliku CSS.

Jest mocno wskazane używanie [assetów](structure-assets.md) do rejestrowania zewnętrznych plików CSS. Użycie ich pozwala Ci na łączenie i kompresowanie 
wielu plików CSS, które jest wręcz niezbędne na stronach internetowych o dużym natężeniu ruchu.

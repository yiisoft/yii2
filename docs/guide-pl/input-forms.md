Tworzenie formularzy
====================

Formularze oparte na ActiveRecord: ActiveForm
---------------------------------------------
Podstawowym sposobem korzystania z formularzy w Yii jest użycie [[yii\widgets\ActiveForm|ActiveForm]]. Ten sposób powinien być używany, jeśli formularz jest bazowany na modelu.
Dodatkowo, klasa [[yii\helpers\Html|Html]] zawiera sporo użytecznych metod, które zazwyczaj używane są do dodawania przycisków i tekstów pomocniczych do każdego formularza.

Formularz, który jest wyświetlany po stronie klienta, w większości przypadków, posiada odpowiedni [model](structure-models.md), który jest używany do walidacji danych wejściowych po 
stronie serwera (sprawdź sekcję [Walidacja danych wejściowych](input-validation.md) aby uzyskać więcej szczegółów).  
Podczas tworzenia formularza na podstawie modelu, pierwszym krokiem jest zdefiniowanie samego modelu. 
Model może być bazowany na klasie [Active Record](db-active-record.md), reprezentując dane z bazy danych, lub może być też bazowany na klasie generycznej [[yii\base\Model|Model]], 
aby przechwytywać dowolne dane wejściowe, np. formularz logowania.

> Tip: Jeśli pola formularza są różne od kolumn tabeli w bazie danych lub też występuje tu formatowanie i logika specyficzna tylko dla tego formularza, 
> zaleca się stworzenie oddzielnego modelu rozszerzającego [[yii\base\Model]].

W poniższym przykładzie pokażemy, jak model generyczny może być użyty do stworzenia formularza logowania:

```php
<?php

class LoginForm extends \yii\base\Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            // zasady walidacji
        ];
    }
}
```

W kontrolerze przekażemy instancję tego modelu do widoku, gdzie widżet [[yii\widgets\ActiveForm|ActiveForm]] zostanie użyty do wyświetlenia formularza:

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'login-form',
    'options' => ['class' => 'form-horizontal'],
]) ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>

    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('Login', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
<?php ActiveForm::end() ?>
```

### Otaczanie kodu przez `begin()` i `end()` <span id="wrapping-with-begin-and-end"></span>
W powyższym kodzie, [[yii\widgets\ActiveForm::begin()|begin()]] nie tylko tworzy instancję formularza, ale zaznacza też jego początek.
Cała zawartość położona pomiędzy [[yii\widgets\ActiveForm::begin()|begin()]] i [[yii\widgets\ActiveForm::end()|end()]] zostanie otoczona tagiem HTML'owym `<form>`.
Jak w przypadku każdego widżetu, możesz określić kilka opcji z jakimi widżet powinien być skonfigurowany przez przekazanie tablicy do metody `begin`.
W tym przypadku dodatkowa klasa CSS i identyfikator ID zostały przekazane do otwierającego tagu `<form>`.
Aby zobaczyć wszystkie dostępne opcje, zajrzyj do dokumentacji API [[yii\widgets\ActiveForm|ActiveForm]].

Do utworzenia formularza, wraz z elementami etykiet oraz wszelkimi walidacjami JavaScript, wywoływana jest metoda [[yii\widgets\ActiveForm::field()|field()]], która zwraca instancję 
obiektu [[yii\widgets\ActiveField|ActiveField]].
Kiedy rezultat tej metody jest bezpośrednio wyświetlany, tworzone jest regularne pole tekstowe.
Aby dostosować pola, możesz używać dodatkowych metod łączonych [[yii\widgets\ActiveField|ActiveField]]:

```php
// pole hasła
<?= $form->field($model, 'password')->passwordInput() ?>
// dodanie podpowiedzi oraz zmiana etykiety
<?= $form->field($model, 'username')->textInput()->hint('Please enter your name')->label('Name') ?>
// utworzenie pola email w formacie HTML5
<?= $form->field($model, 'email')->input('email') ?>
```

Powyższy kod utworzy tagi `<label>`, `<input>` oraz wszystkie inne, według pól formularza zdefiniowanych w [[yii\widgets\ActiveField::$template|template]].
Nazwa pola określana jest automatycznie z modelu [[yii\base\Model::formName()|formName()]] i nazwy atrybutu.
Dla przykładu, nazwą pola dla atrybutu `username` w powyższym przykładzie będzie `LoginForm[username]`. 
Ta zasada nazewnictwa spowoduje, że tablica wszystkich atrybutów z formularza logowania będzie dostępna w zmiennej `$_POST['LoginForm']` po stronie serwera.

Określanie atrybutów modelu może być wykonane w bardziej wyrafinowany sposób. 
Dla przykładu, kiedy atrybut będzie potrzebował pobierać tablicę wartości, podczas przesyłania wielu plików lub wybrania wielu pozycji, możesz określić go jako tablicę dodając `[]` do 
nazwy atrybutu:

```php
// pozwól na przesłanie wielu plików
echo $form->field($model, 'uploadFile[]')->fileInput(['multiple'=>'multiple']);

// pozwól na zaznaczenie wielu pozycji
echo $form->field($model, 'items[]')->checkboxList(['a' => 'Item A', 'b' => 'Item B', 'c' => 'Item C']);
```

Bądź ostrożny podczas nazywania elementów formularza takich jak przyciski wysyłania. 
Odnosząc się do [dokumentacji jQuery](https://api.jquery.com/submit), istnieje kilka zarezerwowanych nazw, które mogą powodować konflikty.

> Formularz i jego elementy podrzędne powinny nie używać nazw pól lub nazw identyfikatorów które tworzą konflikt z właściwościami formularza,
> takich jak `submit`, `length` lub `method`. Konflikty nazw mogą powodować mylące błędy.
> Kompletna lista zasad oraz walidator znaczników dla tych problemów znajduje się na stronie [DOMLint](http://kangax.github.io/domlint). 

Dodatkowe tagi HTML mogą zostać dodane do formularza używając czystego HTML'a lub używając metody z klasy pomocniczej - [[yii\helpers\Html|Html]],
tak jak było to zrobione w przykładzie wyżej z [[yii\helpers\Html::submitButton()|submitButton()]].

> Tip: Jeśli używasz Twitter Bootstrap CSS w Twojej aplikacji, możesz użyć [[yii\bootstrap\ActiveForm]] zamiast [[yii\widgets\ActiveForm]]. 
> Rozszerza on [[yii\widgets\ActiveForm|ActiveForm]] i podczas generowania pól formularza używa stylu specyficznego dla Bootstrap.


> Tip: Jeśli chcesz oznaczyć wymagane pola gwiazdką, możesz uzyć poniższego kodu CSS:
>
> ```css
> div.required label:after {
>     content: " *";
>     color: red;
> }
> ```

Tworzenie list <span id="creating-activeform-lists"></span>
--------------

Wyróżniamy trzy typy list:
* Listy rozwijane 
* Listy opcji typu radio
* Listy opcji typu checkbox

Aby stworzyć listę, musisz najpierw przygotować jej elementy. Można to zrobić ręcznie:

```php
$items = [
    1 => 'item 1', 
    2 => 'item 2'
]
```

lub też pobierając elementy z bazy danych:

```php
$items = Category::find()
        ->select(['label'])
        ->indexBy('id')
        ->column();
```

Elementy `$items` muszą być następnie przetworzone przez odpowiednie widżety list.
Wartość pola formularza (i aktualnie aktywny element) będzie automatycznie ustawiony przez aktualną wartość atrybutu `$model`. 

#### Tworzenie listy rozwijanej <span id="creating-activeform-dropdownlist"></span>

Możemy użyć metody klasy ActiveForm [[yii\widgets\ActiveForm::dropDownList()|dropDownList()]] do utworzenia rozwijanej listy:

```php
/* @var $form yii\widgets\ActiveForm */

echo $form->field($model, 'category')->dropdownList([
        1 => 'item 1', 
        2 => 'item 2'
    ],
    ['prompt'=>'Wybierz kategorię']
);
```

#### Tworzenie radio listy <span id="creating-activeform-radioList"></span>

Do stworzenia takiej listy możemy użyć metody ActiveField [[\yii\widgets\ActiveField::radioList()]]:

```php
/* @var $form yii\widgets\ActiveForm */

echo $form->field($model, 'category')->radioList([
    1 => 'radio 1', 
    2 => 'radio 2'
]);
```

#### Tworzenie checkbox listy <span id="creating-activeform-checkboxList"></span>

Do stworzenia takiej listy możemy użyć metody ActiveField [[\yii\widgets\ActiveField::checkboxList()]]:

```php
/* @var $form yii\widgets\ActiveForm */

echo $form->field($model, 'category')->checkboxList([
    1 => 'checkbox 1', 
    2 => 'checkbox 2'
]);
```

Praca z Pjaxem <span id="working-with-pjax"></span>
-----------------------

Widżet [[yii\widgets\Pjax|Pjax]] pozwala na aktualizację określonej sekcji strony, 
zamiast przeładowywania jej całkowicie. Możesz użyć go do odświeżenia formularza 
i podmienić jego zawartość po wysłaniu danych.

Możesz skonfigurować [[yii\widgets\Pjax::$formSelector|$formSelector]], aby wskazać, 
które formularze powinny wyzwalać użycie pjaxa. Jeśli nie zostanie to ustawione inaczej, 
wszystkie formularze z atrybutem `data-pjax` objęte widżetem Pjax będą wyzwalały jego użycie.

```php
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

Pjax::begin([
    // opcje Pjaxa
]);
    $form = ActiveForm::begin([
        'options' => ['data' => ['pjax' => true]],
        // więcej opcji ActiveForm
    ]);

        // zawartość ActiveForm

    ActiveForm::end();
Pjax::end();
```
> Tip: Należy być ostrożnym z użyciem linków wewnątrz widżetu [[yii\widgets\Pjax|Pjax]], ponieważ
> ich cel również zostanie wyrenderowany wewnątrz widżetu. Aby temu zapobiec, należy użyć atrybutu HTML `data-pjax="0"`.

#### Wartości w przyciskach submit i przesyłanie plików

Znane są problemy z użyciem `jQuery.serializeArray()` podczas obsługi 
[[https://github.com/jquery/jquery/issues/2321|plików]] i 
[[https://github.com/jquery/jquery/issues/2321|wartości przycisku submit]], które nie 
będą jednak rozwiązane i zamiast tego zostały porzucone na rzecz klasy `FormData` 
wprowadzonej w HTML5.

Oznacza to, że oficjalne wsparcie dla plików i wartości przycisku submit używanych w połączeniu 
z ajaxem lub widżetem [[yii\widgets\Pjax|Pjax]] zależy od 
[[https://developer.mozilla.org/en-US/docs/Web/API/FormData#Browser_compatibility|wsparcia przeglądarki]]
dla klasy `FormData`.

Dalsza lektura <span id="further-reading"></span>
---------------

Następna sekcja [Walidacja danych wejściowych](input-validation.md) dotyczy walidacji przesłanych przed formularz danych po stronie serwera, przy użyciu ajax oraz walidacji po stronie 
klienta.

Aby przeczytać o bardziej złożonych użyciach formularzy możesz zajrzeć do poniższych sekcji:

- [Odczytywanie tablicowych danych wejściowych](input-tabular-input.md) - do pobierania danych dla wielu modeli tego samego rodzaju.
- [Pobieranie danych dla wielu modeli](input-multiple-models.md) - do obsługi wielu różnych modeli w tym samym formularzu.
- [Wysyłanie plików](input-file-upload.md) - jak używać formularzy do przesyłania plików.

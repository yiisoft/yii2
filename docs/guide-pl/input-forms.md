Tworzenie formularzy
==============

Podstawowym sposobem korzystania z formularzy w Yii jest użycie [[yii\widgets\ActiveForm]]. Ten sposób powinien być używany jeśli formularz jest bazowany na modelu.
Dodatkowo, [[yii\helpers\Html]] zawiera sporo użytecznych metod, które zazwyczaj używane są do dodawania przycisków i tekstów pomocniczych do każdego formularza.

Formularz, który jest wyświetlany po stronie klienta, w większości przypadków, posiada odpowiedni [model](structure-models.md), który jest używany do walidacji danych wejściowych po stronie serwera.
(Sprawdź sekcję [Walidacja danych wejściowych](input-validation.md) aby uzyskać więcej szczegółów).
Podczas tworzenia formularza na podstawie modelu, pierwszym krokiem jest zdefiniowanie samego modelu. 
Model może być bazowany na klasie [Active Record](db-active-record.md), reprezentując dane z bazy danych, lub może być też bazowany na klasie generycznej Model ([[yii\base\Model]]) aby przechwytywać dowolne dane wejściowe, np. formularz logowania.
W poniższym przykładzie pokażemy, jak model generyczny model może być użyty do formularza logowania:

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

W powyższym kodzie, [[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] nie tylko tworzy instancję formularza, ale zaznacza też jego początek.
Cała zawartość położona pomiędzy [[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] i [[yii\widgets\ActiveForm::end()|ActiveForm::end()]] zostanie otoczona tagiem HTML'owym `<form>`.
Jak w każdym widżecie, możesz sprecyzować kilka opcji jak widżet powinien być skonfigurowany przez przekazanie tablicy do metody `begin`.
W tym przypadku dodatkowa klasa CSS i identyfikator ID zostały przekazane do otwierającego tagu `<form>`.
Aby zobaczyć wszystkie dostępne opcje, zajrzyj do dokumentacji API [[yii\widgets\ActiveForm]].

Do utworzenia formularza, wraz z elementami etykiet oraz wszelkimi walidacjami JavaScript, wywoływana jest metoda [[yii\widgets\ActiveForm::field()|ActiveForm::field()]], która zwraca instancję obiektu [[yii\widgets\ActiveField]].
Kiedy rezultat tej metody jest bezpośrednio wyświetlany, będzie on regularnym polem tekstowym.
Aby dostosować pola, możesz używać dodatkowych metod łączonych [[yii\widgets\ActiveField|ActiveField]]:

```php
// pole hasła
<?= $form->field($model, 'password')->passwordInput() ?>
// dodanie podpowiedzi oraz zmiana etykiety
<?= $form->field($model, 'username')->textInput()->hint('Please enter your name')->label('Name') ?>
// utworzenie pola email w formacie HTML5
<?= $form->field($model, 'email')->input('email') ?>
```

Powyższy kod utworzy tagi `<label>`, `<input>`, oraz wszystkie inne, według pól formularza zdefiniowanych w [[yii\widgets\ActiveField::$template|template]].
Nazwa pola określana jest automatycznie z modelu [[yii\base\Model::formName()|form name]] i nazwy atrybutu.
Dla przykładu, nazwą pola dla atrybutu `username` w powyższym przykładzie będzie `LoginForm[username]`. 
Ta zasada nazewnictwa spowoduje, że tablica wszystkich atrybutów z formularza logowania będzie dostępna w zmiennej `$_POST['LoginForm']` po stronie serwera.

Określanie atrybutów modelu może być wykonane w bardziej wyrafinowany sposób. 
Dla przykładu, kiedy atrybut będzie potrzebował pobierać tablicę wartości, podczas przesyłania wielu plików lub wybrania wielu pozycji, możesz określić go jako tablicę dodając `[]` do nazwy atrybutu:

```php
// pozwól na przesłanie wielu plików
echo $form->field($model, 'uploadFile[]')->fileInput(['multiple'=>'multiple']);

// pozwól na zaznaczenie wielu pozycji
echo $form->field($model, 'items[]')->checkboxList(['a' => 'Item A', 'b' => 'Item B', 'c' => 'Item C']);
```

Bądź ostrożny podczas nazywania elementów formularza takich jak przyciski wysyłania. 
Odnosząc się do [dokumentacji jQuery](https://api.jquery.com/submit/), istnieje kilka zarezerwowanych nazw, które mogą powodować konflikty.

> Formularz i jego elementy podrzędne powinny nie używać nazw pól lub nazw identyfikatorów które tworzą konflikt z właściwościami formularza,
> takich jak `submit`, `length` lub `method`. Konflikty nazw mogą powodować mylące błędy.
> Kompletna lista zasad oraz walidator znaczników dla tych problemów znajduje się na stronie [DOMLint](http://kangax.github.io/domlint/). 

Dodatkowe tagi HTML mogą zostać dodane do formularza używając czystego HTML'a lub używając metody z klasy pomocniczej - [[yii\helpers\Html|Html]],
tak jak było to zrobione w przykładzie wyżej z [[yii\helpers\Html::submitButton()|Html::submitButton()]].

> Tip: Jeśli używasz Twitter Bootstrap CSS w Twojej aplikacji, możesz użyć
> [[yii\bootstrap\ActiveForm]] zamiast [[yii\widgets\ActiveForm]]. 
> Rozszerza on [[yii\widgets\ActiveForm]] i używa specyficzne dla Bootstrap style, podczas generowania pól formularza.


> Tip: Jeśli chcesz oznaczyć wymagane pola gwiazdką, możesz uzyć poniższego kodu CSS:
>
> ```css
> div.required label:after {
>     content: " *";
>     color: red;
> }
> ```

Tworzenie listy rozwijanej <span id="creating-activeform-dropdownlist"></span>
---------------------

Możemy użyć metody klasy ActiveForm [dropDownList()](http://www.yiiframework.com/doc-2.0/yii-widgets-activefield.html#dropDownList()-detail)
do utworzenia rozwijanej listy:

```php
use app\models\ProductCategory;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\Product */

echo $form->field($model, 'product_category')->dropdownList(
    ProductCategory::find()->select(['category_name', 'id'])->indexBy('id')->column(),
    ['prompt'=>'Select Category']
);
```

Wartość z Twojego modelu będzie automatycznie wybrana po wyświetleniu formularza.

Dalsza lektura <span id="further-reading"></span>
---------------

Następna sekcja [Walidacja danych wejściowych](input-validation.md) dotyczy walidacji przesłanych przed formularz danych po stronie serwera, przy użyciu ajax oraz walidacji po stronie kklienta.

Aby przeczytać o bardziej złożonych użyciach formularzy możesz zajrzeć do poniższych sekcji:

- [Odczytywanie tablicowych danych wejściowych](input-tabular-input.md - do pobierania danych dla wielu modeli tego samego rodzaju.
- [Pobieranie danych dla wielu modeli](input-multiple-models.md) - do obsługi wielu różnych modeli w tym samym formularzu.
- [Wysyłanie plików](input-file-upload.md) - jak używać formularzy do przesyłania plików.

Odczytywanie tablicowych danych wejściowych
========================

Czasami zachodzi potrzeba obsłużenia wielu modeli tego samego rodzaju w jednym formularzu. Dla przykładu - ustawienia, gdzie każde z nich jest przechowywane jako para klucz-wartość 
i każde z nich jest reprezentowane przez model `Setting` [active record](db-active-record.md). 
Dla kontrastu obsługa wielu modeli różnych rodzajów pokazana jest w sekcji [Pobieranie danych dla wielu modeli](input-multiple-models.md).


Poniższe przykłady pokazują jak zaimplementować tablicowe dane wejściowe w Yii.

Występują trzy różne sytuacje, które należy obsłużyć inaczej:
- Aktualizacja określonej liczby rekordów z bazy danych
- Dynamiczne tworzenie nowych rekordów
- Aktualizacja, tworzenie oraz usuwanie rekordów na jednej stronie


W porównaniu do formularza z pojedyńczym modelem, wytłumaczonym poprzednio, pracujemy teraz na tablicy modeli.
Tablica przekazywana jest do widoku, aby wyświetlić pola wejściowe dla każdego modelu w stylu tabeli, 
użyjemy do tego metod pomocniczych z [[yii\base\Model|Model]], które pozwalają na wczytywanie oraz walidację wielu modeli na raz:

- [[yii\base\Model::loadMultiple()|loadMultiple()]] wczytuje dane z tablicy POST do tablicy modeli. 
- [[yii\base\Model::validateMultiple()|validateMultiple()]] waliduje tablicę modeli.

### Aktualizacja określonej liczby rekordów

Zacznijmy od akcji kontrolera:

```php
<?php

namespace app\controllers;

use Yii;
use yii\base\Model;
use yii\web\Controller;
use app\models\Setting;

class SettingsController extends Controller
{
    // ...

    public function actionUpdate()
    {
        $settings = Setting::find()->indexBy('id')->all();

        if (Model::loadMultiple($settings, Yii::$app->request->post()) && Model::validateMultiple($settings)) {
            foreach ($settings as $setting) {
                $setting->save(false);
            }
            return $this->redirect('index');
        }

        return $this->render('update', ['settings' => $settings]);
    }
}
```

W powyższym kodzie używamy [[yii\db\ActiveQuery::indexBy()|indexBy()]] podczas pobierania danych z bazy danych aby zasilić tablicę modelami zaindeksowanymi przez główny klucz.
Będzie to później użyte do zidentyfikowania pól formularza. [[yii\base\Model::loadMultiple()|loadMultiple()]] uzupełnia modele danymi formularza przesłanymi metodą POST 
a następnie metoda [[yii\base\Model::validateMultiple()|validateMultiple()]] waliduje te modele. 
Po walidacji przekazujemy parametr `false` do metody [[yii\db\ActiveRecord::save()|save()]], aby nie uruchamiać walidacji ponownie.

Przejdziemy teraz do formularza w widoku `update`:

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin();

foreach ($settings as $index => $setting) {
    echo $form->field($setting, "[$index]value")->label($setting->name);
}

ActiveForm::end();
```

Dla każdego z ustawień renderujemy nazwę oraz pole wejściowe z wartością. Bardzo ważne jest dodanie odpowiedniego indeksu do nazwy pola, ponieważ dzięki temu 
metoda [[yii\base\Model::loadMultiple()|loadMultiple()]] określa który model powinna uzupełnić przekazanymi wartościami.

### Dynamiczne tworzenie nowych rekordów

Tworzenie nowych rekordów jest podobne do ich aktualizacji, poza częścią, w której instancjujemy modele:

```php
public function actionCreate()
{
    $count = count(Yii::$app->request->post('Setting', []));
    $settings = [new Setting()];
    for($i = 1; $i < $count; $i++) {
        $settings[] = new Setting();
    }

    // ...
}
```

Tworzymy tutaj początkową tablicę `$settings` zawierającą domyślnie jeden model, dlatego zawsze co najmniej jedno pole będzie widoczne w widoku.
Dodatkowo dodajemy więcej modeli dla każdej linii pól wejściowych jakie otrzymaliśmy.

W widoku możemy użyć kodu JavaScript do dynamicznego dodawania nowych linii pól wejściowych.

### Aktualizacja, tworzenie oraz usuwanie rekordów na jednej stronie

> Note: Ta sekcja nie została jeszcze skończona.

TBD

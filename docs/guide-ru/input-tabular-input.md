Табличный ввод
========================

Иногда возникает необходимость обработки нескольких моделей одного вида в одной форме. Например, несколько параметров, каждый из которых сохраняется как пара имя-значение и представляется моделью `Setting` [active record](db-active-record.md).
Такой тип форм часто называют "табличным вводом".
Обработка данных нескольких моделей разных видов в одной форме описана в разделе [Работа с несколькими моделями](input-multiple-models.md).

Дальше будет рассмотрен вариант реализации табличного ввода при помощи Yii.

Выделим три сценария, которые потребуют немного разных подходов:
- Изменение фиксированного набора записей из базы данных;
- Создание произвольного набора записей;
- Изменение, создание и удаление записей на одной странице.

В отличие от форм с одной моделью, рассмотренных ранее, теперь будем иметь дело с массивом моделей. Этот массив передается в представление и для каждой модели отображаются поля ввода в табличном виде. Для загрузки и валидации нескольких моделей сразу будем использовать вспомогательные методы класса [[yii\base\Model]]:

- [[yii\base\Model::loadMultiple()|Model::loadMultiple()]] загружает данные post в массив моделей;
- [[yii\base\Model::validateMultiple()|Model::validateMultiple()]] валидирует массив моделей.

### Изменение фиксированного набора записей

Начнем с действия контроллера:

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

В коде выше, для получения из базы данных массива моделей, индексированного по главному ключу, использован метод [[yii\db\ActiveQuery::indexBy()|indexBy()]]. В дальнейшем будем использовать это для идентификации полей формы. Метод [[yii\base\Model::loadMultiple()|Model::loadMultiple()]] загружает данные запроса POST в массив моделей, а метод [[yii\base\Model::validateMultiple()|Model::validateMultiple()]] проводит валидацию всех моделей. Так, как модели уже прошли валидацию, мы передаем методу [[yii\db\ActiveRecord::save()|save()]] параметр `false` для отключения повторной валидации.

Теперь займемся формой в представлении `update`:

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

Для каждого элемента массива `$settings` генерируется имя и поле ввода значения. Важно указать правильный индекс в имени поля ввода значения, так как [[yii\base\Model::loadMultiple()|Model::loadMultiple()]] определяет модель по этому индексу.


### Создание произвольного набора записей

Процесс создания новых записей похож на их изменение, за исключением части, где создаются новые модели:

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

Сначала создается массив `$settings`, содержащий один экземпляр модели, так что, по умолчанию в представлении всегда будет отображено хотя бы одно поле. Дополнительно, добавляются модели для каждой полученной строки ввода.

В представлении возможно использовать javascript для добавления новых полей динамически.


### Изменение, создание и удаление записей на одной странице

> Note: Раздел находится в разработке

TBD

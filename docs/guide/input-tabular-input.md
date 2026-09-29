Collecting Tabular Input
========================

Sometimes you need to handle multiple models of the same kind in a single form. For example, multiple settings, where
each setting is stored as a name-value pair and is represented by a `Setting` [active record](db-active-record.md) model.
This kind of form is also often referred to as "tabular input".
In contrast to this, handling different models of different kind, is handled in the section
[Complex Forms with Multiple Models](input-multiple-models.md).

The following shows how to implement tabular input with Yii.

There are three different situations to cover, which have to be handled slightly different:
- Updating a fixed set of records from the database
- Creating a dynamic set of new records
- Updating, creating and deleting of records on one page

In contrast to the single model forms explained before, we are working with an array of models now.
This array is passed to the view to display the input fields for each model in a table like style and we
will use helper methods of [[yii\base\Model]] that allow loading and validating multiple models at once:

- [[yii\base\Model::loadMultiple()|Model::loadMultiple()]] load post data into an array of models.
- [[yii\base\Model::validateMultiple()|Model::validateMultiple()]] validates an array of models.

### Updating a fixed set of records

Let's start with the controller action:

```php
<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\Setting;

class SettingsController extends Controller
{
    // ...

    public function actionUpdate()
    {
        $settings = Setting::find()->indexBy('id')->all();

        if ($this->request->isPost) {
            if (Setting::loadMultiple($settings, $this->request->post()) && Setting::validateMultiple($settings)) {
                foreach ($settings as $setting) {
                    $setting->save(false);
                }
                return $this->redirect('index');
            }
        }

        return $this->render('update', ['settings' => $settings]);
    }
}
```

In the code above we're using [[yii\db\ActiveQuery::indexBy()|indexBy()]] when retrieving models from the database to populate an array indexed by models primary keys.
These will be later used to identify form fields. [[yii\base\Model::loadMultiple()|Model::loadMultiple()]] fills multiple
models with the form data coming from POST
and [[yii\base\Model::validateMultiple()|Model::validateMultiple()]] validates all models at once.
As we have validated our models before, using `validateMultiple()`, we're now passing `false` as
a parameter to [[yii\db\ActiveRecord::save()|save()]] to not run validation twice.

Now the form that's in `update` view:

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin();

foreach ($settings as $id => $setting) {
    echo $form->field($setting, "[$id]value")->label($setting->name);
}

echo Html::submitButton('Save');

ActiveForm::end();
```

Here for each setting we are rendering name and an input with a value. It is important to add a proper index
to input name since that is how [[yii\base\Model::loadMultiple()|Model::loadMultiple()]] determines which model to fill with which values.

### Creating a dynamic set of new records

Creating new records is similar to updating, except the part, where we instantiate the models:

```php
public function actionCreate()
{
    $settings = [];
    if ($this->request->isPost) {
        $count = count($this->request->post($setting->tableName()));
        for ($i = 0; $i < $count; $i++) {
            $settings[$i] = new Setting();
        }
        if (Setting::loadMultiple($settings, $this->request->post()) && Setting::validateMultiple($settings)) {
            foreach ($settings as $setting) {
                $setting->save(false);
            }
            return $this->redirect('index');
        }
    }
    $settings[] = new Setting();

    return $this->render('create', ['settings' => $settings]);
}
```

Here we create an initial `$settings` array containing one model by default so that always at least one text field will be
visible in the view. Additionally we add more models for each line of input we may have received.

In the view you can use JavaScript to add new input lines dynamically.

### Combining Update, Create and Delete on one page

> Note: This section is under development.
>
> It has no content yet.

TBD

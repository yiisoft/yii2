Collecting tabular input
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

foreach ($settings as $index => $setting) {
    echo $form->field($setting, "[$index]value")->label($setting->name);
}

ActiveForm::end();
```

Here for each setting we are rendering name and an input with a value. It is important to add a proper index
to input name since that is how [[yii\base\Model::loadMultiple()|Model::loadMultiple()]] determines which model to fill with which values.

### Creating a dynamic set of new records

Creating new records is similar to updating, except the part, where we instantiate the models:

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

Here we create an initial `$settings` array containing one model by default so that always at least one text field will be
visible in the view. Additionally we add more models for each line of input we may have received.

In the view you can use javascript to add new input lines dynamically.

### Combining Update, Create and Delete on one page

To combine create, update, delete on one page, We can do the following step.
* Create your controller action. You can use other name like `actionCreateOrUpdate()` or anyting else.
First, we load original data. In posting request, we compare original data and posting data. For original data that
not posted, we delete it.
```php
public function actionCreate()
{
    $settings = $origins = Setting::findAll();
    if (Yii::$app-request->getIsPost()) {
        $valid = true;
        $settings = Yii::$app->request->post('Setting', []);
        foreach ($settings as $i => $setting) {
            if (isset($origins[$i])) {
                $settings[$i] = $origins[$i];
                unset($origins[$i]);
            } else {
                $settings[$i] = new Setting();
            }
            $settings[$i]->attributes = $setting;
            $valid = $settings[$i]->validate() && $valid;
        }
        if ($valid) {
            // delete not listed origin
            foreach ($origins as $setting) {
                $setting->delete();
            }
            
            foreach ($settings as $setting) {
                $setting->save(false);
            }
            return $this->redirect('index');
        }
    }
    return $this->render('create', ['settings' => $settings];
}
```

* Create view `create.php`

```php
<div class="create-form">
    <?php $form = ActiveForm::begin(); ?>
    <?= Html::errorSummary($settings) ?>
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
    <?php
        // use $key = '_key_'.
        // in javascript it will replaced using new number
        $template = $this->render('_row', ['key' => '_key_', 'model' => new Setting(), 'form' => $form]);
    ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Value</th>
                <th><a href="#" id="button-add"><span class="glyphicon glyphicon-plus"></span></a></th>
            </tr>
        </thead>
        <tbody id="tabular">
        <?php 
            $i = -1;
            foreach($settings as $i => $setting) {
                echo $this->render('_row', ['key' => $i, 'model' => $setting, 'form' => $form]);
            }
            $i++;
        ?>
        </tbody>
    <?php ActiveForm::end(); ?>
</div>
<?php
// define opts variable in javascript
$this->registerJs('var opts = ' . json_encode(['count' => $i, 'template' => $template]) . ';');
$this->registerJs($this->render('_script.js');
```

* Create view `_row.php`
This view use to generate all table row and also use to generate row template.
The row template will contain frasa `_key_` that must be replace with new row number (automatic counted).
```php
<tr data-key="<?= $key ?>">
    <td><?= $form->field($model, "[$key]name")->label(false) ?></td>
    <td><?= $form->field($model, "[$key]value")->label(false) ?></td>
    <td><a href="#" class="button-del"><span class="glyphicon glyphicon-trush"></span></a></td>
</tr>
```

* Then create view `_script.js`
Variable `opts` is provided from view `created.php` via `registerJs()`.
```javascript
$('#button-add').click(function(){
    var $row = $(opts.template.replace(/_key_/g, opts.count));
    opts.count++;
    $('#tabular').append($row);
    return false;
});

$('#tabular').on('click', 'a.button-del', function(){
    var $row = $(this).closest('tr[data-key]');
    $row.remove();
    return false;
});
```
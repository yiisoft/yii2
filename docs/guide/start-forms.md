Working with Forms
==================

In this section, we will describe how to create a new page to get data from users.
The page will display a form with a name input field and an email input field.
After getting these data from a user, the page will echo them back to the user for confirmation.

To achieve this goal, besides creating an [action](structure-controllers.md) and
two [views](structure-views.md), you will also create a [model](structure-models.md).

Through this tutorial, you will learn

* How to create a [model](structure-models.md) to represent the data entered by a user;
* How to declare rules to validate the data entered by users;
* How to build an HTML form in a [view](structure-views.md).


Creating a Model <a name="creating-model"></a>
----------------

To represent the data entered by a user, create an `EntryForm` model class as shown below and
save the class in the file `models/EntryForm.php`. Please refer to the [Class Autoloading](concept-autoloading.md)
section for more details about the class file naming convention.

```php
<?php

namespace app\models;

use yii\base\Model;

class EntryForm extends Model
{
    public $name;
    public $email;

    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            ['email', 'email'],
        ];
    }
}
```

The class extends from [[yii\base\Model]], a base class provided by Yii that is commonly used to
represent form data.

The class contains two public members, `name` and `email`, which are used to keep
the data entered by the user. It also contains a method named `rules()` which returns a set
of rules used for validating the data. The validation rules declared above state that

* both the `name` and `email` data are required;
* the `email` data must be a valid email address.

If you have an `EntryForm` object populated with the data entered by a user, you may call
its [[yii\base\Model::validate()|validate()]] to trigger the data validation. A data validation
failure will turn on the [[yii\base\Model::hasErrors|hasErrors]] property, and through
[[yii\base\Model::getErrors|errors]] you may learn what validation errors the model has.


Creating an Action <a name="creating-action"></a>
------------------

Next, create an `entry` action in the `site` controller, like you did in the previous section.

```php
<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\EntryForm;

class SiteController extends Controller
{
    // ...existing code...

    public function actionEntry()
    {
        $model = new EntryForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // valid data received in $model

            // do something meaningful here about $model ...

            return $this->render('entry-confirm', ['model' => $model]);
        } else {
            // either the page is initially displayed or there is some validation error
            return $this->render('entry', ['model' => $model]);
        }
    }
}
```

The action first creates an `EntryForm` object. It then tries to populate the model
with the data from `$_POST` which is provided in Yii through [[yii\web\Request::post()]].
If the model is successfully populated (i.e., the user has submitted the HTML form),
it will call [[yii\base\Model::validate()|validate()]] to make sure the data entered
are valid.

If everything is fine, the action will render a view named `entry-confirm` to confirm
with the user that the data he has entered is accepted. Otherwise, the `entry` view will
be rendered, which will show the HTML form together with the validation error messages (if any).

> Info: The expression `Yii::$app` represents the [application](structure-applications.md) instance
  which is a globally accessible singleton. It is also a [service locator](concept-service-locator.md)
  providing components, such as `request`, `response`, `db`, etc. to support specific functionalities.
  In the above code, the `request` component is used to access the `$_POST` data.


Creating Views <a name="creating-views"></a>
--------------

Finally, create two views named `entry-confirm` and `entry` that are rendered by the `entry` action,
as described in the last subsection.

The `entry-confirm` view simply displays the name and email data. It should be stored as the file `views/site/entry-confirm.php`.

```php
<?php
use yii\helpers\Html;
?>
<p>You have entered the following information:</p>

<ul>
    <li><label>Name</label>: <?= Html::encode($model->name) ?></li>
    <li><label>Email</label>: <?= Html::encode($model->email) ?></li>
</ul>
```

The `entry` view displays an HTML form. It should be stored as the file `views/site/entry.php`.

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'email') ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>
```

The view uses a powerful [widget](structure-widgets.md) called [[yii\widgets\ActiveForm|ActiveForm]] to
build the HTML form. The `begin()` and `end()` methods of the widget render the opening and close
form tags, respectively. Between the two method calls, input fields are created by the
[[yii\widgets\ActiveForm::field()|field()]] method. The first input field is about the "name" data,
and the second the "email" data. After the input fields, the [[yii\helpers\Html::submitButton()]] method
is called to generate a submit button.


How It Works <a name="how-it-works"></a>
------------

To see how it works, use your browser to access the following URL:

```
http://hostname/index.php?r=site/entry
```

You will see a page displaying a form with two input fields. In front of each input field, a label
is also displayed indicating what data you need to enter. If you click the submit button without
entering anything, or if you do not provide a valid email address, you will see an error message that
is displayed next to each problematic input field.

![Form with Validation Errors](images/start-form-validation.png)

After entering a valid name and email address and clicking the submit button, you will see a new page
displaying the data that you just entered.

![Confirmation of Data Entry](images/start-entry-confirmation.png)



### Magic Explained <a name="magic-explained"></a>

You may wonder how the HTML form works behind the scene, because it seems almost magical that it can
display a label for each input field and show error messages if you do not enter the data correctly
without reloading the page.

Yes, the data validation is actually done on the client side using JavaScript as well as on the server side.
[[yii\widgets\ActiveForm]] is smart enough to extract the validation rules that you have declared in `EntryForm`,
turn them into JavaScript code, and use the JavaScript to perform data validation. In case you have disabled
JavaScript on your browser, the validation will still be performed on the server side, as shown in
the `actionEntry()` method. This ensures data validity in all circumstances.

The labels for input fields are generated by the `field()` method based on the model property names.
For example, the label `Name` will be generated for the `name` property. You may customize a label by
the following code:

```php
<?= $form->field($model, 'name')->label('Your Name') ?>
<?= $form->field($model, 'email')->label('Your Email') ?>
```

> Info: Yii provides many such widgets to help you quickly build complex and dynamic views.
  As you will learn later, writing a new widget is also extremely easy. You may turn much of your
  view code into reusable widgets to simplify view development in future.


Summary <a name="summary"></a>
-------

In this section, you have touched every part in the MVC design pattern. You have learned how
to create a model class to represent the user data and validate them.

You have also learned how to get data from users and how to display them back. This is a task that
could take you a lot of time when developing an application. Yii provides powerful widgets
to make this task very easy.

In the next section, you will learn how to work with databases which are needed in nearly every application.

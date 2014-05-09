Working with Forms
==================

> Note: This section is under development.

In this section, we will describe how to create a new page to get data from users.
The page will display a form with a name input field and an email input field.
After getting these data from a user, the page will echo them back to the user for confirmation.

To achieve this goal, besides creating an [action](structure-controllers.md) and
two [views](structure-views.md), you will also create a [model](structure-models.md).

Through this tutorial, you will learn

* How to create a [model](structure-models.md) to represent the data entered by a user;
* How to declare rules to validate the data entered by users;
* How to build an HTML form in a [view](structure-views.md).


Creating a Model
----------------

To represent the data entered by a user, create an `EntryForm` model class as shown below and
save the class in the file `models/EntryForm.php`. Please refer to the [Class Autoloading](concept-autoloading.md)
section for more details about the class file naming convention.

```php
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


Creating an Action
------------------

Next, create an `entry` action in the `site` controller, like you did in the previous section.

```php
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
  which is a singleton and is globally accessible through this expression. The application instance
  is also a [service locator](concept-service-locator.md) providing service components such as `request`,
  `response`, `db`, etc. In the above code, the `request` component is used to get the `$_POST` data.


Creating Views
--------------

How It Works
------------

Summary
-------

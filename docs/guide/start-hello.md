Saying Hello
============

In this section, we will describe how to add to your application a new page that displays "Hello".
To achieve this goal, you will create an [action](structure-controllers.md) as well as
a [view](structure-views.md). Yii dispatches the page request to the action which in turn
renders the view with "Hello" to the user.

Through this tutorial, you will learn

* How to create an [action](structure-controllers.md) to respond to requests;
* How to create a [view](structure-views.md) to compose response content;
* How an application dispatches requests to [actions](structure-controllers.md).


Creating an Action
------------------

[Actions](structure-controllers.md) are the only objects that end users can directly refer to and request
for execution. Actions are grouped by [controllers](structure-controllers.md). The execution result of
an action is the response that an end user will receive.

For the "Hello" task, you will create a `say` action which reads a `message` parameter from
a request and displays the message content back to the user. If the `message` parameter is not given,
it will use the default value "Hello". For simplicity, you may put this action in an existing
controller `SiteController` which is defined in the class file `controllers/SiteController.php`:

```php
class SiteController extends Controller
{
    // ...existing code...

    public function actionSay($message = 'Hello')
    {
        return $this->render('say', ['message' => $message]);
    }
}
```

In the above code, the `say` action is defined as a method named `actionSay` in `SiteController`.
Yii uses the prefix `action` to differentiate action methods from non-action methods in the class.
The name after the `action` prefix is treated as the ID of the corresponding action.

> Info: Actions defined by action methods are called *inline actions*. Yii will create an [[yii\base\InlineAction|InlineAction]]
  object during runtime which will call the corresponding action method to handle a request.

The action method takes a parameter `$message` which defaults to `"Hello"`. When the application
receives a request and determines the `say` action is responsible to handle the request, it will
populate this parameter with the same named parameter found in the request.

Within the action method, the [[yii\web\Controller::render()|render()]] method is called which
renders a [view](structure-views.md) named `say` and passes along the `message` parameter. The rendering
result is returned by the action method, which will be taken by the application and displayed to the end user.


Creating a View
---------------

[Views](structure-views.md) are scripts that you write to compose response content.
For the "Hello" task, you will create a `say` view which echoes the `message` parameter
passed from the `say` action when it calls `render()`:

```php
<?php
use yii\helpers\Html;
?>
<?= Html::encode($message) ?>
```

Note that in the above code, the `message` parameter is [[yii\helpers\Html::encode()|HTML-encoded]]
before being echoed. This is necessary because the parameter is coming from end users who may attempt
[cross-site scripting (XSS) attacks](http://en.wikipedia.org/wiki/Cross-site_scripting) by embedding
malicious JavaScript code in the parameter.

The `say` view should be saved in the file `views/site/say.php`. When the method [[yii\web\Controller::render()|render()]]
is called in an action, it will look for a PHP file named as `views/ControllerID/ActionID/ViewName.php`.

> Tip: You may put more content in the `say` view. They can be HTML tags, plain text, or even PHP statements.
  In fact, the `say` view is just a PHP script which is executed by the [[yii\web\Controller::render()|render()]] method.
  The content echoed by the view script will be forwarded by the application as the response to the end user.


How It Works
------------

After creating the action and the view, you may access the new page by the following URL:

```
http://hostname/index.php?r=site/say&message=Hello+World
```

This will show a page displaying "Hello World". The page shares the same header and footer as other pages of
the application. If you omit the `message` parameter in the URL, you would see the page displays "Hello".
This is because `message` is passed as a parameter to the `actionSay()` method, and when it is omitted,
the default value of `"Hello"` will come into play.

The `r` parameter requires more explanation. It stands for [route](runtime-routing.md) which is a globally unique ID
referring to an action. Its format is `ControllerID/ActionID`. When the application receives
a request, it will check this parameter and use the `ControllerID` part to determine which controller
class should be instantiated to handle the request. Then, the controller will use the `ActionID` part
to determine which action should be instantiated to do the real work. In our case, the route `site/say`
will be resolved into the `SiteController` controller class and the `say` action. As a result,
the `SiteController::actionSay()` method will be called to handle the request.


Summary
-------

In this section, you have touched the controller part and the view part in the MVC design pattern.
You created an action as part of a controller to handle requests. And you also created a view script
to compose response content. There is no model involved in this task because the only data used is
the simple `message` parameter.

In the next section, you will get into touch with the model part through building HTML forms.

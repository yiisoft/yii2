Saying Hello
============

In this section, we will describe how to create a new page displaying "Hello" in your application.
To achieve this goal, you will create an [action](structure-controllers.md) as well as
a [view](structure-views.md):

* The application will dispatch the page request to the action;
* And the action will in turn render the view that shows "Hello" to the end user.

Through this tutorial, you will learn

* How to create an [action](structure-controllers.md) to respond to requests;
* How to create a [view](structure-views.md) to compose response content;
* How an application dispatches requests to [actions](structure-controllers.md).


Creating an Action <a name="creating-action"></a>
------------------

For the "Hello" task, you will create a `say` [action](structure-controllers.md) which reads
a `message` parameter from a request and displays the message back to the user. If the request
does not provide a `message` parameter, the action will display the default "Hello".

> Info: [Actions](structure-controllers.md) are the objects that end users can directly refer to for
  execution. Actions are grouped by [controllers](structure-controllers.md). The execution result of
  an action is the response that an end user will receive.

Actions must be declared in [controllers](structure-controllers.md). For simplicity, you may
declare the `say` action in the existing controller `SiteController` which is defined
in the class file `controllers/SiteController.php`:

```php
<?php

namespace app\controllers;

use yii\web\Controller;

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
Yii uses the prefix `action` to differentiate action methods from non-action methods in a controller class.
The name after the `action` prefix is treated as the ID of the corresponding action.

> Info: Action IDs are in lower case. If an action ID has multiple words, they should be concatenated by dashes,
  e.g., `create-comment`. Action method names are derived from action IDs by removing dashes from the IDs,
  turning the first letter in each word into upper case, and prefixing them with `action`. For example,
  the action ID `create-comment` corresponds to the action method name `actionCreateComment`.

The action method takes a parameter `$message` which defaults to `"Hello"`. When the application
receives a request and determines that the `say` action is responsible for handling the request, it will
populate this parameter with the same named parameter found in the request.

Within the action method, [[yii\web\Controller::render()|render()]] is called to render
a [view](structure-views.md) named `say`. The `message` parameter is also passed to the view
so that it can be echoed there. The rendering result is returned by the action method, which will be taken
by the application and displayed to the end user.


Creating a View <a name="creating-view"></a>
---------------

[Views](structure-views.md) are scripts that you write to compose response content.
For the "Hello" task, you will create a `say` view to echo the `message` parameter received from the action method:

```php
<?php
use yii\helpers\Html;
?>
<?= Html::encode($message) ?>
```

The `say` view should be saved in the file `views/site/say.php`. When the method [[yii\web\Controller::render()|render()]]
is called in an action, it will look for a PHP file named as `views/ControllerID/ActionID/ViewName.php`.

Note that in the above code, the `message` parameter is [[yii\helpers\Html::encode()|HTML-encoded]]
before being echoed. This is necessary because the parameter comes from end users who may attempt
[cross-site scripting (XSS) attacks](http://en.wikipedia.org/wiki/Cross-site_scripting) by embedding
malicious JavaScript code in the parameter.

You may put more content in the `say` view. They can be HTML tags, plain text, or even PHP statements.
In fact, the `say` view is just a PHP script which is executed by the [[yii\web\Controller::render()|render()]] method.
The content echoed by the view script will be forwarded by the application as the response to the end user.


How It Works <a name="how-it-works"></a>
------------

After creating the action and the view, you may access the new page by the following URL:

```
http://hostname/index.php?r=site/say&message=Hello+World
```

![Hello World](images/start-hello-world.png)

This will show a page displaying "Hello World". The page shares the same header and footer as other pages of
the application. If you omit the `message` parameter in the URL, you would see the page displays "Hello".
This is because `message` is passed as a parameter to the `actionSay()` method, and when it is omitted,
the default value of `"Hello"` will come into play.

> Info: The new page shares the same header and footer as other pages because the [[yii\web\Controller::render()|render()]]
  method will automatically embed the result of the `say` view in a so-called [layout](structure-views.md) `views/layouts/main.php`.

The `r` parameter requires more explanation. It stands for [route](runtime-routing.md) which is a globally unique ID
referring to an action. Its format is `ControllerID/ActionID`. When the application receives
a request, it will check this parameter and use the `ControllerID` part to determine which controller
class should be instantiated to handle the request. Then, the controller will use the `ActionID` part
to determine which action should be instantiated to do the real work. In our case, the route `site/say`
will be resolved into the `SiteController` controller class and the `say` action. As a result,
the `SiteController::actionSay()` method will be called to handle the request.

> Info: Like actions, controllers also have IDs that uniquely identify them in an application.
  Controller IDs use the same naming rules as action IDs. Controller class names are derived from
  controller IDs by removing dashes from the IDs, turning the first letter in each word into upper case,
  and suffixing them with the word `Controller`. For example, the controller ID `post-comment` corresponds
  to the controller class name `PostCommentController`.


Summary <a name="summary"></a>
-------

In this section, you have touched the controller part and the view part in the MVC design pattern.
You created an action as part of a controller to handle requests. And you also created a view
to compose response content. There is no model involved because the only data used is the simple `message` parameter.

You have also learned the route concept which is the bridge between user requests and controller actions.

In the next section, you will learn how to create a model and add a new page with an HTML form.

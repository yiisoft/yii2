MVC Overview
============

Yii implements the model-view-controller (MVC) design pattern, which is
widely adopted in Web programming. MVC aims to separate business logic from
user interface considerations, so that developers can more easily change
each part without affecting the other. In MVC, the model represents the
information (the data) and the business rules; the view contains elements
of the user interface such as text, form inputs; and the controller manages
the communication between the model and the view.

Besides implementing MVC, Yii also introduces a front-controller, called
`application`, which encapsulates the execution context for the processing
of a request. Application collects information about a user request and
then dispatches it to an appropriate controller for further handling.

The following diagram shows the static structure of a Yii application:

![Static structure of Yii application](structure.png)


A Typical Workflow
------------------

The following diagram shows a typical workflow of a Yii application when
it is handling a user request:

![Typical workflow of a Yii application](flow.png)

   1. A user makes a request with the URL `http://www.example.com/index.php?r=post/show&id=1`
and the Web server handles the request by executing the bootstrap script `index.php`.
   2. The bootstrap script creates an [Application](/doc/guide/basics.application)
instance and runs it.
   3. The Application obtains detailed user request information from
an [application component](/doc/guide/basics.application#application-component)
named `request`.
   4. The application determines the requested [controller](/doc/guide/basics.controller)
and [action](/doc/guide/basics.controller#action) with the help
of an application component named `urlManager`. For this example, the controller
is `post`, which refers to the `PostController` class; and the action is `show`,
whose actual meaning is determined by the controller.
   5. The application creates an instance of the requested controller
to further handle the user request. The controller determines that the action
`show` refers to a method named `actionShow` in the controller class. It then
creates and executes filters (e.g. access control, benchmarking) associated
with this action. The action is executed if it is allowed by the filters.
   6. The action reads a `Post` [model](/doc/guide/basics.model) whose ID is `1` from the database.
   7. The action renders a [view](/doc/guide/basics.view) named `show` with the `Post` model.
   8. The view reads and displays the attributes of the `Post` model.
   9. The view executes some [widgets](/doc/guide/basics.view#widget).
   10. The view rendering result is embedded in a [layout](/doc/guide/basics.view#layout).
   11. The action completes the view rendering and displays the result to the user.

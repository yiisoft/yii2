Application Structure
=====================

> Note: This chapter is under development.

Yii implements the model-view-controller (MVC) design pattern, which is
widely adopted in Web and other application programming. MVC aims to separate business logic from
user interface considerations, allowing developers to more easily change one component of an application without affecting, or even touching, another.

In MVC, the *model* represents both the
information (the data) and the business rules to which the data must adhere. The *view* contains elements
of the user interface, such as text, images, and form elements. The *controller* manages
the communication between the model and the view, acting as an agent that handles actions and requests.

Besides implementing the MVC design pattern, Yii also introduces a *front-controller*, called
*application*. The front-controller encapsulates the *execution context* for the processing of a request. This means that the front-controller collects information about a user request, and
then dispatches it to an appropriate controller for the actual handling of that request. In other words, the front-controller is the primary application manager, handling all requests and delegating action accordingly.

The following diagram shows the static structure of a Yii application:

![Static structure of Yii application](images/structure.png)


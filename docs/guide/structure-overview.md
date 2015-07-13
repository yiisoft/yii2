Overview
========

Yii applications are organized according to the [model-view-controller (MVC)](http://wikipedia.org/wiki/Model-view-controller)
architectural pattern. [Models](structure-models.md) represent data, business logic and rules; [views](structure-views.md)
are output representation of models; and [controllers](structure-controllers.md) take input and convert
it to commands for [models](structure-models.md) and [views](structure-views.md).

Besides MVC, Yii applications also have the following entities:

* [entry scripts](structure-entry-scripts.md): they are PHP scripts that are directly accessible by end users.
  They are responsible for starting a request handling cycle.
* [applications](structure-applications.md): they are globally accessible objects that manage application components
  and coordinate them to fulfill requests.
* [application components](structure-application-components.md): they are objects registered with applications and
  provide various services for fulfilling requests.
* [modules](structure-modules.md): they are self-contained packages that contain complete MVC by themselves.
  An application can be organized in terms of multiple modules.
* [filters](structure-filters.md): they represent code that need to be invoked before and after the actual
  handling of each request by controllers.
* [widgets](structure-widgets.md): they are objects that can be embedded in [views](structure-views.md). They
  may contain controller logic and can be reused in different views.

The following diagram shows the static structure of an application:

![Static Structure of Application](images/application-structure.png)

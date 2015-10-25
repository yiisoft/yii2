Overview
========

Each time when a Yii application handles a request, it undergoes a similar workflow.

1. A user makes a request to the [entry script](structure-entry-scripts.md) `web/index.php`.
2. The entry script loads the application [configuration](concept-configurations.md) and creates
   an [application](structure-applications.md) instance to handle the request.
3. The application resolves the requested [route](runtime-routing.md) with the help of
   the [request](runtime-requests.md) application component.
4. The application creates a [controller](structure-controllers.md) instance to handle the request.
5. The controller creates an [action](structure-controllers.md) instance and performs the filters for the action.
6. If any [filter](structure-filters.md) fails, the action is cancelled.
7. If all filters pass, the action is executed.
8. The action loads a data [model](structure-models.md), possibly from a database.
9. The action renders a [view](structure-views.md), providing it with the data model.
10. The rendered result is returned to the [response](runtime-responses.md) application component.
11. The response component sends the rendered result to the user's browser.

The following diagram shows how an application handles a request.

![Request Lifecycle](images/request-lifecycle.png)

In this section, we will describe in detail how some of these steps work.

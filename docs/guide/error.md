Error Handling
==============

Error handling in Yii is different from plain PHP. First of all, all non-fatal errors are converted to exceptions so
you can use `try`-`catch` to work with these. Second, even fatal errors are rendered in a nice way. In debug mode that
means you have a trace and a piece of code where it happened so it takes less time to analyze and fix it.


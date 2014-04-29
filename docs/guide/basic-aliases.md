Path Aliases
============

> Note: This chapter is under development.

Yii 2.0 expands the usage of path aliases to both file/directory paths and URLs. An alias
must start with an `@` symbol so that it can be differentiated from file/directory paths and URLs.
For example, the alias `@yii` refers to the Yii installation directory while `@web` contains the base URL for the currently running web application. Path aliases are supported in most places in the Yii core code. For example, `FileCache::cachePath` can accept both a path alias and a normal directory path.

Path aliases are also closely related to class namespaces. It is recommended that a path
alias should be defined for each root namespace so that Yii's class autoloader can be used without
any further configuration. For example, because `@yii` refers to the Yii installation directory,
a class like `yii\web\Request` can be autoloaded by Yii. If you use a third party library
such as Zend Framework, you may define a path alias `@Zend` which refers to its installation
directory and Yii will be able to autoload any class in this library.

The following aliases are predefined by the core framework:

- `@yii` - framework directory.
- `@app` - base path of currently running application.
- `@runtime` - runtime directory.
- `@vendor` - Composer vendor directory.
- `@webroot` - web root directory of currently running web application.
- `@web` - base URL of currently running web application.


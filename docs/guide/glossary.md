# A

## alias

Alias is a string that's used by Yii to refer to the class or directory such as `@app/vendor`.

## application

The application is the central object during HTTP request. It contains a number of components and with these is getting info from request and dispatching it to an appropriate controller for further processing.

The application object is instantiated as a singleton by the entry script. The application singleton can be accessed at any place via `\Yii::$app`.

## assets

Asset refers to a resource file. Typically it contains JavaScript or CSS code but can be anything else that is accessed via HTTP.

## attribute

An attribute is a model property (a class member variable or a magic property defined via `__get()`/`__set()`) that stores **business data**.

# B

## bundle

Bundle, known as package in Yii 1.1, refers to a number of assets and a configuration file that describes dependencies and lists assets.

# C

## configuration

Configuration may refer either to the process of setting properties of an object or to a configuration file that stores settings for an object or a class of objects.

# E

## extension

Extension is a set of classes, asset bundles and configurations that adds more features to the application.

# I

## installation

Installation is a process of preparing something to work either by following a readme file or by executing specially prepared script. In case of Yii it's setting permissions and fullfilling software requirements.

# M

## module

Module is a sub-application which contains MVC elements by itself, such as models, views, controllers, etc. and can be used withing the main application. Typically by forwarding requests to the module instead of handling it via controllers.

# N

## namespace

Namespace refers to a [PHP language feature](http://php.net/manual/en/language.namespaces.php) which is actively used in Yii2.

# P

## package

[See bundle](#bundle).

# V

## vendor

Vendor is an organization or individual developer providing code in form of extensions, modules or libraries.
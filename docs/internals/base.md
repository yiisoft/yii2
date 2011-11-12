Base classes and interfaces
===========================

Object
------

Object is the base class for many other Yii2 classes.

### property feature

#### Why

To be able to make property `public` initially and then seamlessly make it
`private` or `protected` by adding getter and setter method. That will *not*
change API. Results in less repetitive code. Performance drop isn't significant.

### callbacks and expressions

### [[Object::create()]|create] method

This method is a powerful way to instantiate a class. Differences from `new`:

- Calls class constructor (same the `new` operator);
- Initializes the object properties using the name-value pairs given as the
  last parameter to this method;
- Calls [[Initable::init|init]] if the class implements [[Initable]].

#### Why

To support class dependencies and their lazy loading.

### [[Initable]] interface

Developer will implement initable interface if running `init()` needed and will
skip it if not.

#### Why

Indicates where `init()` will be called and where not. More explicit than it was
in Yii 1.

Component
---------


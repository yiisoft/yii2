Console applications
====================

Yii has full featured support of console. Console application structure in Yii is very similar to web application. It
consists of one or more [[\yii\console\Controller]] (often referred to as commands). Each has one or more actions.

Usage
-----

User executes controller action using the following syntax:

```
yii <route> [--param1=value1 --param2 ...]
```

For example, `MigrationController::create` with `MigrationController::$migrationTable` set can be called from command
line like the following:

```
yii migreate/create --migrationTable=my_migration
```

Entry script
------------


Configuration
-------------

Creating your own console commands
----------------------------------

### Controller

### Action

### Parameters

### Return codes
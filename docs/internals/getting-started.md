Getting started with Yii2 development
=====================================

1. Clone your fork of yii2 `git clone git@github.com:<yourname>/yii2`.
2. Change into the repo folder `cd yii2`.
3. run `./build/build app/link basic` to install composer dependecies for the basic app.
   This command will install foreign composer packages as normal but will link the yii2 repo to
   the currently checked out repo, so you have one instance of all the code installed.
4. Do the same for the advanced app if needed: `./build/build app/link advanced`
   This command will also be used to update dependecies, it runs `composer update` internally.
5. Now you have a working playground for hacking on Yii 2.

You may also add the yii2 upstream repo to pull the latest changes:

```
git remote add upstream https://github.com/yiisoft/yii2.git
```

Please refer to [Git workflow for Yii 2 contributors](git-workflow.md) for details about creating pull requests.

Unit tests
----------

To run the unit tests you have to install composer packages for the dev-repo.
Run `composer update` in the repo root directory to get the latest packages.

You can now execute unit tests by running `phpunit`.

You may limit the tests to a group of tests you are working on e.g. to run only tests for the validators and redis
`phpunit --group=validators,redis`.

Functional and acceptance tests
-------------------------------

In order to run functional and acceptance tests you need to install additional composer packages for the application you're going
to test. Add the following four packages to your `composer.json` `require-dev` section: 

```
"yiisoft/yii2-codeception": "*",
```

For advanced application you may need `yiisoft/yii2-faker: *` as well.

Then for the basic application template run `./build/build app/link basic`. For advanced template command is
`./build/build app/link advanced`.

After package installation is complete you can run the following for basic app:

```
cd apps/basic
codecept build
codecept run
```

For advanced application frontend it will be:

```
cd apps/advanced/frontend
codecept build
codecept run
```

Note that you need a running webserver in order to pass acceptance tests. That can be easily achieved with PHP's built-in
webserver:

```
cd apps/advanced/frontend/www
php -S 127.0.0.1:8080
```

Note that you should have Codeception and PHPUnit installed globally:
 
```
composer global require "phpunit/phpunit=4.1.*"
composer global require "codeception/codeception=2.0.*"
composer global require "codeception/specify=*"
composer global require "codeception/verify=*"
```

After running commands you'll see "Changed current directory to /your/global/composer/dir" message. If it's the
first time you're installing a package globally you need to add `/your/global/composer/dir/vendor/bin/` to your `PATH`.

Extensions
----------

To work on extensions you have to install them in the application you want to try them in.
Just add them to the `composer.json` as you would normally do e.g. add `"yiisoft/yii2-redis": "*"` to the
`require` section of the basic app.
Running `./build/build app/link basic` will install the extension and its dependecies and create
a symlink to `extensions/redis` so you are not working the composer vendor dir but the yii2 repo directly.


Getting started with Yii2 development
=====================================

1. Clone your fork of yii2 `git clone git@github.com:<yourname>/yii2.git`.
2. Change into the repo folder `cd yii2`.
3. run `./build/build dev/app basic` to clone the basic app and install composer dependencies for the basic app.
   This command will install foreign composer packages as normal but will link the yii2 repo to
   the currently checked out repo, so you have one instance of all the code installed.
4. Do the same for the advanced app if needed: `./build/build dev/app advanced`
   This command will also be used to update dependencies, it runs `composer update` internally.
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

Extensions
----------

> Note: this section is outdated as we are currently changing the repository structure. instructions will be updated soon.

To work on extensions you have to install them in the application you want to try them in.
Just add them to the `composer.json` as you would normally do e.g. add `"yiisoft/yii2-redis": "*"` to the
`require` section of the basic app.
Running `./build/build app/link basic` will install the extension and its dependecies and create
a symlink to `extensions/redis` so you are not working the composer vendor dir but the yii2 repo directly.

Functional and acceptance tests for applications
------------------------------------------------

See `apps/advanced/tests/README.md` and `apps/basic/tests/README.md` to learn about how to run Codeception tests.

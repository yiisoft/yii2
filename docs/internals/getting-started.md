Getting started with Yii2 development
=====================================

The best way to have a locally runnable webapp that uses codebase cloned from main repository is to use `yii2-dev`
Composer package. Here's how to do it:

1. `git clone https://github.com/yiisoft/yii2-app-basic`.
2. Remove `.git` directory from cloned directory.
3. Change `composer.json`. Instead of all stable requirements add just one `"yiisoft/yii2-dev": "*"`.
4. Execute `composer create-project`. Do not add `--prefer-dist` to the command since it will not download git repository
   then.
5. Now you have working playground that uses latest code.

Note that requirements of extensions that come with `yii2-dev` are not loaded automatically.
If you want to use an extension, check if there are dependencies suggested for it and add them
to your `composer.json`. You can see suggested packages by running `composer show yiisoft/yii2-dev`.

If you're core developer there's no extra step needed. You can change framework code under
`vendor/yiisoft/yii2-dev` and push it to main repository.

If you're not core developer or want to use your own fork for pull requests:

1. Fork `https://github.com/yiisoft/yii2` and get your own repository address such as
   `git://github.com/username/yii2.git`.
2. Edit `vendor/yiisoft/yii2-dev/.git/config`. Change remote `origin` url to your own:

```
[remote "origin"]
  url = git://github.com/username/yii2.git
```

> Hint: The workflow of forking a package and pushing changes back into your fork and then sending a pull-request to the
  maintainer is the same for all extensions you require via composer.

Please refer to [Git workflow for Yii 2 contributors](git-workflow.md) for details about creating pull requests.


An Alternative way
------------------

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

### Unit tests

To run the unit tests you have to install composer packages for the dev-repo.
Run `composer update` in the repo root directory to get the latest packages.

You can now execute unit tests by running `./vendor/bin/phpunit`.

You may limit the tests to a group of tests you are working on e.g. to run only tests for the validators and redis
`./vendor/bin/phpunit --group=validators,redis`.

### Extensions

To work on extensions you have to install them in the application you want to try them in.
Just add them to the `composer.json` as you would normally do e.g. add `"yiisoft/yii2-redis": "*"` to the
`require` section of the basic app.
Running `./build/build app/link basic` will install the extension and its dependecies and create
a symlink to `extensions/redis` so you are not working the composer vendor dir but the yii2 repo directly.


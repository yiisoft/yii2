Getting started with Yii2 development
=====================================

The best way to have a locally runnable webapp that uses codebase cloned from main repository is to use `yii2-dev`
Composer package. Here's how to do it:

1. `git clone git@github.com:yiisoft/yii2-app-basic.git`.
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

> Hint: The workflow of forking a package and pushing changes back into your fork and then sending a pull-request to the maintainer is the same for all extensions you require via composer.

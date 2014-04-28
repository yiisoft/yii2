Creating your own Application structure
=======================================

While [basic](apps-basic.md) and [advanced](apps-advanced.md) application templates are great for most of your needs
you may want to create your own application template to start your projects with.

Application templates are repositories containing `composer.json` and registered as Composer packages so you can make
any repository a package and it will be installable via `create-project` command.

Since it's a bit too much to start building your template from scratch it is better to use one of built-in templates
as a base. Let's use basic template.

Clone basic template repository from git
----------------------------------------

```
git clone git@github.com:yiisoft/yii2-app-basic.git
```

And wait till it's downloaded. Since we don't need to push our changes back to Yii's repository we delete `.git` and all
of its contents.

Modify files
------------

Now we need to modify `composer.json`. Change `name`, `description`, `keywords`, `homepage`, `license`, `support`
to match your new template. Adjust `require`, `require-dev`, `suggest` and the rest of the options.

> **Note**: In `composer.json` file `writable` under `extra` is functionality added by Yii that allows you to specify
> per file permissions to set after an application is created using the template.

Next actually modify the structure of the future application as you like and update readme.


Make a package
--------------

Create git repository and push your files there. If you're going to make it open source github is the best way to host it.
If it should remain private, any git repository would do.

Then you need to register your package. For public templates it should be registered at [packagist](https://packagist.org/).
For private ones it is a bit more tricky but well defined in
[Composer documentation](https://getcomposer.org/doc/05-repositories.md#hosting-your-own).

Use it
------

That's it. Now you can create projects using a template:

```
php composer.phar create-project --prefer-dist --stability=dev mysoft/yii2-app-coolone new-project
```

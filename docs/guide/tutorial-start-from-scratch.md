Creating your own Application structure
=======================================

> Note: This section is under development.

While the [basic](https://github.com/yiisoft/yii2-app-basic) and [advanced](https://github.com/yiisoft/yii2-app-advanced)
project templates are great for most of your needs, you may want to create your own project template with which
to start your projects.

Project templates in Yii are simply repositories containing a `composer.json` file, and registered as a Composer package.
Any repository can be identified as a Composer package, making it installable via `create-project` Composer command.

Since it's a bit too much to start building your entire template from scratch, it is better to use one of the built-in
templates as a base. Let's use the basic template here.

Clone the Basic Template
----------------------------------------

The first step is to clone the basic Yii template's Git repository:

```bash
git clone git@github.com:yiisoft/yii2-app-basic.git
```

Then wait for the repository to be downloaded to your computer. Since the changes made to the template won't be pushed 
back, you can delete the `.git` directory and all of its contents from the download.

Modify the Files
------------

Next, you'll want to modify the `composer.json` to reflect your template. Change the `name`, `description`, `keywords`, 
`homepage`, `license`, and `support` values to describe your new template. Also adjust the `require`, `require-dev`, 
`suggest`, and other options to match your template's requirements.

> Note: In the `composer.json` file, use the `writable` parameter under `extra` to specify
> per file permissions to be set after an application is created using the template.

Next, actually modify the structure and contents of the application as you would like the default to be. 
Finally, update the README file to be applicable to your template.

Make a Package
--------------

With the template defined, create a Git repository from it, and push your files there. If you're going to open source 
your template, [Github](http://github.com) is the best place to host it. If you intend to keep your template 
non-collaborative, any Git repository site will do.

Next, you need to register your package for Composer's sake. For public templates, the package should be registered 
at [Packagist](https://packagist.org/). For private templates, it is a bit more tricky to register the package. For 
instructions, see the [Composer documentation](https://getcomposer.org/doc/05-repositories.md#hosting-your-own).

Use the Template
------

That's all that's required to create a new Yii project template. Now you can create projects using your template:

```
composer create-project --prefer-dist --stability=dev mysoft/yii2-app-coolone new-project
```

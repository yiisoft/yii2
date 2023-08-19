Git workflow for Yii 2 contributors
===================================

So you want to contribute to Yii? Great! But to increase the chances of your changes being accepted quickly, please
follow the following steps. If you are new to Git
and GitHub, you might want to first check out [GitHub help](https://help.github.com/), [try Git](https://try.github.com)
or learn something about [Git internal data model](https://nfarina.com/post/9868516270/git-is-simpler).

Prepare your development environment
------------------------------------

The following steps will create a development environment for Yii, which you can use to work
on the core code of Yii framework. These steps only need to be done the first time you contribute.

### 1. [Fork](https://help.github.com/fork-a-repo/) the Yii repository on GitHub and clone your fork to your development environment

```
git clone git@github.com:YOUR-GITHUB-USERNAME/yii2.git
```

If you have trouble setting up Git with GitHub in Linux, or are getting errors like "Permission Denied (publickey)",
then you must [setup your Git installation to work with GitHub](https://help.github.com/linux-set-up-git/)

> Tip: if you're not fluent with Git, we recommend reading excellent free [Pro Git book](https://git-scm.com/book/en/v2).

### 2. Add the main Yii repository as an additional git remote called "upstream"

Change to the directory where you cloned Yii, normally, "yii2". Then enter the following command:

```
git remote add upstream https://github.com/yiisoft/yii2.git
```

### 3. Prepare the testing environment <span id="prepare-the-test-environment"></span>

The following steps are not necessary if you want to work only on translations or documentation.

- run `composer install` to install dependencies (assuming you have [composer installed globally](https://getcomposer.org/doc/00-intro.md#globally)).

If you are going to work with JavaScript:

- run `npm install` to install JavaScript testing tools and dependencies (assuming you have [Node.js and NPM installed](https://nodejs.org/en/download/package-manager/)).

> Note: JavaScript tests depend on [jsdom](https://github.com/tmpvar/jsdom) library which requires Node.js 4 or newer.
Using of Node.js 6 or 7 is more preferable.

- run `php build/build dev/app basic <fork>` to clone the basic app and install composer dependencies for the basic app.
  `<fork>` is URL of your repository fork such as `git@github.com:my_nickname/yii2-app-basic.git`. If you are core framework contributor you may skip specifying fork.
  This command will install foreign composer packages as normal but will link the yii2 repo to
  the currently checked out repo, so you have one instance of all the code installed.
  
  Do the same for the advanced app if needed: `php build/build dev/app advanced <fork>`.
  
  This command will also be used to update dependencies, it runs `composer update` internally.

> Note: The default git repository Urls clone from github via SSH, you may add the `--useHttp` flag to the `build` command
> to use HTTPs instead.

**Now you have a working playground for hacking on Yii 2.**

The following steps are optional.

### Unit tests

You can execute unit tests by running `phpunit` in the repo root directory. If you do not have phpunit installed globally
you can run `php vendor/bin/phpunit` or `vendor/bin/phpunit.bat` in case of execution from the Windows OS.

Some tests require additional databases to be set up and configured. You can create `tests/data/config.local.php` to override
settings that are configured in `tests/data/config.php`.

You may limit the tests to a group of tests you are working on e.g. to run only tests for the validators and redis
`phpunit --group=validators,redis`. You get the list of available groups by running `phpunit --list-groups`.

You can execute JavaScript unit tests by running `npm test` in the repo root directory.

### Extensions

To work on extensions you have to clone the extension repository. We have created a command that can do this for you:

```
php build/build dev/ext <extension-name> <fork>
```

where `<extension-name>` is the name of the extension, e.g. `redis` and `<fork>` is URL of your extension fork such as `git@github.com:my_nickname/yii2-redis.git`. If you are core framework contributor you may skip specifying fork.

If you want to test the extension in one of the application templates, just add it to the `composer.json` of the application as you would
normally do e.g. add `"yiisoft/yii2-redis": "~2.0.0"` to the `require` section of the basic app.
Running `php build/build dev/app basic <fork>` will install the extension and its dependencies and create
a symlink to `extensions/redis` so you are not working in the composer vendor dir but in the yii2 repository directly.

> Note: The default git repository Urls clone from github via SSH, you may add the `--useHttp` flag to the `build` command
> to use HTTPs instead.


Working on bugs and features
----------------------------

Having prepared your develop environment as explained above you can now start working on the feature or bugfix.

### 1. Make sure there is an issue created for the thing you are working on if it requires significant effort to fix

All new features and bug fixes should have an associated issue to provide a single point of reference for discussion
and documentation. Take a few minutes to look through the existing issue list for one that matches the contribution you
intend to make. If you find one already on the issue list, then please leave a comment on that issue indicating you
intend to work on that item. If you do not find an existing issue matching what you intend to work on, please
[open a new issue](report-an-issue.md) or create a pull request directly if it is straightforward fix. This will allow the team to
review your suggestion, and provide appropriate feedback along the way.

> For small changes or documentation issues or straightforward fixes, you don't need to create an issue, a pull request is enough in this case.

### 2. Pull the latest code from the main Yii branch

```
git pull upstream
```

You should start at this point for every new contribution to make sure you are working on the latest code.

### 3. Create a new branch for your feature based on the current Yii master branch

> That's very important since you will not be able to submit more than one pull request from your account if you'll
  use master.

Each separate bug fix or change should go in its own branch. Branch names should be descriptive and start with
the number of the issue that your code relates to. If you aren't fixing any particular issue, just skip number.
For example:

```
git checkout upstream/master
git checkout -b 999-name-of-your-branch-goes-here
```

### 4. Do your magic, write your code

Make sure it works :)

Unit tests are always welcome. Tested and well covered code greatly simplifies the task of checking your contributions.
Failing unit tests as issue description are also accepted.

### 5. Update the CHANGELOG

Edit the CHANGELOG file to include your change, you should insert this at the top of the file under the
first heading (the version that is currently under development), the line in the change log should look like one of the following:

```
Bug #999: a description of the bug fix (Your Name)
Enh #999: a description of the enhancement (Your Name)
```

`#999` is the issue number that the `Bug` or `Enh` is referring to.
The changelog should be grouped by type (`Bug`,`Enh`) and ordered by issue number.

For very small fixes, e.g. typos and documentation changes, there is no need to update the CHANGELOG.

### 6. Commit your changes

add the files/changes you want to commit to the [staging area](https://git.github.io/git-reference/basic/#add) with

```
git add path/to/my/file.php
```

You can use the `-p` option to select the changes you want to have in your commit.

Commit your changes with a descriptive commit message. Make sure to mention the ticket number with `#XXX` so GitHub will
automatically link your commit with the ticket:

```
git commit -m "A brief description of this change which fixes #999 goes here"
```

### 7. Pull the latest Yii code from upstream into your branch

```
git pull upstream master
```

This ensures you have the latest code in your branch before you open your pull request. If there are any merge conflicts,
you should fix them now and commit the changes again. This ensures that it's easy for the Yii team to merge your changes
with one click.

### 8. Having resolved any conflicts, push your code to GitHub

```
git push -u origin 999-name-of-your-branch-goes-here
```

The `-u` parameter ensures that your branch will now automatically push and pull from the GitHub branch. That means
if you type `git push` the next time it will know where to push to. This is useful if you want to later add more commits
to the pull request.

### 9. Open a [pull request](https://help.github.com/articles/creating-a-pull-request-from-a-fork/) against upstream.

Go to your repository on GitHub and click "Pull Request", choose your branch on the right and enter some more details
in the comment box. To link the pull request to the issue put anywhere in the pull comment `#999` where 999 is the
issue number.

> Note that each pull-request should fix a single change. For multiple, unrelated changes, please open multiple pull requests.

### 10. Someone will review your code

Someone will review your code, and you might be asked to make some changes, if so go to step #6 (you don't need to open
another pull request if your current one is still open). If your code is accepted it will be merged into the main branch
and become part of the next Yii release. If not, don't be disheartened, different people need different features and Yii
can't be everything to everyone, your code will still be available on GitHub as a reference for people who need it.

### 11. Cleaning it up

After your code was either accepted or declined you can delete branches you've worked with from your local repository
and `origin`.

```
git checkout master
git branch -D 999-name-of-your-branch-goes-here
git push origin --delete 999-name-of-your-branch-goes-here
```

### Note:

To detect regressions early every merge to the Yii codebase on GitHub will be picked up by
[Travis CI](https://travis-ci.com) for an automated testrun. As core team doesn't wish to overtax this service,
[`[ci skip]`](https://docs.travis-ci.com/user/customizing-the-build/#Skipping-a-build) will be included to the merge description if
the pull request:

* affect javascript, css or image files only,
* updates the documentation,
* modify fixed strings only (e.g. translation updates)

Doing so will save travis from commencing testruns on changes that are not covered by tests in the first place.

### Command overview (for advanced contributors)

```
git clone git@github.com:YOUR-GITHUB-USERNAME/yii2.git
git remote add upstream https://github.com/yiisoft/yii2.git
```

```
git fetch upstream
git checkout upstream/master
git checkout -b 999-name-of-your-branch-goes-here

/* do your magic, update changelog if needed */

git add path/to/my/file.php
git commit -m "A brief description of this change which fixes #999 goes here"
git pull upstream master
git push -u origin 999-name-of-your-branch-goes-here
```

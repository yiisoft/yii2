Git workflow for Yii 2 contributors
===================================

So you want to contribute to Yii? Great! But to increase the chances of your changes being accepted quickly, please
follow the following steps (the first 2 steps only need to be done the first time you contribute). If you are new to git
and github, you might want to first check out [github help](http://help.github.com/), [try git](https://try.github.com)
or learn something about [git internal data model](http://nfarina.com/post/9868516270/git-is-simpler).

### 1. [Fork](http://help.github.com/fork-a-repo/) the Yii repository on github and clone your fork to your development
environment

```
git clone git@github.com:YOUR-GITHUB-USERNAME/yii2.git
```

If you have trouble setting up GIT with GitHub in Linux, or are getting errors like "Permission Denied (publickey)",
then you must [setup your GIT installation to work with GitHub](http://help.github.com/linux-set-up-git/)

### 2. Add the main Yii repository as an additional git remote called "upstream"

Change to the directory where you cloned Yii, normally, "yii2". Then enter the following command:

```
git remote add upstream git://github.com/yiisoft/yii2.git
```

### 3. Make sure there is an issue created for the thing you are working on if it requires significant effort to fix

All new features and bug fixes should have an associated issue to provide a single point of reference for discussion
and documentation. Take a few minutes to look through the existing issue list for one that matches the contribution you
intend to make. If you find one already on the issue list, then please leave a comment on that issue indicating you
intend to work on that item. If you do not find an existing issue matching what you intend to work on, please open a
new issue for your item or create a pull request directly if it is straightforward fix. This will allow the team to review your suggestion, and provide appropriate feedback along
the way.

> For small changes or documentation issues or straightforward fixes, you don't need to create an issue, a pull request is enough in this case.

### 4. Fetch the latest code from the main Yii branch

```
git fetch upstream
```

You should start at this point for every new contribution to make sure you are working on the latest code.

### 5. Create a new branch for your feature based on the current Yii master branch

> That's very important since you will not be able to submit more than one pull request from your account if you'll
  use master.

Each separate bug fix or change should go in its own branch. Branch names should be descriptive and start with
the number of the issue that your code relates to. If you aren't fixing any particular issue, just skip number.
For example:

```
git checkout upstream/master
git checkout -b 999-name-of-your-branch-goes-here
```

### 6. Do your magic, write your code

Make sure it works :)

Unit tests are always welcome. Tested and well covered code greatly simplifies the task of checking your contributions.
Failing unit tests as issue description are also accepted.

### 7. Update the CHANGELOG

Edit the CHANGELOG file to include your change, you should insert this at the top of the file under the
"Work in progress" heading, the line in the change log should look like one of the following:

```
Bug #999: a description of the bug fix (Your Name)
Enh #999: a description of the enhancement (Your Name)
```

`#999` is the issue number that the `Bug` or `Enh` is referring to.
The changelog should be grouped by type (`Bug`,`Enh`) and ordered by issue number.

For very small fixes, e.g. typos and documentation changes, there is no need to update the CHANGELOG.

### 8. Commit your changes

add the files/changes you want to commit to the [staging area](http://gitref.org/basic/#add) with

```
git add path/to/my/file.php
```

You can use the `-p` option to select the changes you want to have in your commit.

Commit your changes with a descriptive commit message. Make sure to mention the ticket number with `#XXX` so github will
automatically link your commit with the ticket:

```
git commit -m "A brief description of this change which fixes #42 goes here"
```

### 9. Pull the latest Yii code from upstream into your branch

```
git pull upstream master
```

This ensures you have the latest code in your branch before you open your pull request. If there are any merge conflicts,
you should fix them now and commit the changes again. This ensures that it's easy for the Yii team to merge your changes
with one click.

### 10. Having resolved any conflicts, push your code to github

```
git push -u origin 999-name-of-your-branch-goes-here
```

The `-u` parameter ensures that your branch will now automatically push and pull from the github branch. That means
if you type `git push` the next time it will know where to push to.

### 11. Open a [pull request](http://help.github.com/send-pull-requests/) against upstream.

Go to your repository on github and click "Pull Request", choose your branch on the right and enter some more details
in the comment box. To link the pull request to the issue put anywhere in the pull comment `#999` where 999 is the
issue number.

> Note that each pull-request should fix a single change.

### 12. Someone will review your code

Someone will review your code, and you might be asked to make some changes, if so go to step #6 (you don't need to open
another pull request if your current one is still open). If your code is accepted it will be merged into the main branch
and become part of the next Yii release. If not, don't be disheartened, different people need different features and Yii
can't be everything to everyone, your code will still be available on github as a reference for people who need it.

### 13. Cleaning it up

After your code was either accepted or declined you can delete branches you've worked with from your local repository
and `origin`.

```
git checkout master
git branch -D 999-name-of-your-branch-goes-here
git push origin --delete 999-name-of-your-branch-goes-here
```

### Note:

To detect regressions early every merge to the Yii codebase on github will be picked up by
[Travis CI](http://travis-ci.org) for an automated testrun. As core team doesn't wish to overtax this service,
[`[ci skip]`](http://about.travis-ci.org/docs/user/how-to-skip-a-build/) will be included to the merge description if
the pull request:

* affect javascript, css or image files only,
* updates the documentation,
* modify fixed strings only (e.g. translation updates)

Doing so will save travis from commencing testruns on changes that are not covered by tests in the first place.

### Command overview (for advanced contributors)

```
git clone git@github.com:YOUR-GITHUB-USERNAME/yii2.git
git remote add upstream git://github.com/yiisoft/yii2.git
```

```
git fetch upstream
git checkout upstream/master
git checkout -b 999-name-of-your-branch-goes-here

/* do your magic, update changelog if needed */

git add path/to/my/file.php
git commit -m "A brief description of this change which fixes #42 goes here"
git pull upstream master
git push -u origin 999-name-of-your-branch-goes-here
```

Робота з Git для співучасників Yii 2
====================================

Ви бажаєте прийняти участь в розробці Yii? Чудово! Але, щоб підвищити шанси швидкого прийняття ваших змін, будь ласка,
дотримуйтесь наступних кроків. Якщо ви новачок у Git та GitHub, спершу можете ознайомитись із
[довідкою GitHub](http://help.github.com/), [тренером Git](https://try.github.com)
або почитати книгу [Магія Git](http://www-cs-students.stanford.edu/~blynn/gitmagic/intl/uk/)
чи [Розділ Git у Вікіпідручнику](http://uk.wikibooks.org/wiki/Git), щоб краще зрозуміти внутрішню структуру Git.

Підготовка вашого середовища розробки
-------------------------------------

Наступні кроки створять середовище розробки для Yii, яке ви зможете використовувати для роботи
над основним кодом фреймворку Yii. Ці кроки необхідні лише тоді, коли ви вперше долучаєтесь до співпраці.

### 1. [Створіть форк](http://help.github.com/fork-a-repo/) репозиторію Yii на GitHub та клонуйте свій форк у своє середовище розробки

```
git clone git@github.com:ВАШЕ-ІМ’Я-НА-GITHUB/yii2.git
```

Якщо у вас виникли проблеми із роботою Git з GitHub в Linux, або ви отримали помилку заборони доступу "Permission Denied (publickey)",
то вам необхідно [налаштувати Git для роботи з GitHub](https://help.github.com/articles/set-up-git/#platform-linux).

### 2. Додайте головний репозиторій Yii як додатковий віддалений репозиторій із назвою "upstream"

Перейдіть у директорію, в яку ви клонували Yii, зазвичай "yii2". Потім виконайте наведену команду:

```
git remote add upstream git://github.com/yiisoft/yii2.git
```

### 3. Підготуйте середовище тестування

Наступні кроки не обов'язкові, якщо ви хочете процювати лише над перекладом або документацією.

- виконайте `composer update` для встановлення залежностей (припускається, що ви маєте [глобально встановлений composer](https://getcomposer.org/doc/00-intro.md#globally)).
- виконайте `php build/build dev/app basic` для клонування базового додатку та встановлення його залежностей.
  Ця команда встановить сторонні пакунки composer як завжди, а також створить посилання з репозиторію yii2
  на поточний репозиторій. Таким чином ви будете мати один екземпляр встановленого коду.
  
  Якщо необхідно зробіть те ж саме для розширеного додатку: `php build/build dev/app advanced`.
  
  Ця команда може використовуватись для оновлення залежностей, вона викликає `composer update` в процесі виконання.

**Тепер ви маєте робочий майданчик для експериментів з Yii 2.**

Наступні кроки не обов’язкові.

### Модульні тести

Ви можете виконати модульні тести запустивши `phpunit` у кореневій директорії репозиторію. Якщо у вас phpunit не встановлений глобально,
ви можете запускати `php vendor/bin/phpunit`.

Деякі тести потребують додатково встановлених та налаштованих баз даних. Ви можете створити `tests/data/config.local.php` для перевизначення
налаштувань сконфігурованих у `tests/data/config.php`.

Можливо обмежити тести групою тестів, що покривають область над якою ви працюєте, наприклад, щоб запустити тести для валідаторів
та redis виконайте `phpunit --group=validators,redis`. Для отримання списку доступних груп виконайте `phpunit --list-groups`.

### Розширення

Для роботи з розширеннями необхідно клонувати відповідні репозиторії. Наступна команда зробить це для вас:

```
php build/build dev/ext <extension-name>
```    

де `<extension-name>` є назвою розширення, наприклад `redis`.

Якщо бажаєте протестувати розширення в одному із шаблонів додатку, просто додайте його до `composer.json` додатку, як ви будете
робити зазвичай. Наприклад, додайте `"yiisoft/yii2-redis": "*"` до секції `require` базового додатку.
Команда `php build/build dev/app basic` встановить розширення та його залежності й створить
символьне посилання на `extensions/redis`, тому ви можете працювати безпосередньо в директорії репозиторію yii2,
а не у специфічній для composer директорії vendor.


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

### 2. Fetch the latest code from the main Yii branch

```
git fetch upstream
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
"Work in progress" heading, the line in the change log should look like one of the following:

```
Bug #999: a description of the bug fix (Your Name)
Enh #999: a description of the enhancement (Your Name)
```

`#999` is the issue number that the `Bug` or `Enh` is referring to.
The changelog should be grouped by type (`Bug`,`Enh`) and ordered by issue number.

For very small fixes, e.g. typos and documentation changes, there is no need to update the CHANGELOG.

### 6. Commit your changes

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

### 7. Pull the latest Yii code from upstream into your branch

```
git pull upstream master
```

This ensures you have the latest code in your branch before you open your pull request. If there are any merge conflicts,
you should fix them now and commit the changes again. This ensures that it's easy for the Yii team to merge your changes
with one click.

### 8. Having resolved any conflicts, push your code to github

```
git push -u origin 999-name-of-your-branch-goes-here
```

The `-u` parameter ensures that your branch will now automatically push and pull from the github branch. That means
if you type `git push` the next time it will know where to push to. This is useful if you want to later add more commits
to the pull request.

### 9. Open a [pull request](http://help.github.com/send-pull-requests/) against upstream.

Go to your repository on github and click "Pull Request", choose your branch on the right and enter some more details
in the comment box. To link the pull request to the issue put anywhere in the pull comment `#999` where 999 is the
issue number.

> Note that each pull-request should fix a single change. For multiple, unrelated changes, please open multiple pull requests.

### 10. Someone will review your code

Someone will review your code, and you might be asked to make some changes, if so go to step #6 (you don't need to open
another pull request if your current one is still open). If your code is accepted it will be merged into the main branch
and become part of the next Yii release. If not, don't be disheartened, different people need different features and Yii
can't be everything to everyone, your code will still be available on github as a reference for people who need it.

### 11. Cleaning it up

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

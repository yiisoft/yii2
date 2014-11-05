<!-----del---Advanced application template-->
Шаблон сложного(Advanced) приложения
=============================

<!-----del--- Note: This section is under development.-->
> Обратите внимание: Данная глава находится в разработке.

<!-----del---This template is for large projects developed in teams where the backend is divided from the frontend, application is deployed to multiple servers etc. This application template also goes a bit further regarding features and provides essential database, signup and password restore out of the box.-->
Этот шаблон предназначен для крупных проектов разрабатываемых в командах где приложение администратора(backend)  отделено от приложения пользователя(frontend), прилжения располагаются на нескольких разных серверах. Этот шаблон приложения имеет немного больше возможностей и содержит необходимую для этого БД, позволяет регистрироваться и восстанавливать пароль без дополнительных настроект. 

<!-----del---Installation-->
Установка
------------

<!-----del---### Install via Composer-->
###Установка при помощи Composer'a

<!-----del---If you do not have [Composer[Кампоузер]](http://getcomposer.org/), follow the instructions in the [Installing Yii](start-installation.md#installing-via-composer) section to install it.-->
Если у Вас нет [Composer'a[Кампоузер]](http://getcomposer.org/), следуйте инструкциям в разделе [Установка Yii](start-installation.md#installing-via-composer) для его установки.

<!-----del---With Composer installed, you can then install the application using the following commands:-->
Если Composer установлен, Вы можете установить приложение использую следующие команды:

    composer global require "fxp/composer-asset-plugin:1.0.0-beta3"
    composer create-project --prefer-dist yiisoft/yii2-app-advanced yii-application

<!-----del---The first command installs the [composer asset plugin](https://github.com/francoispluchino/composer-asset-plugin/) which allows managing bower and npm package dependencies through Composer. You only need to run this command once for all. The second command installs the advanced application in a directory named `yii-application`. You can choose a different directory name if you want.-->
Первая команда установит плагин [composer asset plugin](https://github.com/francoispluchino/composer-asset-plugin/), который позволит управлять Composer'у зависимостями из других пакетных менеджеров (bower и npm). Нужно всего один раз выполнить эту команду и эти возможности будут доступны всегда.
Вторая команда установит `сложное приложение` в директорию `yii-application`. Вы можете выбрать другое имя директория если пожелаете.


<!-----del---Getting started-->
Начало работы
---------------

<!-----del---After you install the application, you have to conduct the following steps to initialize the installed application. You only need to do these once for all.-->
После установки приложения, Вам нужно выполнить следующие действия, чтобы инициализировать установленное приложение. Вам нужно сделать это только один раз для всех последующих приложений.

<!-----del---1. Execute the `init` command and select `dev` as environment.-->
1. Выполните команду `init` и выберите окружение `dev`.

    ```
    php /path/to/yii-application/init
    ```
    
    можно также вручную запустить пакетный файл (/path/to/yii-application/init.bat <для Windows> или /path/to/yii-application/init <для Linux>
    в командном окне указать `0` и тем самым выбрать окружение 'dev' и подтвердить запись изменений `yes`.
    
<!-----del---    Otherwise, in production execute `init` in non-interactive mode.-->
    Также можно выполнить эту команду "втихую" без интерактивного режима.

    ```
    php /path/to/yii-application/init --env=Production overwrite=All
    ```

<!-----del---2. Create a new database and adjust the `components.db` configuration in `common/config/main-local.php` accordingly.-->
2. Создайте новую базу данных и внесите соответствующие изменения в файл `common/config/main-local.php` в разделе конфигурации `components.db`. 
<!-----del---3. Apply migrations with console command `yii migrate`.-->
3. Примените миграции при помощи консольной команды  `yii migrate` (в консоле(ОС) выполняем `/path/to/yii-application/yii migrate` и на вопрос `Apply the above migration? (yes|no) [no]:` пишем `yes`)
<!-----del---4. Set document roots of your web server:-->
4. Настройте на вебсервере URL и корневые директории для двух сайтов:

<!-----del---- for frontend `/path/to/yii-application/frontend/web/` and using the URL `http://frontend/`-->
<!-----del---- for backend `/path/to/yii-application/backend/web/` and using the URL `http://backend/`-->
- для приложения frontend директория `/path/to/yii-application/frontend/web/` и используйте URL например такой `http://yourdomain/frontend/`
- для приложения backend директория `/path/to/yii-application/frontend/web/` и используйте URL например такой `http://yourdomain/backend/`
(примечание: я просто поместил директорию `yii-application` в веб директорию моего вебсервера и сайт стал доступен по  URL http://127.0.0.1/yii-application/frontend/web/ )

<!-----del---Directory structure-->
Структура директорий
-------------------

<!-----del---The root directory contains the following subdirectories:-->
Корневая директория содерит следующие поддиректории:

<!-----del--- `backend` - backend web application.-->
<!-----del--- `common` - files common to all applications.-->
<!-----del--- `console` - console application.-->
<!-----del--- `environments` - environment configs.-->
<!-----del--- `frontend` - frontend web application..-->
- `backend` - веб-приложение backend(административное).
- `common` - общие файлы для всех приложений.
- `console` - приложение для консоли.
- `environments` - настройки окружения.
- `frontend` - веб-приложение frontend(пользовательское).

<!-----del---Root directory contains a set of files.-->
Корневая директория содержит следующий список файлов.

<!-----del---- `.gitignore` contains a list of directories ignored by git version system. If you need something never get to your source-->
<!-----del---  code repository, add it there.-->
<!-----del---- `composer.json` - Composer config described in detail below.-->
<!-----del---- `init` - initialization script described in "Composer config described in detail below".-->
<!-----del---- `init.bat` - same for Windows.-->
<!-----del---- `LICENSE.md` - license info. Put your project license there. Especially when opensourcing.-->
<!-----del---- `README.md` - basic info about installing template. Consider replacing it with information about your project and its-->
<!-----del---  installation.-->
<!-----del---- `requirements.php` - Yii requirements checker.-->
<!-----del---- `yii` - console application bootstrap.-->
<!-----del---- `yii.bat` - same for Windows.-->
- `.gitignore` содержит список директорий игнорируемых системой контроля версий git. Если Вам необходимо, чтобы git не добавлял в репозиторий какие-то файлы или папки, то добавьте инструкцию в этот файл.
- `composer.json` - Конфигурация Composer'a - подробно описана ниже в главе `Настройка Composer'a`.
- `init` - скрипт инициализации (подробно в главе `Настройка Composer'a` ниже) для Linux.
- `init.bat` - скрипт инициализации - такой же командный файл для Windows.
- `LICENSE.md` - информация о лицензии. Разместите лицензию вашего проекта в нем. Особенно когда код доступен всем ( opensourcing).
- `README.md` - основная информация об установки шаблона. Можете разместить в нем информацию о вашем проекте и его настройке.
- `requirements.php` - проверка соответствия требованиям Yii.
- `yii` - консольное приложение начальной загрузки (bootstrap) для Linux.
- `yii.bat` - такое же приложение для Windows.

<!-----del---Predefined path aliases-->
Предустановленные псевдонимы путей
-----------------------

<!-----del---- `@yii` - framework directory.-->
<!-----del---- `@app` - base path of currently running application.-->
<!-----del---- `@common` - common directory.-->
<!-----del---- `@frontend` - frontend web application directory.-->
<!-----del---- `@backend` - backend web application directory.-->
<!-----del---- `@console` - console directory.-->
<!-----del---- `@runtime` - runtime directory of currently running web application.-->
<!-----del---- `@vendor` - Composer vendor directory.-->
<!-----del---- `@bower` - vendor directory that contains the [bower packages](http://bower.io/).-->
<!-----del---- `@npm` - vendor directory that contains [npm packages](https://www.npmjs.org/).-->
<!-----del---- `@web` - base URL of currently running web application.-->
<!-----del---- `@webroot` - web root directory of currently running web application.-->
- `@yii` - директория фрэймворка.
- `@app` - корневая директория исполняемого приложения.
- `@common` - директория common.
- `@frontend` - директория веб-приложения frontend.
- `@backend` - директория веб-приложения backend.
- `@console` - директория console.
- `@runtime` - директория runtime исполняемого приложения.
- `@vendor` - директория vendor, содержащая пакеты загруженые Composer'ом.
- `@bower` - директория vendor, содержащая пакеты [bower packages](http://bower.io/).
- `@npm` - директория vendor, содержащая пакеты [npm packages](https://www.npmjs.org/).
- `@web` - основной URL исполняемого веб-приложения.
- `@webroot` - корневая веб-директория исполняемого веб-приложения.

<!-----del---The aliases specific to the directory structure of the advanced application-->
<!-----del---(`@common`,  `@frontend`, `@backend`, and `@console`) are defined in `common/config/bootstrap.php`.-->
Псевдонимы характерные для структуры директорий сложного(advanced) приложения (`@common`,  `@frontend`, `@backend` и `@console`) заданы в `common/config/bootstrap.php`.

<!-----del---Applications-->
Приложения
------------

<!-----del---There are three applications in advanced template: frontend, backend and console. Frontend is typically what is presented-->
<!-----del---to end user, the project itself. Backend is admin panel, analytics and such functionality. Console is typically used for-->
<!-----del---cron jobs and low-level server management. Also it's used during application deployment and handles migrations and assets.-->
В сложном(advanced) шаблоне размещается три приложения: frontend, backend and console. Frontend это та часть приложения которое обеспечивает взаимодействие системы с конечным пользователем проекта.  Backend это административная панель, аналитика и прочий подобный функционал. Console обычно используется для выполнения заданий по рассписанию(cron) и низкоуровневого управления сервером.

<!-----del---There's also a `common` directory that contains files used by more than one application. For example, `User` model. frontend and backend are both web applications and both contain the `web` directory. That's the webroot you should point your web server to.-->
Также есть директория `common`, которая содержит файлы используемые более чем одним приложением. Например, модель `User`. Веб-приложения Frontend и backend оба содержать директория `web`. Это корневая папка сайтов которую вы должны настроить в вебсервере. 
<!-----del---Each application has its own namespace and alias corresponding to its name. Same applies to common directory.-->
У каждого приложения есть собственное пространство имен (namespace) и псевдоним соответствующий его названию. Это же справедливо и для общей директории `common`. 

<!-----del---Configuration and environments-->
Конфигурация и окружение
------------------------------

<!-----del---There are multiple problems with a typical approach to configuration:-->
Существует множество проблем при обычном подходе к настройке конфигурации:

<!-----del---- Each team member has its own configuration options. Committing such config will affect other team members.-->
<!-----del---- Production database password and API keys should not end up in the repository.-->
<!-----del---- There are multiple server environments: development, testing, production. Each should have its own configuration.-->
<!-----del---- Defining all configuration options for each case is very repetitive and takes too much time to maintain.-->
Каждый участник команды разработки имеет свою собственную конфигурацию. Изменение конфигурации в общем репозитории повлияет на настройки других участников команды.
Пароль от эксплуатационной БД и API ключи не должны оказаться в хранилище.
Существует много серверных режимов(environments): development(разработка), testing(тестирование), production(эксплуатация). Каждый режим должен иметь свою собственную конфигурацию.
Настройка всех параметров конфигурации для каждого случая использования очень однотипна и отнимает много времени для ее поддержки.


<!-----del---In order to solve these issues Yii introduces a simple environments concept. Each environment is represented by a set of files under the `environments` directory. The `init` command is used to switch between these. What it really does is copy everything from the environment directory over to the root directory where all applications are.-->
Для решения этих проблем Yii вводит простую концепцию окружений. Каждое окружение(режим) представлено набором файлов в директории `environments`. Команда `init` используется для переключения между ними(режимами). Она просто копирует все файлы из директории `environments` в корневую директорию, где находятся все приложения.

<!-----del---Typically environment contains application bootstrap files such as `index.php` and config files suffixed with `-local.php`. These are added to `.gitignore` and never added to source code repository.-->
Обычно окружение содержит файлы первоначальной загрузки приложения такие как `index.php` и файлы конфигурации, имена которых дополнены с суфиксами `-local.php`. Они добавлены в файл `.gitignore` и никогда не попадут в хранилище кода.

<!-----del---In order to avoid duplication configurations are overriding each other. For example, the frontend reads configuration in the following order:-->
Чтобы избежать дублирования конфигураций они перекрывают друг друга. Например, приложение frontend считывает конфигурацию из файлов в следующем порядке:

- `common/config/main.php`
- `common/config/main-local.php`
- `frontend/config/main.php`
- `frontend/config/main-local.php`

<!-----del---Parameters are read in the following order:-->
Парамтры считываются в следующем порядке:

- `common/config/params.php`
- `common/config/params-local.php`
- `frontend/config/params.php`
- `frontend/config/params-local.php`

<!-----del---The later config file overrides the former.-->
Значения из последующего конфигурационного файла перекрывают аналогичные значения из предыдущих конфигурационных файлов.

<!-----del---Here's the full scheme:-->
Полная схема:
<!-----del---(!!!переводить изображение не требуется, нужно только скопировать его папку /images)-->
![Конфигурации сложного(advanced) приложения](images/advanced-app-configs.png)

<!-----del---Configuring Composer-->
Настройка Composer'a
--------------------

<!-----del---After the application template is installed it's a good idea to adjust default `composer.json` that can be found in the root directory:-->
После того как шаблон приложения установлен, будет хорошей идеей настроить дефолтный `composer.json` который находится в корневой директории проекта:

```json
{
    "name": "yiisoft/yii2-app-advanced",
    "description": "Yii 2 Advanced Application Template",
    "keywords": ["yii", "framework", "advanced", "application template"],
    "homepage": "http://www.yiiframework.com/",
    "type": "project",
    "license": "BSD-3-Clause",
    "support": {
        "issues": "https://github.com/yiisoft/yii2/issues?state=open",
        "forum": "http://www.yiiframework.com/forum/",
        "wiki": "http://www.yiiframework.com/wiki/",
        "irc": "irc://irc.freenode.net/yii",
        "source": "https://github.com/yiisoft/yii2"
    },
    "minimum-stability": "dev",
    "require": {
        "php": ">=5.4.0",
        "yiisoft/yii2": "*",
        "yiisoft/yii2-swiftmailer": "*",
        "yiisoft/yii2-bootstrap": "*",
        "yiisoft/yii2-debug": "*",
        "yiisoft/yii2-gii": "*"
    },
    "scripts": {
        "post-create-project-cmd": [
            "yii\\composer\\Installer::setPermission"
        ]
    },
    "extra": {
        "writable": [
            "backend/runtime",
            "backend/web/assets",

            "console/runtime",
            "console/migrations",

            "frontend/runtime",
            "frontend/web/assets"
        ]
    }
}
```

<!-----del---First we're updating basic information. Change `name`, `description`, `keywords`, `homepage` and `support` to match
your project.-->
Во-первых мы обновляем основную информацию. Меняем значения параметро `name`, `description`, `keywords`, `homepage` и `support` на соответствующие вашему проекту.

<!-----del---Now the interesting part. You can add more packages your application needs to the `require` section.-->
<!-----del---All these packages are coming from [packagist.org](https://packagist.org/) so feel free to browse the website for useful code.-->
А сейчас интересная часть. Вы можете добавить больше пакетов необходимых для вашего приложения в раздел `require`.
Все эти пакеты загрузятся с [packagist.org](https://packagist.org/), так что не стесняйтесь полазить по этому сайту в поисках полезного кода. 

<!-----del---After your `composer.json` is changed you can run `composer update --prefer-dist`, wait till packages are downloaded and installed and then just use them. Autoloading of classes will be handled automatically.-->
После того как ваш `composer.json` настроен, Вы можете выполнить в консоле команду `composer update --prefer-dist`, подождать пока требуемые пакеты загрузятся и установятся, и просто начать их использовать. Автозагрузка классов из этих пакетов будет осуществляться автоматически. 

<!-----del---Creating links from backend to frontend-->
Создание ссылок из backend'а в frontend
---------------------------------------

<!-----del---Often it's required to create links from the backend application to the frontend application. Since the frontend application may contain its own URL manager rules you need to duplicate that for the backend application by naming it differently:-->
Часто приходиться создавать ссылки из приложения backend в приложение frontend. Так как frontend приложение может содержать свои собственные правила для URL менеджера, то вам придется продублировать их в конфигурации backend приложения в отдельной секции с отличающимся от основного блока правил(urlManager) названием (например: 'urlManagerFrontend'): 

```php
return [
    'components' => [
        'urlManager' => [
            // это ваши обычные правила URL менеджера в конфигурации backend приложения
        ],
        'urlManagerFrontend' => [
            // а это ваши правила URL менеджера взятые из конфигурации frontend приложения
        ],

    ],
];
```

<!-----del---After it is done, you can get an URL pointing to frontend like the following:-->
После того, как это будет сделано, вы сможете получить URL, указывающий на Frontend приложение следующим способом:
```php
echo Yii::$app->urlManagerFrontend->createUrl(...);
```

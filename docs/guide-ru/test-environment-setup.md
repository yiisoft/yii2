Настройка тестового окружения
=============================

> Note: Данный раздел находится в разработке.

Yii 2 официально поддерживает интеграцию с фреймворком для тестирования [`Codeception`](https://github.com/Codeception/Codeception),
который позволяет вам проводить следующие типы тестов:

- [Модульное тестирование](test-unit.md) - проверяет что отдельный модуль кода работает верно;
- [Функциональное тестирование](test-functional.md) - проверяет пользовательские сценарии через эмуляцию браузера;
- [Приёмочное тестирование](test-acceptance.md) - проверяет пользовательские сценарии в браузере.

Все три типа тестов представлены в шаблонах проектов
[`yii2-basic`](https://github.com/yiisoft/yii2-app-basic) и
[`yii2-advanced`](https://github.com/yiisoft/yii2-app-advanced).

Для того, чтобы запустить тесты, необходимо установить [Codeception](https://github.com/Codeception/Codeception).
Сделать это можно как локально, то есть только для текущего проекта, так и глобально для компьютера разработчика.

Для локальной установки используйте следующие команды:

```
composer require "codeception/codeception=2.1.*"
composer require "codeception/specify=*"
composer require "codeception/verify=*"
```

Для глобальной установки необходимо добавить директиву `global`:

```
composer global require "codeception/codeception=2.1.*"
composer global require "codeception/specify=*"
composer global require "codeception/verify=*"
```

Если вы никогда не пользовались Composer для установки глобальных пакетов, запустите `composer global status`.
На выходе вы должны получить:

```
Changed current directory to <directory>
```

Затем `<directory>/vendor/bin` добавьте в переменную окружения `PATH`. После этого можно использовать `codecept` глобально
из командной строки.

> Note: глобальная установка позволяет вам использовать Codeception для всех проектов на компьютере разработчика
  путём запуска команды `codecept` без указания пути. Тем не менее, данный подход может не подойти. К примеру, в двух 
  разных проектах может потребоваться установить разные версии Codeception. Для простоты все команды в разделах про
  тестирование используются так, будто Codeception установлен глобально.
  
### Настройка веб-сервера Apache

Если вы используете Apache и настроили его как описано в разделе «[Установка Yii](start-installation.md)», то для тестов вам необходимо создать отдельный виртуальный хост который будет работать с той же папкой, но использовать входной скрипт `index-test.php`:

```
<VirtualHost *:80>
    DocumentRoot "path/to/basic/web"
    ServerName mysite-test
    <Directory "path/to/basic/web">
        Order Allow,Deny
        Allow from all
        AddDefaultCharset utf-8
        DirectoryIndex index-test.php
        RewriteEngine on
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . index-test.php
    </Directory>
</VirtualHost>
```

Так мы укажем веб серверу перенаправлять все запросы на скрипт `index-test.php`.
> Note: Обратите внимание, что здесь мы указываем параметр `DirectoryIndex`, помимо тех параметров, которые были указаны для первого хоста. Это сделано с той целью, чтобы при обращении к главной странице по адресу `mysite-test` также использовался бы скрипт `index-test.php`.

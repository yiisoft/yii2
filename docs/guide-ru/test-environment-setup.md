Настройка тестового окружения
=============================

> Примечание: Данный раздел находится в разработке.

Yii 2 официально поддерживает интеграцию с фреймворком для тестирования [`Codeception`](https://github.com/Codeception/Codeception),
который позволяет вам проводить следующие типы тестов:

- [Модульное тестирование](test-unit.md) - проверяет что отдельный модуль кода работает верно;
- [Функциональное тестирование](test-functional.md) - проверяет пользовательские сценарии через эмуляцию браузера;
- [Приёмочное тестирование](test-acceptance.md) - проверяет пользовательские сценарии в браузере.

Все три типа тестов представлены в шаблонах проектов
[`yii2-basic`](https://github.com/yiisoft/yii2/tree/master/apps/basic) и
[`yii2-advanced`](https://github.com/yiisoft/yii2/tree/master/apps/advanced).

Для того, чтобы запустить тесты необходимо установить [Codeception](https://github.com/Codeception/Codeception).
Сделать это можно как локально, то есть только для текущего проекта, так и глобально для компьютера разработчика.

Для локальной установки используйте следующие команды:

```
composer require "codeception/codeception=2.0.*"
composer require "codeception/specify=*"
composer require "codeception/verify=*"
```

Для глобальной установки необходимо добавить директиву `global`:

```
composer global require "codeception/codeception=2.0.*"
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

> Примечание: глобальная установка позволяет вам использовать Codeception для всех проектов на компьютере разработчика
  путём запуска команды `codecept` без указания пути. Тем не менее, данный подход может не подойти. К примеру, в двух 
  разных проектах может потребоваться установить разные версии Codeception. Для простоты все команды в разделах про
  тестирование используются так, будто Codeception установлен глобально.

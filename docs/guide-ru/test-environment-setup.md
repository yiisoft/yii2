Настройка тестового окружения
=============================

Yii 2 официально поддерживает интеграцию с фреймворком для тестирования [`Codeception`](https://github.com/Codeception/Codeception),
который позволяет вам проводить следующие типы тестов:

- [Модульное тестирование](test-unit.md) - проверяет что отдельный модуль кода работает верно;
- [Функциональное тестирование](test-functional.md) - проверяет пользовательские сценарии через эмуляцию браузера;
- [Приёмочное тестирование](test-acceptance.md) - проверяет пользовательские сценарии в браузере.

Все три типа тестов представлены в шаблонах проектов
[`yii2-basic`](https://github.com/yiisoft/yii2-app-basic) и
[`yii2-advanced`](https://github.com/yiisoft/yii2-app-advanced).

Codeception поставляется предустановленным как часть проектных шаблонов basic и advanced.
В случае, если вы не используете один из этих шаблонов, Codeception можно установить,
выполнив следующие консольные команды:

```
composer require --dev codeception/codeception
composer require --dev codeception/specify
composer require --dev codeception/verify
```

Модульные тесты
===============

> Note: Данный раздел находится в разработке.

Модульный тест проверяет что отдельный модуль кода работает верно. В ООП самым базовым модулем является класс. То есть
модульный тест проверяет все методы интерфейса класса. На вход подаются различные параметры и тест проверяет, что методы
возвращают ожидаемые значения. Модульные тесты обычно пишутся тем же, кто реализует тестируемый класс.

Модульное тестирование в Yii использует PHPUnit и, опционально, Codeception. Рекомендуется проверить его документацию:

- [Документация PHPUnit начиная с главы 2](https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html).
- [Codeception Unit Tests](https://codeception.com/docs/05-UnitTests).

Запуск тестов шаблонов проектов basic и advanced
------------------------------------------------

- [Инструкции для шаблона advanced](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/start-testing.md).  
- [Инструкции для шаблона basic](https://github.com/yiisoft/yii2-app-basic/blob/master/README.md#testing).

Модульные тесты фреймворка
--------------------------

Если вам необходимо запустить набор модульных тестов для самого Yii, прочитайте
"[Подготовка к разработке Yii 2](https://github.com/yiisoft/yii2/blob/master/docs/internals-ru/getting-started.md)".

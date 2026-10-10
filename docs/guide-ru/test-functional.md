Функциональные тесты
====================

Функциональный тест проверяет сценарии с точки зрения пользователя. Он похож на [приёмочный тест](test-acceptance.md),
но вместо взаимодействия через HTTP заполняет окружение (параметры POST и GET) и запускает экземпляр приложения
прямо из кода.

Функциональные тесты, как правило, быстрее приёмочных и предоставляют подробные stack trace при ошибках.
Рекомендуется отдавать им предпочтение, если только у вас нет специфической настройки веб-сервера или сложного UI
на JavaScript.

Функциональное тестирование реализуется с помощью фреймворка Codeception, который имеет отличную документацию:

- [Codeception for Yii framework](https://codeception.com/for/yii)
- [Codeception Functional Tests](https://codeception.com/docs/04-FunctionalTests)

## Запуск тестов в шаблонах проектов basic и advanced

Если вы начали с шаблона advanced, обратитесь к руководству по ["Тестированию"](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/start-testing.md) для получения более детальной информации о запуске тестов.

Если вы начали с шаблона basic, обратитесь к разделу ["Тестирование"](https://github.com/yiisoft/yii2-app-basic/blob/master/README.md#testing) в его README.

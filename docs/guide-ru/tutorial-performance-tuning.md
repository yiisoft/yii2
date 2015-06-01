Оптимизация производительности
==================

Существует много факторов, влияющих на производительность веб-приложения. *Какие-то относятся к окружению, какие-то 
к вашему коду, а какие-то к самому Yii*. В этом разделе мы перечислим большинство из них и объясним, как можно улучшить 
производительность приложения, регулируя эти факторы.


## Оптимизация окружения PHP <span id="optimizing-php"></span>

Хорошо сконфигурированное окружение PHP очень важно. Для получения максимальной производительности,

- Используйте последнюю стабильную версию PHP. Мажорные релизы PHP могут принести значительные улучшения производительности.
- Включите кеширование байткода в [Opcache](http://php.net/opcache) (PHP 5.5 и старше) или [APC](http://ru2.php.net/apc) 
  (PHP 5.4 и более ранние версии). Кеширование байткода позволяет избежать затрат времени на обработку и подключение PHP 
  скриптов при каждом входящем запросе.

## Отключение режима отладки <span id="disable-debug"></span>

При запуске приложения в *производственном режиме*, вам нужно отключить режим отладки. Yii использует значение константы
`YII_DEBUG` чтобы указать, следует ли включить режим отладки. Когда режим отладки включен, Yii тратит дополнительное 
время чтобы создать и записать отладочную информацию.

Вы можете разместить следующую строку кода в начале [входного скрипта](structure-entry-scripts.md) чтобы 
отключить режим отладки:

```php
defined('YII_DEBUG') or define('YII_DEBUG', false);
```

> Info: Значение по умолчанию для константы `YII_DEBUG` — false. 
Так что, если вы уверены, что не изменяете значение по умолчанию где-то в коде приложения, можете просто удалить эту 
строку, чтобы отключить режим отладки.
  

## Использование техник кеширования <span id="using-caching"></span>

Вы можете использовать различные техники кеширования чтобы значительно улучшить производительность вашего приложения. 
Например, если ваше приложение позволяет пользователям вводить текст в формате Markdown, вы можете рассмотреть 
кэширование разобранного содержимого Markdown, чтобы избежать разбора одной и той же разметки Markdown неоднократно 
при каждом запросе. Пожалуйста, обратитесь к разделу [Кеширование](caching-overview.md) чтобы узнать о поддержке 
кеширования, которую предоставляет Yii.


## Включение кеширования схемы <span id="enable-schema-caching"></span>

Кэширование схемы - это специальный *тип кеширования*, которая должна быть включена при использовании [Active Record](db-active-record.md).
Как вы знаете, Active Record достаточно умен, чтобы обнаружить информацию о схеме (например, имена столбцов, типы столбцов, 
ограничения) таблицы БД без необходимости описывать ее вручную. Active Record получает эту информацию, выполняя 
дополнительные SQL запросы. При включении кэширования схемы, полученная информация о схеме будет сохранена в кэше и 
повторно использована при последующих запросах.

Чтобы включить кеширование схемы, сконфигурируйте [компонент приложения](structure-application-components.md) `cache` 
для хранения информации о схеме и установите [[yii\db\Connection::enableSchemaCache]] в `true` в [конфигурации приложения](concept-configurations.md):

```php
return [
    // ...
    'components' => [
        // ...
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=mydatabase',
            'username' => 'root',
            'password' => '',
            'enableSchemaCache' => true,

            // Продолжительность кеширования схемы.
            'schemaCacheDuration' => 3600,

            // Название компонента кеша, используемого для хранения информации о схеме
            'schemaCache' => 'cache',
        ],
    ],
];
```


## Объединение и минимизация ресурсов <span id="optimizing-assets"></span>

Сложные веб-страницы часто подключают много CSS и/или JavaScript ресурсных файлов. Для уменьшения числа HTTP запросов
и общего размера загрузки этих ресурсов, вы должны рассмотреть вопрос об их объединении в один файл и его сжатии.
Это может сильно улучшить скорость загрузки страницы и уменьшить нагрузку на сервер. Для получения более подробной
информации обратитесь, пожалуйста, к разделу [Ресурсы](structure-assets.md)


## Оптимизация хранилища сессий <span id="optimizing-session"></span>

По умолчанию данные сессий хранятся в файлах. Это удобно для разработки или в маленьких проектах.
Но когда дело доходит до обработки множества параллельных запросов, то лучше использовать более сложные хранилища, 
такие как базы данных. Yii поддерживает различные хранилища "из коробки". 
Вы можете использовать эти хранилища, сконфигурировав компонент `session` в
[конфигурации приложения](concept-configurations.md) как показано ниже,

```php
return [
    // ...
    'components' => [
        'session' => [
            'class' => 'yii\web\DbSession',

            // Установите следующее, если вы хотите использовать компонент БД, с названием
            // отличным от значения по умолчанию 'db'.
            // 'db' => 'mydb',

            // Чтобы перезаписать таблицу сессий, заданную по умолчанию, установите
            // 'sessionTable' => 'my_session',
        ],
    ],
];
```

Приведенная выше конфигурация использует таблицу базы данных для хранения сессионных данных. По умолчанию, используется 
компонент приложения `db` для подключения к базе данных и сохранения сессионных данных в таблице `session`. Вам надо 
создать таблицу `session` *as follows in advance, though*,

```sql
CREATE TABLE session (
    id CHAR(40) NOT NULL PRIMARY KEY,
    expire INTEGER,
    data BLOB
)
```

Вы также можете хранить сессионные данные в кеше с помощью [[yii\web\CacheSession]]. Теоретически, вы можете использовать 
любое поддерживаемое [хранилище кеша](caching-data.md#supported-cache-storage). Тем не менее, помните, что некоторые 
хранилища кеша могут *сбрасывать* закешированные данные when the storage limit is reached. For this reason, you should mainly use those cache storage that do not enforce
storage limit.

Если на вашем сервере установлен [Redis](http://redis.io/), настоятельно рекомендуется использовать его в качестве 
хранилища сессий *используя* [[yii\redis\Session]].


## Оптимизация базы данных <span id="optimizing-databases"></span>

Execute DB queries and fetching data from databases is often the main performance bottleneck in
a Web application. Although using [data caching](caching-data.md) techniques may alleviate the performance hit,
it does not fully solve the problem. When the database contains enormous amounts of data and the cached data is invalid, 
fetching the latest data could be prohibitively expensive without proper database and query design.

A general technique to improve the performance of DB queries is to create indices for table columns that
need to be filtered by. For example, if you need to look for a user record by `username`, you should create an index
on `username`. Note that while indexing can make SELECT queries much faster, it will slow down INSERT, UPDATE and DELETE queries.

For complex DB queries, it is recommended that you create database views to save the query parsing and preparation time.

Last but not least, use `LIMIT` in your `SELECT` queries. This avoids fetching an overwhelming amount of data from the database
and exhausting the memory allocated to PHP.


## Использование обычных массивов <span id="using-arrays"></span>

Although [Active Record](db-active-record.md) is very convenient to use, it is not as efficient as using plain arrays
when you need to retrieve a large amount of data from database. In this case, you may consider calling `asArray()`
while using Active Record to query data so that the retrieved data is represented as arrays instead of bulky Active
Record objects. For example,

```php
class PostController extends Controller
{
    public function actionIndex()
    {
        $posts = Post::find()->limit(100)->asArray()->all();
        
        return $this->render('index', ['posts' => $posts]);
    }
}
```

В приведенном выше коде, `$posts` will be populated as an array of table rows. Каждая строка - это обычный массив. Чтобы 
получить доступ к столбцу `title` в i-й строке, вы можете использовать выражение `$posts[$i]['title']`.

You may also use [DAO](db-dao.md) to build queries and retrieve data in plain arrays. 


## Оптимизация автозагрузчика Composer <span id="optimizing-autoloader"></span>


Потому автозагрузчик Composer'а используется для подключения большого количества файлов сторонних классов, вы должны 
оптимизировать его, выполнив следующую команду:

```
composer dumpautoload -o
```


## *Обработка данных в offline* <span id="processing-data-offline"></span>

Когда запрос включает в себя некоторые ресурсоемкие операции, вы должны подумать о том, чтобы выполнить эти операции в
автономном режиме, не заставляя пользователя ожидать их окончания.

Существует два метода обработки данных в фоне: pull и push. 

В методе pull, всякий раз, когда запрос включает в себя некоторые сложные операции, вы создаете задачу и сохраняете ее в 
постоянном хранилище, таком как база данных. Затем в отдельном процессе (таком как задание cron) получаете эту задачу и 
обрабатываете ее.

Этот метод легко реализовать, но у него есть некоторые недостатки. Например, задачи надо периодически забирать из 
места их хранения. Если делать это слишком редко, задачи будут обрабатываться с большой задержкой; если слишком часто - 
это будет создавать большие накладные расходы.

В методе push, вы можете использовать очереди сообщений (например, RabbitMQ, ActiveMQ, Amazon SQS, и т.д.) для управления задачами.
Всякий раз, когда новая задача попадает в очередь, *процесс ее обработки будет запускаться автоматически*.


## Профилирование производительности <span id="performance-profiling"></span>

Вы должны профилировать код, чтобы определить узкие места в производительности и принять соответствующие меры.
Следующие инструменты для профилирования могут оказаться полезными:

- [Yii debug toolbar and debugger](https://github.com/yiisoft/yii2-debug/blob/master/docs/guide/README.md)
- [Профайлер XDebug](http://xdebug.org/docs/profiler)
- [XHProf](http://www.php.net/manual/en/book.xhprof.php)

性能优化
========

有许多因素影响你的 Web 应用程序的性能。有些是环境，
有些是你的代码，而其他一些与 Yii 本身有关。
在本节中，我们将列举这些因素并解释如何通过调整这些因素来提高应用程序的性能。


## 优化你的 PHP 环境 <span id="optimizing-php"></span>

一个好的 PHP 环境是非常重要的。为了得到最大的性能，

- 使用最新稳定版本的 PHP。 PHP 的主要版本可能带来显著的性能提升。
- 启用字节码缓存 [Opcache](https://www.php.net/manual/zh/book.opcache.php)（PHP 5.5或更高版本）
  或 [APC](https://www.php.net/manual/zh/book.apcu.php)
  （PHP 5.4或更早版本）。字节码缓存省去了每次解析和加载 PHP 脚本所带来的开销。
- [Tune `realpath()` cache](https://github.com/samdark/realpath_cache_tuner).


## 禁用调试模式 <span id="disable-debug"></span>

对于运行在生产环境中的应用程序，你应该禁用调试模式。
Yii 中使用名为 `YII_DEBUG` 的常量来定义调试模式是否应被激活。
若启用了调试模式，Yii 将需要额外的时间来产生和记录调试信息。

你可以将下面的代码行放在 [入口脚本](structure-entry-scripts.md) 
的开头来禁用调试模式：

```php
defined('YII_DEBUG') or define('YII_DEBUG', false);
```

> Tip: `YII_DEBUG` 的默认值是 false 。所以如果你确信你不在你应用程序代码中别的地方更改其默认值，
  你可以简单地删除上述行来禁用调试模式。
  

## 使用缓存技术 <span id="using-caching"></span>

你可以使用各种缓存技术来提高应用程序的性能。例如，如果你的应用程序允许用户以 Markdown 格式输入文字，
你可以考虑缓存解析后的 Markdown 内容，避免每个请求都重复解析相同的 Markdown 文本。
请参阅 [缓存](caching-overview.md) 一节，
了解 Yii 提供的缓存支持。


## 开启 Schema 缓存 <span id="enable-schema-caching"></span>

Schema 缓存是一个特殊的缓存功能，
每当你使用[活动记录](db-active-record.md)时应该要开启这个缓存功能。如你所知，
活动记录能智能检测数据库对象的集合（例如列名、列类型、约束）而不需要手动地描述它们。
活动记录是通过执行额外的 SQL 查询来获得该信息。
通过启用 Schema 缓存，检索到的数据库对象的集合将被保存在缓存中并在将来的请求中重用。

要开启 Schema 缓存，需要配置一个 `cache` [应用组件](structure-application-components.md)来储存 Schema 信息，
并在 [配置](concept-configurations.md) 中设置 [[yii\db\Connection::enableSchemaCache]] 为 `true` :

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

            // Duration of schema cache.
            'schemaCacheDuration' => 3600,

            // Name of the cache component used to store schema information
            'schemaCache' => 'cache',
        ],
    ],
];
```


## 合并和压缩资源文件 <span id="optimizing-assets"></span>

一个复杂的网页往往包括许多 CSS 和 JavaScript 资源文件。
为减少 HTTP 请求的数量和这些资源总下载的大小，应考虑将它们合并成一个单一的文件并压缩。
这可大大提高页面加载时间，且减少了服务器负载。
想了解更多细节，请参阅[前端资源](structure-assets.md)部分。


## 优化会话存储 <span id="optimizing-session"></span>

默认会话数据被存储在文件中。
这是好的对处于发展项目或小型项目。
但是，当涉及要处理大量并发请求时，
最好使用其他的会话存储方式，比如数据库。
Yii 支持各种会话存储。
你可以通过在[配置](concept-configurations.md)中配置 `session` 组件来使用这些存储，
如下代码：

```php
return [
    // ...
    'components' => [
        'session' => [
            'class' => 'yii\web\DbSession',

            // Set the following if you want to use DB component other than
            // default 'db'.
            // 'db' => 'mydb',

            // To override default session table, set the following
            // 'sessionTable' => 'my_session',
        ],
    ],
];
```

以上配置是使用数据库来存储会话数据。默认情况下，
它会使用 `db` 应用组件连接数据库并将会话数据存储在 `session` 表。
因此，你必须创建如下的 `session` 表，

```sql
CREATE TABLE session (
    id CHAR(40) NOT NULL PRIMARY KEY,
    expire INTEGER,
    data BLOB
)
```

你也可以通过使用缓存来存储会话数据 [[yii\web\CacheSession]] 。
理论上讲，你可以使用只要支持[数据缓存](caching-data.md#supported-cache-storage)。
但是请注意，某些缓存的存储当达到存储限制会清除缓存数据。出于这个原因，你应主要在不存在存储限制时才使用这些缓存存储。
如果你的服务器支持 [Redis](https://redis.io/)，强烈建议你通过使用 [[yii\redis\Session]] 来作为会话存储。

如果您的服务器上有 [Redis](https://redis.io/)，
强烈建议您使用 [[yii\redis\Session]] 作为会话存储。


## 优化数据库 <span id="optimizing-databases"></span>

执行数据库查询并从数据库中取出数据往往是一个 Web 应用程序主要的性能瓶颈。
尽管使用[数据缓存](caching-data.md)技术可以缓解性能下降，但它并不完全解决这个问题。
当数据库包含大量的数据且缓存数据是无效的，
获取最新的数据可能是最耗性能的假如在没有适当地设计数据库和查询条件。

一般来说，提高数据库查询的性能是创建索引。例如，如果你需要找一个用户表的“用户名”，
你应该为“用户名”创建一个索引。
注意，尽管索引可以使选择查询的速度快得多，但它会减慢插入、更新和删除的查询。

对于复杂的数据库查询，建议你创建数据库视图来保存查询分析和准备的时间。

最后，在“SELECT”中使用“LIMIT”查询。
这可以避免从数据库中取出大量数据。


## 使用普通数组 <span id="using-arrays"></span>

尽管[活动记录](db-active-record.md)对象使用起来非常方便，
但当你需要从数据库中检索大量数据时它的效率不如使用普通的数组。
在这种情况下，你可以考虑在使用活动记录查询数据时调用 `asArray()`，
使检索到的数据被表示为数组而不是笨重的活动记录对象。例如，

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

在上述代码中，$posts 将被表中的行填充形成数组。每一行是一个普通的数组。要访问
第 i 行的 `title` 列，你可以使用表达式 `$post[$i]['title']`。

你也可以使用 [DAO](db-dao.md) 以数组的方式来构建查询和检索数据。


## 优化 Composer 自动加载 <span id="optimizing-autoloader"></span>

因为 Composer 自动加载用于加载大多数第三方类文件，
应考虑对其进行优化，通过执行以下命令：

```
composer dumpautoload -o
```

另外，您可以考虑使用
[authoritative class maps](https://getcomposer.org/doc/articles/autoloader-optimization.md#optimization-level-2-a-authoritative-class-maps)
和 [APCu 缓存](https://getcomposer.org/doc/articles/autoloader-optimization.md#optimization-level-2-b-apcu-cache)。
请注意，这两种选择可能适用于您的特定情况，也可能不适合您。


## 处理离线数据 <span id="processing-data-offline"></span>

当一个请求涉及到一些资源密集操作，
你应该想办法在无需用户等待他们完成脱机模式时来执行这些操作。

有两种方法可以离线数据处理：推和拉。

在拉中，只要有请求涉及到一些复杂的操作，你创建一个任务，并将其保存在永久存储，例如数据库。然后，
使用一个单独的进程（如 cron 作业）拉任务，并进行处理。
这种方法很容易实现，但它也有一些缺点。
例如，该任务过程中需要定期地从任务存储拉。如果拉频率太低，这些任务可以延迟处理；
但是如果频率过高，将引起的高开销。

在推中，你可以使用消息队列（如 RabbitMQ ，ActiveMQ ， Amazon SQS 等）来管理任务。
每当一个新的任务放在队列中，它会启动或者通知任务处理过程去触发任务处理。


## 性能分析 <span id="performance-profiling"></span>

你应该配置你的代码来找出性能缺陷，并相应地采取适当措施。
以下分析工具可能是有用的:

- [Yii debug toolbar and debugger](https://github.com/yiisoft/yii2-debug/blob/master/docs/guide/README.md)
- [Blackfire](https://blackfire.io/)
- [XHProf](https://www.php.net/manual/zh/book.xhprof.php)
- [XDebug profiler](https://xdebug.org/docs/profiler)

## 准备扩展应用程序

当没有任何帮助时，您可以尝试使您的应用程序可扩展。[Configuring a Yii2 Application for an Autoscaling Stack](https://github.com/samdark/yii2-cookbook/blob/master/book/scaling.md) 中提供了一个很好的介绍。有关进一步阅读，请参阅 [Web apps performance and scaling](https://thehighload.com/).

# yii2-redis 扩展详解 


## 简介

yii2-redis 扩展为 Yii2 框架提供了 redis 键值存储支持。包括缓存（Cache）、会话存储处理（Session），并实现了 ActiveRecord 模式，允许您将活动记录存储在 redis 中。

#### 相关链接
- [yii2-redis 扩展网址：https://github.com/yiisoft/yii2-redis](https://github.com/yiisoft/yii2-redis)


## 安装扩展

在 Yii2 项目根目录，执行以下命令安装：
```
$ composer require yiisoft/yii2-redis
```

也可以先在 composer.json 文件中声明如下依赖：
```
"yiisoft/yii2-redis": "~2.0.0"
```

再执行下面命令安装：
```
$ composer update
```


## 基本使用

继续阅读请确保已安装并开启了 redis 服务，安装请参考[《Redis 安装》](http://mayanlong.com/archives/2016/359.html)。

#### 1. 配置

在组件中添加如下配置：
```php
'components' => [
    'redis' => [
        'class' => 'yii\redis\Connection',
        'hostname' => 'localhost',
        'port' => 6379,
        'database' => 0,
    ],
]  
```

#### 2. 示例

下面代码演示了 redis 最基本的 string 类型的使用：
```php
// 获取 redis 组件
$redis = Yii::$app->redis;

// 判断 key 为 username 的是否有值，有则打印，没有则赋值
$key = 'username';
if ($val = $redis->get($key);) {
    var_dump($val);
} else {
    $redis->set($key, 'marko');
    $redis->expire($key, 5);
}
```

这个类中（`yii\redis\Connection`）提供了操作 redis 所有的数据类型和服务（String、Hash、List、Set、SortedSet、HyperLogLog、GEO、Pub/Sub、Transaction、Script、Connection、Server）所需要的方法，并且和 redis 中的方法同名，如果不清楚可以直接到该类中查看。


## 缓存组件

该扩展中的 `yii\redis\Cache` 实现了 Yii2 中的缓存相关接口，所以我们也可以用 redis 来存储缓存，且用法和原来一样。

#### 1. 配置

修改组件中 cache 的 class 为 `yii\redis\Cache` 即可，配置如下：
```php
'components' => [
    'cache' => [
        // 'class' => 'yii\caching\FileCache',
        'class' => 'yii\redis\Cache',
    ],
],
```

如果没有配置过 redis 组件，需要在 cache 组件下配置 redis 服务相关参数，完整配置如下：
```php
'components' => [
    'cache' => [
        // 'class' => 'yii\caching\FileCache',
        'class' => 'yii\redis\Cache',
        'redis' => [
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
    ],
],
```

#### 2. 示例

下面代码演示了缓存的基本使用：
```php
// 获取 cache 组件
$cache = Yii::$app->cache;

// 判断 key 为 username 的缓存是否存在，有则打印，没有则赋值
$key = 'username';
if ($cache->exists($key)) {
    var_dump($cache->get($key));
} else {
    $cache->set($key, 'marko', 60);
}
```

使用文件缓存（FileCache）时，缓存是存储在 runtime/cache 目录下；使用 redis 缓存后，缓存将存储在 redis 数据库中，性能将大大提高。


## 会话组件

该扩展中的 `yii\redis\Session` 实现了 Yii2 中的会话相关接口，所以我们也可以用 redis 来存储会话信息，且用法和原来一样。

#### 1. 配置

修改组件 session 的配置，指定 class 为 `yii\redis\Session` 即可，配置如下：
```php
'components' => [
    'session' => [
        'name' => 'advanced-frontend',
        'class' => 'yii\redis\Session'
    ],
],
```

如果没有配置过 redis 组件，需要在 session 组件下配置 redis 服务相关参数，完整配置如下：
```php
'components' => [
    'session' => [
        'name' => 'advanced-frontend',
        'class' => 'yii\redis\Session',
        'redis' => [
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
    ],
],
```

#### 2. 使用

在开发过程中，切记一定不要使用 PHP 原生的 $_SESSION 去操作，而要使用 Yii 提供的 session 组件，获取方式如下：
```php
$session = Yii::$app->session;
```


## ActiveRecord

该扩展中的 `yii\redis\ActiveRecord` 实现了 Yii2 中的 ActiveRecord 相关接口，所以我们可以使用 AR 的方式操作 redis 数据库。关于如何使用 Yii 的 ActiveRecord，请阅读权威指南中有关 [ActiveRecord](http://www.yiichina.com/doc/guide/2.0/db-active-record) 的基础文档。 

定义 redis ActiveRecord 类，我们的模型需要继承 `yii\redis\ActiveRecord`，并至少实现 `attributes()` 方法来定义模型的属性。 

主键可以通过 `yii\redis\ActiveRecord::primaryKey()` 定义，如果未指定，则默认为 id。 primaryKey 必须在 attributes() 方法定义的属性中，如果没有指定主键，请确保 id 在属性中。

下面定义一个 Customer 模型来演示：
```php
class Customer extends \yii\redis\ActiveRecord
{
    /**
     * 主键 默认为 id
     *
     * @return array|string[]
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * 模型对应记录的属性列表
     *
     * @return array
     */
    public function attributes()
    {
        return ['id', 'name', 'age', 'phone', 'status', 'created_at', 'updated_at'];
    }

    /**
     * 定义和其它模型的关系
     *
     * @return \yii\db\ActiveQueryInterface
     */
    public function getOrders()
    {
         return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }

}
```

使用示例：
```php
// 使用 AR 方式新增一条记录
$customer = new Customer();
$customer->name = 'marko';
$customer->age = 18;
$customer->phone = 13888888888;
$customer->status = 1;
$customer->save();
echo $customer->id;

// 使用 AR 查询
$customer = Customer::findOne($customer->id);
$customer = Customer::find()->where(['status' => 1])->all();
```

redis ActiveRecord 的一般用法与权威指南中数据库的 ActiveRecord 用法非常相似。它们支持相同的接口和方法，除了以下限制：
- 由于 redis 不支持 sql，查询方法仅限于使用以下方法：where()，limit()，offset()，orderBy() 和 indexBy()。 【 orderBy() 尚未实现：[＃1305](https://github.com/yiisoft/yii2/issues/1305)）】
- 由于 redis 没有表的概念，因此不能通过表定义关联关系，只能通过其它记录来定义关系。


## 直接使用命令

直接使用 redis 连接，就可以使用 redis 提供的很多有用的命令。配置好 redis 后，用以下方式获取 redis 组件：
```php
$redis = Yii::$app->redis;
```

然后就可以执行命令了，最通用的方法是使用 executeCommand 方法：
```php
$result = $redis->executeCommand('hmset', ['test_collection', 'key1', 'val1', 'key2', 'val2']);
```

支持的每个命令都有一些快捷方式，可以按照如下方式使用：
```php
$result = $redis->hmset('test_collection', 'key1', 'val1', 'key2', 'val2');
```

有关可用命令及其参数的列表，请参阅 redis 命令： 
- [redis 命令英文版：http://redis.io/commands](http://redis.io/commands)
- [redis 命令中文版：http://redisdoc.com](http://redisdoc.com)












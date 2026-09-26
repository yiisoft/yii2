/**
 * Database schema required by \yii\caching\DbCache.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @link https://www.yiiframework.com/
 * @copyright 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 * @since 2.0.7
 */
if object_id('[cache]', 'U') is not null
    drop table [cache];

drop table if exists [cache];

create table [cache]
(
    [id]  varchar(128) not null,
    [expire] integer,
    [data]   BLOB,
    primary key ([id])
);

/**
 * Database schema required by \yii\caching\DbCache.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @since 2.0.7
 */

drop table if exists "cache";

create table "cache"
(
    "id"  varchar(128) not null,
    "expire" integer,
    "data"   bytea,
    primary key ("id")
);

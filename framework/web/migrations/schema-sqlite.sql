/**
 * Database schema required by \yii\web\DbSession.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @since 2.0.8
 */

drop table if exists "session";

create table "session"
(
    "id"  varchar(256) not null,
    "expire" integer,
    "data"   BLOB,
    primary key ("id")
);

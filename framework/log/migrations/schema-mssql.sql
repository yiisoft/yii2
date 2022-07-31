/**
 * Database schema required by \yii\log\DbTarget.
 *
 * The indexes declared are not required. They are mainly used to improve the performance
 * of some queries about message levels and categories. Depending on your actual needs, you may
 * want to create additional indexes (e.g. index on `log_time`).
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @link https://www.yiiframework.com/
 * @copyright 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 * @since 2.0.1
 */

if object_id('[log]', 'U') is not null
    drop table [log];

create table [log]
(
   [id]          bigint IDENTITY PRIMARY KEY,
   [level]       integer,
   [category]    varchar(255),
   [log_time]    float,
   [prefix]      text,
   [message]     text
);

create index [idx_log_level] on [log] ([level]);
create index [idx_log_category] on [log] ([category]);

/**
 * Database schema required by \yii\rbac\DbManager.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @link https://www.yiiframework.com/
 * @copyright 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 * @since 2.0
 */

if object_id('[auth_assignment]', 'U') is not null
    drop table [auth_assignment];

if object_id('[auth_item_child]', 'U') is not null
    drop table [auth_item_child];

if object_id('[auth_item]', 'U') is not null
    drop table [auth_item];

if object_id('[auth_rule]', 'U') is not null
    drop table [auth_rule];

create table [auth_rule]
(
    [name]  varchar(64) not null,
    [data]  blob,
    [created_at]           integer,
    [updated_at]           integer,
    primary key ([name])
);

create table [auth_item]
(
   [name]                 varchar(64) not null,
   [type]                 smallint not null,
   [description]          text,
   [rule_name]            varchar(64),
   [data]                 blob,
   [created_at]           integer,
   [updated_at]           integer,
   primary key ([name]),
   foreign key ([rule_name]) references [auth_rule] ([name])
);

create index [idx-auth_item-type] on [auth_item] ([type]);

create table [auth_item_child]
(
   [parent]               varchar(64) not null,
   [child]                varchar(64) not null,
   primary key ([parent],[child]),
   foreign key ([parent]) references [auth_item] ([name]),
   foreign key ([child]) references [auth_item] ([name])
);

create table [auth_assignment]
(
   [item_name]            varchar(64) not null,
   [user_id]              varchar(64) not null,
   [created_at]           integer,
   primary key ([item_name], [user_id]),
   foreign key ([item_name]) references [auth_item] ([name]) on delete cascade on update cascade
);

create index [auth_assignment_user_id_idx] on [auth_assignment] ([user_id]);


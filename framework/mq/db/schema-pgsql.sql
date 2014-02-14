/**
 * Database schema required by \yii\mq\db\Queue.
 *
 * @author Jan Was <janek.jan@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @since 2.0
 */

drop table if exists "tbl_messages";
drop table if exists "tbl_subscriptions";
drop table if exists "tbl_subscription_categories";

create table "tbl_subscriptions"
(
   "id"                 serial not null,
   "queue_id"           varchar not null,
   "label"              varchar,
   "subscriber_id"      varchar not null, -- optionally references a user table
   "created_on"         timestamp,
   "is_deleted"         boolean not null default false,
   primary key ("id")
);

create table "tbl_subscription_categories"
(
   "id"                 serial not null,
   "subscription_id"    integer not null references "tbl_subscriptions" ("id") on delete cascade on update cascade,
   "category"           varchar not null,
   "is_exception"       boolean not null default false,
   primary key ("id")
);

create table "tbl_messages"
(
   "id"                 serial not null,
   "queue_id"           varchar not null,
   "created_on"         timestamp not null,
   "sender_id"          varchar, -- optionally references a user table
   "message_id"         integer,
   "subscription_id"    integer references "tbl_subscriptions" ("id") on delete cascade on update cascade,
   "status"             integer not null,
   "times_out_on"       timestamp,
   "reserved_on"        timestamp,
   "deleted_on"         timestamp,
   "mimetype"           varchar not null default 'text/plain',
   "body"               text,
   primary key ("id")
);

create index tbl_auth_item_type_idx on "tbl_auth_item" ("type");

create index tbl_messages_queue_id_idx on "tbl_messages" ("queue_id");
create index tbl_messages_sender_id_idx on "tbl_messages" ("sender_id");
create index tbl_messages_message_id_idx on "tbl_messages" ("message_id");
create index tbl_messages_status_idx on "tbl_messages" ("status");
create index tbl_messages_times_out_on_idx on "tbl_messages" ("times_out_on");
create index tbl_messages_reserved_on_idx on "tbl_messages" ("reserved_on");
create index tbl_messages_subscription_id_idx on "tbl_messages" ("subscription_id");

create index tbl_subscriptions_queue_id_idx on "tbl_subscriptions" ("queue_id");
create index tbl_subscriptions_subscriber_id_idx on "tbl_subscriptions" ("subscriber_id");
create unique index tbl_subscriptions_queue_id_subscriber_id_idx on "tbl_subscriptions" ("queue_id,subscriber_id");
create index tbl_subscriptions_is_deleted_idx on "tbl_subscriptions" ("is_deleted");

create index tbl_subscription_categories_subscription_id_idx on "tbl_subscription_categories" ("subscription_id");
create unique index tbl_subscription_categories_subscription_id_category_idx on "tbl_subscription_categories" ("subscription_id,category");


/**
 * Database schema required by \yii\rbac\DbManager.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
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

CREATE TRIGGER dbo.trigger_auth_item_child
    ON dbo.[auth_item]
    INSTEAD OF DELETE, UPDATE
    AS
    DECLARE @old_name VARCHAR (64) = (SELECT name FROM deleted)
    DECLARE @new_name VARCHAR (64) = (SELECT name FROM inserted)
    BEGIN
        IF COLUMNS_UPDATED() > 0
        BEGIN
            IF @old_name <> @new_name
                BEGIN
                    ALTER TABLE auth_item_child NOCHECK CONSTRAINT FK__auth_item__child;
                    UPDATE auth_item_child SET child = @new_name WHERE child = @old_name;
                END
                UPDATE auth_item
                SET name = (SELECT name FROM inserted),
                type = (SELECT type FROM inserted),
                description = (SELECT description FROM inserted),
                rule_name = (SELECT rule_name FROM inserted),
                data = (SELECT data FROM inserted),
                created_at = (SELECT created_at FROM inserted),
                updated_at = (SELECT updated_at FROM inserted)
                WHERE name IN (SELECT name FROM deleted)
                IF @old_name <> @new_name
                    BEGIN
                        ALTER TABLE auth_item_child CHECK CONSTRAINT FK__auth_item__child;
                    END
                END
                ELSE
                    BEGIN
                        DELETE FROM dbo.[auth_item_child] WHERE parent IN (SELECT name FROM deleted) OR child IN (SELECT name FROM deleted);
                        DELETE FROM dbo.[auth_item] WHERE name IN (SELECT name FROM deleted);
                    END
        END;

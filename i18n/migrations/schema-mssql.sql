/**
 * Database schema required by \yii\i18n\DbMessageSource.
 *
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @since 2.0.7
 */

drop table if exists [source_message];
drop table if exists [message];

CREATE TABLE [source_message]
(
   [id]          integer IDENTITY PRIMARY KEY,
   [category]    varchar(255),
   [message]     text
);

CREATE TABLE [message]
(
   [id]          integer NOT NULL,
   [language]    varchar(16) NOT NULL,
   [translation] text
);

ALTER TABLE [message] ADD CONSTRAINT [pk_message_id_language] PRIMARY KEY ([id], [language]);
ALTER TABLE [message] ADD CONSTRAINT [fk_message_source_message] FOREIGN KEY ([id]) REFERENCES [source_message] ([id]) ON UPDATE CASCADE ON DELETE NO ACTION;

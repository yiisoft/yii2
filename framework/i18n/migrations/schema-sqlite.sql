/**
 * Database schema required by \yii\i18n\DbMessageSource.
 *
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @since 2.0.7
 */

drop table if exists `source_message`;
drop table if exists `message`;

CREATE TABLE `source_message`
(
   `id`          integer PRIMARY KEY AUTOINCREMENT NOT NULL,
   `category`    varchar(255),
   `message`     text
);

CREATE TABLE `message`
(
   `id`          integer NOT NULL REFERENCES `source_message` (`id`) ON UPDATE RESTRICT ON DELETE CASCADE,
   `language`    varchar(16) NOT NULL,
   `translation` text,
   PRIMARY KEY (`id`, `language`)
);

CREATE INDEX idx_message_language ON message (language);
CREATE INDEX idx_source_message_category ON source_message (category);

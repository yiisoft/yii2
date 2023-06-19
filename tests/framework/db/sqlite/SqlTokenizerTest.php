<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite;

use yii\db\sqlite\SqlTokenizer;
use yii\db\SqlToken;
use yiiunit\TestCase;

/**
 * @group db
 * @group sqlite
 */
class SqlTokenizerTest extends TestCase
{
    public function sqlProvider()
    {
        return [
            'complex' => [
                <<<'SQL'
CREATE TABLE `constraints_test_1` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`col_1` integer,
	`col_2` integer NOT NULL, `chktest` text NOT NULL DEFAULT 'none' CHECK([chktest] <> '''' and not(chktest=='foo')),
	-- CONSTRAINT `ch` CHECK -- (`col_1` <> 0 and col_2 <> 1))
CONSTRAINT `ch2` CHECK -- (`col_1` <> 0 and col_2 <> -1))
(`col_1` <> 41 and not (col_2 == 'тес''т')));
CREATE TABLE t300(id INTEGER PRIMARY KEY);
CREATE TABLE t301(
    id INTEGER PRIMARY KEY,
    c1 INTEGER NOT NULL,
    c2 INTEGER NOT NULL,
    c3 BOOLEAN NOT NULL DEFAULT 0,
    FOREIGN KEY(c1) REFERENCES t300(id) ON DELETE CASCADE ON UPDATE RESTRICT
    /* no comma */
    FOREIGN KEY(c2) REFERENCES t300(id) ON DELETE CASCADE ON UPDATE RESTRICT
    /* no comma */
    UNIQUE(c1, c2)
);
PRAGMA foreign_key_list(t301);
SELECT*from/*foo*/`T_constraints_1`WHERE not`C_check`='foo''bar'--bar
;;;;;;;;;/*
SQL
,
                new SqlToken([
                    'type' => SqlToken::TYPE_CODE,
                    'content' => 'CREATE TABLE `constraints_test_1` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`col_1` integer,
	`col_2` integer NOT NULL, `chktest` text NOT NULL DEFAULT \'none\' CHECK([chktest] <> \'\'\'\' and not(chktest==\'foo\')),
	-- CONSTRAINT `ch` CHECK -- (`col_1` <> 0 and col_2 <> 1))
CONSTRAINT `ch2` CHECK -- (`col_1` <> 0 and col_2 <> -1))
(`col_1` <> 41 and not (col_2 == \'тес\'\'т\')));
CREATE TABLE t300(id INTEGER PRIMARY KEY);
CREATE TABLE t301(
    id INTEGER PRIMARY KEY,
    c1 INTEGER NOT NULL,
    c2 INTEGER NOT NULL,
    c3 BOOLEAN NOT NULL DEFAULT 0,
    FOREIGN KEY(c1) REFERENCES t300(id) ON DELETE CASCADE ON UPDATE RESTRICT
    /* no comma */
    FOREIGN KEY(c2) REFERENCES t300(id) ON DELETE CASCADE ON UPDATE RESTRICT
    /* no comma */
    UNIQUE(c1, c2)
);
PRAGMA foreign_key_list(t301);
SELECT*from/*foo*/`T_constraints_1`WHERE not`C_check`=\'foo\'\'bar\'--bar
;;;;;;;;;/*',
                    'startOffset' => 0,
                    'endOffset' => 875,
                    'children' => [
                        new SqlToken([
                            'type' => SqlToken::TYPE_STATEMENT,
                            'content' => null,
                            'startOffset' => 0,
                            'endOffset' => 383,
                            'children' => [
                                new SqlToken([
                                    'type' => SqlToken::TYPE_KEYWORD,
                                    'content' => 'CREATE',
                                    'startOffset' => 0,
                                    'endOffset' => 6,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_KEYWORD,
                                    'content' => 'TABLE',
                                    'startOffset' => 7,
                                    'endOffset' => 12,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_IDENTIFIER,
                                    'content' => 'constraints_test_1',
                                    'startOffset' => 13,
                                    'endOffset' => 33,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_OPERATOR,
                                    'content' => '(',
                                    'startOffset' => 34,
                                    'endOffset' => 35,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_PARENTHESIS,
                                    'content' => null,
                                    'startOffset' => 37,
                                    'endOffset' => 381,
                                    'children' => [
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_IDENTIFIER,
                                            'content' => 'id',
                                            'startOffset' => 37,
                                            'endOffset' => 41,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 'integer',
                                            'startOffset' => 42,
                                            'endOffset' => 49,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'PRIMARY',
                                            'startOffset' => 50,
                                            'endOffset' => 57,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'KEY',
                                            'startOffset' => 58,
                                            'endOffset' => 61,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'AUTOINCREMENT',
                                            'startOffset' => 62,
                                            'endOffset' => 75,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'NOT',
                                            'startOffset' => 76,
                                            'endOffset' => 79,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'NULL',
                                            'startOffset' => 80,
                                            'endOffset' => 84,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => ',',
                                            'startOffset' => 84,
                                            'endOffset' => 85,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_IDENTIFIER,
                                            'content' => 'col_1',
                                            'startOffset' => 87,
                                            'endOffset' => 94,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 'integer',
                                            'startOffset' => 95,
                                            'endOffset' => 102,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => ',',
                                            'startOffset' => 102,
                                            'endOffset' => 103,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_IDENTIFIER,
                                            'content' => 'col_2',
                                            'startOffset' => 105,
                                            'endOffset' => 112,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 'integer',
                                            'startOffset' => 113,
                                            'endOffset' => 120,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'NOT',
                                            'startOffset' => 121,
                                            'endOffset' => 124,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'NULL',
                                            'startOffset' => 125,
                                            'endOffset' => 129,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => ',',
                                            'startOffset' => 129,
                                            'endOffset' => 130,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_IDENTIFIER,
                                            'content' => 'chktest',
                                            'startOffset' => 131,
                                            'endOffset' => 140,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 'text',
                                            'startOffset' => 141,
                                            'endOffset' => 145,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'NOT',
                                            'startOffset' => 146,
                                            'endOffset' => 149,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'NULL',
                                            'startOffset' => 150,
                                            'endOffset' => 154,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'DEFAULT',
                                            'startOffset' => 155,
                                            'endOffset' => 162,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_STRING_LITERAL,
                                            'content' => 'none',
                                            'startOffset' => 163,
                                            'endOffset' => 169,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'CHECK',
                                            'startOffset' => 170,
                                            'endOffset' => 175,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => '(',
                                            'startOffset' => 175,
                                            'endOffset' => 176,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_PARENTHESIS,
                                            'content' => null,
                                            'startOffset' => 176,
                                            'endOffset' => 217,
                                            'children' => [
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_IDENTIFIER,
                                                    'content' => 'chktest',
                                                    'startOffset' => 176,
                                                    'endOffset' => 185,
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_OPERATOR,
                                                    'content' => '<>',
                                                    'startOffset' => 186,
                                                    'endOffset' => 188,
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_STRING_LITERAL,
                                                    'content' => '\'',
                                                    'startOffset' => 189,
                                                    'endOffset' => 193,
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_KEYWORD,
                                                    'content' => 'AND',
                                                    'startOffset' => 194,
                                                    'endOffset' => 197,
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_KEYWORD,
                                                    'content' => 'NOT',
                                                    'startOffset' => 198,
                                                    'endOffset' => 201,
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_OPERATOR,
                                                    'content' => '(',
                                                    'startOffset' => 201,
                                                    'endOffset' => 202,
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_PARENTHESIS,
                                                    'content' => null,
                                                    'startOffset' => 202,
                                                    'endOffset' => 216,
                                                    'children' => [
                                                        new SqlToken([
                                                            'type' => SqlToken::TYPE_TOKEN,
                                                            'content' => 'chktest',
                                                            'startOffset' => 202,
                                                            'endOffset' => 209,
                                                        ]),
                                                        new SqlToken([
                                                            'type' => SqlToken::TYPE_OPERATOR,
                                                            'content' => '==',
                                                            'startOffset' => 209,
                                                            'endOffset' => 211,
                                                        ]),
                                                        new SqlToken([
                                                            'type' => SqlToken::TYPE_STRING_LITERAL,
                                                            'content' => 'foo',
                                                            'startOffset' => 211,
                                                            'endOffset' => 216,
                                                        ]),
                                                    ],
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_OPERATOR,
                                                    'content' => ')',
                                                    'startOffset' => 216,
                                                    'endOffset' => 217,
                                                ]),
                                            ],
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => ')',
                                            'startOffset' => 217,
                                            'endOffset' => 218,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => ',',
                                            'startOffset' => 218,
                                            'endOffset' => 219,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'CONSTRAINT',
                                            'startOffset' => 280,
                                            'endOffset' => 290,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_IDENTIFIER,
                                            'content' => 'ch2',
                                            'startOffset' => 291,
                                            'endOffset' => 296,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'CHECK',
                                            'startOffset' => 297,
                                            'endOffset' => 302,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => '(',
                                            'startOffset' => 338,
                                            'endOffset' => 339,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_PARENTHESIS,
                                            'content' => null,
                                            'startOffset' => 339,
                                            'endOffset' => 380,
                                            'children' => [
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_IDENTIFIER,
                                                    'content' => 'col_1',
                                                    'startOffset' => 339,
                                                    'endOffset' => 346,
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_OPERATOR,
                                                    'content' => '<>',
                                                    'startOffset' => 347,
                                                    'endOffset' => 349,
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_TOKEN,
                                                    'content' => '41',
                                                    'startOffset' => 350,
                                                    'endOffset' => 352,
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_KEYWORD,
                                                    'content' => 'AND',
                                                    'startOffset' => 353,
                                                    'endOffset' => 356,
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_KEYWORD,
                                                    'content' => 'NOT',
                                                    'startOffset' => 357,
                                                    'endOffset' => 360,
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_OPERATOR,
                                                    'content' => '(',
                                                    'startOffset' => 361,
                                                    'endOffset' => 362,
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_PARENTHESIS,
                                                    'content' => null,
                                                    'startOffset' => 362,
                                                    'endOffset' => 379,
                                                    'children' => [
                                                        new SqlToken([
                                                            'type' => SqlToken::TYPE_TOKEN,
                                                            'content' => 'col_2',
                                                            'startOffset' => 362,
                                                            'endOffset' => 367,
                                                        ]),
                                                        new SqlToken([
                                                            'type' => SqlToken::TYPE_OPERATOR,
                                                            'content' => '==',
                                                            'startOffset' => 368,
                                                            'endOffset' => 370,
                                                        ]),
                                                        new SqlToken([
                                                            'type' => SqlToken::TYPE_STRING_LITERAL,
                                                            'content' => 'тес\'т',
                                                            'startOffset' => 371,
                                                            'endOffset' => 379,
                                                        ]),
                                                    ],
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_OPERATOR,
                                                    'content' => ')',
                                                    'startOffset' => 379,
                                                    'endOffset' => 380,
                                                ]),
                                            ],
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => ')',
                                            'startOffset' => 380,
                                            'endOffset' => 381,
                                        ]),
                                    ],
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_OPERATOR,
                                    'content' => ')',
                                    'startOffset' => 381,
                                    'endOffset' => 382,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_OPERATOR,
                                    'content' => ';',
                                    'startOffset' => 382,
                                    'endOffset' => 383,
                                ]),
                            ],
                        ]),
                        new SqlToken([
                            'type' => SqlToken::TYPE_STATEMENT,
                            'content' => null,
                            'startOffset' => 384,
                            'endOffset' => 426,
                            'children' => [
                                new SqlToken([
                                    'type' => SqlToken::TYPE_KEYWORD,
                                    'content' => 'CREATE',
                                    'startOffset' => 384,
                                    'endOffset' => 390,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_KEYWORD,
                                    'content' => 'TABLE',
                                    'startOffset' => 391,
                                    'endOffset' => 396,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_TOKEN,
                                    'content' => 't300',
                                    'startOffset' => 397,
                                    'endOffset' => 401,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_OPERATOR,
                                    'content' => '(',
                                    'startOffset' => 401,
                                    'endOffset' => 402,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_PARENTHESIS,
                                    'content' => null,
                                    'startOffset' => 402,
                                    'endOffset' => 424,
                                    'children' => [
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 'id',
                                            'startOffset' => 402,
                                            'endOffset' => 404,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 'INTEGER',
                                            'startOffset' => 405,
                                            'endOffset' => 412,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'PRIMARY',
                                            'startOffset' => 413,
                                            'endOffset' => 420,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'KEY',
                                            'startOffset' => 421,
                                            'endOffset' => 424,
                                        ]),
                                    ],
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_OPERATOR,
                                    'content' => ')',
                                    'startOffset' => 424,
                                    'endOffset' => 425,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_OPERATOR,
                                    'content' => ';',
                                    'startOffset' => 425,
                                    'endOffset' => 426,
                                ]),
                            ],
                        ]),
                        new SqlToken([
                            'type' => SqlToken::TYPE_STATEMENT,
                            'content' => null,
                            'startOffset' => 427,
                            'endOffset' => 772,
                            'children' => [
                                new SqlToken([
                                    'type' => SqlToken::TYPE_KEYWORD,
                                    'content' => 'CREATE',
                                    'startOffset' => 427,
                                    'endOffset' => 433,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_KEYWORD,
                                    'content' => 'TABLE',
                                    'startOffset' => 434,
                                    'endOffset' => 439,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_TOKEN,
                                    'content' => 't301',
                                    'startOffset' => 440,
                                    'endOffset' => 444,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_OPERATOR,
                                    'content' => '(',
                                    'startOffset' => 444,
                                    'endOffset' => 445,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_PARENTHESIS,
                                    'content' => null,
                                    'startOffset' => 450,
                                    'endOffset' => 769,
                                    'children' => [
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 'id',
                                            'startOffset' => 450,
                                            'endOffset' => 452,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 'INTEGER',
                                            'startOffset' => 453,
                                            'endOffset' => 460,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'PRIMARY',
                                            'startOffset' => 461,
                                            'endOffset' => 468,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'KEY',
                                            'startOffset' => 469,
                                            'endOffset' => 472,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => ',',
                                            'startOffset' => 472,
                                            'endOffset' => 473,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 'c1',
                                            'startOffset' => 478,
                                            'endOffset' => 480,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 'INTEGER',
                                            'startOffset' => 481,
                                            'endOffset' => 488,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'NOT',
                                            'startOffset' => 489,
                                            'endOffset' => 492,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'NULL',
                                            'startOffset' => 493,
                                            'endOffset' => 497,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => ',',
                                            'startOffset' => 497,
                                            'endOffset' => 498,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 'c2',
                                            'startOffset' => 503,
                                            'endOffset' => 505,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 'INTEGER',
                                            'startOffset' => 506,
                                            'endOffset' => 513,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'NOT',
                                            'startOffset' => 514,
                                            'endOffset' => 517,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'NULL',
                                            'startOffset' => 518,
                                            'endOffset' => 522,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => ',',
                                            'startOffset' => 522,
                                            'endOffset' => 523,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 'c3',
                                            'startOffset' => 528,
                                            'endOffset' => 530,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 'BOOLEAN',
                                            'startOffset' => 531,
                                            'endOffset' => 538,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'NOT',
                                            'startOffset' => 539,
                                            'endOffset' => 542,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'NULL',
                                            'startOffset' => 543,
                                            'endOffset' => 547,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'DEFAULT',
                                            'startOffset' => 548,
                                            'endOffset' => 555,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => '0',
                                            'startOffset' => 556,
                                            'endOffset' => 557,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => ',',
                                            'startOffset' => 557,
                                            'endOffset' => 558,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'FOREIGN',
                                            'startOffset' => 563,
                                            'endOffset' => 570,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'KEY',
                                            'startOffset' => 571,
                                            'endOffset' => 574,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => '(',
                                            'startOffset' => 574,
                                            'endOffset' => 575,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_PARENTHESIS,
                                            'content' => null,
                                            'startOffset' => 575,
                                            'endOffset' => 577,
                                            'children' => [
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_TOKEN,
                                                    'content' => 'c1',
                                                    'startOffset' => 575,
                                                    'endOffset' => 577,
                                                ]),
                                            ],
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => ')',
                                            'startOffset' => 577,
                                            'endOffset' => 578,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'REFERENCES',
                                            'startOffset' => 579,
                                            'endOffset' => 589,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 't300',
                                            'startOffset' => 590,
                                            'endOffset' => 594,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => '(',
                                            'startOffset' => 594,
                                            'endOffset' => 595,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_PARENTHESIS,
                                            'content' => null,
                                            'startOffset' => 595,
                                            'endOffset' => 597,
                                            'children' => [
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_TOKEN,
                                                    'content' => 'id',
                                                    'startOffset' => 595,
                                                    'endOffset' => 597,
                                                ]),
                                            ],
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => ')',
                                            'startOffset' => 597,
                                            'endOffset' => 598,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'ON',
                                            'startOffset' => 599,
                                            'endOffset' => 601,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'DELETE',
                                            'startOffset' => 602,
                                            'endOffset' => 608,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'CASCADE',
                                            'startOffset' => 609,
                                            'endOffset' => 616,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'ON',
                                            'startOffset' => 617,
                                            'endOffset' => 619,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'UPDATE',
                                            'startOffset' => 620,
                                            'endOffset' => 626,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'RESTRICT',
                                            'startOffset' => 627,
                                            'endOffset' => 635,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'FOREIGN',
                                            'startOffset' => 659,
                                            'endOffset' => 666,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'KEY',
                                            'startOffset' => 667,
                                            'endOffset' => 670,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => '(',
                                            'startOffset' => 670,
                                            'endOffset' => 671,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_PARENTHESIS,
                                            'content' => null,
                                            'startOffset' => 671,
                                            'endOffset' => 673,
                                            'children' => [
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_TOKEN,
                                                    'content' => 'c2',
                                                    'startOffset' => 671,
                                                    'endOffset' => 673,
                                                ]),
                                            ],
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => ')',
                                            'startOffset' => 673,
                                            'endOffset' => 674,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'REFERENCES',
                                            'startOffset' => 675,
                                            'endOffset' => 685,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 't300',
                                            'startOffset' => 686,
                                            'endOffset' => 690,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => '(',
                                            'startOffset' => 690,
                                            'endOffset' => 691,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_PARENTHESIS,
                                            'content' => null,
                                            'startOffset' => 691,
                                            'endOffset' => 693,
                                            'children' => [
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_TOKEN,
                                                    'content' => 'id',
                                                    'startOffset' => 691,
                                                    'endOffset' => 693,
                                                ]),
                                            ],
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => ')',
                                            'startOffset' => 693,
                                            'endOffset' => 694,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'ON',
                                            'startOffset' => 695,
                                            'endOffset' => 697,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'DELETE',
                                            'startOffset' => 698,
                                            'endOffset' => 704,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'CASCADE',
                                            'startOffset' => 705,
                                            'endOffset' => 712,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'ON',
                                            'startOffset' => 713,
                                            'endOffset' => 715,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'UPDATE',
                                            'startOffset' => 716,
                                            'endOffset' => 722,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'RESTRICT',
                                            'startOffset' => 723,
                                            'endOffset' => 731,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_KEYWORD,
                                            'content' => 'UNIQUE',
                                            'startOffset' => 755,
                                            'endOffset' => 761,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => '(',
                                            'startOffset' => 761,
                                            'endOffset' => 762,
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_PARENTHESIS,
                                            'content' => null,
                                            'startOffset' => 762,
                                            'endOffset' => 768,
                                            'children' => [
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_TOKEN,
                                                    'content' => 'c1',
                                                    'startOffset' => 762,
                                                    'endOffset' => 764,
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_OPERATOR,
                                                    'content' => ',',
                                                    'startOffset' => 764,
                                                    'endOffset' => 765,
                                                ]),
                                                new SqlToken([
                                                    'type' => SqlToken::TYPE_TOKEN,
                                                    'content' => 'c2',
                                                    'startOffset' => 766,
                                                    'endOffset' => 768,
                                                ]),
                                            ],
                                        ]),
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_OPERATOR,
                                            'content' => ')',
                                            'startOffset' => 768,
                                            'endOffset' => 769,
                                        ]),
                                    ],
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_OPERATOR,
                                    'content' => ')',
                                    'startOffset' => 770,
                                    'endOffset' => 771,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_OPERATOR,
                                    'content' => ';',
                                    'startOffset' => 771,
                                    'endOffset' => 772,
                                ]),
                            ],
                        ]),
                        new SqlToken([
                            'type' => SqlToken::TYPE_STATEMENT,
                            'content' => null,
                            'startOffset' => 773,
                            'endOffset' => 803,
                            'children' => [
                                new SqlToken([
                                    'type' => SqlToken::TYPE_KEYWORD,
                                    'content' => 'PRAGMA',
                                    'startOffset' => 773,
                                    'endOffset' => 779,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_TOKEN,
                                    'content' => 'foreign_key_list',
                                    'startOffset' => 780,
                                    'endOffset' => 796,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_OPERATOR,
                                    'content' => '(',
                                    'startOffset' => 796,
                                    'endOffset' => 797,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_PARENTHESIS,
                                    'content' => null,
                                    'startOffset' => 797,
                                    'endOffset' => 801,
                                    'children' => [
                                        new SqlToken([
                                            'type' => SqlToken::TYPE_TOKEN,
                                            'content' => 't301',
                                            'startOffset' => 797,
                                            'endOffset' => 801,
                                        ]),
                                    ],
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_OPERATOR,
                                    'content' => ')',
                                    'startOffset' => 801,
                                    'endOffset' => 802,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_OPERATOR,
                                    'content' => ';',
                                    'startOffset' => 802,
                                    'endOffset' => 803,
                                ]),
                            ],
                        ]),
                        new SqlToken([
                            'type' => SqlToken::TYPE_STATEMENT,
                            'content' => null,
                            'startOffset' => 804,
                            'endOffset' => 875,
                            'children' => [
                                new SqlToken([
                                    'type' => SqlToken::TYPE_KEYWORD,
                                    'content' => 'SELECT',
                                    'startOffset' => 804,
                                    'endOffset' => 810,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_OPERATOR,
                                    'content' => '*',
                                    'startOffset' => 810,
                                    'endOffset' => 811,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_KEYWORD,
                                    'content' => 'FROM',
                                    'startOffset' => 811,
                                    'endOffset' => 815,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_IDENTIFIER,
                                    'content' => 'T_constraints_1',
                                    'startOffset' => 822,
                                    'endOffset' => 839,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_KEYWORD,
                                    'content' => 'WHERE',
                                    'startOffset' => 839,
                                    'endOffset' => 844,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_KEYWORD,
                                    'content' => 'NOT',
                                    'startOffset' => 845,
                                    'endOffset' => 848,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_IDENTIFIER,
                                    'content' => 'C_check',
                                    'startOffset' => 848,
                                    'endOffset' => 857,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_OPERATOR,
                                    'content' => '=',
                                    'startOffset' => 857,
                                    'endOffset' => 858,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_STRING_LITERAL,
                                    'content' => 'foo\'bar',
                                    'startOffset' => 858,
                                    'endOffset' => 868,
                                ]),
                                new SqlToken([
                                    'type' => SqlToken::TYPE_OPERATOR,
                                    'content' => ';',
                                    'startOffset' => 874,
                                    'endOffset' => 875,
                                ]),
                            ],
                        ]),
                    ],
                ]),
            ],
        ];
    }

    /**
     * @dataProvider sqlProvider
     * @param string $sql
     */
    public function testTokenizer($sql, SqlToken $expectedToken)
    {
        $actualToken = (new SqlTokenizer($sql))->tokenize();
        $this->assertEquals($expectedToken, $actualToken);
    }

    /**
     * Use this to export SqlToken for tests.
     * @param SqlToken $token
     * @return array
     */
    private function exportToken(SqlToken $token)
    {
        $result = get_object_vars($token);
        unset($result['parent']);
        $result['children'] = array_map(function (SqlToken $token) {
            return $this->exportToken($token);
        }, $token->children);
        return $result;
    }
}

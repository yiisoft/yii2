<?php
namespace yii\unit\framework\helpers;

use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yiiunit\TestCase;

/**
 * StringHelperTest
 * @group helpers
 */
class StringHelperTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testStrlen()
    {
        $this->assertEquals(4, StringHelper::byteLength('this'));
        $this->assertEquals(6, StringHelper::byteLength('это'));
    }

    public function testSubstr()
    {
        $this->assertEquals('th', StringHelper::byteSubstr('this', 0, 2));
        $this->assertEquals('э', StringHelper::byteSubstr('это', 0, 2));
    }

    public function testBasename()
    {
        $this->assertEquals('', StringHelper::basename(''));

        $this->assertEquals('file', StringHelper::basename('file'));
        $this->assertEquals('file.test', StringHelper::basename('file.test', '.test2'));
        $this->assertEquals('file', StringHelper::basename('file.test', '.test'));

        $this->assertEquals('file', StringHelper::basename('/file'));
        $this->assertEquals('file.test', StringHelper::basename('/file.test', '.test2'));
        $this->assertEquals('file', StringHelper::basename('/file.test', '.test'));

        $this->assertEquals('file', StringHelper::basename('/path/to/file'));
        $this->assertEquals('file.test', StringHelper::basename('/path/to/file.test', '.test2'));
        $this->assertEquals('file', StringHelper::basename('/path/to/file.test', '.test'));

        $this->assertEquals('file', StringHelper::basename('\file'));
        $this->assertEquals('file.test', StringHelper::basename('\file.test', '.test2'));
        $this->assertEquals('file', StringHelper::basename('\file.test', '.test'));

        $this->assertEquals('file', StringHelper::basename('C:\file'));
        $this->assertEquals('file.test', StringHelper::basename('C:\file.test', '.test2'));
        $this->assertEquals('file', StringHelper::basename('C:\file.test', '.test'));

        $this->assertEquals('file', StringHelper::basename('C:\path\to\file'));
        $this->assertEquals('file.test', StringHelper::basename('C:\path\to\file.test', '.test2'));
        $this->assertEquals('file', StringHelper::basename('C:\path\to\file.test', '.test'));

        // mixed paths
        $this->assertEquals('file.test', StringHelper::basename('/path\to/file.test'));
        $this->assertEquals('file.test', StringHelper::basename('/path/to\file.test'));
        $this->assertEquals('file.test', StringHelper::basename('\path/to\file.test'));

        // \ and / in suffix
        $this->assertEquals('file', StringHelper::basename('/path/to/filete/st', 'te/st'));
        $this->assertEquals('st', StringHelper::basename('/path/to/filete/st', 'te\st'));
        $this->assertEquals('file', StringHelper::basename('/path/to/filete\st', 'te\st'));
        $this->assertEquals('st', StringHelper::basename('/path/to/filete\st', 'te/st'));

        // http://www.php.net/manual/en/function.basename.php#72254
        $this->assertEquals('foo', StringHelper::basename('/bar/foo/'));
        $this->assertEquals('foo', StringHelper::basename('\\bar\\foo\\'));
    }

    public function testTruncate()
    {
        $this->assertEquals('привет, я multibyte...', StringHelper::truncate('привет, я multibyte строка!', 20));
        $this->assertEquals('Не трогаем строку', StringHelper::truncate('Не трогаем строку', 20));
        $this->assertEquals('исполь!!!', StringHelper::truncate('используем восклицательные знаки', 6, '!!!'));
    }

    public function testTruncateWords()
    {
        $this->assertEquals('это тестовая multibyte строка', StringHelper::truncateWords('это тестовая multibyte строка', 5));
        $this->assertEquals('это тестовая multibyte...', StringHelper::truncateWords('это тестовая multibyte строка', 3));
        $this->assertEquals('это тестовая multibyte!!!', StringHelper::truncateWords('это тестовая multibyte строка', 3, '!!!'));
        $this->assertEquals('это строка с          неожиданными...', StringHelper::truncateWords('это строка с          неожиданными пробелами', 4));
    }

    public function testTruncateHtml(){
        $html       = '<div class="article_content withbar"><p><a href="http://www.auto-motor-und-sport.de/porsche-8478.html">Porsche</a> 921 nennt <a href="http://www.anthonycolard.net/Anthony_Colard_-_A_World_Of_Design/Porsche_921.html" target="_blank">Colard</a> seinen Entwurf, den er im Zuge einer Bewerbung bei Porsche erstellt hat. Auch der 921 bleibt dem Ursprungskonzept mit Frontmotor und Transaxle-Bauweise treu. Projektiert ist ein rund 400 PS starker V8-Motor, der den Porsche 921 auf gut 280 km/h beschleunigen soll.</p><h2 class="zwischenueberschrift">Porsche 928-Look mit Elfer-Zitaten</h2><p>Beim Design findet sich viel von der DNA des 928 wieder, an der Front setzte Colard aber auf Gleichteile und fügte Scheinwerfer des 991 ein. Das Passagierabteil gestaltete Colard filigraner, die Kotflügel vorn zeigen sich wie beim Elfer leicht überhöht. Die hinteren Kotflügelbacken sind ebenfalls deutlich ausgeformt und an den Elfer angelehnt.</p><p>Auch beim Heckdesign ist Anthony Colard eine elegante Mischung aus 928er-Elementen und 911er-Zitaten gelungen. Die dreieckigen Rückleuchten reichen bis weit in die Flanken. Anders als beim 928 mündet das Heck in eine markante Abrisskante. Das Untergeschoss wird von einem Zweirohr-Auspuff und dem obligatorischen Diffusor bestimmt.</p><p>Was Porsche zu dem Entwurf von Anthony Colard gesagt hat, wissen wir nicht. Was Sie darüber denken, würden wir gerne in den Kommentaren lesen.</p><div class="autor"><img src="http://img4.auto-motor-und-sport.de/Uli-Baumann-authorThumbnail-3ff3c523-681045.jpg" alt="Uli Baumann" height="40" width="40" class="is_Lazyload_Image"><div>Von <a rel="author" target="_blank" href="https://plus.google.com/115490205104687086703/posts">Uli Baumann</a> am 30. April 2014</div></div></div>';
        $shorten    = '<div class="article_content withbar"><p><a href="http://www.auto-motor-und-sport.de/porsche-8478.html">Porsche</a> 921 nennt <a href="http://www.anthonycolard.net/Anthony_Colard_-_A_World_Of_Design/Porsche_921.html" target="_blank">Colard</a> seinen Entwurf, den er im Zuge einer Bewerbung bei Porsche erstellt hat. Auch der...</p></div>';
        $shorten2   = '<div class="article_content withbar"><p><a href="http://www.auto-motor-und-sport.de/porsche-8478.html">Porsche</a> 921 nennt <a href="http://www.anthonycolard.net/Anthony_Colard_-_A_World_Of_Design/Porsche_921.html" target="_blank">Colard</a> seinen Entwurf, den er im Zuge einer Bewerbung bei<a href="site/index">read more</a></p></div>';
        $shorten3   = '<div class="article_content withbar"><p><a href="http://www.auto-motor-und-sport.de/porsche-8478.html">Porsche</a> 921 nennt <a href="http://www.anthonycolard.net/Anthony_Colard_-_A_World_Of_Design/Porsche_921.html" target="_blank">Colard</a> seinen Entwurf, den er im Zuge einer Bewerbung bei <a href="site/index">read more</a></p></div>';

        $this->assertEquals($shorten, StringHelper::truncateHtml($html,110,'...',false,true));
        $this->assertEquals($shorten, StringHelper::truncateHtml($html,110));
        $this->assertEquals($shorten2, StringHelper::truncateHtml($html,110,'<a href="site/index">read more</a>',false,true));
        $this->assertEquals($shorten2, StringHelper::truncateHtml($html,110,'<a href="site/index">read more</a>'));
        $this->assertEquals($shorten3, StringHelper::truncateHtml($html,110,'<a href="site/index">read more</a>',true,true));
    }
}

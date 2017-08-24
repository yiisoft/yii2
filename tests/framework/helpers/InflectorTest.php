<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use yii\helpers\Inflector;
use yiiunit\TestCase;

/**
 * @group helpers
 */
class InflectorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // destroy application, Helper must work without Yii::$app
        $this->destroyApplication();
    }

    public function testPluralize()
    {
        $testData = [
            'move' => 'moves',
            'foot' => 'feet',
            'child' => 'children',
            'human' => 'humans',
            'man' => 'men',
            'staff' => 'staff',
            'tooth' => 'teeth',
            'person' => 'people',
            'mouse' => 'mice',
            'touch' => 'touches',
            'hash' => 'hashes',
            'shelf' => 'shelves',
            'potato' => 'potatoes',
            'bus' => 'buses',
            'test' => 'tests',
            'car' => 'cars',
            'netherlands' => 'netherlands',
            'currency' => 'currencies',
        ];

        foreach ($testData as $testIn => $testOut) {
            $this->assertSame($testOut, Inflector::pluralize($testIn));
            $this->assertSame(ucfirst($testOut), ucfirst(Inflector::pluralize($testIn)));
        }
    }

    public function testSingularize()
    {
        $testData = [
            'moves' => 'move',
            'feet' => 'foot',
            'children' => 'child',
            'humans' => 'human',
            'men' => 'man',
            'staff' => 'staff',
            'teeth' => 'tooth',
            'people' => 'person',
            'mice' => 'mouse',
            'touches' => 'touch',
            'hashes' => 'hash',
            'shelves' => 'shelf',
            'potatoes' => 'potato',
            'buses' => 'bus',
            'tests' => 'test',
            'cars' => 'car',
            'Netherlands' => 'Netherlands',
            'currencies' => 'currency',
        ];
        foreach ($testData as $testIn => $testOut) {
            $this->assertSame($testOut, Inflector::singularize($testIn));
            $this->assertSame(ucfirst($testOut), ucfirst(Inflector::singularize($testIn)));
        }
    }

    public function testTitleize()
    {
        $this->assertSame('Me my self and i', Inflector::titleize('MeMySelfAndI'));
        $this->assertSame('Me My Self And I', Inflector::titleize('MeMySelfAndI', true));
    }

    public function testCamelize()
    {
        $this->assertSame('MeMySelfAndI', Inflector::camelize('me my_self-andI'));
        $this->assertSame('QweQweEwq', Inflector::camelize('qwe qwe^ewq'));
    }

    public function testUnderscore()
    {
        $this->assertSame('me_my_self_and_i', Inflector::underscore('MeMySelfAndI'));
    }

    public function testCamel2words()
    {
        $this->assertSame('Camel Case', Inflector::camel2words('camelCase'));
        $this->assertSame('Lower Case', Inflector::camel2words('lower_case'));
        $this->assertSame('Tricky Stuff It Is Testing', Inflector::camel2words(' tricky_stuff.it-is testing... '));
    }

    public function testCamel2id()
    {
        $this->assertSame('post-tag', Inflector::camel2id('PostTag'));
        $this->assertSame('post_tag', Inflector::camel2id('PostTag', '_'));

        $this->assertSame('post-tag', Inflector::camel2id('postTag'));
        $this->assertSame('post_tag', Inflector::camel2id('postTag', '_'));

        $this->assertSame('foo-ybar', Inflector::camel2id('FooYBar', '-', false));
        $this->assertSame('foo_ybar', Inflector::camel2id('fooYBar', '_', false));

        $this->assertSame('foo-y-bar', Inflector::camel2id('FooYBar', '-', true));
        $this->assertSame('foo_y_bar', Inflector::camel2id('fooYBar', '_', true));
    }

    public function testId2camel()
    {
        $this->assertSame('PostTag', Inflector::id2camel('post-tag'));
        $this->assertSame('PostTag', Inflector::id2camel('post_tag', '_'));

        $this->assertSame('PostTag', Inflector::id2camel('post-tag'));
        $this->assertSame('PostTag', Inflector::id2camel('post_tag', '_'));

        $this->assertSame('FooYBar', Inflector::id2camel('foo-y-bar'));
        $this->assertSame('FooYBar', Inflector::id2camel('foo_y_bar', '_'));
    }

    public function testHumanize()
    {
        $this->assertSame('Me my self and i', Inflector::humanize('me_my_self_and_i'));
        $this->assertSame('Me My Self And I', Inflector::humanize('me_my_self_and_i', true));
    }

    public function testVariablize()
    {
        $this->assertSame('customerTable', Inflector::variablize('customer_table'));
    }

    public function testTableize()
    {
        $this->assertSame('customer_tables', Inflector::tableize('customerTable'));
    }

    public function testSlugCommons()
    {
        $data = [
            '' => '',
            'hello world 123' => 'hello-world-123',
            'remove.!?[]{}…symbols' => 'removesymbols',
            'minus-sign' => 'minus-sign',
            'mdash—sign' => 'mdash-sign',
            'ndash–sign' => 'ndash-sign',
            'áàâéèêíìîóòôúùûã' => 'aaaeeeiiiooouuua',
            'älä lyö ääliö ööliä läikkyy' => 'ala-lyo-aalio-oolia-laikkyy',
        ];

        foreach ($data as $source => $expected) {
            if (extension_loaded('intl')) {
                $this->assertSame($expected, FallbackInflector::slug($source));
            }
            $this->assertSame($expected, Inflector::slug($source));
        }
    }

    public function testSlugIntl()
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('intl extension is required.');
        }

        // Some test strings are from https://github.com/bergie/midgardmvc_helper_urlize. Thank you, Henri Bergius!
        $data = [
            // Korean
            '해동검도' => 'haedong-geomdo',
            // Hiragana
            'ひらがな' => 'hiragana',
            // Georgian
            'საქართველო' => 'sakartvelo',
            // Arabic
            'العربي' => 'alrby',
            'عرب' => 'rb',
            // Hebrew
            'עִבְרִית' => 'iberiyt',
            // Turkish
            'Sanırım hepimiz aynı şeyi düşünüyoruz.' => 'sanirim-hepimiz-ayni-seyi-dusunuyoruz',
            // Russian
            'недвижимость' => 'nedvizimost',
            'Контакты' => 'kontakty',
            // Chinese
            '美国' => 'mei-guo',
            // Estonian
            'Jääär' => 'jaaar',
        ];

        foreach ($data as $source => $expected) {
            $this->assertSame($expected, Inflector::slug($source));
        }
    }

    public function testTransliterateStrict()
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('intl extension is required.');
        }

        // Some test strings are from https://github.com/bergie/midgardmvc_helper_urlize. Thank you, Henri Bergius!
        $data = [
            // Korean
            '해동검도' => 'haedong-geomdo',
            // Hiragana
            'ひらがな' => 'hiragana',
            // Georgian
            'საქართველო' => 'sakartvelo',
            // Arabic
            'العربي' => 'ạlʿrby',
            'عرب' => 'ʿrb',
            // Hebrew
            'עִבְרִית' => 'ʻibĕriyţ',
            // Turkish
            'Sanırım hepimiz aynı şeyi düşünüyoruz.' => 'Sanırım hepimiz aynı şeyi düşünüyoruz.',

            // Russian
            'недвижимость' => 'nedvižimostʹ',
            'Контакты' => 'Kontakty',

            // Ukrainian
            'Українська: ґанок, європа' => 'Ukraí̈nsʹka: g̀anok, êvropa',

            // Serbian
            'Српска: ђ, њ, џ!' => 'Srpska: đ, n̂, d̂!',

            // Spanish
            '¿Español?' => '¿Español?',
            // Chinese
            '美国' => 'měi guó',
        ];

        foreach ($data as $source => $expected) {
            $this->assertSame($expected, Inflector::transliterate($source, Inflector::TRANSLITERATE_STRICT));
        }
    }

    public function testTransliterateMedium()
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('intl extension is required.');
        }

        // Some test strings are from https://github.com/bergie/midgardmvc_helper_urlize. Thank you, Henri Bergius!
        $data = [
            // Korean
            '해동검도' => ['haedong-geomdo'],
            // Hiragana
            'ひらがな' => ['hiragana'],
            // Georgian
            'საქართველო' => ['sakartvelo'],
            // Arabic
            'العربي' => ['alʿrby'],
            'عرب' => ['ʿrb'],
            // Hebrew
            'עִבְרִית' => ['\'iberiyt', 'ʻiberiyt'],
            // Turkish
            'Sanırım hepimiz aynı şeyi düşünüyoruz.' => ['Sanirim hepimiz ayni seyi dusunuyoruz.'],

            // Russian
            'недвижимость' => ['nedvizimost\'', 'nedvizimostʹ'],
            'Контакты' => ['Kontakty'],

            // Ukrainian
            'Українська: ґанок, європа' => ['Ukrainsʹka: ganok, evropa', 'Ukrains\'ka: ganok, evropa'],

            // Serbian
            'Српска: ђ, њ, џ!' => ['Srpska: d, n, d!'],

            // Spanish
            '¿Español?' => ['¿Espanol?'],
            // Chinese
            '美国' => ['mei guo'],
        ];

        foreach ($data as $source => $allowed) {
            $this->assertIsOneOf(Inflector::transliterate($source, Inflector::TRANSLITERATE_MEDIUM), $allowed);
        }
    }

    public function testTransliterateLoose()
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('intl extension is required.');
        }

        // Some test strings are from https://github.com/bergie/midgardmvc_helper_urlize. Thank you, Henri Bergius!
        $data = [
            // Korean
            '해동검도' => ['haedong-geomdo'],
            // Hiragana
            'ひらがな' => ['hiragana'],
            // Georgian
            'საქართველო' => ['sakartvelo'],
            // Arabic
            'العربي' => ['alrby'],
            'عرب' => ['rb'],
            // Hebrew
            'עִבְרִית' => ['\'iberiyt', 'iberiyt'],
            // Turkish
            'Sanırım hepimiz aynı şeyi düşünüyoruz.' => ['Sanirim hepimiz ayni seyi dusunuyoruz.'],

            // Russian
            'недвижимость' => ['nedvizimost\'', 'nedvizimost'],
            'Контакты' => ['Kontakty'],

            // Ukrainian
            'Українська: ґанок, європа' => ['Ukrainska: ganok, evropa', 'Ukrains\'ka: ganok, evropa'],

            // Serbian
            'Српска: ђ, њ, џ!' => ['Srpska: d, n, d!'],

            // Spanish
            '¿Español?' => ['Espanol?'],
            // Chinese
            '美国' => ['mei guo'],
        ];

        foreach ($data as $source => $allowed) {
            $this->assertIsOneOf(Inflector::transliterate($source, Inflector::TRANSLITERATE_LOOSE), $allowed);
        }
    }

    public function testSlugPhp()
    {
        $data = [
            'we have недвижимость' => 'we-have',
        ];

        foreach ($data as $source => $expected) {
            $this->assertSame($expected, FallbackInflector::slug($source));
        }
    }

    public function testClassify()
    {
        $this->assertSame('CustomerTable', Inflector::classify('customer_tables'));
    }

    public function testOrdinalize()
    {
        $this->assertSame('21st', Inflector::ordinalize('21'));
        $this->assertSame('22nd', Inflector::ordinalize('22'));
        $this->assertSame('23rd', Inflector::ordinalize('23'));
        $this->assertSame('24th', Inflector::ordinalize('24'));
        $this->assertSame('25th', Inflector::ordinalize('25'));
        $this->assertSame('111th', Inflector::ordinalize('111'));
        $this->assertSame('113th', Inflector::ordinalize('113'));
    }

    public function testSentence()
    {
        $array = [];
        $this->assertSame('', Inflector::sentence($array));

        $array = ['Spain'];
        $this->assertSame('Spain', Inflector::sentence($array));

        $array = ['Spain', 'France'];
        $this->assertSame('Spain and France', Inflector::sentence($array));

        $array = ['Spain', 'France', 'Italy'];
        $this->assertSame('Spain, France and Italy', Inflector::sentence($array));

        $array = ['Spain', 'France', 'Italy', 'Germany'];
        $this->assertSame('Spain, France, Italy and Germany', Inflector::sentence($array));

        $array = ['Spain', 'France'];
        $this->assertSame('Spain or France', Inflector::sentence($array, ' or '));

        $array = ['Spain', 'France', 'Italy'];
        $this->assertSame('Spain, France or Italy', Inflector::sentence($array, ' or '));

        $array = ['Spain', 'France'];
        $this->assertSame('Spain and France', Inflector::sentence($array, ' and ', ' or ', ' - '));

        $array = ['Spain', 'France', 'Italy'];
        $this->assertSame('Spain - France or Italy', Inflector::sentence($array, ' and ', ' or ', ' - '));
    }
}

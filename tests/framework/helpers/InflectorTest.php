<?php

namespace yiiunit\framework\helpers;

use Yii;
use yii\helpers\Inflector;
use yiiunit\TestCase;

/**
 * @group helpers
 */
class InflectorTest extends TestCase
{
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
        ];

        foreach ($testData as $testIn => $testOut) {
            $this->assertEquals($testOut, Inflector::pluralize($testIn));
            $this->assertEquals(ucfirst($testOut), ucfirst(Inflector::pluralize($testIn)));
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
        ];
        foreach ($testData as $testIn => $testOut) {
            $this->assertEquals($testOut, Inflector::singularize($testIn));
            $this->assertEquals(ucfirst($testOut), ucfirst(Inflector::singularize($testIn)));
        }
    }

    public function testTitleize()
    {
        $this->assertEquals("Me my self and i", Inflector::titleize('MeMySelfAndI'));
        $this->assertEquals("Me My Self And I", Inflector::titleize('MeMySelfAndI', true));
    }

    public function testCamelize()
    {
        $this->assertEquals("MeMySelfAndI", Inflector::camelize('me my_self-andI'));
        $this->assertEquals("QweQweEwq", Inflector::camelize('qwe qwe^ewq'));
    }

    public function testUnderscore()
    {
        $this->assertEquals("me_my_self_and_i", Inflector::underscore('MeMySelfAndI'));
    }

    public function testCamel2words()
    {
        $this->assertEquals('Camel Case', Inflector::camel2words('camelCase'));
        $this->assertEquals('Lower Case', Inflector::camel2words('lower_case'));
        $this->assertEquals('Tricky Stuff It Is Testing', Inflector::camel2words(' tricky_stuff.it-is testing... '));
    }

    public function testCamel2id()
    {
        $this->assertEquals('post-tag', Inflector::camel2id('PostTag'));
        $this->assertEquals('post_tag', Inflector::camel2id('PostTag', '_'));

        $this->assertEquals('post-tag', Inflector::camel2id('postTag'));
        $this->assertEquals('post_tag', Inflector::camel2id('postTag', '_'));

        $this->assertEquals('foo-ybar', Inflector::camel2id('FooYBar', '-', false));
        $this->assertEquals('foo_ybar', Inflector::camel2id('fooYBar', '_', false));

        $this->assertEquals('foo-y-bar', Inflector::camel2id('FooYBar', '-', true));
        $this->assertEquals('foo_y_bar', Inflector::camel2id('fooYBar', '_', true));
    }

    public function testId2camel()
    {
        $this->assertEquals('PostTag', Inflector::id2camel('post-tag'));
        $this->assertEquals('PostTag', Inflector::id2camel('post_tag', '_'));

        $this->assertEquals('PostTag', Inflector::id2camel('post-tag'));
        $this->assertEquals('PostTag', Inflector::id2camel('post_tag', '_'));

        $this->assertEquals('FooYBar', Inflector::id2camel('foo-y-bar'));
        $this->assertEquals('FooYBar', Inflector::id2camel('foo_y_bar', '_'));
    }

    public function testHumanize()
    {
        $this->assertEquals("Me my self and i", Inflector::humanize('me_my_self_and_i'));
        $this->assertEquals("Me My Self And I", Inflector::humanize('me_my_self_and_i', true));
    }

    public function testVariablize()
    {
        $this->assertEquals("customerTable", Inflector::variablize('customer_table'));
    }

    public function testTableize()
    {
        $this->assertEquals("customer_tables", Inflector::tableize('customerTable'));
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
                $this->assertEquals($expected, FallbackInflector::slug($source));
            }
            $this->assertEquals($expected, Inflector::slug($source));
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
            $this->assertEquals($expected, Inflector::slug($source));
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
            $this->assertEquals($expected, Inflector::transliterate($source, Inflector::TRANSLITERATE_STRICT));
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
            '해동검도' => 'haedong-geomdo',
            // Hiragana
            'ひらがな' => 'hiragana',
            // Georgian
            'საქართველო' => 'sakartvelo',
            // Arabic
            'العربي' => 'alʿrby',
            'عرب' => 'ʿrb',
            // Hebrew
            'עִבְרִית' => 'ʻiberiyt',
            // Turkish
            'Sanırım hepimiz aynı şeyi düşünüyoruz.' => 'Sanirim hepimiz ayni seyi dusunuyoruz.',

            // Russian
            'недвижимость' => 'nedvizimostʹ',
            'Контакты' => 'Kontakty',

            // Ukrainian
            'Українська: ґанок, європа' => 'Ukrainsʹka: ganok, evropa',

            // Serbian
            'Српска: ђ, њ, џ!' => 'Srpska: d, n, d!',

            // Spanish
            '¿Español?' => '¿Espanol?',
            // Chinese
            '美国' => 'mei guo',
        ];

        foreach ($data as $source => $expected) {
            $this->assertEquals($expected, Inflector::transliterate($source, Inflector::TRANSLITERATE_MEDIUM));
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
            'Sanırım hepimiz aynı şeyi düşünüyoruz.' => 'Sanirim hepimiz ayni seyi dusunuyoruz.',

            // Russian
            'недвижимость' => 'nedvizimost',
            'Контакты' => 'Kontakty',

            // Ukrainian
            'Українська: ґанок, європа' => 'Ukrainska: ganok, evropa',

            // Serbian
            'Српска: ђ, њ, џ!' => 'Srpska: d, n, d!',

            // Spanish
            '¿Español?' => 'Espanol?',
            // Chinese
            '美国' => 'mei guo',
        ];

        foreach ($data as $source => $expected) {
            $this->assertEquals($expected, Inflector::transliterate($source, Inflector::TRANSLITERATE_LOOSE));
        }
    }

    public function testSlugPhp()
    {
        $data = [
            'we have недвижимость' => 'we-have',
        ];

        foreach ($data as $source => $expected) {
            $this->assertEquals($expected, FallbackInflector::slug($source));
        }
    }

    public function testClassify()
    {
        $this->assertEquals("CustomerTable", Inflector::classify('customer_tables'));
    }

    public function testOrdinalize()
    {
        $this->assertEquals('21st', Inflector::ordinalize('21'));
        $this->assertEquals('22nd', Inflector::ordinalize('22'));
        $this->assertEquals('23rd', Inflector::ordinalize('23'));
        $this->assertEquals('24th', Inflector::ordinalize('24'));
        $this->assertEquals('25th', Inflector::ordinalize('25'));
        $this->assertEquals('111th', Inflector::ordinalize('111'));
        $this->assertEquals('113th', Inflector::ordinalize('113'));
    }

    public function testSentence()
    {
        $array = [];
        $this->assertEquals('', Inflector::sentence($array));

        $array = ['Spain'];
        $this->assertEquals('Spain', Inflector::sentence($array));

        $array = ['Spain', 'France'];
        $this->assertEquals('Spain and France', Inflector::sentence($array));

        $array = ['Spain', 'France', 'Italy'];
        $this->assertEquals('Spain, France and Italy', Inflector::sentence($array));

        $array = ['Spain', 'France', 'Italy', 'Germany'];
        $this->assertEquals('Spain, France, Italy and Germany', Inflector::sentence($array));

        $array = ['Spain', 'France'];
        $this->assertEquals('Spain or France', Inflector::sentence($array, ' or '));

        $array = ['Spain', 'France', 'Italy'];
        $this->assertEquals('Spain, France or Italy', Inflector::sentence($array, ' or '));

        $array = ['Spain', 'France'];
        $this->assertEquals('Spain and France', Inflector::sentence($array, ' and ', ' or ', ' - '));

        $array = ['Spain', 'France', 'Italy'];
        $this->assertEquals('Spain - France or Italy', Inflector::sentence($array, ' and ', ' or ', ' - '));
    }
}

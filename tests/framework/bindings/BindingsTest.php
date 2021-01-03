<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use DateTime;
use DateTimeImmutable;
use ReflectionProperty;
use yii\base\Controller;
use yii\base\InlineAction;
use yii\bindings\BindingProperty;
use yii\bindings\ActionParameterBinder;
use yii\bindings\binders\ActiveRecordBinder;
use yii\bindings\binders\BuiltinTypeBinder;
use yii\bindings\binders\ContainerBinder;
use yii\bindings\binders\DateTimeBinder;
use yii\bindings\binders\DataFilterBinder;
use yii\bindings\BindingContext;
use yii\bindings\ModelBinderInterface;
use yiiunit\TestCase;

class TypeReflector
{
    public array $array;
    public int $int;
    public float $float;
    public bool $bool;
    public string $string;
    public ?int $nullable_int;
    public ?float $nullable_float;
    public ?bool $nullable_bool;
    public ?string $nullable_string;
    public DateTime $DateTime;
    public DateTimeImmutable $DateTimeImmutable;

    public static function getReflectionProperty($name)
    {
        $name = str_replace("?", "nullable_", $name);
        $name = str_replace("\\", "_", $name);
        return new ReflectionProperty(self::class, $name);
    }

    public static function getBindingTarget($name, $value)
    {
        return new BindingProperty(self::getReflectionProperty($name), $value);
    }
}

class ComplexObject {
    public int $int;
    public float $float;
    public bool $bool;
}

class TestController extends Controller
{

    public function actionParams(
        $mixed,
        int $int,
        float $float,
        bool $bool,
        DateTime $dateTime,
        DateTimeImmutable $dateTimeImmutable
    )
    {
    }

    public function actionNoType($value)
    {
    }

    public function actionBuiltin(int $int, float $float, bool $bool)
    {
    }

    public function actionBuiltinNullable(?int $int, ?float $float, ?bool $bool)
    {
    }

    public function actionDateTime(DateTime $dateTime, DateTimeImmutable $dateTimeImmutable)
    {
    }

    public function actionDateTimeNullable(?DateTime $dateTime, ?DateTimeImmutable $dateTimeImmutable)
    {
    }
}

class BindingTestCase extends TestCase
{
    private const DELTA = 0.0001;
    /**
     * @var ActionParameterBinder
     */
    private $parameterBinder;

    /**
     * @var ModelBinderInterface
     */
    private $builtInBinder;

    /**
     * @var ModelBinderInterface
     */
    private $dateTimeBinder;

    /**
     * @var ModelBinderInterface
     */
    private $containerBinder;

    /**
     * @var ModelBinderInterface
     */
    private $activeRecordBinder;

    /**
     * @var BindingContext
     */
    private $context = null;

    protected function setUp()
    {
        parent::setUp();
        $this->parameterBinder = new ActionParameterBinder();
        $this->builtInBinder = new BuiltinTypeBinder();
        $this->dateTimeBinder = new DateTimeBinder();
        $this->containerBinder = new ContainerBinder();
        $this->activeRecordBinder = new ActiveRecordBinder();
        $this->dataFilterBinder = new DataFilterBinder();

        $this->mockWebApplication([
            'components' => [
            ],
        ]);
    }

    public function testActionBuiltinProvider()
    {
        return [
            [0, 0, 0, /**/ "0", "0", "0"],
            [0, 0, 0, /**/ "0", "0", "0"],
            [0, 0, 0, /**/ "0", "0", "0"],
            [0, 0, 0, /**/ "0", "0", "0"],
        ];
    }

    /**
     * @dataProvider testActionBuiltinProvider
     */
    public function testActionBuiltin($int, $float, $bool, $intParam, $floatParam, $boolParam)
    {
        $action = new InlineAction("action", $this->controller, "actionBuiltin");

        $result = $this->parameterBinder->bindActionParams($action, [
            "int" => $intParam,
            "float" => $floatParam,
            "bool" => $boolParam
        ]);

        $args = $result->arguments;

        $this->assertCount(3, $args);
        $this->assertSame($int, $args['int']);
        $this->assertSame($float, $args['float']);
        $this->assertSame($bool, $args['bool']);
    }

    public function builtInBinderProvider()
    {
        return [
            [ "int", 100, "100"],
            [ "int", -100, "-100"],
            [ "int", null, "boo"],

            [ "float", 100, "100"],
            [ "float", -100.5, "-100.5"],
            [ "float", null, "boo"],

            [ "bool", true, "true"],
            [ "bool", true, "yes"],
            [ "bool", true, "on"],
            [ "bool", false, "false"],
            [ "bool", false, "no"],
            [ "bool", false, "off"],
            [ "bool", null, null]
        ];
    }

    /**
     * @dataProvider builtInBinderProvider
     */
    public function testBuiltInBinder($typeName, $expected, $value)
    {
        $binding = $this->builtInBinder->bindModel(TypeReflector::getBindingTarget($typeName, $value), $this->context);

        if ($expected) {
            $this->assertNotNull($binding);
            $this->assertInstanceOf("yii\\bindings\\BindingResult", $binding);
            $this->assertSame($expected, $binding->value);
        } else {
            $this->assertNull($binding);
        }
    }


    public function dateTimeProvider()
    {
        return [
            // DateTime
            [ "DateTime", "Y-m-d", "2020-01-01"],
            [ "DateTime", null,    "boo"],
            // DateTimeImmutable
            [ "DateTimeImmutable", "Y-m-d", "2020-01-01"],
            [ "DateTimeImmutable", null,    "boo"],
        ];
    }

    /**
     * @dataProvider dateTimeProvider
     */
    public function testDateTimeBinder($typeName, $format, $value)
    {
        $binding = $this->dateTimeBinder->bindModel(TypeReflector::getBindingTarget($typeName, $value), $this->context);

        if ($format) {
            $this->assertNotNull($binding);
            $this->assertInstanceOf("yii\\bindings\\BindingResult", $binding);
            $this->assertSame($value, $binding->value->format($format));
        } else {
            $this->assertNull($binding);
        }
    }
}
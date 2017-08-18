<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Yii;
use yii\base\InlineAction;
use yii\web\Response;
use yiiunit\TestCase;

/**
 * @group web
 */
class ControllerTest extends TestCase
{
    public function testBindActionParams()
    {
        $aksi1 = new InlineAction('aksi1', $this->controller, 'actionAksi1');

        $params = ['fromGet' => 'from query params', 'q' => 'd426', 'validator' => 'avaliable'];
        list($fromGet, $other) = $this->controller->bindActionParams($aksi1, $params);
        $this->assertSame('from query params', $fromGet);
        $this->assertSame('default', $other);

        $params = ['fromGet' => 'from query params', 'q' => 'd426', 'other' => 'avaliable'];
        list($fromGet, $other) = $this->controller->bindActionParams($aksi1, $params);
        $this->assertSame('from query params', $fromGet);
        $this->assertSame('avaliable', $other);
    }

    public function testAsJson()
    {
        $data = [
            'test' => 123,
            'example' => 'data',
        ];
        $result = $this->controller->asJson($data);
        $this->assertInstanceOf('yii\web\Response', $result);
        $this->assertSame(Yii::$app->response, $result, 'response should be the same as Yii::$app->response');
        $this->assertSame(Response::FORMAT_JSON, $result->format);
        $this->assertSame($data, $result->data);
    }

    public function testAsXml()
    {
        $data = [
            'test' => 123,
            'example' => 'data',
        ];
        $result = $this->controller->asXml($data);
        $this->assertInstanceOf('yii\web\Response', $result);
        $this->assertSame(Yii::$app->response, $result, 'response should be the same as Yii::$app->response');
        $this->assertSame(Response::FORMAT_XML, $result->format);
        $this->assertSame($data, $result->data);
    }

    public function testRedirect()
    {
        $_SERVER['REQUEST_URI'] = 'http://test-domain.com/';
        $this->assertSame($this->controller->redirect('')->headers->get('location'), '/');
        $this->assertSame($this->controller->redirect('http://some-external-domain.com')->headers->get('location'), 'http://some-external-domain.com');
        $this->assertSame($this->controller->redirect('/')->headers->get('location'), '/');
        $this->assertSame($this->controller->redirect('/something-relative')->headers->get('location'), '/something-relative');
        $this->assertSame($this->controller->redirect(['/'])->headers->get('location'), '/index.php?r=');
        $this->assertSame($this->controller->redirect(['view'])->headers->get('location'), '/index.php?r=fake%2Fview');
        $this->assertSame($this->controller->redirect(['/controller'])->headers->get('location'), '/index.php?r=controller');
        $this->assertSame($this->controller->redirect(['/controller/index'])->headers->get('location'), '/index.php?r=controller%2Findex');
        $this->assertSame($this->controller->redirect(['//controller/index'])->headers->get('location'), '/index.php?r=controller%2Findex');
        $this->assertSame($this->controller->redirect(['//controller/index', 'id' => 3])->headers->get('location'), '/index.php?r=controller%2Findex&id=3');
        $this->assertSame($this->controller->redirect(['//controller/index', 'id_1' => 3, 'id_2' => 4])->headers->get('location'), '/index.php?r=controller%2Findex&id_1=3&id_2=4');
        $this->assertSame($this->controller->redirect(['//controller/index', 'slug' => 'äöüß!"§$%&/()'])->headers->get('location'), '/index.php?r=controller%2Findex&slug=%C3%A4%C3%B6%C3%BC%C3%9F%21%22%C2%A7%24%25%26%2F%28%29');
    }

    protected function setUp()
    {
        parent::setUp();
        $this->controller = new FakeController('fake', new \yii\web\Application([
            'id' => 'app',
            'basePath' => __DIR__,

            'components' => [
                'request' => [
                    'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                    'scriptFile' => __DIR__ . '/index.php',
                    'scriptUrl' => '/index.php',
                ],
            ],
        ]));
        $this->mockWebApplication(['controller' => $this->controller]);
    }
}

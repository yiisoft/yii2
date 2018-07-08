<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Error;
use Exception;
use RuntimeException;
use yii\helpers\StringHelper;
use yii\web\HttpException;

/**
 * @group web
 */
class ResponseTest extends \yiiunit\TestCase
{
    /**
     * @var \yii\web\Response
     */
    public $response;

    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
        $this->response = new \yii\web\Response();
    }

    public function rightRanges()
    {
        // TODO test more cases for range requests and check for rfc compatibility
        // https://tools.ietf.org/html/rfc2616
        return [
            ['0-5', '0-5', 6, '12ёж'],
            ['2-', '2-66', 65, 'ёжик3456798áèabcdefghijklmnopqrstuvwxyz!"§$%&/(ёжик)=?'],
            ['-12', '55-66', 12, '(ёжик)=?'],
        ];
    }

    /**
     * @dataProvider rightRanges
     * @param string $rangeHeader
     * @param string $expectedHeader
     * @param int $length
     * @param string $expectedContent
     */
    public function testSendFileRanges($rangeHeader, $expectedHeader, $length, $expectedContent)
    {
        $dataFile = \Yii::getAlias('@yiiunit/data/web/data.txt');
        $fullContent = file_get_contents($dataFile);
        $_SERVER['HTTP_RANGE'] = 'bytes=' . $rangeHeader;
        ob_start();
        $this->response->sendFile($dataFile)->send();
        $content = ob_get_clean();

        $this->assertEquals($expectedContent, $content);
        $this->assertEquals(206, $this->response->statusCode);
        $this->assertEquals(['bytes'], $this->response->getHeader('Accept-Ranges'));
        $this->assertEquals(['bytes ' . $expectedHeader . '/' . StringHelper::byteLength($fullContent)], $this->response->getHeader('Content-Range'));
        $this->assertEquals(['text/plain'], $this->response->getHeader('Content-Type'));
        $this->assertEquals(["$length"], $this->response->getHeader('Content-Length'));
    }

    public function wrongRanges()
    {
        // TODO test more cases for range requests and check for rfc compatibility
        // https://tools.ietf.org/html/rfc2616
        return [
            ['1-2,3-5,6-10'], // multiple range request not supported
            ['5-1'],          // last-byte-pos value is less than its first-byte-pos value
            ['-100000'],      // last-byte-pos bigger then content length
            ['10000-'],       // first-byte-pos bigger then content length
        ];
    }

    /**
     * @dataProvider wrongRanges
     * @param string $rangeHeader
     */
    public function testSendFileWrongRanges($rangeHeader)
    {
        $this->expectException('yii\web\RangeNotSatisfiableHttpException');

        $dataFile = \Yii::getAlias('@yiiunit/data/web/data.txt');
        $_SERVER['HTTP_RANGE'] = 'bytes=' . $rangeHeader;
        $this->response->sendFile($dataFile);
    }

    protected function generateTestFileContent()
    {
        return '12ёжик3456798áèabcdefghijklmnopqrstuvwxyz!"§$%&/(ёжик)=?';
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/7529
     */
    public function testSendContentAsFile()
    {
        ob_start();
        $this->response->sendContentAsFile('test', 'test.txt')->send([
            'mimeType' => 'text/plain',
        ]);
        $content = ob_get_clean();

        static::assertEquals('test', $content);
        static::assertEquals(200, $this->response->statusCode);
        static::assertEquals(['application/octet-stream'], $this->response->getHeader('Content-Type'));
        static::assertEquals(['attachment; filename="test.txt"'], $this->response->getHeader('Content-Disposition'));
        static::assertEquals([4], $this->response->getHeader('Content-Length'));
    }

    public function testRedirect()
    {
        $_SERVER['REQUEST_URI'] = 'http://test-domain.com/';
        $this->assertEquals($this->response->redirect('')->getHeader('location'), ['/']);
        $this->assertEquals($this->response->redirect('http://some-external-domain.com')->getHeader('location'), ['http://some-external-domain.com']);
        $this->assertEquals($this->response->redirect('/')->getHeader('location'), ['/']);
        $this->assertEquals($this->response->redirect('/something-relative')->getHeader('location'), ['/something-relative']);
        $this->assertEquals($this->response->redirect(['/'])->getHeader('location'), ['/index.php?r=']);
        $this->assertEquals($this->response->redirect(['view'])->getHeader('location'), ['/index.php?r=view']);
        $this->assertEquals($this->response->redirect(['/controller'])->getHeader('location'), ['/index.php?r=controller']);
        $this->assertEquals($this->response->redirect(['/controller/index'])->getHeader('location'), ['/index.php?r=controller%2Findex']);
        $this->assertEquals($this->response->redirect(['//controller/index'])->getHeader('location'), ['/index.php?r=controller%2Findex']);
        $this->assertEquals($this->response->redirect(['//controller/index', 'id' => 3])->getHeader('location'), ['/index.php?r=controller%2Findex&id=3']);
        $this->assertEquals($this->response->redirect(['//controller/index', 'id_1' => 3, 'id_2' => 4])->getHeader('location'), ['/index.php?r=controller%2Findex&id_1=3&id_2=4']);
        $this->assertEquals($this->response->redirect(['//controller/index', 'slug' => 'äöüß!"§$%&/()'])->getHeader('location'), ['/index.php?r=controller%2Findex&slug=%C3%A4%C3%B6%C3%BC%C3%9F%21%22%C2%A7%24%25%26%2F%28%29']);
    }

    /**
     * @dataProvider dataProviderSetStatusCodeByException
     * @param \Exception $exception
     * @param int $statusCode
     */
    public function testSetStatusCodeByException($exception, $statusCode)
    {
        $this->response->setStatusCodeByException($exception);
        $this->assertEquals($statusCode, $this->response->getStatusCode());
    }

    public function dataProviderSetStatusCodeByException()
    {
        $data = [
            [
                new Exception(),
                500,
            ],
            [
                new RuntimeException(),
                500,
            ],
            [
                new HttpException(500),
                500,
            ],
            [
                new HttpException(403),
                403,
            ],
            [
                new HttpException(404),
                404,
            ],
            [
                new HttpException(301),
                301,
            ],
            [
                new HttpException(200),
                200,
            ],
        ];

        if (class_exists('Error')) {
            $data[] = [
                new Error(),
                500,
            ];
        }

        return $data;
    }
}

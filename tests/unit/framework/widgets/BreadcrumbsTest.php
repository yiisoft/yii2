<?php

namespace yiiunit\framework\widgets;

use Yii;
use yii\widgets\Breadcrumbs;

/**
 * @author Nelson J Morais <njmorais@gmail.com>
 * 
 * @group widgets
 */
class BreadcrumbsTest extends \yiiunit\TestCase
{
    private $breadcrumbs;
    
    public function setUp()
    {
        // dirty way to have Request object not throwing exception when running testHomeLinkNull()
        $_SERVER['SCRIPT_FILENAME'] = "/index.php";
        $_SERVER['SCRIPT_NAME'] = "/index.php";
        
        $this->mockWebApplication();
        $this->breadcrumbs = new Breadcrumbs();
    }
    
    public function testHomeLinkNull()
    {
        $this->breadcrumbs->homeLink = null;
        $this->breadcrumbs->links = ['label' => 'My Home Page', 'url' => 'http://my.example.com/yii2/link/page'];
        
        $expectedHtml = "<ul class=\"breadcrumb\"><li><a href=\"/index.php\">Home</a></li>\n"
            . "<li class=\"active\">My Home Page</li>\n"
            . "<li class=\"active\">http://my.example.com/yii2/link/page</li>\n"
            . "</ul>";
        
        ob_start();
        $this->breadcrumbs->run();
        $actualHtml = ob_get_contents();
        ob_end_clean();
  
        $this->assertEquals($expectedHtml, $actualHtml);        
    }
    
    public function testEmptyLinks()
    {
        $this->assertNull($this->breadcrumbs->run());
    }
    
    public function testHomeLinkFalse()
    {
        $this->breadcrumbs->homeLink = false;
        $this->breadcrumbs->links = ['label' => 'My Home Page', 'url' => 'http://my.example.com/yii2/link/page'];
        
        $expectedHtml = "<ul class=\"breadcrumb\"><li class=\"active\">My Home Page</li>\n"
            . "<li class=\"active\">http://my.example.com/yii2/link/page</li>\n"
            . "</ul>";
        
        ob_start();
        $this->breadcrumbs->run();
        $actualHtml = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals($expectedHtml, $actualHtml);
    }

    
    public function testHomeLink()
    { 
        $this->breadcrumbs->homeLink = ['label' => 'home-link'];
        $this->breadcrumbs->links = ['label' => 'My Home Page', 'url' => 'http://my.example.com/yii2/link/page'];
        
        $expectedHtml = "<ul class=\"breadcrumb\"><li>home-link</li>\n"
            . "<li class=\"active\">My Home Page</li>\n"
            . "<li class=\"active\">http://my.example.com/yii2/link/page</li>\n"
            . "</ul>";
        
        ob_start();
        $this->breadcrumbs->run();
        $actualHtml = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals($expectedHtml, $actualHtml);
    }
    
    public function testRenderItemException()
    {
        $link = ['url' => 'http://localhost/yii2'];
        $method = $this->reflectMethod();
        $this->setExpectedException('yii\base\InvalidConfigException');
        $method->invoke($this->breadcrumbs, $link, $this->breadcrumbs->itemTemplate);
    }
    
    public function testRenderItemLabelOnly()
    {
        $link = ['label' => 'My-<br>Test-Label'];
        $method = $this->reflectMethod();
        $encodedValue = $method->invoke($this->breadcrumbs, $link, $this->breadcrumbs->itemTemplate); 
        
        $this->assertEquals("<li>My-&lt;br&gt;Test-Label</li>\n", $encodedValue);
        
        //without encodeLabels
        $this->breadcrumbs->encodeLabels = false;
        $unencodedValue = $method->invoke($this->breadcrumbs, $link, $this->breadcrumbs->itemTemplate);
        
        $this->assertEquals("<li>My-<br>Test-Label</li>\n", $unencodedValue);
    }
    
    public function testRenderItemWithLabelAndUrl()
    {
        $link = ['label' => 'My-<br>Test-Label', 'url' => 'http://localhost/yii2'];
        $method = $this->reflectMethod();
        $encodedValue = $method->invoke($this->breadcrumbs, $link, $this->breadcrumbs->itemTemplate); 
        
        $this->assertEquals("<li><a href=\"http://localhost/yii2\">My-&lt;br&gt;Test-Label</a></li>\n", $encodedValue);
        
        // without encodeLabels
        $this->breadcrumbs->encodeLabels = false; 
        $unencodedValue = $method->invoke($this->breadcrumbs, $link, $this->breadcrumbs->itemTemplate);         
        $this->assertEquals("<li><a href=\"http://localhost/yii2\">My-<br>Test-Label</a></li>\n", $unencodedValue);
    }
    
    public function testRenderItemTemplate()
    {
        $link = ['label' => 'My-<br>Test-Label', 'url' => 'http://localhost/yii2', 'template' => "<td>{link}</td>\n"];
        $method = $this->reflectMethod();
        $encodedValue = $method->invoke($this->breadcrumbs, $link, $this->breadcrumbs->itemTemplate); 
        
        $this->assertEquals("<td><a href=\"http://localhost/yii2\">My-&lt;br&gt;Test-Label</a></td>\n", $encodedValue);
        
        // without encodeLabels
        $this->breadcrumbs->encodeLabels = false; 
        $unencodedValue = $method->invoke($this->breadcrumbs, $link, $this->breadcrumbs->itemTemplate);         
        $this->assertEquals("<td><a href=\"http://localhost/yii2\">My-<br>Test-Label</a></td>\n", $unencodedValue);        
    }
    
    /**
     * Helper methods
     */
    protected function reflectMethod($class = '\yii\widgets\Breadcrumbs', $method = 'renderItem')
    {
        $value = new \ReflectionMethod($class, $method);
        $value->setAccessible(true);
        
        return $value;
    }
}

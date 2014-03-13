<?php

namespace yii\codeception;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * BasePage is the base class for page classes that represent Web pages to be tested.
 *
 * @property string $url The URL to this page. This property is read-only.
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
abstract class BasePage extends Component
{
    /**
     * @var string|array the route (controller ID and action ID, e.g. `site/about`) to this page.
     * Use array to represent a route with GET parameters. The first element of the array represents
     * the route and the rest of the name-value pairs are treated as GET parameters, e.g. `array('site/page', 'name' => 'about')`.
     */
    public $route;
    /**
     * @var \Codeception\AbstractGuy the testing guy object
     */
    protected $guy;

    /**
     * Constructor.
     * @param \Codeception\AbstractGuy the testing guy object
     */
    public function __construct($I)
    {
        $this->guy = $I;
    }

    /**
     * Returns the URL to this page.
     * The URL will be returned by calling the URL manager of the application
     * with [[route]] and the provided parameters.
     * @param  array                  $params the GET parameters for creating the URL
     * @return string                 the URL to this page
     * @throws InvalidConfigException if [[route]] is not set or invalid
     */
    public function getUrl($params = [])
    {
        if (is_string($this->route)) {
            $params[0] = $this->route;

            return Yii::$app->getUrlManager()->createUrl($params);
        } elseif (is_array($this->route) && isset($this->route[0])) {
            return Yii::$app->getUrlManager()->createUrl(array_merge($this->route, $params));
        } else {
            throw new InvalidConfigException('The "route" property must be set.');
        }
    }

    /**
     * Creates a page instance and sets the test guy to use [[url]].
     * @param  \Codeception\AbstractGuy $I      the test guy instance
     * @param  array                    $params the GET parameters to be used to generate [[url]]
     * @return static                   the page instance
     */
    public static function openBy($I, $params = [])
    {
        $page = new static($I);
        $I->amOnPage($page->getUrl($params));

        return $page;
    }
}

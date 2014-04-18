<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\bootstrap;

use Yii;
use yii\helpers\Console;

/**
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ApiRenderer extends \yii\apidoc\templates\html\ApiRenderer
{
    use RendererTrait;

    public $layout = '@yii/apidoc/templates/bootstrap/layouts/api.php';
    public $indexView = '@yii/apidoc/templates/bootstrap/views/index.php';

    /**
     * @inheritdoc
     */
    public function render($context, $targetDir)
    {
        $types = array_merge($context->classes, $context->interfaces, $context->traits);

        $extTypes = [];
        foreach ($this->extensions as $k => $ext) {
            $extType = $this->filterTypes($types, $ext);
            if (empty($extType)) {
                unset($this->extensions[$k]);
                continue;
            }
            $extTypes[$ext] = $extType;
        }

        // render view files
        parent::render($context, $targetDir);

        if ($this->controller !== null) {
            $this->controller->stdout('generating extension index files...');
        }

        foreach ($extTypes as $ext => $extType) {
            $readme = @file_get_contents("https://raw.github.com/yiisoft/yii2-$ext/master/README.md");
            $indexFileContent = $this->renderWithLayout($this->indexView, [
                'docContext' => $context,
                'types' => $extType,
                'readme' => $readme ?: null,
            ]);
            file_put_contents($targetDir . "/ext-{$ext}-index.html", $indexFileContent);
        }

        $yiiTypes = $this->filterTypes($types, 'yii');
        if (empty($yiiTypes)) {
//			$readme = @file_get_contents("https://raw.github.com/yiisoft/yii2-framework/master/README.md");
            $indexFileContent = $this->renderWithLayout($this->indexView, [
                'docContext' => $context,
                'types' => $this->filterTypes($types, 'app'),
                'readme' => null,
            ]);
        } else {
            $readme = @file_get_contents("https://raw.github.com/yiisoft/yii2-framework/master/README.md");
            $indexFileContent = $this->renderWithLayout($this->indexView, [
                'docContext' => $context,
                'types' => $yiiTypes,
                'readme' => $readme ?: null,
            ]);
        }
        file_put_contents($targetDir . '/index.html', $indexFileContent);

        if ($this->controller !== null) {
            $this->controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
        }
    }

    public function getSourceUrl($type, $line = null)
    {
        if (is_string($type)) {
            $type = $this->apiContext->getType($type);
        }

        $baseUrl = 'https://github.com/yiisoft/yii2/blob/master';
        switch ($this->getTypeCategory($type)) {
            case 'yii':
                $url = '/framework/' . str_replace('\\', '/', substr($type->name, 4)) . '.php';
                break;
            case 'app':
                return null;
            default:
                $url = '/extensions/' . str_replace('\\', '/', substr($type->name, 4)) . '.php';
                break;
        }

        if ($line === null)
            return $baseUrl . $url;
        else
            return $baseUrl . $url . '#L' . $line;
    }
}

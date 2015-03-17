{use class="yii\web\JqueryAsset"}
{JqueryAsset::register($this)|void}
{$this->beginPage()}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="{$app->charset}"/>
    <title>{$this->title|escape}</title>
    {$this->head()}
</head>
<body>
{$this->beginBody()}
    body
{$this->endBody()}
</body>
{$this->endPage()}
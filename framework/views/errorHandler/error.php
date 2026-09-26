<?php
/**
 * @var \yii\web\View $this
 * @var \Throwable $exception
 * @var \yii\web\ErrorHandler $handler
 */
if ($exception instanceof \yii\web\HttpException) {
    $code = $exception->statusCode;
} else {
    $code = $exception->getCode();
}
$name = $handler->getExceptionName($exception);
if ($name === null) {
    $name = 'Error';
}
if ($code) {
    $name .= " (#$code)";
}

if ($exception instanceof \yii\base\UserException) {
    $message = $exception->getMessage();
} else {
    $message = 'An internal server error occurred.';
}

if (method_exists($this, 'beginPage')) {
    $this->beginPage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?= $handler->htmlEncode($name) ?></title>

    <style>
        body {
            font: normal 9pt "Verdana";
            color: #000;
            background: #fff;
        }

        h1 {
            font: normal 18pt "Verdana";
            color: #f00;
            margin-bottom: .5em;
        }

        h2 {
            font: normal 14pt "Verdana";
            color: #800000;
            margin-bottom: .5em;
        }

        h3 {
            font: bold 11pt "Verdana";
        }

        p {
            font: normal 9pt "Verdana";
            color: #000;
        }

        .version {
            color: gray;
            font-size: 8pt;
            border-top: 1px solid #aaa;
            padding-top: 1em;
            margin-bottom: 1em;
        }
    </style>
</head>

<body>
    <h1><?= $handler->htmlEncode($name) ?></h1>
    <h2><?= nl2br($handler->htmlEncode($message)) ?></h2>
    <p>
        The above error occurred while the Web server was processing your request.
    </p>
    <p>
        Please contact us if you think this is a server error. Thank you.
    </p>
    <div class="version">
        <?= date('Y-m-d H:i:s') ?>
    </div>
    <?php if (method_exists($this, 'endBody')): ?>
        <?php $this->endBody() // to allow injecting code into body (mostly by Yii Debug Toolbar)?>
    <?php endif ?>
</body>
</html>
<?php if (method_exists($this, 'endPage')): ?>
    <?php $this->endPage() ?>
<?php endif ?>

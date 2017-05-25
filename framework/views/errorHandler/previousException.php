<?php
/* @var $exception \yii\base\Exception */
/* @var $handler \yii\web\ErrorHandler */
?>
<div class="previous">
    <span class="arrow">&crarr;</span>
    <h2>
        <span>Caused by:</span>
        <?php $name = $handler->getExceptionName($exception);
            if ($name !== null): ?>
            <span><?= $handler->htmlEncode($name) ?></span> &ndash;
            <?= $handler->addTypeLinks(get_class($exception)) ?>
        <?php else: ?>
            <span><?= $handler->htmlEncode(get_class($exception)) ?></span>
        <?php endif; ?>
    </h2>
    <h3><?= nl2br($handler->htmlEncode($exception->getMessage())) ?></h3>
    <p>in <span class="file"><?= $exception->getFile() ?></span> at line <span class="line"><?= $exception->getLine() ?></span></p>
    <?php if ($exception instanceof \yii\db\Exception && !empty($exception->errorInfo)) {
        echo '<pre>Error Info: ' . print_r($exception->errorInfo, true) . '</pre>';
    } ?>

    <div class="call-stack">
        <ul>
            <?= $handler->renderCallStackItem($exception->getFile(), $exception->getLine(), null, null, [], 1) ?>
            <?php for ($i = 0, $trace = $exception->getTrace(), $length = count($trace); $i < $length; ++$i): ?>
                <?= $handler->renderCallStackItem(@$trace[$i]['file'] ?: null, @$trace[$i]['line'] ?: null,
                    @$trace[$i]['class'] ?: null, @$trace[$i]['function'] ?: null, $trace[$i]['args'], $i + 2) ?>
            <?php endfor; ?>
        </ul>
    </div>

    <?= $handler->renderPreviousExceptions($exception) ?>
</div>

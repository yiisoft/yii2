<?php
/* @var $panel yii\debug\panels\MailPanel */
/* @var $mailCount integer */
if ($mailCount): ?>
<div class="yii-debug-toolbar-block">
    <a href="<?= $panel->getUrl() ?>">Mail <span class="label"><?= $mailCount ?></span></a>
</div>
<?php endif ?>

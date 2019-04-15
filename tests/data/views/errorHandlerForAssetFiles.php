<?php if (method_exists($this, 'beginPage')): ?>
<?php $this->beginPage(); ?>
<?php endif; ?>
Exception View
<?php if (method_exists($this, 'endBody')): ?>
<?php $this->endBody(); ?>
<?php endif; ?>
<?php if (method_exists($this, 'endPage')): ?>
<?php $this->endPage(); ?>
<?php endif;

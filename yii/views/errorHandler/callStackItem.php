<?php
/**
 * @var \yii\base\View $this
 * @var string|null $file
 * @var integer|null $line
 * @var string|null $class
 * @var string|null $method
 * @var integer $index
 * @var string[] $lines
 * @var integer $begin
 * @var integer $end
 * @var \yii\base\ErrorHandler $context
 */
$context = $this->context;
?>
<li class="<?php if (!$context->isCoreFile($file) || $index === 1) echo 'application'; ?> call-stack-item">
	<div class="element-wrap">
		<div class="element">
			<span class="line-number"><?php echo (int)$index; ?>.</span>
			<span class="text"><?php if ($file !== null) echo 'in ' . $context->htmlEncode($file); ?></span>
			<?php if ($method !== null): ?>
				<span class="call">
					<?php if ($file !== null) echo '&ndash;' ?>
					<?php if ($class !== null) echo $context->addTypeLinks($class) . '→'; ?><?php echo $context->addTypeLinks($method . '()'); ?>
				</span>
			<?php endif; ?>
			<span class="at"><?php if ($line !== null) echo 'at line'; ?></span>
			<span class="line"><?php if ($line !== null) echo (int)$line; ?></span>
		</div>
	</div>
	<?php if (!empty($lines)): ?>
		<div class="code-wrap">
			<div class="error-line" style="top: <?php echo 18 * (int)($line - $begin); ?>px;"></div>
			<?php for ($i = $begin; $i <= $end; ++$i): ?>
				<div class="hover-line" style="top: <?php echo 18 * (int)($i - $begin); ?>px;"></div>
			<?php endfor; ?>
			<div class="code">
				<span class="lines"><?php for ($i = $begin; $i <= $end; ++$i) echo (int)$i . '<br/>'; ?></span>
				<pre><?php for ($i = $begin; $i <= $end; ++$i) echo $context->htmlEncode($lines[$i]); ?></pre>
			</div>
		</div>
	<?php endif; ?>
</li>

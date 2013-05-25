<?php
/**
 * @var \yii\base\View $this
 * @var string $file
 * @var integer $line
 * @var integer $index
 * @var string[] $lines
 * @var integer $begin
 * @var integer $end
 * @var \yii\base\ErrorHandler $c
 */
$c = $this->context;
?>

<li class="<?php if (!$c->isCoreFile($file)) echo 'application'; ?> call-stack-item">
	<div class="element-wrap">
		<div class="element">
			<span class="number"><?php echo (int)$index; ?>.</span>
			<span class="text">in <?php echo $c->htmlEncode($file); ?></span>
			<span class="at">at line</span>
			<span class="line"><?php echo (int)$line; ?></span>
		</div>
	</div>
	<div class="code-wrap">
		<div class="error-line" style="top: <?php echo 18 * (int)($line - $begin); ?>px;"></div>
		<?php for ($i = $begin; $i <= $end; ++$i): ?>
			<div class="hover-line" style="top: <?php echo 18 * (int)($i - $begin); ?>px;"></div>
		<?php endfor; ?>
		<div class="code">
			<span class="lines"><?php for ($i = $begin; $i <= $end; ++$i) echo (int)$i . '<br/>'; ?></span>
			<pre><?php for ($i = $begin; $i <= $end; ++$i) echo $c->htmlEncode($lines[$i]); ?></pre>
		</div>
	</div>
</li>

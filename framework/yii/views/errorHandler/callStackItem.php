<?php
use yii\helpers\Html;

/**
 * @var string|null $file
 * @var integer|null $line
 * @var string|null $class
 * @var string|null $method
 * @var integer $index
 * @var string[] $lines
 * @var integer $begin
 * @var integer $end
 * @var \yii\base\ErrorHandler $this
 */
?>
<li class="<?php if (!$this->isCoreFile($file) || $index === 1) echo 'application'; ?> call-stack-item"
	data-line="<?php echo (int)($line - $begin); ?>">
	<div class="element-wrap">
		<div class="element">
			<span class="item-number"><?php echo (int)$index; ?>.</span>
			<span class="text"><?php if ($file !== null) echo 'in ' . Html::encode($file); ?></span>
			<?php if ($method !== null): ?>
				<span class="call">
					<?php if ($file !== null) echo '&ndash;' ?>
					<?php if ($class !== null) echo $this->addTypeLinks($class) . '→'; ?><?php echo $this->addTypeLinks($method . '()'); ?>
				</span>
			<?php endif; ?>
			<span class="at"><?php if ($line !== null) echo 'at line'; ?></span>
			<span class="line"><?php if ($line !== null) echo (int)$line + 1; ?></span>
		</div>
	</div>
	<?php if (!empty($lines)): ?>
		<div class="code-wrap">
			<div class="error-line"></div>
			<?php for ($i = $begin; $i <= $end; ++$i): ?><div class="hover-line"></div><?php endfor; ?>
			<div class="code">
				<?php for ($i = $begin; $i <= $end; ++$i): ?><span class="lines-item"><?php echo (int)($i + 1); ?></span><?php endfor; ?>
				<pre><?php
					// fill empty lines with a whitespace to avoid rendering problems in opera
					for ($i = $begin; $i <= $end; ++$i) {
						echo (trim($lines[$i]) == '') ? " \n" : Html::encode($lines[$i]);
					}
				?></pre>
			</div>
		</div>
	<?php endif; ?>
</li>

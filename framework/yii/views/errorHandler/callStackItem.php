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

<li class="<?php if (!$context->isCoreFile($file) || $index === 1) echo 'application '; ?>call-stack-item">
	<div class="element-wrap">
		<div class="element">
			<span class="item-number"><?php echo (int)$index; ?>.</span>
			<span class="text"><?php if ($file !== null) echo 'in ' . $context->htmlEncode($file); ?></span>
			<?php if ($method !== null): ?><span class="call"><?php if ($file !== null) echo '&ndash;' ?><?php if ($class !== null) echo $context->addTypeLinks($class) . '→'; ?><?php echo $context->addTypeLinks($method . '()'); ?></span><?php endif; ?>
			
			<span class="at"><?php if ($line !== null) echo 'at line'; ?></span>
			<span class="line"><?php if ($line !== null) echo (int)$line + 1; ?></span>
		</div>
	</div>
	<?php if (!empty($lines)): ?>
	
	<div class="code-wrap">
		<div class="code">
			<ol start="<?php echo $begin + 1; ?>">
			<?php
				$error_class = '';
				for ($i = $begin; $i <= $end; ++$i) {
					$error_class = $i == $line ? ' class="error"' : '';
					$indent = $i == $begin ? "\t" : "\t\t\t\t"; // html indentation
					echo "{$indent}<li{$error_class}><pre>" . $context->htmlEncode($lines[$i]) . "</pre></li>\n";
				}
			?>
			</ol>
		</div>
	</div>
	<?php endif; ?>
	
</li>

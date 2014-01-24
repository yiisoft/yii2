<?php
use yii\apidoc\templates\bootstrap\SideNavWidget;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 */

$this->beginContent('@yii/apidoc/templates/bootstrap/layouts/main.php'); ?>

<div class="row">
	<div class="col-md-2">
		<?php
		ksort($types);
		$nav = [];
		foreach($types as $i=>$class) {
			$namespace = $class->namespace;
			if (empty($namespace)) {
				$namespace = 'Not namespaced classes';
			}
			if (!isset($nav[$namespace])) {
				$nav[$namespace] = [
					'label' => $namespace,
					'url' => '#',
					'items' => [],
				];
			}
			$nav[$namespace]['items'][] = [
				'label' => StringHelper::basename($class->name),
				'url' => './' . $this->context->generateUrl($class->name),
				'active' => isset($type) && ($class->name == $type->name),
			];
		} ?>
		<?= SideNavWidget::widget([
			'id' => 'navigation',
			'items' => $nav,
			'view' => $this,
		])?>
	</div>
	<div class="col-md-9" role="main">
		<?= $content ?>
	</div>
</div>

<script type="text/javascript">
	/*<![CDATA[*/
	$("a.toggle").on('click', function() {
		var $this = $(this);
		if ($this.hasClass('properties-hidden')) {
			$this.text($this.text().replace(/Show/,'Hide'));
			$this.parents(".summary").find(".inherited").show();
			$this.removeClass('properties-hidden');
		} else {
			$this.text($this.text().replace(/Hide/,'Show'));
			$this.parents(".summary").find(".inherited").hide();
			$this.addClass('properties-hidden');
		}
		return false;
	});
	/*
	 $(".sourceCode a.show").toggle(function(){
	 $(this).text($(this).text().replace(/show/,'hide'));
	 $(this).parents(".sourceCode").find("div.code").show();
	 },function(){
	 $(this).text($(this).text().replace(/hide/,'show'));
	 $(this).parents(".sourceCode").find("div.code").hide();
	 });
	 $("a.sourceLink").click(function(){
	 $(this).attr('target','_blank');
	 });
	 */
	/*]]>*/
</script>

<?php $this->endContent(); ?>
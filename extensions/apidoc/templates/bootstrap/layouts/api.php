<?php

use yii\apidoc\templates\bootstrap\ApiRenderer;
use yii\apidoc\templates\bootstrap\SideNavWidget;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $types array */
/* @var $content string */

/** @var $renderer ApiRenderer */
$renderer = $this->context;

$this->beginContent('@yii/apidoc/templates/bootstrap/layouts/main.php', isset($type) ? ['type' => $type] : []); ?>

<div class="row">
    <div class="col-md-3">
        <?php
        $types = $renderer->getNavTypes(isset($type) ? $type : null, $types);
        ksort($types);
        $nav = [];
        foreach ($types as $i => $class) {
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
                'url' => './' . $renderer->generateApiUrl($class->name),
                'active' => isset($type) && ($class->name == $type->name),
            ];
        } ?>
        <?= SideNavWidget::widget([
            'id' => 'navigation',
            'items' => $nav,
            'view' => $this,
        ])?>
    </div>
    <div class="col-md-9 api-content" role="main">
        <?= $content ?>
    </div>
</div>

<script type="text/javascript">
    /*<![CDATA[*/
    $("a.toggle").on('click', function () {
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
     $(".sourceCode a.show").toggle(function () {
     $(this).text($(this).text().replace(/show/,'hide'));
     $(this).parents(".sourceCode").find("div.code").show();
     },function () {
     $(this).text($(this).text().replace(/hide/,'show'));
     $(this).parents(".sourceCode").find("div.code").hide();
     });
     $("a.sourceLink").click(function () {
     $(this).attr('target','_blank');
     });
     */
    /*]]>*/
</script>

<?php $this->endContent(); ?>

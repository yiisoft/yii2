<?php

/**
 * @var yii\web\View $this
 */

$this->beginPage();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="language" content="en" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="css/api.css" />
<script type="text/javascript" src="js/jquery.js"></script>
<?php $this->head(); ?>
<title><?php echo $this->context->pageTitle; ?></title>
</head>

<body>
<?php $this->beginBody(); ?>
<div id="apiPage">

<div id="apiHeader">
<a href="http://www.yiiframework.com">Yii Framework</a> v<?php echo Yii::getVersion(); ?> Class Reference
</div><!-- end of header -->

<div id="content">
<?php echo $content; ?>
</div><!-- end of content -->

<div id="apiFooter">
&copy; 2008-2013 by <a href="http://www.yiisoft.com">Yii Software LLC</a><br/>
All Rights Reserved.<br/>
</div><!-- end of footer -->

<script type="text/javascript">
/*<![CDATA[*/
$("a.toggle").toggle(function(){
	$(this).text($(this).text().replace(/Hide/,'Show'));
	$(this).parents(".summary").find(".inherited").hide();
},function(){
	$(this).text($(this).text().replace(/Show/,'Hide'));
	$(this).parents(".summary").find(".inherited").show();
});
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
/*]]>*/
</script>

</div><!-- end of page -->
<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
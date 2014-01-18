<?php
use yii\helpers\Html;
use yii\helpers\Json;

/* @var $this \yii\base\View */
/* @var $url string */
/* @var $enforceRedirect boolean */

$redirectJavaScript = <<<EOL
function popupWindowRedirect(url, enforceRedirect) {
	window.close();
	if ((enforceRedirect === undefined || enforceRedirect) && window.opener && !window.opener.closed) {
		window.opener.location = url;
	}
}
EOL;

$redirectJavaScript .= 'popupWindowRedirect(' . Json::encode($url) . ', ' . Json::encode($enforceRedirect) . ');';

?>
<!DOCTYPE html>
<html>
<head>
	<?= Html::script($redirectJavaScript); ?>
</head>
<body>
<h2 id="title" style="display:none;">Redirecting back to the &quot;<?= Yii::$app->name; ?>&quot;...</h2>
<h3 id="link"><a href="<?= $url; ?>">Click here to return to the &quot;<?= Yii::$app->name; ?>&quot;.</a></h3>
<script type="text/javascript">
	document.getElementById('title').style.display = '';
	document.getElementById('link').style.display = 'none';
</script>
</body>
</html>

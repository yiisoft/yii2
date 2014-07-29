<?php
use yii\helpers\Json;

/* @var $this \yii\base\View */
/* @var $url string */
/* @var $enforceRedirect boolean */
?>
<!DOCTYPE html>
<html>
<head>
    <script>
        function popupWindowRedirect(url, enforceRedirect)
        {
            if (window.opener && !window.opener.closed) {
                if (enforceRedirect === undefined || enforceRedirect) {
                    window.opener.location = url;
                }
                window.opener.focus();
                window.close();
            } else {
                window.location = url;
            }
        }
        popupWindowRedirect(<?= Json::encode($url) ?>, <?= Json::encode($enforceRedirect) ?>);
    </script>
</head>
<body>
<h2 id="title" style="display:none;">Redirecting back to the &quot;<?= Yii::$app->name ?>&quot;...</h2>
<h3 id="link"><a href="<?= $url ?>">Click here to return to the &quot;<?= Yii::$app->name ?>&quot;.</a></h3>
<script type="text/javascript">
    document.getElementById('title').style.display = '';
    document.getElementById('link').style.display = 'none';
</script>
</body>
</html>

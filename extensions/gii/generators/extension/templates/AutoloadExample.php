<?php
/**
 * This is just an example. *
 */
echo "<?php\n";
?>

namespace <?= $generator->namespace ?>;

class AutoloadExample extends \yii\base\widget {
    function run() {
        return "Hello!";
    }
}

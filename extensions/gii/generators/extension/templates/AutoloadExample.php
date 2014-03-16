<?php
/**
 * This is just an example.
 */
echo "<?php\n";
?>

namespace <?= substr($generator->namespace, 0, -1) ?>;

class AutoloadExample extends \yii\base\widget
{
    public function run()
    {
        return "Hello!";
    }
}

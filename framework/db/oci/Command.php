<?php

namespace yii\db\oci;

class Command extends \yii\db\Command
{
    /**
     * {@inheritdoc}
     */
    protected function bindPendingParams()
    {
        foreach ($this->pendingParams as $name => $value) {
            if (\PDO::PARAM_STR === $value[1]) {
                $this->pdoStatement->bindParam($name, $value[0], $value[1], strlen($value[0]));
            } else {
                $this->pdoStatement->bindValue($name, $value[0], $value[1]);
            }
        }
        $this->pendingParams = [];
    }

}

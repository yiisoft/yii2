<?php


namespace yii\db\oci;


class Command extends \yii\db\Command
{
    /**
     * {@inheritdoc}
     */
    protected function bindPendingParams()
    {
        foreach ($this->_pendingParams as $name => $value) {
            if($value[1] === \PDO::PARAM_STR) {
                $clonedValue = clone $value[0];
                $this->pdoStatement->bindParam($name, $clonedValue, $value[1], strlen($clonedValue));
            } else {
                $this->pdoStatement->bindValue($name, $value[0], $value[1]);
            }
        }
        $this->_pendingParams = [];
    }

}

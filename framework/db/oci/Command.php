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
                $val = $value[0];
                $this->pdoStatement->bindParam($name, $val, $value[1], strlen($val));
            } else {
                $this->pdoStatement->bindValue($name, $value[0], $value[1]);
            }
        }
        $this->_pendingParams = [];
    }

}

<?php

namespace yii\twig;

class TwigFilesystemLoader extends \Twig_Loader_Filesystem
{

    protected function normalizeName($name)
    {
        return parent::normalizeName(strtr($name, PATH_SEPARATOR, DIRECTORY_SEPARATOR));
    }

}

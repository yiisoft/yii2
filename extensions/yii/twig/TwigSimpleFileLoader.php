<?php

namespace yii\twig;

/**
 * Twig view file loader class
 *
 * @author dev-mraj <dev.meghraj@gmail.com>
 * @version 1.0.0
 */
class TwigSimpleFileLoader implements \Twig_LoaderInterface {

    /**
     * Path to directory where all file exists
     * @var string
     */
    private $dir;

    public function __construct($dir){
        $this->dir=$dir;
    }

    public function isFresh($name, $time){
        return filemtime($this->getFilePath($name))<=$time;
    }
    public function getSource($name){
        return file_get_contents($this->getFilePath($name));
    }
    public function getCacheKey($name){
        return $this->getFilePath($name);
    }

    protected  function getFilePath($name){
        return $this->dir.'/'.$name;
    }
}
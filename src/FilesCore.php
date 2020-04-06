<?php


namespace Fragmency\Files;


use Fragmency\Core\Application;
use Fragmency\Core\Page;
use Fragmency\Files\Readers\docx;

class FilesCore
{
    use docx;

    private $app;
    public function __construct(Application $app){
        $this->app = $app;

        Page::_S_addExtention('.docx',[$this,"readDocx"]);
    }
}
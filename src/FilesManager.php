<?php


namespace Fragmency\Files;


abstract class FilesManager
{
    private static function _S_getRoot (){return dirname(__FILE__,5);}
    private static function _S_fileExist($path){
        if(file_exists($path)) return true;

    }
    private static function _S_dirExist($path){
        $s = [strrpos($path,"/"),strrpos($path,"\\")];
        $pos = $s[0] > $s[1] ? $s[0] : $s[1];
        $path = substr($path,0,$pos);
        return file_exists($path);
    }

    private static function _S_create($name,$content = ""){
        $path = $name;
        if(file_exists($path)) return false;
        if(!self::_S_dirExist($path)) $path = root($name);
        if(file_exists($path)) return false;
        if(!self::_S_dirExist($path)) return "noFolder";
        return file_put_contents($path,$content);
    }

    private static function _S_read($name){
        $path = $name;
        if(!file_exists($path)) return false;
        if(!self::_S_dirExist($path)) $path = root($name);
        if(!file_exists($path)) return false;
        if(!self::_S_dirExist($path)) return "noFolder";
        return file_get_contents($path);
    }

    private static function _S_remove($name){
        $path = $name;
        if(!file_exists($path)) return false;
        if(!self::_S_dirExist($path)) $path = root($name);
        if(!file_exists($path)) return false;
        return unlink($path);
    }

    public static function __callStatic($name, $arguments){
        $call = '_S_'.ucfirst(strtolower($name));
        if(is_callable([__CLASS__,$call])) return call_user_func_array([__CLASS__,$call],$arguments);
    }

    public function __call($name, $arguments){
        $call = '_'.ucfirst(strtolower($name));
        if(is_callable([$this,$call])) return call_user_func_array([$this,$call],$arguments);
    }
}
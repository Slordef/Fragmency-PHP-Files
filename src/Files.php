<?php


namespace Fragmency\Files;

/**
 * Class Files
 * @package Fragmency\Files
 * @method static string getRoot ()
 * @method static bool fileExist(string $path)
 * @method static bool dirExist(string $path)
 * @method static bool|mixed create(string $name,string $content = "")
 * @method static string|mixed read(string $name)
 * @method static bool|mixed remove(string $name)
 */
class Files extends FilesManager
{

}
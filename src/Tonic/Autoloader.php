<?php

namespace Tonic;

/**
 * Autoload
 */
class Autoloader
{
    /**
     * Handles autoloading of classes
     * @param string $className Name of the class to load
     */
    public static function autoload($className)
    {
        if ('Tonic\\' === substr($className, 0, strlen('Tonic\\'))) {
            $fileName = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
            $namespace = '';
            if (false !== ($lastNsPos = strripos($className, '\\'))) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }
            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
            require $fileName;
        }
    }

}

ini_set('unserialize_callback_func', 'spl_autoload_call');
spl_autoload_register(array(new Autoloader, 'autoload'));

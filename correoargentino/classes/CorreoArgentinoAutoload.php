<?php

spl_autoload_register(function ($className) {
    $filePath = '';
    $moduleNamespace = 'CorreoArgentino\\';

    if (strpos($className, $moduleNamespace) === 0) {
        $classWithoutNamespace = substr($className, strlen($moduleNamespace));
        $filePath = '../src/' . str_replace('\\', DIRECTORY_SEPARATOR, $classWithoutNamespace) . '.php';
    } elseif (strpos($className, 'CorreoArgentino') !== false) {
        $filePath =  $className . '.php';
    }

    if (!empty($filePath) && file_exists($filePath)) {
        include_once $filePath;
    }
});



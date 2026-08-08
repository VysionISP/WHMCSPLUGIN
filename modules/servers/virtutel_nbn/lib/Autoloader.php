<?php

/**
 * PSR-4 style autoloader for the module's lib/ classes.
 *
 * Namespace WHMCS\Module\Server\VirtutelNbn\* maps onto this directory.
 * Registered once; safe to require from every module entrypoint.
 */

spl_autoload_register(function ($class) {
    $prefix = 'WHMCS\\Module\\Server\\VirtutelNbn\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

<?php

namespace Vysion\VirtutelNbn;

/**
 * Minimal PSR-4 autoloader so the module runs inside WHMCS without
 * requiring a composer install step. If composer's autoloader is
 * present it is preferred.
 */
final class Autoloader
{
    private const PREFIX = 'Vysion\\VirtutelNbn\\';

    public static function register(): void
    {
        static $registered = false;
        if ($registered) {
            return;
        }
        $registered = true;

        $composer = __DIR__ . '/../vendor/autoload.php';
        if (is_file($composer)) {
            require $composer;

            return;
        }

        spl_autoload_register(static function (string $class): void {
            if (strncmp($class, self::PREFIX, strlen(self::PREFIX)) !== 0) {
                return;
            }
            $relative = substr($class, strlen(self::PREFIX));
            $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) {
                require $file;
            }
        });
    }
}

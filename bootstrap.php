<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'SocialApi\\';
    if(!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';

    if(is_file($file)) {
        require_once $file;
    }
});

// PHP installations often omit E from variables_order; preserve process config.
foreach (['APP_ENV', 'APP_DEBUG', 'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD',
    'JWT_KEY'] as $key) {
    $value = getenv($key);
    if($value !== false && !array_key_exists($key, $_ENV)) {
        $_ENV[$key] = $value;
    }
}

$configurationFile = __DIR__ . '/config/.env';
if(is_file($configurationFile)) {
    $configuration = @parse_ini_file($configurationFile, false, INI_SCANNER_RAW);
    if($configuration === false) {
        throw new RuntimeException('Invalid local configuration.');
    }

    foreach($configuration as $key => $value) {
        if(!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }
    }
}

date_default_timezone_set('UTC');

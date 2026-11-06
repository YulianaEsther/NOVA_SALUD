<?php
declare(strict_types=1);

return [
    'name' => getenv('APP_NAME') ?: 'NOVA SALUD',
    'environment' => getenv('APP_ENV') ?: 'production',
    'debug' => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOL),
    'url' => rtrim(getenv('APP_URL') ?: '', '/'),
    'timezone' => 'America/Lima',
];

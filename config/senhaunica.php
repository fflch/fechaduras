<?php

return [
    'routes' => true,
    'prefix' => '',
    'middleware' => ['web'],
    'session_key' => 'senhaunica-socialite',
    
    'template' => 'laravel-usp-theme::master',
    'userRoutes' => 'senhaunica-users',
    'destroyUser' => true,
    'customUserField' => [],
    'findUsersGate' => 'admin',
    
    'permission' => true,
    'onlyLocalUsers' => false,
    'dropPermissions' => env('SENHAUNICA_DROP_PERMISSIONS', false),
    
    'admins' => array_map('trim', explode(',', env('SENHAUNICA_ADMINS', ''))),
    'gerentes' => array_map('trim', explode(',', env('SENHAUNICA_GERENTES', ''))),
    'users' => array_map('trim', explode(',', env('SENHAUNICA_USERS', ''))),
    
    'debug' => (bool) env('SENHAUNICA_DEBUG', false),
    'dev' => env('SENHAUNICA_DEV', 'no'),
    'callback_id' => env('SENHAUNICA_CALLBACK_ID'),
    
    'codigoUnidade' => array_map('trim', explode(',', env('SENHAUNICA_CODIGO_UNIDADE', ''))),
    'disableLoginas' => (bool) env('SENHAUNICA_DISABLE_LOGINAS', false),
];
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel TCX
    |--------------------------------------------------------------------------
    |
    | appId, appKey, tokenKey must be filled.
    | token can be filled with type 'time' or 'key',
    | if you want to use key then token key must be filled and used by client
    | but do not send it to server.
    |
    */

    'url' => '',
    'application' => [
        'id' => '',
        'secret' => '',
        'public' => '',
        'masterKey' => ''
    ],
    'token' => [
        'type' => 'param', // 'param', 'time', 'none'
    ],
    'debug' => true
];
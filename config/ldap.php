<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default LDAP Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the LDAP connections below you wish
    | to use as your default connection for all LDAP operations.
    |
    */

    'default' => env('LDAP_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | LDAP Connections
    |--------------------------------------------------------------------------
    |
    | Below you may configure each LDAP connection your application requires
    | access to. Be sure to include a valid host, port and the base DN.
    |
    */

    'connections' => [

        'default' => [
            'hosts' => explode(',', env('LDAP_HOSTS', '127.0.0.1')),
            'username' => env('LDAP_USERNAME', 'cn=user,dc=local,dc=com'),
            'password' => env('LDAP_PASSWORD', 'secret'),
            'port' => env('LDAP_PORT', 389),
            'base_dn' => env('LDAP_BASE_DN', 'dc=local,dc=com'),
            'timeout' => 5,
            'use_ssl' => false,
            'use_tls' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | LDAP Logging
    |--------------------------------------------------------------------------
    |
    | When LDAP logging is enabled, all LDAP operations will be logged using
    | the default application logging channel. This is useful for debugging
    | connection issues and verifying attributes are being retrieved.
    |
    */

    'logging' => true,

    /*
    |--------------------------------------------------------------------------
    | LDAP Cache
    |--------------------------------------------------------------------------
    |
    | LDAP caching enables the ability of caching search results to ensure
    | the LDAP server is not bombarded with requests.
    |
    */

    'cache' => [
        'enabled' => false,
        'driver' => 'cache',
    ],

];

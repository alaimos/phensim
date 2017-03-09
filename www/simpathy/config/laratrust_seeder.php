<?php

return [
    'role_structure'       => [
        'administrator' => [
            'users'   => 'c,r,u,d',
            'acl'     => 'c,r,u,d',
            'profile' => 'r,u',
            'keys'    => 'c,r,u,d',
            'job'     => 'c,r,u,d',
        ],
        'user'          => [
            'profile' => 'r,u',
            'keys'    => 'c,r,u,d',
            'job'     => 'c,r,u,d',
        ],
    ],
    'permission_structure' => [],
    'permissions_map'      => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
    ],
];

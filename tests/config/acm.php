<?php

return [
    'default' => 'default',
    'clients' => [
        'default' => [
            'Namespace' => env('ACM_NAMESPACE'),
            'Group' => env('ACM_GROUP'),
            'AccessKeyID' => env('ACM_ACCESS_KEY_ID'),
            'AccessKeySecret' => env('ACM_ACCESS_KEY_SECRET'),
        ]
    ],
];

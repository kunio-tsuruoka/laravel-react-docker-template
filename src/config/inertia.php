<?php

return [
    // This template renders in the browser; no SSR service is required.
    'ssr' => [
        'enabled' => false,
    ],

    'pages' => [
        'ensure_pages_exist' => true,
        'paths' => [resource_path('js/Pages')],
        'extensions' => ['tsx'],
    ],

    'testing' => [
        'ensure_pages_exist' => true,
    ],
];

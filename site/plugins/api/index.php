<?php

Kirby::plugin('medienhaus/api', [
    'api' => [
        'routes' => [
            [
                'pattern' => '2025/contexts',
                'action' => function () {
                    return asset('assets/2025/contexts.json')->read();
                },
            ],
            [
                'pattern' => '2025/formats',
                'action' => function () {
                    return asset('assets/2025/formats.json')->read();
                },
            ],
            [
                'pattern' => '2025/locations',
                'action' => function () {
                    return asset('assets/2025/locations.json')->read();
                },
            ],
        ],
    ],
]);

<?php

Kirby::plugin('medienhaus/api', [
    'api' => [
        'routes' => [
            [
                'pattern' => '2025/contexts',
                'action' => function () {
                    return asset('assets/udk_contexts_2025.json')->read();
                },
            ],
            [
                'pattern' => '2025/formats',
                'action' => function () {
                    return asset('assets/udk_formats_2025.json')->read();
                },
            ],
            [
                'pattern' => '2025/locations',
                'action' => function () {
                    return asset('assets/udk_locations_2025.json')->read();
                },
            ],
        ],
    ],
]);

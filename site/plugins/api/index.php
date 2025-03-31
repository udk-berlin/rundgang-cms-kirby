<?php

Kirby::plugin('medienhaus/api', [
    'api' => [
        'routes' => [
            [
                'pattern' => 'locations',
                'action' => function () {
                    return asset('assets/udk_locations_2025.json')->read();
                },
            ],
        ],
    ],
]);


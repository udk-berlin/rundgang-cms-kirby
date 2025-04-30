<?php

Kirby::plugin('medienhaus/api', [
    'api' => [
        'data' => [
            'filterPagesBy' => function ($filter, $value) {
                return kirby()->site()->pages()->filterBy(
                    $filter,
                    $value,
                    ',',
                );
            },
        ],
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
            [
                // API endpoint to filter pages by specific field, for example by value:
                // `/api/2025/filterPagesBy/?filter=format&value=project_presentation`
                //
                // for more information on Kirby’s `filterBy` method, see referece:
                // https://getkirby.com/docs/reference/objects/cms/pages/filter-by
                //
                'pattern' => '2025/filterPagesBy',
                'action' => function () {
                    $api = kirby()->api();
                    $filter = $api->requestQuery('filter', '');
                    $value = $api->requestQuery('value', '');
                    return [
                        $this->filterPagesBy($filter, $value),
                    ];
                },
            ],

            /*
            [
                'pattern' => '2026/contexts',
                'action' => function () {
                    return asset('assets/2026/contexts.json')->read();
                },
            ],
            [
                'pattern' => '2026/formats',
                'action' => function () {
                    return asset('assets/2026/formats.json')->read();
                },
            ],
            [
                'pattern' => '2026/locations',
                'action' => function () {
                    return asset('assets/2026/locations.json')->read();
                },
            ],
             */
        ],
    ],
]);

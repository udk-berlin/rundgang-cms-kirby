<?php

Kirby::plugin('medienhaus/multiselect-custom', [
    'fields' => [
        'multiselect_custom' => [
            'extends' => 'tags',
            'props' => [
                /**
                 * If set to `all`, any type of input is accepted. If set to `options` only the predefined options are accepted as input.
                 */
                'accept' => function ($value = 'options') {
                    return V::in($value, ['all', 'options']) ? $value : 'all';
                },
                /**
                 * Custom icon to replace the arrow down.
                 */
                'icon' => function (string $icon = 'checklist') {
                    return $icon;
                },
            ],

        ],
    ],
]);
<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('medienhaus/default', [
    'blueprints' => [
        'pages/default' => function ($kirby) {
            return include __DIR__ . '/blueprints/pages/default.php';
        },
    ],
]);

<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('medienhaus/2025-blueprint', [
    'blueprints' => [
        'pages/2025' => function ($kirby) {
            return include __DIR__ . '/blueprints/pages/2025.php';
        },
    ],
]);

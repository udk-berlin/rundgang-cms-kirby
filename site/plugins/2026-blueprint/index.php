<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('medienhaus/2026-blueprint', [
    'blueprints' => [
        'pages/2026' => function ($kirby) {
            return include __DIR__ . '/blueprints/pages/2026.php';
        },
    ],
]);

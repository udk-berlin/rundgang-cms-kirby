<?php

// guide: https://getkirby.com/docs/cookbook/development-deployment/programmable-blueprints#load-different-blueprints-per-user

use Kirby\Cms\App as Kirby;

Kirby::plugin('cookbook/programmable-blueprints', [
    'blueprints' => [
        'site' => function () {
            if (($user = kirby()->user()) && $user->isAdmin()) {
                return Data::read(__DIR__ . '/blueprints/site.admin.yml');
            } else {
                return Data::read(__DIR__ . '/blueprints/site.editor.yml');
            }
        },
    ]
]);

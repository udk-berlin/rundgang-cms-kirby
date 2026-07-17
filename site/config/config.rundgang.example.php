<?php

/**
 * The config file is optional. It accepts a return array with config options
 * Note: Never include more than one return statement, all options go within this single return array
 * In this example, we set debugging to true, so that errors are displayed onscreen.
 * This setting must be set to false in production.
 * All config options: https://getkirby.com/docs/reference/system/options
 */
return [
    // docs: https://getkirby.com/docs/reference/system/options/debug
    //
    'debug' => false,

    // docs: https://getkirby.com/docs/reference/system/options/yaml
    //
    'yaml.handler' => 'symfony',

    // docs: https://getkirby.com/docs/guide/api
    //
    'api' => [
        'basicAuth' => true,
    ],

    // docs: https://getkirby.com/docs/guide/languages
    //
    'languages' => true,
    'languages.detect' => true,

    // docs: https://getkirby.com/docs/guide/security#disable-the-vue-template-compiler
    //
    'panel.vue.compiler' => false,

    // docs: github.com/medienhaus/kirby-plugin-auth-ldap/
    //
    // 'medienhaus.kirby-plugin-auth-ldap' => [
    //     ...
    // ],
];

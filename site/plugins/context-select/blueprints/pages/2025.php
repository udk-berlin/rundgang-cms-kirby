<?php

// Load your JSON file
$json = file_get_contents(kirby()->root('assets') . '/exportedNested.json');
$data = json_decode($json, true);
$options = [];

// Flatten the nested structure
foreach ($data['faculties'] as $faculty) {
    if (!isset($faculty['institutes'])) {
        continue;
    }

    foreach ($faculty['institutes'] as $institute) {
        if (!isset($institute['courses'])) {
            continue;
        }

        foreach ($institute['courses'] as $course) {
            if (!isset($course['classes'])) {
                continue;
            }

            foreach ($course['classes'] as $class) {
                $id = $class['name'] . ' - ' . $course['name'] . ' - ' . $institute['name'] . ' - ' . $faculty['name'];
                $text = $class['name'];
                $info = $id;

                $options[] = [
                    'value' => $id,
                    'text' => $text,
                    'info' => $info,
                ];
            }
        }
    }
}

// sort options by text key
usort($options, function ($a, $b) {
    return strcasecmp($a['text'], $b['text']);
});


$yaml = [
    'title' => 'Rundgang 2025',
    'buttons' => [
        'settings' => true,
        'languages' => true,
        'status' => true,
    ],
    'columns' => [
        'main' => [
            'width' => '2/3',
            'sections' => [
                'editor' => [
                    'type' => 'fields',
                    'fields' => [
                        'intro' => [
                            'type' => 'textarea',
                            'label' => [
                                'en' => 'Short Description',
                                'de' => 'Kurzbeschreibung',
                            ],
                            'buttons' => false,
                            'maxlength' => 500,
                            'required' => true,
                            'size' => 'small',
                            'uploads' => false,
                        ],
                        'text' => [
                            'type' => 'textarea',
                            'label' => [
                                'en' => 'Content',
                                'de' => 'Inhalt',
                            ],
                            'required' => true,
                            'size' => 'large',
                        ],
                    ],
                ],
            ],
        ],
        'sidebar' => [
            'width' => '1/3',
            'sections' => [
                'info' => [
                    'type' => 'fields',
                    'fields' => [
                        'info' => [
                            'type' => 'info',
                            'label' => [
                                'en' => 'Multi-Language Support',
                                'de' => 'Mehrsprachenunterstützung',
                            ],
                            'icon' => 'translate',
                            'text' => [
                                'en' => 'You can select the language for your <strong>Short Description</strong> and <strong>Content</strong> in the dropdown menu above, next to the page title.',
                                'de' => 'Wähle die Sprache für deine <strong>Kurzbeschreibung</strong> und deinen <strong>Inhalt</strong> im Dropdown-Menü neben/unter dem Seiten-Titel aus.',
                            ],
                            'theme' => 'info',
                        ],
                    ],
                ],
                'authorship' => [
                    'type' => 'fields',
                    'fields' => [
                        'author' => [
                            'type' => 'users',
                            'label' => [
                                'en' => 'Author',
                                'de' => 'Autor:in',
                            ],
                            'default' => true,
                            'disabled' => true,
                            'link' => false,
                            'required' => true,
                            'translate' => false,
                        ],
                        'coauthor' => [
                            'type' => 'users',
                            'label' => [
                                'en' => 'Co-Authors (optional)',
                                'de' => 'Co-Author:innen (optional)',
                            ],
                            'help' => [
                                'en' => 'Add co-authors to <strong>{{ page.title.short(30) }}</strong> to grant them editing access. You can search for names or email addresses of other institution members.',
                                'de' => 'Füge Co-Autor:innen zu <strong>{{ page.title.short(30) }}</strong> hinzu, um diesen das Editieren zu ermöglichen. Du kannst nach Namen und Mail-Adresse von anderen Institutionsmitgliedern suchen.',
                            ],
                            'info' => '{{ user.email }}',
                            'link' => false,
                            'multiple' => true,
                            'query' => 'kirby.users.filterBy("role", "LdapUser").not(kirby.user)',
                            'search' => true,
                            'translate' => false,
                        ],
                        'categories' => [
                            'label' => 'Categories',
                            'type' => 'multiselect',
                            'max' => 1,
                            'options' => $options,
                            'required' => true,
                        ],
                    ],
                ],
                'date' => [
                    'type' => 'fields',
                    'fields' => [
                        'date' => [
                            'type' => 'structure',
                            'label' => [
                                'en' => 'Date & Time (optional)',
                                'de' => 'Datum & Uhrzeit (optional)',
                            ],
                            'help' => [
                                'en' => 'Add date(s) and time(s) for your event; this should only be filled for concerts, performances, et cetera happening at only specific times.',
                                'de' => 'Füge Tage und Uhrzeiten für dein Event hinzu; dies sollte ausschließlich für Konzerte, Performances, et cetera gemacht werden, welche nur zu bestimmten Zeitpunkten stattfinden.',
                            ],
                            'translate' => false,
                            'columns' => [
                                'date' => [
                                    'mobile' => true,
                                    'width' => '1/2',
                                ],
                                'from' => [
                                    'mobile' => true,
                                    'width' => '1/4',
                                ],
                                'to' => [
                                    'mobile' => true,
                                    'width' => '1/4',
                                ],
                            ],
                            'fields' => [
                                'date' => [
                                    'type' => 'date',
                                    'label' => [
                                        'en' => 'Date',
                                        'de' => 'Datum',
                                    ],
                                    'help' => [
                                        'en' => 'The date of the event.',
                                        'de' => 'Das Datum des Events.',
                                    ],
                                    'default' => '2025-07-18',
                                    'min' => '2025-07-18',
                                    'max' => '2025-07-20',
                                    'required' => true,
                                    'translate' => false,
                                ],
                                'from' => [
                                    'type' => 'time',
                                    'label' => [
                                        'en' => 'Start',
                                        'de' => 'Beginn',
                                    ],
                                    'help' => [
                                        'en' => 'The time the event starts.',
                                        'de' => 'Die Uhrzeit, zu der das Event beginnt.',
                                    ],
                                    'default' => '13:00',
                                    'min' => '10:00',
                                    'max' => '23:59',
                                    'required' => true,
                                    'translate' => false,
                                ],
                                'to' => [
                                    'type' => 'time',
                                    'label' => [
                                        'en' => 'End',
                                        'de' => 'Ende',
                                    ],
                                    'help' => [
                                        'en' => 'The time the event ends.',
                                        'de' => 'Die Uhrzeit, zu der das Event endet.',
                                    ],
                                    'default' => '15:00',
                                    'min' => '10:00',
                                    'max' => '23:59',
                                    'required' => true,
                                    'translate' => false,
                                ],
                            ],
                        ],
                    ],
                ],
                'files' => [
                    'type' => 'files',
                    'label' => [
                        'en' => 'Images',
                        'de' => 'Bilder',
                    ],
                    'help' => [
                        'en' => 'The following file/image types can be uploaded: <code>jpg</code>, <code>png</code>',
                        'de' => 'Folgende Bilddateien können hochgeladen werden: <code>jpg</code>, <code>png</code>',
                    ],
                    'link' => false,
                    'max' => 50,
                    'template' => '2025',
                ],
            ],
        ],
    ],
];

return $yaml;
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
                $id = $class['name'];
                $options[$id] = $class['name'] . ' - Course: ' . $course['name'] . ' - Faculty: ' . $faculty['name'];
                $options['value'] = $id;
            }
        }
    }
}


$yaml = [
    'title' => 'Content',
    'columns' => [
        'main' => [
            'width' => '2/3',
            'sections' => [
                'editor' => [
                    'type' => 'fields',
                    'fields' => [
                        'text' => [
                            'type' => 'textarea',
                            'size' => 'large',
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ],
        'sidebar' => [
            'width' => '1/3',
            'sections' => [
                'authorship' => [
                    'type' => 'fields',
                    'fields' => [
                        'categories' => [
                            'label' => 'Categories',
                            'type' => 'multiselect',
                            'max' => 1,
                            'options' => $options,
                        ],
                        'author' => [
                            'type' => 'users',
                            'default' => true,
                            'disabled' => true,
                        ],
                        'coauthor' => [
                            'type' => 'users',
                            'label' => 'Co-Authors (optional)',
                            'help' => 'Add co-authors to <strong>{{ page.title.short(30) }}</strong> to grant them editing access. You can search for names or email addresses.',
                            'info' => '{{ user.email }}',
                            'limit' => 10,
                            'link' => false,
                            'multiple' => true,
                            'query' => 'kirby.users.filterBy("role", "LdapUser").not(kirby.user)',
                            'search' => true,
                        ],
                    ],
                ],
                'date' => [
                    'type' => 'fields',
                    'fields' => [
                        'date' => [
                            'type' => 'structure',
                            'label' => 'Date & Time (optional)',
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
                                    'label' => 'Date',
                                    'help' => 'The date of the event.',
                                    'default' => '2025-07-18',
                                    'min' => '2025-07-18',
                                    'max' => '2025-07-20',
                                    'required' => true,
                                ],
                                'from' => [
                                    'type' => 'time',
                                    'label' => 'Start',
                                    'help' => 'The time the event starts.',
                                    'default' => '13:00',
                                    'min' => '10:00',
                                    'max' => '23:59',
                                    'required' => true,
                                ],
                                'to' => [
                                    'type' => 'time',
                                    'label' => 'End',
                                    'help' => 'The time the event end.',
                                    'default' => '15:00',
                                    'min' => '10:00',
                                    'max' => '23:59',
                                    'required' => true,
                                ],
                            ],
                        ],
                    ],
                ],
                'files' => [
                    'type' => 'files',
                ],
            ],
        ],
    ],
];

return $yaml;
<?php

// load the contexts JSON file
//
$context_json = asset('assets/2025/contexts.json')->read();
$context_data = json_decode($context_json, true);
$context_options = [];

// Sanitize method to remove all spaces from the string
function sanitizeId(string $id): string
{
    return str_replace(' ', '', $id);
}

// flatten the nested structure
//
foreach ($context_data['faculties'] as $faculty) {
    $faculty_id = $faculty['id'];
    $faculty_text = $faculty['name'];
    $faculty_info = $faculty['name'];

    // Add faculty if it has no institutes
    if (!isset($faculty['institutes']) || empty($faculty['institutes'])) {
        $context_options[] = [
            'value' => $faculty_id,
            'text' => $faculty_text,
            'info' => $faculty_info,
        ];
        continue;
    }

    foreach ($faculty['institutes'] as $institute) {
        $institute_id = $institute['id'];
        $institute_text = $institute['name'];
        $institute_info = $institute['name'] . ' - ' . $faculty['name'];

        // Add institute if it has no courses
        if (!isset($institute['courses']) || empty($institute['courses'])) {
            $context_options[] = [
                'value' => $institute_id,
                'text' => $institute_text,
                'info' => $institute_info,
            ];
            continue;
        }

        foreach ($institute['courses'] as $course) {
            $course_id = $course['id'];
            $course_text = $course['name'];
            $course_info = $course['name'] . ' - ' . $institute['name'] . ' - ' . $faculty['name'];

            // Add course if it has no classes
            if (!isset($course['classes']) || empty($course['classes'])) {
                $context_options[] = [
                    'value' => $course_id,
                    'text' => $course_text,
                    'info' => $course_info,
                ];
                continue;
            }

            foreach ($course['classes'] as $class) {
                $id = $class['id'];
                $text = $class['name'];
                $info = $class['name'] . ' - ' . $course['name'] . ' - ' . $institute['name'] . ' - ' . $faculty['name'];

                $context_options[] = [
                    'value' => $id,
                    'text' => $text,
                    'info' => $info,
                ];
            }
        }
    }
}

// sort context_options by text key
//
usort($context_options, function ($a, $b) {
    return strcasecmp($a['text'], $b['text']);
});

// create format object with translations
//
$format_json = asset('assets/2025/formats.json')->read();
$format_data = json_decode($format_json, true);
$format_options = [];

foreach ($format_data as $format) {
    $format_options[] = [
        'text' => ucfirst($format[kirby()->user()->language()]),
        'value' => $format['key'],
    ];
}

// sort format_options by text key
//
usort($format_options, function ($a, $b) {
    return strcasecmp($a['text'], $b['text']);
});

return [
    'title' => 'Rundgang 2025',

    # info: https://getkirby.com/releases/5/view-buttons
    #
    'buttons' => [
        //'preview' => true,
        'settings' => true,
        'languages' => true,
        'status' => true,
    ],

    # docs: https://getkirby.com/docs/reference/panel/blueprints/page#statuses
    #
    'status' => [
        'draft' => [
            'label' => [
                'en' => 'Draft',
                'de' => 'Entwurf',
            ],
        ],
        'unlisted' => [
            'label' => [
                'en' => 'Published',
                'de' => 'Veröffentlicht',
            ],
        ],
    ],

    # guide: https://getkirby.com/docs/guide/blueprints/layout
    #
    'tabs' => [

        # guide: https://getkirby.com/docs/guide/blueprints/layout
        #
        'metadata_tab' => [
            'label' => [
                'en' => 'Metadata',
                'de' => 'Metadaten',
            ],
            'icon' => 'info',

            # docs: https://getkirby.com/docs/reference/panel/blueprints/page
            #
            'columns' => [

                # guide: https://getkirby.com/docs/guide/blueprints/layout#columns
                #
                'metadata_column_main' => [
                    'width' => '1/1',

                    # docs: https://getkirby.com/docs/reference/panel/sections
                    #
                    'sections' => [

                        # docs: https://getkirby.com/docs/reference/panel/sections/fields
                        #
                        'metadata_section_info' => [
                            'type' => 'fields',
                            'fields' => [

                                # docs: https://getkirby.com/docs/reference/panel/fields/info
                                #
                                'metadata_field_single_language' => [
                                    'type' => 'info',
                                    'label' => false,
                                    'icon' => 'translate',
                                    'text' => [
                                        'en' => 'The <strong>Metadata</strong> fields can only be filled for the default language, i.e. <strong>DE</strong> in the dropdown menu above, next to the page title.',
                                        'de' => 'Die <strong>Metadaten</strong> können nur für die Standard-Sprache eingetragen werden, d.h. <strong>DE</strong> im Dropdown-Menü neben/unter dem Seiten-Titel.',
                                    ],
                                    'theme' => 'notice',
                                ],
                            ],
                        ],

                        # docs: https://getkirby.com/docs/reference/panel/sections/fields
                        #
                        'metadata_section_authorship' => [
                            'type' => 'fields',
                            'fields' => [

                                # docs: https://getkirby.com/docs/reference/panel/fields/users
                                #
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

                                # docs: https://getkirby.com/docs/reference/panel/fields/users
                                #
                                'coauthor' => [
                                    'type' => 'users',
                                    'label' => [
                                        'en' => 'Co-Authors (optional)',
                                        'de' => 'Co-Autor:innen (optional)',
                                    ],
                                    'help' => [
                                        'en' => "Add co-authors to <strong>{{ page.title.short(30) }}</strong> to grant them editing access. You can search for names or email addresses of other institution members.",
                                        'de' => "Füge Co-Autor:innen zu <strong>{{ page.title.short(30) }}</strong> hinzu, um diesen das Editieren zu ermöglichen. Du kannst nach Namen und Mail-Adresse von anderen Institutionsmitgliedern suchen.",
                                    ],
                                    'info' => "{{ user.email }}",
                                    'link' => false,
                                    'multiple' => true,
                                    'query' => "kirby.users.filterBy('role', 'LdapUser').not(kirby.user)",
                                    'search' => true,
                                    'translate' => false,
                                ],

                                # docs: https://getkirby.com/docs/reference/panel/fields/users
                                #
                                'coauthor_removal_notice' => [
                                    'type' => 'info',
                                    'label' => false,
                                    'icon' => 'alert',
                                    'text' => [
                                        'en' => 'If you are a co-author, you can remove yourself from the project, but doing so will result in losing editing access to the project.',
                                        'de' => 'Wenn du als Co-Autor:in eingetragen bist, kannst du dich selbst entfernen, aber dadurch verlierst du Editier-Zugriff für das Projekt.',
                                    ],
                                    'theme' => 'passive',
                                ],
                            ],
                        ],

                        # docs: https://getkirby.com/docs/reference/panel/sections/fields
                        #
                        'metadata_section_category' => [
                            'type' => 'fields',
                            'fields' => [

                                # docs: https://getkirby.com/docs/reference/panel/fields/multiselect
                                #
                                'categories' => [
                                    'type' => 'multiselect',
                                    'label' => [
                                        'en' => 'Context',
                                        'de' => 'Kontext',
                                    ],
                                    'help' => [
                                        'en' => 'The context is, for example, the class or course in which the project was created.',
                                        'de' => 'Der Kontext ist beispielsweise die Klasse oder der Kurs, in dem das Projekt entstanden ist.',
                                    ],
                                    #'max' => 1,
                                    'options' => $context_options,
                                    'required' => true,
                                    'translate' => false,
                                ],
                            ],
                        ],

                        # docs: https://getkirby.com/docs/reference/panel/sections/fields
                        #
                        #'metadata_section_format' => [
                        #    'type' => 'fields',
                        #    'fields' => [

                        #        # docs: https://getkirby.com/docs/reference/panel/fields/multiselect
                        #        #
                        #        'format' => [
                        #            'type' => 'select',
                        #            'label' => [
                        #                'en' => 'Format',
                        #                'de' => 'Format',
                        #            ],
                        #            'help' => [
                        #                'en' => 'The format is the type of the content, for example a concert, a live performance, an installation, et cetera.',
                        #                'de' => 'Das Format ist die Art des Inhalts, beispielsweise ein Konzert, eine Live-Performance, eine Installation, et cetera.',
                        #            ],
                        #            'icon' => 'palette',
                        #            'max' => 1,
                        #            'options' => $format_options,
                        #            'required' => true,
                        #            'translate' => false,
                        #        ],
                        #    ],
                        #],

                        # docs: https://getkirby.com/docs/reference/panel/sections/fields
                        #
                        #'metadata_section_location' => [
                        #    'type' => 'fields',
                        #    'fields' => [

                        #        # docs: https://getkirby.com/docs/reference/panel/fields/multiselect
                        #        #
                        #        'location' => [
                        #            'type' => 'select',
                        #            'label' => [
                        #                'en' => 'Location',
                        #                'de' => 'Standort',
                        #            ],
                        #            'help' => [
                        #                'en' => 'At which location of Berlin University of the Arts is this content exhibited/presented?',
                        #                'de' => 'An welchem Standort der Universität der Künste Berlin wird dieser Inhalt ausgestellt/präsentiert?',
                        #            ],
                        #            'icon' => 'pin',
                        #            'max' => 1,
                        #            'options' => [
                        #                'type' => 'api',
                        #                'url' => 'assets/2025/locations.json',
                        #                'query' => 'sortBy("name", "asc")',
                        #                'text' => '{{ item.name }}',
                        #                'value' => '{{ item.name.slug }}',
                        #            ],
                        #            'required' => true,
                        #            'translate' => false,
                        #        ],
                        #    ],
                        #],

                        # docs: https://getkirby.com/docs/reference/panel/sections/fields
                        #
                        'metadata_section_terms' => [
                            'type' => 'fields',
                            'fields' => [

                                # docs: https://getkirby.com/docs/reference/panel/fields/toggle
                                #
                                'metadata_field_terms' => [
                                    'type' => 'toggle',
                                    'label' => [
                                        'en' => 'Terms & Conditions',
                                        'de' => 'Nutzungsbedingungen',
                                    ],
                                    'help' => [
                                        'en' => 'See: <a href="https://udk-berlin.de/impressum" target="_blank">Terms & Conditions</a>',
                                        'de' => 'Siehe: <a href="https://udk-berlin.de/impressum" target="_blank">Nutzungsbedingungen</a>',
                                    ],
                                    'icon' => 'page',
                                    'required' => true,
                                    'text' => [
                                        'en' => 'I hereby accept the <a href="https://udk-berlin.de/impressum" target="_blank">Terms & Conditions</a>.',
                                        'de' => 'Ich akzeptiere die <a href="https://udk-berlin.de/impressum" target="_blank">Nutzungsbedingungen</a>.',
                                    ],
                                    'translate' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        # guide: https://getkirby.com/docs/guide/blueprints/layout
        #
        'format_tab' => [
            'label' => [
                'en' => 'Format',
                'de' => 'Format',
            ],
            'icon' => 'palette',

            # docs: https://getkirby.com/docs/reference/panel/blueprints/page
            #
            'columns' => [

                # guide: https://getkirby.com/docs/guide/blueprints/layout#columns
                #
                'format_column_banner' => [
                    'width' => '1/1',

                    # docs: https://getkirby.com/docs/reference/panel/sections
                    #
                    'sections' => [

                        # docs: https://getkirby.com/docs/reference/panel/sections/fields
                        #
                        'format_section_info' => [
                            'type' => 'fields',
                            'fields' => [

                                # docs: https://getkirby.com/docs/reference/panel/fields/info
                                #
                                'format_field_info' => [
                                    'type' => 'info',
                                    'label' => false,
                                    'icon' => 'translate',
                                    'text' => [
                                        'en' => 'The <strong>Format</strong> field can only be filled for the default language, i.e. <strong>DE</strong> in the dropdown menu above, next to the page title.',
                                        'de' => 'Das <strong>Format</strong> kann nur für die Standard-Sprache eingetragen werden, d.h. <strong>DE</strong> im Dropdown-Menü neben/unter dem Seiten-Titel.',
                                    ],
                                    'theme' => 'notice',
                                ],
                            ],
                        ],
                    ],
                ],

                # guide: https://getkirby.com/docs/guide/blueprints/layout#columns
                #
                'format_column_main' => [
                    'width' => '1/1',

                    # docs: https://getkirby.com/docs/reference/panel/sections
                    #
                    'sections' => [

                        # docs: https://getkirby.com/docs/reference/panel/sections/fields
                        #
                        'format_section_format' => [
                            'type' => 'fields',
                            'fields' => [

                                # docs: https://getkirby.com/docs/reference/panel/fields/select
                                #
                                'format_field_select' => [
                                    'type' => 'select',
                                    'label' => [
                                        'en' => 'Format',
                                        'de' => 'Format',
                                    ],
                                    'help' => [
                                        'en' => 'The format is the type of the content, for example a concert, a live performance, an installation, et cetera.',
                                        'de' => 'Das Format ist die Art des Inhalts, beispielsweise ein Konzert, eine Live-Performance, eine Installation, et cetera.',
                                    ],
                                    #'icon' => 'palette',
                                    #'max' => 1,
                                    'options' => $format_options,
                                    #'required' => true,
                                    'translate' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        # guide: https://getkirby.com/docs/guide/blueprints/layout
        #
        'location_tab' => [
            'label' => [
                'en' => 'Location',
                'de' => 'Standort',
            ],
            'icon' => 'pin',

            # docs: https://getkirby.com/docs/reference/panel/blueprints/page
            #
            'columns' => [

                # guide: https://getkirby.com/docs/guide/blueprints/layout#columns
                #
                'location_column_banner' => [
                    'width' => '1/1',

                    # docs: https://getkirby.com/docs/reference/panel/sections
                    #
                    'sections' => [

                        # docs: https://getkirby.com/docs/reference/panel/sections/fields
                        #
                        'location_section_info' => [
                            'type' => 'fields',
                            'fields' => [

                                # docs: https://getkirby.com/docs/reference/panel/fields/info
                                #
                                'location_field_info' => [
                                    'type' => 'info',
                                    'label' => false,
                                    'icon' => 'translate',
                                    'text' => [
                                        'en' => 'The <strong>Location</strong> field can only be filled for the default language, i.e. <strong>DE</strong> in the dropdown menu above, next to the page title.',
                                        'de' => 'Der <strong>Standort</strong> kann nur für die Standard-Sprache eingetragen werden, d.h. <strong>DE</strong> im Dropdown-Menü neben/unter dem Seiten-Titel.',
                                    ],
                                    'theme' => 'notice',
                                ],
                            ],
                        ],
                    ],
                ],

                # guide: https://getkirby.com/docs/guide/blueprints/layout#columns
                #
                'location_column_main' => [
                    'width' => '1/1',

                    # docs: https://getkirby.com/docs/reference/panel/sections
                    #
                    'sections' => [

                        # docs: https://getkirby.com/docs/reference/panel/sections/fields
                        #
                        'location_section_location' => [
                            'type' => 'fields',
                            'fields' => [

                                # docs: https://getkirby.com/docs/reference/panel/fields/select
                                #
                                'location_field_select' => [
                                    'type' => 'select',
                                    'label' => [
                                        'en' => 'Location',
                                        'de' => 'Standort',
                                    ],
                                    'help' => [
                                        'en' => 'At which location of Berlin University of the Arts is this content exhibited/presented?',
                                        'de' => 'An welchem Standort der Universität der Künste Berlin wird dieser Inhalt ausgestellt/präsentiert?',
                                    ],
                                    #'icon' => 'pin',
                                    'max' => 1,
                                    'options' => [
                                        'type' => 'api',
                                        'url' => 'assets/2025/locations.json',
                                        'query' => 'sortBy("name", "asc")',
                                        'text' => '{{ item.name }}',
                                        'value' => '{{ item.name.slug }}',
                                    ],
                                    #'required' => true,
                                    'translate' => false,
                                ],

                                # docs: https://getkirby.com/docs/reference/panel/fields/text
                                #
                                'location_field_text' => [
                                    'type' => 'text',
                                    'label' => [
                                        'en' => 'Additional Information (optional)',
                                        'de' => 'Weitere Informationen (optional)',
                                    ],
                                    'help' => [
                                        'en' => 'For example: floor, room number, et cetera.',
                                        'de' => 'Zum Beispiel: Etage, Raumnummer, et cetera.',
                                    ],
                                    'icon' => 'info',
                                    'maxlength' => 50,
                                    'required' => false,
                                    'translate' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        # guide: https://getkirby.com/docs/guide/blueprints/layout
        #
        'event_tab' => [
            'label' => [
                'en' => 'Date & Time',
                'de' => 'Datum & Uhrzeit',
            ],
            'icon' => 'calendar',

            # docs: https://getkirby.com/docs/reference/panel/blueprints/page
            #
            'columns' => [

                # guide: https://getkirby.com/docs/guide/blueprints/layout#columns
                #
                'event_column_main' => [
                    'width' => '1/1',

                    # docs: https://getkirby.com/docs/reference/panel/sections
                    #
                    'sections' => [

                        # docs: https://getkirby.com/docs/reference/panel/sections/fields
                        #
                        'event_section' => [
                            'type' => 'fields',
                            'fields' => [

                                # docs: https://getkirby.com/docs/reference/panel/fields/info
                                #
                                'event_field_info' => [
                                    'type' => 'info',
                                    'label' => false,
                                    #'icon' => 'clock',
                                    'icon' => 'translate',
                                    #'text' => [
                                    #    #'en' => 'Add <strong>Date(s) & Time</strong> for your event; this should only be filled for concerts, performances, et cetera which are <strong>happening at only specific times</strong>.',
                                    #    #'de' => 'Füge <strong>Datum & Uhrzeit</strong> für dein Event hinzu; dies sollte ausschließlich für Konzerte, Performances, et cetera gemacht werden, welche <strong>nur zu bestimmten Zeitpunkten stattfinden</strong>.',
                                    #    'en' => 'Add specific <strong>Time(s)</strong> for your event only for concerts, performances, et cetera which are <strong>happening at only specific times</strong>.',
                                    #    'de' => 'Füge bestimmte <strong>Uhrzeit(en)</strong> für dein Event nur für Konzerte, Performances, et cetera hinzu, welche <strong>nur zu bestimmten Uhrzeiten stattfinden</strong>.',
                                    #],
                                    'text' => [
                                        'en' => 'The <strong>Date & Time</strong> fields can only be filled for the default language, i.e. <strong>DE</strong> in the dropdown menu above, next to the page title.',
                                        'de' => 'Die <strong>Datum & Uhrzeit</strong>-Felder können nur für die Standard-Sprache eingetragen werden werden, d.h. <strong>DE</strong> im Dropdown-Menü neben/unter dem Seiten-Titel.',
                                    ],
                                    #'theme' => 'warning',
                                    'theme' => 'notice',
                                ],

                                # docs: https://getkirby.com/docs/reference/panel/fields/structure
                                #
                                #'event_field_date_time' => [
                                #    'type' => 'structure',
                                #    'label' => [
                                #        'en' => 'Date & Time',
                                #        'de' => 'Datum & Uhrzeit',
                                #    ],
                                #    'translate' => false,
                                #    'columns' => [
                                #        'event_structure_field_date' => [
                                #            'mobile' => true,
                                #            'width' => '1/3',
                                #        ],
                                #        'event_structure_field_from' => [
                                #            'mobile' => true,
                                #            'width' => '1/3',
                                #        ],
                                #        'event_structure_field_to' => [
                                #            'mobile' => true,
                                #            'width' => '1/3',
                                #        ],
                                #    ],
                                #    'fields' => [
                                #
                                #        # docs: https://getkirby.com/docs/reference/panel/fields/date
                                #        #
                                #        'event_structure_field_date' => [
                                #            'type' => 'date',
                                #            'label' => [
                                #                'en' => 'Date',
                                #                'de' => 'Datum',
                                #            ],
                                #            'help' => [
                                #                'en' => 'The date of the event.',
                                #                'de' => 'Das Datum des Events.',
                                #            ],
                                #            'default' => '2025-07-18',
                                #            'min' => '2025-07-18',
                                #            'max' => '2025-07-20',
                                #            'required' => true,
                                #            'translate' => false,
                                #        ],
                                #
                                #        # docs: https://getkirby.com/docs/reference/panel/fields/time
                                #        #
                                #        'event_structure_field_from' => [
                                #            'type' => 'time',
                                #            'label' => [
                                #                'en' => 'Start',
                                #                'de' => 'Beginn',
                                #            ],
                                #            'help' => [
                                #                'en' => 'The time the event starts.',
                                #                'de' => 'Die Uhrzeit, zu der das Event beginnt.',
                                #            ],
                                #            'default' => '13:00',
                                #            'min' => '10:00',
                                #            'max' => '23:59',
                                #            'required' => true,
                                #            'translate' => false,
                                #        ],
                                #
                                #        # docs: https://getkirby.com/docs/reference/panel/fields/time
                                #        #
                                #        'event_structure_field_to' => [
                                #            'type' => 'time',
                                #            'label' => [
                                #                'en' => 'End',
                                #                'de' => 'Ende',
                                #            ],
                                #            'help' => [
                                #                'en' => 'The time the event ends.',
                                #                'de' => 'Die Uhrzeit, zu der das Event endet.',
                                #            ],
                                #            'default' => '15:00',
                                #            'min' => '10:00',
                                #            'max' => '23:59',
                                #            'required' => true,
                                #            'translate' => false,
                                #        ],
                                #    ],
                                #],

                                # docs: https://getkirby.com/docs/reference/panel/fields/radio
                                #
                                'event_field_date_time_friday' => [
                                    'type' => 'radio',
                                    'label' => [
                                        'en' => 'Friday, July 18',
                                        'de' => 'Freitag, 18. Juli',
                                    ],
                                    'options' => [
                                        'allday' => [
                                            'en' => 'All Day',
                                            'de' => 'Ganztägig',
                                        ],
                                        'datetime' => [
                                            'en' => 'Specific Hours(s)',
                                            'de' => 'Bestimmte Uhrzeit(en)',
                                        ],
                                    ],
                                    'default' => 'allday',
                                    'required' => true,
                                    'translate' => false,
                                ],

                                # docs: https://getkirby.com/docs/reference/panel/fields/structure
                                #
                                'event_field_date_time_friday_times' => [
                                    'type' => 'structure',
                                    'label' => [
                                        'en' => 'Timetable for Friday, July 18',
                                        'de' => 'Zeitplan für Freitag, 18. Juli',
                                    ],
                                    'required' => true,
                                    'translate' => false,
                                    'when' => [
                                        'event_field_date_time_friday' => 'datetime',
                                    ],
                                    'columns' => [
                                        'event_structure_field_friday_date' => [
                                            'mobile' => true,
                                            'width' => '1/3',
                                        ],
                                        'event_structure_field_friday_time_from' => [
                                            'mobile' => true,
                                            'width' => '1/3',
                                        ],
                                        'event_structure_field_friday_time_to' => [
                                            'mobile' => true,
                                            'width' => '1/3',
                                        ],
                                    ],
                                    'fields' => [

                                        # docs: https://getkirby.com/docs/reference/panel/fields/date
                                        #
                                        'event_structure_field_friday_date' => [
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
                                            'disabled' => true,
                                            #'min' => '2025-07-18',
                                            #'max' => '2025-07-18',
                                            #'required' => true,
                                            #'time' => true,
                                            'translate' => false,
                                        ],

                                        # docs: https://getkirby.com/docs/reference/panel/fields/time
                                        #
                                        'event_structure_field_friday_time_from' => [
                                            'type' => 'time',
                                            'label' => [
                                                'en' => 'Start',
                                                'de' => 'Beginn',
                                            ],
                                            'help' => [
                                                'en' => 'The time the event starts.',
                                                'de' => 'Die Uhrzeit, zu der das Event beginnt.',
                                            ],
                                            'default' => '16:00',
                                            'min' => '16:00',
                                            'max' => '23:59',
                                            'required' => true,
                                            'translate' => false,
                                        ],

                                        # docs: https://getkirby.com/docs/reference/panel/fields/time
                                        #
                                        'event_structure_field_friday_time_to' => [
                                            'type' => 'time',
                                            'label' => [
                                                'en' => 'End',
                                                'de' => 'Ende',
                                            ],
                                            'help' => [
                                                'en' => 'The time the event ends.',
                                                'de' => 'Die Uhrzeit, zu der das Event endet.',
                                            ],
                                            'default' => '16:00',
                                            'min' => '16:00',
                                            'max' => '23:59',
                                            'required' => true,
                                            'translate' => false,
                                        ],
                                    ],
                                ],

                                # docs: https://getkirby.com/docs/reference/panel/fields/line
                                #
                                #'event_structure_field_friday_to_saturday_line' => [
                                #    'type' => 'line',
                                #],

                                # docs: https://getkirby.com/docs/reference/panel/fields/radio
                                #
                                'event_field_date_time_saturday' => [
                                    'type' => 'radio',
                                    'label' => [
                                        'en' => 'Saturday, July 19',
                                        'de' => 'Samstag, 19. Juli',
                                    ],
                                    'options' => [
                                        'allday' => [
                                            'en' => 'All Day',
                                            'de' => 'Ganztägig',
                                        ],
                                        'datetime' => [
                                            'en' => 'Specific Hours(s)',
                                            'de' => 'Bestimmte Uhrzeit(en)',
                                        ],
                                    ],
                                    'default' => 'allday',
                                    'required' => true,
                                    'translate' => false,
                                ],

                                # docs: https://getkirby.com/docs/reference/panel/fields/structure
                                #
                                'event_field_date_time_saturday_times' => [
                                    'type' => 'structure',
                                    'label' => [
                                        'en' => 'Timetable for Saturday, July 19',
                                        'de' => 'Zeitplan für Samstag, 19. Juli',
                                    ],
                                    'required' => true,
                                    'translate' => false,
                                    'when' => [
                                        'event_field_date_time_saturday' => 'datetime',
                                    ],
                                    'columns' => [
                                        'event_structure_field_saturday_date' => [
                                            'mobile' => true,
                                            'width' => '1/3',
                                        ],
                                        'event_structure_field_saturday_time_from' => [
                                            'mobile' => true,
                                            'width' => '1/3',
                                        ],
                                        'event_structure_field_saturday_time_to' => [
                                            'mobile' => true,
                                            'width' => '1/3',
                                        ],
                                    ],
                                    'fields' => [

                                        # docs: https://getkirby.com/docs/reference/panel/fields/date
                                        #
                                        'event_structure_field_saturday_date' => [
                                            'type' => 'date',
                                            'label' => [
                                                'en' => 'Date',
                                                'de' => 'Datum',
                                            ],
                                            'help' => [
                                                'en' => 'The date of the event.',
                                                'de' => 'Das Datum des Events.',
                                            ],
                                            'default' => '2025-07-19',
                                            'disabled' => true,
                                            #'min' => '2025-07-19',
                                            #'max' => '2025-07-19',
                                            #'required' => true,
                                            #'time' => true,
                                            'translate' => false,
                                        ],

                                        # docs: https://getkirby.com/docs/reference/panel/fields/time
                                        #
                                        'event_structure_field_saturday_time_from' => [
                                            'type' => 'time',
                                            'label' => [
                                                'en' => 'Start',
                                                'de' => 'Beginn',
                                            ],
                                            'help' => [
                                                'en' => 'The time the event starts.',
                                                'de' => 'Die Uhrzeit, zu der das Event beginnt.',
                                            ],
                                            'default' => '11:00',
                                            'min' => '11:00',
                                            'max' => '22:00',
                                            'required' => true,
                                            'translate' => false,
                                        ],

                                        # docs: https://getkirby.com/docs/reference/panel/fields/time
                                        #
                                        'event_structure_field_saturday_time_to' => [
                                            'type' => 'time',
                                            'label' => [
                                                'en' => 'End',
                                                'de' => 'Ende',
                                            ],
                                            'help' => [
                                                'en' => 'The time the event ends.',
                                                'de' => 'Die Uhrzeit, zu der das Event endet.',
                                            ],
                                            'default' => '11:00',
                                            'min' => '11:00',
                                            'max' => '22:00',
                                            'required' => true,
                                            'translate' => false,
                                        ],
                                    ],
                                ],

                                # docs: https://getkirby.com/docs/reference/panel/fields/line
                                #
                                #'event_structure_field_saturday_to_sunday_line' => [
                                #    'type' => 'line',
                                #],

                                # docs: https://getkirby.com/docs/reference/panel/fields/radio
                                #
                                'event_field_date_time_sunday' => [
                                    'type' => 'radio',
                                    'label' => [
                                        'en' => 'Sunday, July 20',
                                        'de' => 'Sonntag, 20. Juli',
                                    ],
                                    'options' => [
                                        'allday' => [
                                            'en' => 'All Day',
                                            'de' => 'Ganztägig',
                                        ],
                                        'datetime' => [
                                            'en' => 'Specific Hours(s)',
                                            'de' => 'Bestimmte Uhrzeit(en)',
                                        ],
                                    ],
                                    'default' => 'allday',
                                    'required' => true,
                                    'translate' => false,
                                ],

                                # docs: https://getkirby.com/docs/reference/panel/fields/structure
                                #
                                'event_field_date_time_sunday_times' => [
                                    'type' => 'structure',
                                    'label' => [
                                        'en' => 'Timetable for Sunday, July 20',
                                        'de' => 'Zeitplan für Sonntag, 20. Juli',
                                    ],
                                    'required' => true,
                                    'translate' => false,
                                    'when' => [
                                        'event_field_date_time_sunday' => 'datetime',
                                    ],
                                    'columns' => [
                                        'event_structure_field_sunday_date' => [
                                            'mobile' => true,
                                            'width' => '1/3',
                                        ],
                                        'event_structure_field_sunday_time_from' => [
                                            'mobile' => true,
                                            'width' => '1/3',
                                        ],
                                        'event_structure_field_sunday_time_to' => [
                                            'mobile' => true,
                                            'width' => '1/3',
                                        ],
                                    ],
                                    'fields' => [

                                        # docs: https://getkirby.com/docs/reference/panel/fields/date
                                        #
                                        'event_structure_field_sunday_date' => [
                                            'type' => 'date',
                                            'label' => [
                                                'en' => 'Date',
                                                'de' => 'Datum',
                                            ],
                                            'help' => [
                                                'en' => 'The date of the event.',
                                                'de' => 'Das Datum des Events.',
                                            ],
                                            'default' => '2025-07-20',
                                            'disabled' => true,
                                            #'min' => '2025-07-20',
                                            #'max' => '2025-07-20',
                                            #'required' => true,
                                            #'time' => true,
                                            'translate' => false,
                                        ],

                                        # docs: https://getkirby.com/docs/reference/panel/fields/time
                                        #
                                        'event_structure_field_sunday_time_from' => [
                                            'type' => 'time',
                                            'label' => [
                                                'en' => 'Start',
                                                'de' => 'Beginn',
                                            ],
                                            'help' => [
                                                'en' => 'The time the event starts.',
                                                'de' => 'Die Uhrzeit, zu der das Event beginnt.',
                                            ],
                                            'default' => '10:00',
                                            'min' => '10:00',
                                            'max' => '21:00',
                                            'required' => true,
                                            'translate' => false,
                                        ],

                                        # docs: https://getkirby.com/docs/reference/panel/fields/time
                                        #
                                        'event_structure_field_sunday_time_to' => [
                                            'type' => 'time',
                                            'label' => [
                                                'en' => 'End',
                                                'de' => 'Ende',
                                            ],
                                            'help' => [
                                                'en' => 'The time the event ends.',
                                                'de' => 'Die Uhrzeit, zu der das Event endet.',
                                            ],
                                            'default' => '10:00',
                                            'min' => '10:00',
                                            'max' => '21:00',
                                            'required' => true,
                                            'translate' => false,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        # guide: https://getkirby.com/docs/guide/blueprints/layout
        #
        'intro_tab' => [
            'label' => [
                'en' => 'Short Description (EN/DE)',
                'de' => 'Kurzbeschreibung (EN/DE)',
            ],
            'icon' => 'paragraph',

            # docs: https://getkirby.com/docs/reference/panel/blueprints/page
            #
            'columns' => [

                # guide: https://getkirby.com/docs/guide/blueprints/layout#columns
                #
                'intro_column_banner' => [
                    'width' => '1/1',

                    # docs: https://getkirby.com/docs/reference/panel/sections
                    #
                    'sections' => [

                        # docs: https://getkirby.com/docs/reference/panel/sections/fields
                        #
                        'intro_section_info' => [
                            'type' => 'fields',
                            'fields' => [

                                # docs: https://getkirby.com/docs/reference/panel/fields/info
                                #
                                'intro_field_multi_language' => [
                                    'type' => 'info',
                                    'label' => false,
                                    'icon' => 'translate',
                                    'text' => [
                                        'en' => 'You can select the language for <strong>Short Description (EN/DE)</strong> in the dropdown menu above, next to the page title.',
                                        'de' => 'Wähle die Sprache für <strong>Kurzbeschreibung (EN/DE)</strong> im Dropdown-Menü neben/unter dem Seiten-Titel aus.',
                                    ],
                                    'theme' => 'info',
                                ],
                            ],
                        ],
                    ],
                ],

                # guide: https://getkirby.com/docs/guide/blueprints/layout#columns
                #
                'intro_column_main' => [
                    'width' => '1/1',

                    # docs: https://getkirby.com/docs/reference/panel/sections
                    #
                    'sections' => [

                        # docs: https://getkirby.com/docs/reference/panel/sections/fields
                        #
                        'intro_section_intro' => [
                            'type' => 'fields',
                            'fields' => [

                                # docs: https://getkirby.com/docs/reference/panel/fields/textarea
                                #
                                'intro_field_intro' => [
                                    'type' => 'textarea',
                                    'label' => [
                                        'en' => 'Short Description',
                                        'de' => 'Kurzbeschreibung',
                                    ],
                                    'buttons' => false,
                                    'help' => [
                                        'en' => 'The short description is a teaser, which offers website visitors a glimpse into your content from the content overview page on the website.',
                                        'de' => 'Die Kurzbeschreibung dient als Teaser und ermöglicht Website-Besucher:innen einen Einblick in den Inhalt von der Inhaltsübersichtseite der Website.',
                                    ],
                                    'icon' => 'title',
                                    'maxlength' => 500,
                                    'required' => true,
                                    'size' => 'small',
                                    'uploads' => false,
                                ],

                                # docs: https://getkirby.com/docs/reference/panel/fields/info
                                #
                                'intro_field_single_language' => [
                                    'type' => 'info',
                                    'label' => false,
                                    'icon' => 'translate',
                                    'text' => [
                                        'en' => 'The <strong>Title Image</strong> field can only be uploaded for the default language, i.e. <strong>DE</strong> in the dropdown menu above, next to the page title.',
                                        'de' => 'Das <strong>Titelbild</strong> kann nur für die Standard-Sprache hochgeladen werden, d.h. <strong>DE</strong> im Dropdown-Menü neben/unter dem Seiten-Titel.',
                                    ],
                                    'theme' => 'notice',
                                ],

                                # docs: https://getkirby.com/docs/reference/panel/fields/blocks
                                #
                                'intro_field_title_image' => [
                                    'type' => 'blocks',
                                    'label' => [
                                        'en' => 'Title Image',
                                        'de' => 'Titelbild',
                                    ],
                                    'default' => [
                                        [
                                            'type' => 'image',
                                        ],
                                    ],
                                    'empty' => [
                                        'en' => 'Please upload a title image …',
                                        'de' => 'Bitte lade ein Titelbild hoch …',
                                    ],
                                    'fieldsets' => [
                                        'image',
                                    ],
                                    'help' => [
                                        'en' => 'The title image might be shown in the content overview next to some more metadata.',
                                        'de' => 'Das Titelbild wäre in der Inhaltsübersicht zu sehen, neben weiteren Metadaten.',
                                    ],
                                    'max' => '1',
                                    'translate' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        # guide: https://getkirby.com/docs/guide/blueprints/layout
        #
        'content_tab' => [
            'label' => [
                'en' => 'Content (EN/DE)',
                'de' => 'Inhalt (EN/DE)',
            ],
            'icon' => 'text',

            # docs: https://getkirby.com/docs/reference/panel/sections
            #
            'sections' => [

                # docs: https://getkirby.com/docs/reference/panel/sections/fields
                #
                'content_section_info' => [
                    'type' => 'fields',
                    'fields' => [

                        # docs: https://getkirby.com/docs/reference/panel/fields/info
                        #
                        'content_field_multi_language' => [
                            'type' => 'info',
                            'label' => false,
                            'icon' => 'translate',
                            'text' => [
                                'en' => 'You can select the language for <strong>Content (EN/DE)</strong> in the dropdown menu above, next to the page title.',
                                'de' => 'Wähle die Sprache für <strong>Inhalt (EN/DE)</strong> im Dropdown-Menü neben/unter dem Seiten-Titel aus.',
                            ],
                            'theme' => 'info',
                        ],
                    ],
                ],

                # docs: https://getkirby.com/docs/reference/panel/sections/fields
                #
                'content_section_blocks' => [
                    'type' => 'fields',
                    'fields' => [

                        # docs: https://getkirby.com/docs/reference/panel/fields/blocks
                        #
                        'content_field' => [
                            'type' => 'blocks',
                            'label' => [
                                'en' => 'Content',
                                'de' => 'Inhalt',
                            ],
                            'default' => [
                                [
                                    'type' => 'text',
                                ],
                            ],
                            'fieldsets' => [
                                'heading',
                                'text',
                                'list',
                                'image',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    # docs: https://getkirby.com/docs/reference/panel/blueprints/page#options
    #
    # NOTE: uncomment complete `options` block to prevent editing/modification
    # of pages after publication deadline and/or after the Rundgang is over !!
    /*
    'options' => [
        //'access' => [
        //  "*" => false,
        //  "admin" => true,
        //],
        'changeSlug' => [
            "*" => false,
            "admin" => true,
        ],
        'changeStatus' => [
            "*" => false,
            "admin" => true,
        ],
        'changeTemplate' => [
            "*" => false,
            "admin" => true,
        ],
        'changeTitle' => [
            "*" => false,
            "admin" => true,
        ],
        'create' => [
            "*" => false,
            "admin" => true,
        ],
        'delete' => [
            "*" => false,
            "admin" => true,
        ],
        //'list' => [
        //  "*" => false,
        //  "admin" => true,
        //],
        'move' => [
            "*" => false,
            "admin" => true,
        ],
        'duplicate' => [
            "*" => false,
            "admin" => true,
        ],
        'preview' => [
            "*" => false,
            "admin" => true,
        ],
        'read' => [
            "*" => false,
            "admin" => true,
        ],
        'sort' => [
            "*" => false,
            "admin" => true,
        ],
        'update' => [
            "*" => false,
            "admin" => true,
        ],
    ],
    */
];

<?php

$functions = [
    'local_restrict_get_users' => [
        'classname' => 'local_restrict_external',
        'methodname' => 'get_users',
        'classpath' => 'local/restrict/externallib.php',
        'desctption' => 'Distrpute users acroos devices for exams',
        'type' => 'write',
        'ajax' => true,
        'capabilites' => 'moodle/site:config'
    ],
    'local_restrict_update_labs' => [
        'classname' => 'local_restrict_external',
        'methodname' => 'update_labs',
        'classpath' => 'local/restrict/externallib.php',
        'desctption' => 'Update devices in lap to active, suspend or make device as an admin',
        'type' => 'write',
        'ajax' => true,
        'capabilites' => 'moodle/site:config'
    ],
    'local_restrict_get_quizes' => [
        'classname' => 'local_restrict_external',
        'methodname' => 'get_quizes',
        'classpath' => 'local/restrict/externallib.php',
        'desctption' => 'Get quizes in course to select It (this is test version)',
        'type' => 'write',
        'ajax' => true,
        'capabilites' => 'moodle/site:config'
    ],
    'local_restrict_get_groups' => [
        'classname' => 'local_restrict_external',
        'methodname' => 'get_groups',
        'classpath' => 'local/restrict/externallib.php',
        'desctption' => 'Get quizes in course to select It (this is test version)',
        'type' => 'write',
        'ajax' => true,
        'capabilites' => 'moodle/site:config'
    ],
    'local_restrict_courses_records' => [
        'classname' => 'local_restrict_external',
        'methodname' => 'courses_records',
        'classpath' => 'local/restrict/externallib.php',
        'desctption' => 'Delete distrputed course record',
        'type' => 'write',
        'ajax' => true,
        'capabilites' => 'moodle/site:config'
    ],

];
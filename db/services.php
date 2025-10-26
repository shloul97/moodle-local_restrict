<?php
// This file is part of Moodle - http://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify.
// it under the terms of the GNU General Public License as published by.
// the Free Software Foundation, either version 3 of the License, or.
// This file is part of Moodle - http://moodle.org/.
// Moodle is distributed in the hope that it will be useful,.
// but WITHOUT ANY WARRANTY; without even the implied warranty of.
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the.
// This file is part of Moodle - http://moodle.org/.
// You should have received a copy of the GNU General Public License.
// This file is part of Moodle - http://moodle.org/.
/*
 * @package   local_restrict
 * @copyright 2025 Moayad Shloul <shloul97@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */


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

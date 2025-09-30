<?php
// This file is part of Moodle - http://moodle.org/
//
// Secure Exam Access plugin for Moodle
// Copyright (C) 2025 Moayad Shloul
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package   local_restrict
 * @copyright 2025 Moayad Shloul <shloul97@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
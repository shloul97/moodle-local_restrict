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

defined('MOODLE_INTERNAL') || die();

function xmldb_local_restrict_install() {

    xmldb_local_restrict_insert_user_status();
    xmldb_local_restrict_insert_device_status();
}


// Add status value to use in user exams { 0 => Exam end , 1 => Exam active}.
/**
 * Summary of insert_user_status
 *
 * @return void
 */
function xmldb_local_restrict_insert_user_status(): void {
    global $DB;
    $recordsuserstatus[] = (object) [
        'code' => 0,
        'status' => 'Inactive'
    ];

    $recordsuserstatus[] = (object) [
        'code' => 1,
        'status' => 'Active'
    ];

    $DB->insert_records('local_restrict_user_exam_status', $recordsuserstatus);
}


// Add status value to use in devices { 0 => suspended , 1 => Active}.
function xmldb_local_restrict_insert_device_status() {
    global $DB;
    $records[] = (object) [
        'code' => 0,
        'status' => 'Inactive'
    ];

    $records[] = (object) [
        'code' => 1,
        'status' => 'Active'
    ];

    $DB->insert_records('local_restrict_devices_status', $records);
}

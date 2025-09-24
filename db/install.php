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

defined('MOODLE_INTERNAL') || die();

function xmldb_local_restrict_install() {

    insert_user_status();
    insert_device_status();
}


// Add status value to use in user exams { 0 => Exam end , 1 => Exam active}
function insert_user_status(): void {
    global $DB;
    $records_user_status[] = (object) [
        'code' => 0,
        'status' => 'Inactive'
    ];

    $records_user_status[] = (object) [
        'code' => 1,
        'status' => 'Active'
    ];


    $DB->insert_records('local_restrict_user_exam_status', $records_user_status);
}


// Add status value to use in devices { 0 => suspended , 1 => Active}
function insert_device_status() {
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
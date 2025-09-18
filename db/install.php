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
 * @package   local_secureaccess
 * @copyright 2025 Moayad Shloul <shloul97@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_secureaccess_install() {

    InsertUserStatus();
    InsertDeviceStatus();
}


function InsertUserStatus(): void {
    global $DB;
    $recordsUserStatus[] = (object) [
        'code' => 0,
        'status' => 'Inactive'
    ];

    $recordsUserStatus[] = (object) [
        'code' => 1,
        'status' => 'Active'
    ];

    $DB->insert_records('local_secureaccess_user_exam_status', $recordsUserStatus);
}

function InsertDeviceStatus() {
    global $DB;
    $records[] = (object) [
        'code' => 0,
        'status' => 'Inactive'
    ];

    $records[] = (object) [
        'code' => 1,
        'status' => 'Active'
    ];

    $DB->insert_records('local_secureaccess_devices_status', $records);
}
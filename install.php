<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_secureexamaccess_install() {

    InsertUserStatus();
    InsertDeviceStatus();
}


function InsertUserStatus(): void {
    global $DB;
    $recordsUserStatus[] = (object) [
        'id' => 0,
        'name' => 'Inactive'
    ];

    $recordsUserStatus[] = (object) [
        'id' => 1,
        'name' => 'Active'
    ];

    $DB->insert_records('local_restrict_user_exam_status', $recordsUserStatus);
}

function InsertDeviceStatus() {
    global $DB;
    $records[] = (object) [
        'id' => 0,
        'name' => 'Inactive'
    ];

    $records[] = (object) [
        'id' => 1,
        'name' => 'Active'
    ];

    $DB->insert_records('local_restrict_devices_status', $records);
}
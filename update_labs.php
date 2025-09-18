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



require_once('../../config.php');
require_once($CFG->dirroot . '/local/secureaccess/classes/form/update_labs_form.php');

require_login();

// Restrict access to admins only
if (!is_siteadmin()) {
    throw new required_capability_exception(
        context_system::instance(),
        'moodle/site:config',
        'nopermissions',
        ''
    );
}

// Setup page
$PAGE->set_context(context_system::instance());



$PAGE->set_url(new moodle_url("/local/secureaccess/index.php"));
$PAGE->set_context(\context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title("Restrict User");





$PAGE->requires->jquery_plugin('ui');
/*$PAGE->requires->js_call_amd('local_secureaccess/action', 'init', [
    'sesskey' => sesskey()
]);*/
$PAGE->requires->js('/local/secureaccess/amd/src/action2.js');

//echo '<script src="/moodle/local/secureaccess/amd/src/action.js"></script>';

$mform = new update_labs();

$dataArr = array();

if ($mform->is_cancelled()) {

} else if ($fromform = $mform->get_data()) {


    $record = new stdClass();


    if (isset($fromform->labid)) {
        $record->labid = $fromform->labid;
    }

    if (isset($fromform->deviceip)) {
        $record->ip = $fromform->deviceip;
    }

    try {
        $records = '';


        $labadmin = $DB->get_records_sql("SELECT device_id, labid FROM {local_secureaccess_admin_devices} WHERE labid = ?",
        [$record->labid]);
        $params = [$record->labid];

        $labid = $record->labid;





        if ($record->labid != '' && $record->ip != '') {


            $data = $DB->get_records('local_secureaccess_devices', [
                'ip' => $record->ip,
                'labid' => $record->labid,
            ]);
        } else if ($record->labid != '' && $record->ip == '') {

            $data = $DB->get_records('local_secureaccess_devices', [
                'labid' => $record->labid,

            ]);
        } else if ($record->labid == '' && $record->ip != '') {
            $data = $DB->get_records('local_secureaccess_devices', [
                'ip' => $record->ip,
            ]);
        }



        foreach ($data as $device) {

            $adminClass;
            $dataAdmin = '';
            $adminId = [];
            $adminStr = '';

            foreach ($labadmin as $adminDevices) {

                if ($device->id == $adminDevices->device_id){
                    $adminClass = "secondary";
                    $adminStr = "Remove Admin";
                    $dataAdmin = "rmadmin";
                    break;
                }
                else{
                    $adminClass = "primary";
                    $adminStr = "Make Admin";
                    $dataAdmin = "mkadmin";
                }
            }




            if ($device->status == 1) {
                $deviceStatus = 'Active';
                $statusClass = "success";
                $btnClass = "warning";
                $btnContent = "Suspend";
                $btnData = "sus";



            } else {
                $deviceStatus = 'Inactive';
                $statusClass = "danger";
                $btnClass = "success";
                $btnContent = "Activate";
                $btnData = "act";

            }
            $dataArr[] = [

                'deviceId' => $device->id,
                'deviceIp' => $device->ip,
                'labName' => $labname,
                'deviceStatus' => $deviceStatus,
                'statusClass' => $statusClass,
                'btnClass' => $btnClass,
                'btnContent' => $btnContent,
                'btnData' => $btnData,
                'adminBtn' => $adminClass,
                'adminStr' => $adminStr,
                'dataAdmin' => $dataAdmin,
                'labid' => $labid
            ];
        }

        $data1 = [
            'table' => [
                'tableData' => $dataArr
            ]
        ];






    } catch (Exception $e) {
        \core\notification::error(get_string('failed_lab_insertion', 'local_secureaccess'));
    }


}





$templatecontext = [
    'submitted' => false,
    'formhtml' => $mform->render()
];

$header = [
    "insertGroup" => new moodle_url("/local/secureaccess/insert_groups.php"),
    "insertLabs" => new moodle_url("/local/secureaccess/insert_labs.php"),
    "insertIp" => new moodle_url("/local/secureaccess/insert_ranges.php"),
    "updateLabs" => new moodle_url("/local/secureaccess/update_labs.php"),
    "inquiry" => new moodle_url("/local/secureaccess/inquiry.php"),
    "home" => new moodle_url("/local/secureaccess/index.php")
];
$context = [
    'form' => [$templatecontext],
    'header' => $header,
    'table' => $data1['table'],
    'sesskey' => sesskey()
];


echo $OUTPUT->header();

//echo $OUTPUT->render_from_template('local_secureaccess/insert_ranges',$context);
echo $OUTPUT->render_from_template('local_secureaccess/update_labs', $context);

echo $OUTPUT->footer();





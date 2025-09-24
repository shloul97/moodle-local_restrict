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



require_once('../../config.php');
require_once($CFG->dirroot . '/local/restrict/classes/form/update_labs_form.php');

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



$PAGE->set_url(new moodle_url("/local/restrict/index.php"));
$PAGE->set_context(\context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title("Restrict User");





$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js_call_amd('local_restrict/action', 'init', [
    'sesskey' => sesskey()
]);

$mform = new update_labs();

$data_array = array();

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


        $labadmin = $DB->get_records_sql("SELECT device_id, labid FROM {local_restrict_admin_devices} WHERE labid = ?",
        [$record->labid]);
        $params = [$record->labid];

        $labid = $record->labid;





        if ($record->labid != '' && $record->ip != '') {


            $data = $DB->get_records('local_restrict_devices', [
                'ip' => $record->ip,
                'labid' => $record->labid,
            ]);
        } else if ($record->labid != '' && $record->ip == '') {

            $data = $DB->get_records('local_restrict_devices', [
                'labid' => $record->labid,

            ]);
        } else if ($record->labid == '' && $record->ip != '') {
            $data = $DB->get_records('local_restrict_devices', [
                'ip' => $record->ip,
            ]);
        }


        // Set data for device admin status
        foreach ($data as $device) {

            $admin_class;
            $data_admin = '';
            $admin_id = [];
            $admin_string = '';

            foreach ($labadmin as $adminDevices) {

                //if admin devices table has selected device send data to make sure user can remove it
                if ($device->id == $adminDevices->device_id){
                    $admin_class = "secondary"; /* => button class btn-{{}}*/
                    $admin_string = "Remove Admin"; /* => button value */
                    $data_admin = "rmadmin"; /* => button data-action to use in JS */
                    break;
                }
                // If admin devices table doesn't has selected device send data to make sure user can add it
                else{
                    $admin_class = "primary"; /* => button class btn-{{}}*/
                    $admin_string = "Make Admin"; /* => button value */
                    $data_admin = "mkadmin"; /* => button data-action to use in JS */
                }
            }



            // Check if device active or suspended
            if ($device->status == 1) {
                $device_status = 'Active'; /* => span value */
                $status_class = "success"; /* => span class btn-{{}}*/
                $btn_class = "warning"; /* => button class btn-{{}}*/
                $btn_content = "Suspend"; /* => button value */
                $btn_data = "sus"; /* => button data-action to use in JS */



            } else {
                $device_status = 'Inactive';
                $status_class = "danger";
                $btn_class = "success";
                $btn_content = "Activate";
                $btn_data = "act";

            }

            // Data to send using ajax
            $data_array[] = [

                'deviceId' => $device->id,
                'deviceIp' => $device->ip,
                'labName' => $labname,
                'device_status' => $device_status,
                'status_class' => $status_class,
                'btn_class' => $btn_class,
                'btn_content' => $btn_content,
                'btn_data' => $btn_data,
                'adminBtn' => $admin_class,
                'admin_string' => $admin_string,
                'data_admin' => $data_admin,
                'labid' => $labid
            ];
        }

        $data1 = [
            'table' => [
                'tableData' => $data_array
            ]
        ];






    } catch (Exception $e) {
        \core\notification::error(get_string('failed_lab_insertion', 'local_restrict'));
    }


}





$templatecontext = [
    'submitted' => false,
    'formhtml' => $mform->render()
];

$header = [
    "insertGroup" => new moodle_url("/local/restrict/insert_groups.php"),
    "insertLabs" => new moodle_url("/local/restrict/insert_labs.php"),
    "insertIp" => new moodle_url("/local/restrict/insert_ranges.php"),
    "updateLabs" => new moodle_url("/local/restrict/update_labs.php"),
    "inquiry" => new moodle_url("/local/restrict/inquiry.php"),
    "home" => new moodle_url("/local/restrict/index.php")
];
$context = [
    'form' => [$templatecontext],
    'header' => $header,
    'table' => $data1['table'],
    'sesskey' => sesskey()
];


echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_restrict/update_labs', $context);

echo $OUTPUT->footer();





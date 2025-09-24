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
require_once($CFG->dirroot . '/local/restrict/classes/form/insert_ranges_form.php');
require_once($CFG->dirroot . '/local/restrict/classes/form/insert_ranges_manual_form.php');
require_once($CFG->dirroot . '/local/restrict/classes/form/insert_admin_devices_form.php');


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



global $OUTPUT, $PAGE, $CFG;

$mform = new insert_ranges();

$mform_manual = new insert_ranges_manual();
$mform_admin = new insert_ranges_admin();

$PAGE->set_url(new moodle_url("/local/restrict/insert_ranges.php"));
$PAGE->set_context(\context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title("Insert Ranges");



if ($mform->is_cancelled()) {

}
else if ($fromform = $mform->get_data()) {
    $records = new stdClass();



    $records->ipstart = $fromform->ipstart;
    $records->ipend = $fromform->ipend;
    $records->labid = $fromform->labid;

    $device_array = array();





    // First digit of last box in ip address Ex: 127.0.0.178 last_start = 1 from (178)
    $last_start = (int)substr(strrchr( $records->ipstart, '.'), 1);
    // Last digit of last box in ip address Ex: 127.0.0.178 last_end = 8 from (178)
    $last_end = (int)substr(strrchr($records->ipend, '.'), 1);

    if($last_start == 100)
    {
        $i = 1;
    }
    else {
        $i = 0;
    }

    $loop = $last_end - $last_start;


    for($i = 0; $i<= $loop; $i++)
    {


        $ip_long = ip2long($records->ipstart);
        $ip_long+= $i;
        $newIp = long2ip($ip_long);


        if($last_start == 1 || $last_start == 100)
        {
            \core\notification::error(get_string('err_iprange', 'local_restrict'));
            break;

        }
        if($last_start >= 100)
        {

            $deviceNumber = $last_start + $i;
            $deviceToSave = (int)substr($deviceNumber, -2);
            array_push($device_array, array(
                "deviceNo" => $deviceToSave,
                "deviceIp" => $newIp
            ));
        }
        else
        {
            $deviceNumber = $last_start + $i;
            array_push($device_array, array(
                "deviceNo" => $deviceNumber,
                "deviceIp" => $newIp
            ));
        }
    }
    $ips_check_array = [];
    foreach ($device_array as $device) {

        $number = $device['deviceNo'];
        $ip = $device['deviceIp'];

        $record = new stdClass();
        $record->ip = $ip;
        $record->device_number = $number;
        $record->device_type = 'private';
        $record->labid = $fromform->labid ;

        if($DB->insert_record('local_restrict_devices', $record)){

        }
        else{
            $ips_check_array[] = $records->ip = $ip[0];
        }
    }
    if(empty($ips_check_array)){
        echo $OUTPUT->notification(get_string('ip_success', 'local_restrict'), \core\output\notification::NOTIFY_SUCCESS, false);
    }
    else{
        foreach($ips_check_array as $ipcheck)
        echo $OUTPUT->notification($ipcheck.' '.get_string('err_ipinsertion', 'local_restrict'), \core\output\notification::NOTIFY_ERROR, false);
    }



}

if($mform_manual->is_cancelled()){

}else if($fromform = $mform_manual->get_data()){
    $records = new stdClass();

    $labId = $fromform->labid;
    $ips_text = $fromform->manualIps;

    $lines = explode("\n", $ips_text);

    $ips = [];

    foreach ($lines as $line) {
        $ips[] = explode(",",$line);
    }

    $ips_check_array = [];
    foreach($ips as $ip){

        $records->ip = $ip[0];
        $records->device_number = $ip[1];
        $records->device_type = 'private';
        $records->labid = $labId;


        if($DB->insert_record('local_restrict_devices', $records)){


        }else{
            $ips_check_array[] = $records->ip = $ip[0];
        }

    }
    if(empty($ips_check_array)){
        echo $OUTPUT->notification(get_string('ip_success', 'local_restrict'), \core\output\notification::NOTIFY_SUCCESS, false);
    }
    else{
        foreach($ips_check_array as $ipcheck)
        echo $OUTPUT->notification($ipcheck.' '.get_string('err_ipinsertion', 'local_restrict'), \core\output\notification::NOTIFY_ERROR, false);
    }


}


if($mform_admin->is_cancelled()){

}
else if($fromform = $mform_admin->get_data()){
    $records = new stdClass();

    $labId = $fromform->labid;
    $ips_text = $fromform->admin_ips;

    $lines = explode("\n", $ips_text);

    $ips = [];

    foreach ($lines as $line) {
        $ips[] = trim($line);

    }


    $ips_check_array = [];
    $device_check = true;
    foreach($ips as $ip){



        $deviceId = $DB->get_field('local_restrict_devices', 'id', ['ip' => $ip]);

        $records->labid = $labId;
        $records->device_id = $deviceId;

        if($deviceId){
            if($DB->execute('INSERT INTO {local_restrict_admin_devices}(labid, device_id) VALUES (?,?)', [
            $labId,
            $deviceId
            ])){


            }
            else{
                $ips_check_array = $ip;
            }
        }else{
            $ips_check_array = $ip;
            $device_check = false;
            echo $OUTPUT->notification(get_string('err_ipnull_admin', 'local_restrict'), \core\output\notification::NOTIFY_ERROR, false);
        }

    }
    if(empty($ips_check_array)){
        echo '<br><br><br><br>';
        echo $OUTPUT->notification(get_string('ip_success', 'local_restrict'), \core\output\notification::NOTIFY_SUCCESS, false);
    }
    else{
        foreach($ips_check_array as $ipcheck)
        echo '<br><br><br><br>';
        echo $OUTPUT->notification($ipcheck.' '.get_string('err_ipinsertion', 'local_restrict'), \core\output\notification::NOTIFY_ERROR, true);
    }


}


$template_ctx_auto = [
    'submitted' => false,
    'formhtml' => $mform->render()
];

$template_ctx_manual = [
    'submitted' => false,
    'formhtml' => $mform_manual->render()
];

$template_admin_devices = [
    'submitted' => false,
    'formhtml' => $mform_admin->render()
];

$header = [
    "insertGroup" => new moodle_url("/local/restrict/insert_groups.php"),
    "insertLabs"=> new moodle_url("/local/restrict/insert_labs.php"),
    "insertIp"=> new moodle_url("/local/restrict/insert_ranges.php"),
    "updateLabs"=> new moodle_url("/local/restrict/update_labs.php"),
    "inquiry"=> new moodle_url("/local/restrict/inquiry.php"),
    "home"=> new moodle_url("/local/restrict/index.php")
];

$context = [
    'form' => [$template_ctx_auto, $template_admin_devices, $template_ctx_manual],
    'header' => $header
];

echo $OUTPUT->header();

echo  $OUTPUT->notification(get_string('ip_range_hint', 'local_restrict'), \core\output\notification::NOTIFY_WARNING, false);
echo  $OUTPUT->notification(get_string('ip_example_hint', 'local_restrict'), \core\output\notification::NOTIFY_WARNING, false);

echo $OUTPUT->render_from_template('local_restrict/container',$context);






echo $OUTPUT->footer();
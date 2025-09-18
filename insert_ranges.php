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
require_once($CFG->dirroot . '/local/secureaccess/classes/form/insert_ranges_form.php');
require_once($CFG->dirroot . '/local/secureaccess/classes/form/insert_ranges_manual_form.php');
require_once($CFG->dirroot . '/local/secureaccess/classes/form/insert_admin_devices_form.php');


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

$mformMan = new insert_ranges_manual();
$mformAdmin = new insert_ranges_admin();

$PAGE->set_url(new moodle_url("/local/secureaccess/insert_ranges.php"));
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

    $deviceArr = array();






    $lastStart = (int)substr(strrchr( $records->ipstart, '.'), 1);
    $lastEnd = (int)substr(strrchr($records->ipend, '.'), 1);

    if($lastStart == 100)
    {
        $i = 1;
    }
    else {
        $i = 0;
    }

    $loop = $lastEnd - $lastStart;


    for($i = 0; $i<= $loop; $i++)
    {


        $ipLong = ip2long($records->ipstart);
        $ipLong+= $i;
        $newIp = long2ip($ipLong);


        if($lastStart == 1 || $lastStart == 100)
        {
            \core\notification::error(get_string('err_iprange', 'local_secureaccess'));
            break;

        }
        if($lastStart >= 100)
        {

            $deviceNumber = $lastStart + $i;
            $deviceToSave = (int)substr($deviceNumber, -2);
            array_push($deviceArr, array(
                "deviceNo" => $deviceToSave,
                "deviceIp" => $newIp
            ));
        }
        else
        {
            $deviceNumber = $lastStart + $i;
            array_push($deviceArr, array(
                "deviceNo" => $deviceNumber,
                "deviceIp" => $newIp
            ));
        }
    }
    $ipsCheckArr = [];
    foreach ($deviceArr as $device) {

        $number = $device['deviceNo'];
        $ip = $device['deviceIp'];

        $record = new stdClass();
        $record->ip = $ip;
        $record->device_number = $number;
        $record->device_type = 'private';
        $record->labid = $fromform->labid ;

        if($DB->insert_record('local_secureaccess_devices', $record)){

        }
        else{
            $ipsCheckArr[] = $records->ip = $ip[0];
        }
    }
    if(empty($ipsCheckArr)){
        echo $OUTPUT->notification(get_string('ip_success', 'local_secureaccess'), \core\output\notification::NOTIFY_SUCCESS, false);
    }
    else{
        foreach($ipsCheckArr as $ipcheck)
        echo $OUTPUT->notification($ipcheck.' '.get_string('err_ipinsertion', 'local_secureaccess'), \core\output\notification::NOTIFY_ERROR, false);
    }



}

if($mformMan->is_cancelled()){

}else if($fromform = $mformMan->get_data()){
    $records = new stdClass();

    $labId = $fromform->labid;
    $ipsText = $fromform->manualIps;

    $lines = explode("\n", $ipsText);

    $ips = [];

    foreach ($lines as $line) {
        $ips[] = explode(",",$line);
    }

    $ipsCheckArr = [];
    foreach($ips as $ip){

        $records->ip = $ip[0];
        $records->device_number = $ip[1];
        $records->device_type = 'private';
        $records->labid = $labId;


        if($DB->insert_record('local_secureaccess_devices', $records)){


        }else{
            $ipsCheckArr[] = $records->ip = $ip[0];
        }

    }
    if(empty($ipsCheckArr)){
        echo $OUTPUT->notification(get_string('ip_success', 'local_secureaccess'), \core\output\notification::NOTIFY_SUCCESS, false);
    }
    else{
        foreach($ipsCheckArr as $ipcheck)
        echo $OUTPUT->notification($ipcheck.' '.get_string('err_ipinsertion', 'local_secureaccess'), \core\output\notification::NOTIFY_ERROR, false);
    }


}


if($mformAdmin->is_cancelled()){

}
else if($fromform = $mformAdmin->get_data()){
    $records = new stdClass();

    $labId = $fromform->labid;
    $ipsText = $fromform->admin_ips;

    $lines = explode("\n", $ipsText);

    $ips = [];

    foreach ($lines as $line) {
        $ips[] = trim($line);

    }


    $ipsCheckArr = [];
    $deviceCheck = true;
    foreach($ips as $ip){



        $deviceId = $DB->get_field('local_secureaccess_devices', 'id', ['ip' => $ip]);

        $records->labid = $labId;
        $records->device_id = $deviceId;

        if($deviceId){
            if($DB->execute('INSERT INTO {local_secureaccess_admin_devices}(labid, device_id) VALUES (?,?)', [
            $labId,
            $deviceId
            ])){


            }
            else{
                $ipsCheckArr = $ip;
            }
        }else{
            $ipsCheckArr = $ip;
            $deviceCheck = false;
            echo $OUTPUT->notification(get_string('err_ipnull_admin', 'local_secureaccess'), \core\output\notification::NOTIFY_ERROR, false);
        }

    }
    if(empty($ipsCheckArr)){
        echo '<br><br><br><br>';
        echo $OUTPUT->notification(get_string('ip_success', 'local_secureaccess'), \core\output\notification::NOTIFY_SUCCESS, false);
    }
    else{
        foreach($ipsCheckArr as $ipcheck)
        echo '<br><br><br><br>';
        echo $OUTPUT->notification($ipcheck.' '.get_string('err_ipinsertion', 'local_secureaccess'), \core\output\notification::NOTIFY_ERROR, true);
    }


}


$template_ctx_auto = [
    'submitted' => false,
    'formhtml' => $mform->render()
];

$template_ctx_manual = [
    'submitted' => false,
    'formhtml' => $mformMan->render()
];

$template_admin_devices = [
    'submitted' => false,
    'formhtml' => $mformAdmin->render()
];

$header = [
    "insertGroup" => new moodle_url("/local/secureaccess/insert_groups.php"),
    "insertLabs"=> new moodle_url("/local/secureaccess/insert_labs.php"),
    "insertIp"=> new moodle_url("/local/secureaccess/insert_ranges.php"),
    "updateLabs"=> new moodle_url("/local/secureaccess/update_labs.php"),
    "inquiry"=> new moodle_url("/local/secureaccess/inquiry.php"),
    "home"=> new moodle_url("/local/secureaccess/index.php")
];

$context = [
    'form' => [$template_ctx_auto, $template_admin_devices, $template_ctx_manual],
    'header' => $header
];

echo $OUTPUT->header();

echo  $OUTPUT->notification(get_string('ip_range_hint', 'local_secureaccess'), \core\output\notification::NOTIFY_WARNING, false);
echo  $OUTPUT->notification(get_string('ip_example_hint', 'local_secureaccess'), \core\output\notification::NOTIFY_WARNING, false);

//echo $OUTPUT->render_from_template('local_secureaccess/insert_ranges',$context);
echo $OUTPUT->render_from_template('local_secureaccess/container',$context);






echo $OUTPUT->footer();
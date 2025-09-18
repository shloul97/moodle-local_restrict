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

define('AJAX_SCRIPT', true);


require_once(__DIR__ . '/../../../../config.php');


//echo json_encode(array('status'=> 0,'message'=> 'error'));

require_login();
require_sesskey();


// Restrict access to admins only
if (!is_siteadmin()) {
    throw new required_capability_exception(
        context_system::instance(),
        'moodle/site:config',
        'nopermissions',
        ''
    );
}


$deviceId = required_param('deviceId', PARAM_INT);

$action = required_param('action', PARAM_TEXT);





$record = new stdClass();
$record->id = $deviceId;
$record->status = 1;



if($action == 'sus'){


    $record = new stdClass();
    $record->id = $deviceId;
    $record->status = 0;

    $DB->update_record('local_secureaccess_devices', $record);


    echo json_encode(array('status'=> 1,'message'=> 'Success'));
}

if($action == 'act'){

    $record = new stdClass();
    $record->id = $deviceId;
    $record->status = 1;

    $DB->update_record('local_secureaccess_devices', $record);

    echo json_encode(array('status'=> 1,'message'=> 'Success'));
}

if($action == 'del'){

    $DB->delete_records('local_secureaccess_devices', array('id' => $record->id));
    echo json_encode(array('status'=> 1,'message'=> 'Success'));
}

if($action == 'admin'){

    $dataaction = required_param('dataaction', PARAM_TEXT);
    $labid = required_param('lab', PARAM_TEXT);

    echo json_encode(array('status'=> 1,'message'=> $deviceId));
    if($dataaction == "rmadmin"){
        $DB->delete_records('local_secureaccess_admin_devices',
         ['labid' => $labid, 'device_id' => $deviceId]);
    }
    else{
        $record = new stdClass();
        $record->labid = $labid;
        $record->device_id = $deviceId;

        $newid = $DB->insert_record('local_secureaccess_admin_devices', $record);

        if($newid){
            echo get_string('record_success', 'local_secureaccess') . $newid;
        }
        else{
            echo get_string('record_err', 'local_secureaccess');
        }
    }


}



//echo json_encode(array('status'=> 0,'message'=> 'Sorry we can\'t handle you request for now'));


die();
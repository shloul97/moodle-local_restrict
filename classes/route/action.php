<?php

/**
 *
* @package local_restrict
* @author Moayad Shloul
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


    $sql = 'UPDATE {local_restrict_devices} set status = 0 where id = ?';
    $DB->execute($sql, params: array($record->id));
    echo json_encode(array('status'=> 1,'message'=> 'Success'));
}

if($action == 'act'){


    $sql = 'UPDATE {local_restrict_devices} set status = 1 where id = ?';
    $DB->execute($sql, params: array($record->id));
    echo json_encode(array('status'=> 1,'message'=> 'Success'));
}

if($action == 'del'){
    //$DB->delete_records('local_restrict_devices', array('id' => $record->id));
    echo json_encode(array('status'=> 1,'message'=> 'Success'));
}



//echo json_encode(array('status'=> 0,'message'=> 'Sorry we can\'t handle you request for now'));


die();
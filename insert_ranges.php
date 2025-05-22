<?php

/**
 *
 * @package local_restrict
 * @author Moayad Shloul
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

$mformMan = new insert_ranges_manual();
$mformAdmin = new insert_ranges_admin();

$PAGE->set_url(new moodle_url("/local/restrict/insert_ranges.php"));
$PAGE->set_context(\context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title("Insert Ranges");
$PAGE->requires->css('/local/restrict/amd/src/css/styles.css');


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
            \core\notification::error('The starting range must not start with (1) or (100) or (200) e.g.: 127.0.0.(1)');
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

        if($DB->insert_record('local_restrict_devices', $record)){

        }
        else{
            $ipsCheckArr[] = $records->ip = $ip[0];
        }
    }
    if(empty($ipsCheckArr)){
        echo $OUTPUT->notification('IPs Inserted Successfully', \core\output\notification::NOTIFY_SUCCESS, false);
    }
    else{
        foreach($ipsCheckArr as $ipcheck)
        echo $OUTPUT->notification($ipcheck.' IP Not Inserted check your database', \core\output\notification::NOTIFY_ERROR, false);
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


        if($DB->insert_record('local_restrict_devices', $records)){


        }else{
            $ipsCheckArr[] = $records->ip = $ip[0];
        }

    }
    if(empty($ipsCheckArr)){
        echo $OUTPUT->notification('IPs Inserted Successfully', \core\output\notification::NOTIFY_SUCCESS, false);
    }
    else{
        foreach($ipsCheckArr as $ipcheck)
        echo $OUTPUT->notification($ipcheck.' IP Not Inserted check your database', \core\output\notification::NOTIFY_ERROR, false);
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
                $ipsCheckArr = $ip;
            }
        }else{
            $ipsCheckArr = $ip;
            $deviceCheck = false;
            echo $OUTPUT->notification('There\'s no device with IP you Entered', \core\output\notification::NOTIFY_ERROR, false);
        }

    }
    if(empty($ipsCheckArr)){
        echo '<br><br><br><br>';
        echo $OUTPUT->notification('IPs Inserted Successfully', \core\output\notification::NOTIFY_SUCCESS, false);
    }
    else{
        foreach($ipsCheckArr as $ipcheck)
        echo '<br><br><br><br>';
        echo $OUTPUT->notification($ipcheck.' IP Not Inserted check your database', \core\output\notification::NOTIFY_ERROR, true);
    }


}


$templatecontextAuto = [
    'submitted' => false,
    'formhtml' => $mform->render()
];

$templatecontextManual = [
    'submitted' => false,
    'formhtml' => $mformMan->render()
];

$adminDevices = [
    'submitted' => false,
    'formhtml' => $mformAdmin->render()
];

$header = [
    "insertGroup" => new moodle_url("/local/restrict/insert_groups.php"),
    "insertLabs"=> new moodle_url("/local/restrict/insert_labs.php"),
    "insertIp"=> new moodle_url("/local/restrict/insert_ranges.php"),
    "updateLabs"=> new moodle_url("/local/restrict/update_labs.php"),
    "home"=> new moodle_url("/local/restrict/index.php")
];

$context = [
    'form' => [$templatecontextAuto, $adminDevices, $templatecontextManual],
    'header' => $header
];

echo $OUTPUT->header();

echo  $OUTPUT->notification('The last one or two digit from device IP should be the device number', \core\output\notification::NOTIFY_WARNING, false);
echo  $OUTPUT->notification('The starting range must not start from (1) e.g.: 127.0.0.(1)', \core\output\notification::NOTIFY_WARNING, false);

//echo $OUTPUT->render_from_template('local_restrict/insert_ranges',$context);
echo $OUTPUT->render_from_template('local_restrict/container',$context);






echo $OUTPUT->footer();
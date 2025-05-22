<?php

/**
 *
 * @package local_restrict
 * @author Moayad Shloul
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


$PAGE->requires->css('/local/restrict/amd/src/css/styles.css');


$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js_call_amd('local_restrict/action', 'init', [
    'sesskey' => sesskey()
]);
//$PAGE->requires->js('/local/restrict/amd/src/action2.js');

//echo '<script src="/moodle/local/restrict/amd/src/action.js"></script>';

$mform = new update_labs();

$dataArr = array();

if ($mform->is_cancelled()) {

}
else if ($fromform = $mform->get_data()) {


    $record = new stdClass();


    if(isset($fromform->labid)) {
        $record->labid = $fromform->labid;
    }

    if(isset($fromform->deviceip)) {
        $record->ip = $fromform->deviceip;
    }

    try
    {
        $records = '';



        if($record->labid != '' && $record->ip != '') {
            $data = $DB->get_records('local_restrict_devices', [
                    'ip' => $record->ip,
                    'labid' => $record->labid
                ]);
        }
        else if($record->labid != '' && $record->ip == '') {
            $data = $DB->get_records('local_restrict_devices', [
                    'labid' => $record->labid
                ]);
        }
        else if ($record->labid == '' && $record->ip != ''){
            $data = $DB->get_records('local_restrict_devices', [
                    'ip' => $record->ip,
                ]);

        }



        foreach ($data as $device) {
            $labname = $DB->get_field('local_restrict_labs', 'lab_name', ['id' => $device->labid]);
            if($device->status == 1){
                $deviceStatus = 'Active';
                $statusClass = "success";
                $btnClass = "warning";
                $btnContent = "Suspend";
                $btnData = "sus";


            }else{
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

            ];
        }

        $data1 = [
            'table' => [
                'tableData' => $dataArr
            ]
        ];


        //var_dump($dataArr);




    }
    catch(Exception $e)
    {
        \core\notification::error("Failed to insert the lab.");
    }


}





$templatecontext = [
    'submitted' => false,
    'formhtml' => $mform->render()
];

$header = [
    "insertGroup" => new moodle_url("/local/restrict/insert_groups.php"),
    "insertLabs"=> new moodle_url("/local/restrict/insert_labs.php"),
    "insertIp"=> new moodle_url("/local/restrict/insert_ranges.php"),
    "updateLabs"=> new moodle_url("/local/restrict/update_labs.php"),
    "home"=> new moodle_url("/local/restrict/index.php")
];
$context = [
    'form' => [$templatecontext],
    'header' => $header,
    'table' => $data1['table'],
    'sesskey' => sesskey()
];


echo $OUTPUT->header();

echo  $OUTPUT->notification('The last one or two digit from device IP should be the device number', \core\output\notification::NOTIFY_WARNING, false);
echo  $OUTPUT->notification('The starting range must not start from (1) e.g.: 127.0.0.(1)', \core\output\notification::NOTIFY_WARNING, false);

//echo $OUTPUT->render_from_template('local_restrict/insert_ranges',$context);
echo $OUTPUT->render_from_template('local_restrict/update_labs',$context);

echo $OUTPUT->footer();





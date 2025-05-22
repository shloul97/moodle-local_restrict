<?php

/**
 *
 * @package local_restrict
 * @author Moayad Shloul
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../config.php');
require_once($CFG->dirroot . '/local/restrict/classes/form/insert_labs_form.php');



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

$mform = new insert_labs();

$PAGE->set_url(new moodle_url("/local/restrict/add_labs.php"));
$PAGE->set_context(\context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title("Add Users");

$PAGE->requires->css('/local/restrict/amd/src/css/styles.css');





if ($mform->is_cancelled()) {

}
else if ($fromform = $mform->get_data()) {


    $record = new stdClass();



    $record->lab_name = $fromform->labname;
    $record->capacity = $fromform->capacity;






    try
    {
        $id = $DB->insert_record('local_restrict_labs', $record);
        if($id)
        {
            \core\notification::success("Inserted lab with ID: $id");
        }


    }
    catch(Exception $e)
    {
        \core\notification::error("Failed to insert the lab.");
    }


}



$templatecontextAuto = [
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
    'form' => [$templatecontextAuto],
    'header' => $header
];



echo $OUTPUT->header();

echo  $OUTPUT->notification('The last one or two digit from device IP should be the device number', \core\output\notification::NOTIFY_WARNING, false);
echo  $OUTPUT->notification('The starting range must not start from (1) e.g.: 127.0.0.(1)', \core\output\notification::NOTIFY_WARNING, false);

echo $OUTPUT->render_from_template('local_restrict/container',$context);



echo $OUTPUT->footer();
<?php

/**
 *
 * @package local_restrict
 * @author Moayad Shloul
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



require_once('../../config.php');

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

$PAGE->set_pagelayout('admin');
$PAGE->set_title("Restrict User");
$PAGE->requires->css('/local/restrict/amd/src/css/styles.css');


$templatecontext = [

    "insertGroup" => new moodle_url("/local/restrict/insert_groups.php"),
    "insertLabs"=> new moodle_url("/local/restrict/insert_labs.php"),
    "insertIp"=> new moodle_url("/local/restrict/insert_ranges.php"),
    "updateLabs"=> new moodle_url("/local/restrict/update_labs.php"),
    "home"=> new moodle_url("/local/restrict/index.php")
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_restrict/home',$templatecontext);
echo $OUTPUT->footer();


<?php
// This file is part of Moodle - http://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify.
// it under the terms of the GNU General Public License as published by.
// the Free Software Foundation, either version 3 of the License, or.
// This file is part of Moodle - http://moodle.org/.
// Moodle is distributed in the hope that it will be useful,.
// but WITHOUT ANY WARRANTY; without even the implied warranty of.
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the.
// This file is part of Moodle - http://moodle.org/.
// You should have received a copy of the GNU General Public License.
// This file is part of Moodle - http://moodle.org/.
/*
 * @package   local_restrict
 * @copyright 2025 Moayad Shloul <shloul97@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/restrict/classes/form/insert_ranges_form.php');
require_once($CFG->dirroot . '/local/restrict/classes/form/insert_ranges_manual_form.php');
require_once($CFG->dirroot . '/local/restrict/classes/form/insert_admin_devices_form.php');

require_login();

// Restrict access to admins only.
if (!is_siteadmin()) {
    throw new required_capability_exception(
        context_system::instance(),
        'moodle/site:config',
        'nopermissions',
        ''
    );
}

// Setup page.
$PAGE->set_context(context_system::instance());
global $OUTPUT, $PAGE, $CFG;

$mform = new insert_ranges();
$mformmanual = new insert_ranges_manual();
$mformadmin = new insert_ranges_admin();

$PAGE->set_url(new moodle_url("/local/restrict/insert_ranges.php"));
$PAGE->set_context(\context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('rangestitle','local_restrict'));

if ($mform->is_cancelled()) {
} else if ($fromform = $mform->get_data()) {
    $records = new stdClass();
    $records->ipstart = $fromform->ipstart;
    $records->ipend = $fromform->ipend;
    $records->labid = $fromform->labid;

    $devicearray = array();
    // First digit of last box in ip address Ex: 127.0.0.178 last_start = 1 from (178).
    $laststart = (int) substr(strrchr($records->ipstart, '.'), 1);
    // Last digit of last box in ip address Ex: 127.0.0.178 last_end = 8 from (178).
    $lastend = (int) substr(strrchr($records->ipend, '.'), 1);

    if ($laststart == 100) {
        $i = 1;
    } else {
        $i = 0;
    }

    $loop = $lastend - $laststart;


    for ($i = 0; $i <= $loop; $i++) {
        // Convert ip to string.
        $iplong = ip2long($records->ipstart);
        // Start Ip from first input ip 127.0.0.1.
        $iplong += $i; /* => 127.0.0.2 */
        // Convert new ip from string to ip.
        $newip = long2ip($iplong);


        if ($laststart == 1 || $laststart == 100) {
            \core\notification::error(get_string('err_iprange', 'local_restrict'));
            break;
        }
        if ($laststart >= 100) {
            $devicenumber = $laststart + $i;
            // Get last two digit from ip if last digits greater tha 100.
            $devicetosave = (int) substr($devicenumber, -2);
            array_push(
                $devicearray, array(
                /* Device number is the last digit\s from ip Ex 127.0.0.(33) Device No. 33 */
                "deviceNo" => $devicetosave,
                "deviceIp" => $newip
                )
            );
        } else {
            $devicenumber = $laststart + $i;
            array_push(
                $devicearray, array(
                /* Device number is the last digit\s from ip Ex 127.0.0.(33) Device No. 33 */
                "deviceNo" => $devicenumber,
                "deviceIp" => $newip
                )
            );
        }
    }
    $ipscheckarray = [];
    foreach ($devicearray as $device) {
        $number = $device['deviceNo'];
        $ip = $device['deviceIp'];

        $record = new stdClass();
        $record->ip = $ip;
        $record->device_number = $number;
        $record->device_type = 'private';
        $record->labid = $fromform->labid;

        // Try To insert device (ip,number) in devices Table.
        try {
            $DB->insert_record('local_restrict_devices', $record);
        } catch (dml_write_exception $e) {
            $ipscheckarray[] = $records->ip = $ip[0];
        }
    }
    if (empty($ipscheckarray)) {
        echo $OUTPUT->notification(
            get_string('ip_success', 'local_restrict'),
            \core\output\notification::NOTIFY_SUCCESS,
            false
        );
    } else {
        echo $OUTPUT->notification(
            get_string('err_ipinsertion', 'local_restrict'),
            \core\output\notification::NOTIFY_ERROR,
            false
        );
    }
}

if ($mformmanual->is_cancelled()) {
} else if ($fromform = $mformmanual->get_data()) {
    $records = new stdClass();

    $labid = $fromform->labid;
    $ipstext = $fromform->manualIps;

    // Get full ip with device number line by line.
    $lines = explode("\n", $ipstext);

    $ips = [];
    foreach ($lines as $line) {
        // separate device ip and device number.
        $ips[] = explode(",", $line);
    }
    $ipscheckarray = [];
    foreach ($ips as $ip) {
        $records->ip = $ip[0];
        $records->device_number = $ip[1];
        $records->device_type = 'private';
        $records->labid = $labid;
        try {
            $DB->insert_record('local_restrict_devices', $records);
        } catch (dml_write_exception $e) {
            $ipscheckarray[] = $records->ip = $ip[0];
        }
    }
    if (empty($ipscheckarray)) {
        echo $OUTPUT->notification(
            get_string('ip_success', 'local_restrict'),
            \core\output\notification::NOTIFY_SUCCESS,
            false
        );
    } else {
        echo $OUTPUT->notification(
            get_string('err_ipinsertion', 'local_restrict'),
            \core\output\notification::NOTIFY_ERROR,
            false
        );
    }
}

if ($mformadmin->is_cancelled()) {
} else if ($fromform = $mformadmin->get_data()) {
    $records = new stdClass();

    $labid = $fromform->labid;
    $ipstext = $fromform->admin_ips;


    // Get full ip with device number line by line.
    $lines = explode("\n", $ipstext);

    $ips = [];

    foreach ($lines as $line) {
        $ips[] = trim($line);
    }

    $ipscheckarray = [];
    $devicecheck = true;
    foreach ($ips as $ip) {
        // Check if device exist or not.
        $deviceid = $DB->get_field('local_restrict_devices', 'id', ['ip' => $ip]);
        $records->labid = $labid;
        $records->device_id = $deviceid;
        if ($deviceid) {
            try {
                // Admin ip insertion.
                $DB->execute(
                    'INSERT INTO {local_restrict_admin_devices}(labid, device_id)
                VALUES (?,?)', [
                    $labid,
                    $deviceid
                    ]
                );
            } catch (dml_write_exception $e) {
                $ipscheckarray = $ip;
            }
        } else {
            $ipscheckarray = $ip;
            $devicecheck = false;
            echo $OUTPUT->notification(
                get_string('err_ipnull_admin', 'local_restrict'),
                \core\output\notification::NOTIFY_ERROR,
                false
            );
        }
    }
    if (empty($ipscheckarray)) {
        echo $OUTPUT->notification(
            get_string('ip_success', 'local_restrict'),
            \core\output\notification::NOTIFY_SUCCESS,
            false
        );
    } else {
        echo $OUTPUT->notification(
            get_string('err_ipinsertion', 'local_restrict'),
            \core\output\notification::NOTIFY_ERROR,
            true
        );
    }
}

// Template context to automatic ip insertion.
$templatectxauto = [
    'submitted' => false,
    'formhtml' => $mform->render()
];

// Template context to manual ip insertion.
$templatectxmanual = [
    'submitted' => false,
    'formhtml' => $mformmanual->render()
];

// Template context to admin ip insertion.
$templateadmindevices = [
    'submitted' => false,
    'formhtml' => $mformadmin->render()
];

// Plugin Header.
$header = [
    "insertGroup" => new moodle_url("/local/restrict/insert_groups.php"),
    "insertLabs" => new moodle_url("/local/restrict/insert_labs.php"),
    "insertIp" => new moodle_url("/local/restrict/insert_ranges.php"),
    "updateLabs" => new moodle_url("/local/restrict/update_labs.php"),
    "inquiry" => new moodle_url("/local/restrict/inquiry.php"),
    "home" => new moodle_url("/local/restrict/index.php")
];

$context = [
    'form' => [$templatectxauto, $templateadmindevices, $templatectxmanual],
    'header' => $header
];

echo $OUTPUT->header();
echo $OUTPUT->notification(
    get_string('ip_range_hint', 'local_restrict'),
    \core\output\notification::NOTIFY_WARNING,
    false
);
echo $OUTPUT->notification(
    get_string('ip_example_hint', 'local_restrict'),
    \core\output\notification::NOTIFY_WARNING,
    false
);
echo $OUTPUT->render_from_template('local_restrict/container', $context);
echo $OUTPUT->footer();

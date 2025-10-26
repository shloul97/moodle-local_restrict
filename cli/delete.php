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

define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php'); // CLI helpers.


list($options, $unrecognized) = cli_get_params(
    [
    'userid' => '',
    ], []
);

$userid = (int)$options['userid'];
if (!$userid) {
    cli_error('Please provide a valid userid, e.g., --userid=2');
}

$user = \core_user::get_user($userid);

echo "Processing export for user: " . fullname($user) . " (id=$userid)\n";

// Initialize empty session for CLI export.
\core\session\manager::init_empty_session();
\core\session\manager::set_user($user);

// Get all contexts for this user for our component.
$manager = new \core_privacy\manager();
$contextlists = $manager->get_contexts_for_userid($userid);
$approvedlist = new \core_privacy\local\request\contextlist_collection($userid);
$trace = new text_progress_trace();

foreach ($contextlists as $contextlist) {
    if ($contextlist->get_component() === 'local_restrict') {
        $approvedlist->add_contextlist(
            new \core_privacy\local\request\approved_contextlist(
                $user,
                $contextlist->get_component(),
                $contextlist->get_contextids()
            )
        );
    }
}



// Delete record.
$manager->delete_data_for_user($approvedlist, $trace);

echo "\n Record Deleted !\n";
echo "============================================================================\n";

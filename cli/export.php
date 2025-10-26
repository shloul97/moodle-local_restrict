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

define('CLI_SCRIPT', true);
require __DIR__ . '/../../../config.php';
require_once $CFG->libdir . '/clilib.php'; // CLI helpers


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

// Initialize empty session for CLI export
\core\session\manager::init_empty_session();
\core\session\manager::set_user($user);

// Get all contexts for this user for our component
$manager = new \core_privacy\manager();
$contextlists = $manager->get_contexts_for_userid($userid);


$approvedlist = new \core_privacy\local\request\contextlist_collection($userid);
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




// Get the exported files path
$basedir = make_temp_directory('privacy');
$exportpath = make_unique_writable_directory($basedir, true);

// Extract exported content (for inspection)
$fp = get_file_packer();
$exportedcontent = $manager->export_user_data($approvedlist); // Run the export
$fp->extract_to_pathname($exportedcontent, $exportpath);

echo "\nExport complete!\n";
echo "You can inspect the files at: {$exportpath}\n";
echo "============================================================================\n";

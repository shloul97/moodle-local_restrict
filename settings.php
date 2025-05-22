<?php

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) { // Only show for admins
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_restrict', // Unique page ID
        get_string('pluginname', 'local_restrict'), // Display name
        new moodle_url('/local/restrict/index.php') // Where the link points
    ));
}


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


defined('MOODLE_INTERNAL') || die();

/**
 * Capabilities for local_restrict plugin
 *
 * @package local_restrict
 */
$capabilities = array(

    // Capability for managing and editing restrictions.
    'local/restrict:manage' => array(
        'riskbitmask' => RISK_CONFIG | RISK_DATALOSS, // modifying restrictions can break setups.
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'admin' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/site:config'
    ),

    // Capability for viewing restrictions, logs, or reports.
    'local/restrict:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'admin' => CAP_ALLOW,
        ),
    ),
);

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


defined('MOODLE_INTERNAL') || die();

/**
 * Capabilities for local_restrict plugin
 *
 * @package   local_restrict
 */
$capabilities = array(

    // Capability for managing and editing restrictions
    'local/restrict:manage' => array(
        'riskbitmask' => RISK_CONFIG | RISK_DATALOSS, // modifying restrictions can break setups
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'admin' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/site:config'
    ),

    // Capability for viewing restrictions, logs, or reports
    'local/restrict:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'admin' => CAP_ALLOW,
        ),
    ),
);

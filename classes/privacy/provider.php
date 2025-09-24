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



namespace local_restrict\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy API provider for local_restrict.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Returns metadata about this plugin's data.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_restrict_user_exam',
            [
                'userid'    => 'privacy:metadata:local_restrict_user_exam:userid',
                'examid'    => 'privacy:metadata:local_restrict_user_exam:examid',
                'groupid'   => 'privacy:metadata:local_restrict_user_exam:groupid',
                'privateip' => 'privacy:metadata:local_restrict_user_exam:privateip',
                'publicip'  => 'privacy:metadata:local_restrict_user_exam:publicip',
                'status_id' => 'privacy:metadata:local_restrict_user_exam:statusid',
            ],
            'privacy:metadata:local_restrict_user_exam'
        );
        return $collection;
    }

    /**
     * Get the list of contexts that contain user information.
     *
     * @param int $userid The user to search.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {local_restrict_user_exam} ue ON ue.userid = :userid
                 WHERE ctx.contextlevel = :systemlevel";

        $params = [
            'userid' => $userid,
            'systemlevel' => CONTEXT_SYSTEM,
        ];

        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Export user data for the given contexts.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $records = $DB->get_records('local_restrict_user_exam', ['userid' => $userid]);
            if ($records) {
                writer::with_context($context)->export_data(
                    ['Secure Exam Access'],
                    (object) $records
                );
            }
        }
    }

    /**
     * Delete data for all users in a context.
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $DB->delete_records('local_restrict_user_exam');
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_users(approved_contextlist $contextlist) {
        global $DB;

        foreach ($contextlist->get_userids() as $userid) {
            $DB->delete_records('local_restrict_user_exam', ['userid' => $userid]);
        }
    }

    /**
     * Delete all user data for the specified user, in the given contexts.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $DB->delete_records('local_restrict_user_exam', ['userid' => $userid]);
        }
    }
}
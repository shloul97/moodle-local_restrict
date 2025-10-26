<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>
/**
 *
 * @package   local_restrict
 * @copyright 2025 Moayad Shloul <shloul97@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
function local_restrict_before_footer() {
    global $USER, $DB;

    $localip = $_SERVER["REMOTE_ADDR"];

    if (isloggedin() && !isguestuser()) {
        $userid = $USER->id;

        $publicip = false;
        if ($DB->get_manager()->table_exists('local_restrict_devices')) {
            $publicdevices = $DB->get_records_sql(
                'SELECT d.ip
				FROM {local_restrict_devices} d
				JOIN  {local_restrict_admin_devices} ad
				ON d.id = ad.device_id
				JOIN {local_restrict_labs} lb
				ON ad.labid = lb.id', []
            );

            foreach ($publicdevices as $pdevice) {
                if (trim($pdevice->ip) == trim($localip)) {
                    $publicip = true;
                    break;
                }
            }

            if (!$publicip) {
                $context = context_system::instance();

                $roles = get_user_roles($context, $USER->id);

                $hasrole = false;
                foreach ($roles as $role) {
                    // Check user role if student or teacher (plugin works on student only).
                    if (in_array($role->shortname, ['teacher', 'manager', 'coursecreator', 'editingteacher'])) {
                        $hasrole = true;

                        break;
                    }
                }

                if (!$hasrole && !is_siteadmin($userid)) {
                    // ? Get all allowed IPs for the user.
                    $userdevices = $DB->get_records_sql(
                        '
                    SELECT d.ip
                      FROM {local_restrict_user_exam} u
                      JOIN {local_restrict_devices} d ON u.privateip = d.id
                      JOIN {quiz} q on q.id = u.examid
                     WHERE u.userid = ? and q.timeopen < UNIX_TIMESTAMP() + 1800 and q.timeclose > UNIX_TIMESTAMP()
                ', [$userid]
                    );

                    $allowedips = array_map(
                        function ($d) {
                            return trim($d->ip);
                        }, $userdevices
                    );

                    // ? Check if current IP is in allowed list.
                    if (!empty($allowedips) && !in_array(trim($localip), $allowedips)) {
                        // Logout if check return true.
                        \core\notification::add(
                            'You don\'t have access on this device.',
                            \core\output\notification::NOTIFY_ERROR
                        );
                        require_logout();
                    }
                }
            }
        }
    }
}

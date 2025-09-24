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



require_once($CFG->dirroot . '/config.php');


function local_restrict_before_footer()
{
	global $USER, $DB;

	$local_ip = $_SERVER["REMOTE_ADDR"];




	if (isloggedin() && !isguestuser()) {
		$userid = $USER->id;

		$public_ip = false;
		if ($DB->get_manager()->table_exists('local_restrict_devices')) {
			$public_devices = $DB->get_records_sql('SELECT d.ip
				FROM {local_restrict_devices} d
				JOIN  {local_restrict_admin_devices} ad
				ON d.id = ad.device_id
				JOIN {local_restrict_labs} lb
				ON ad.labid = lb.id', []);

			foreach ($public_devices as $pdevice) {
				if (trim($pdevice->ip) == trim($local_ip)) {
					$public_ip = true;
					break;
				}
			}

			if (!$public_ip) {

				$context = context_system::instance(); // or use context_course::instance($courseid), etc.

				$roles = get_user_roles($context, $USER->id);

				$has_role = false;
				foreach ($roles as $role) {
					if (in_array($role->shortname, ['teacher', 'manager', 'coursecreator', 'editingteacher'])) {
						$has_role = true;

						break;
					}
				}

				if (!$has_role && !is_siteadmin($userid)) {
                // ? Get all allowed IPs for the user.
                $user_devices = $DB->get_records_sql('
                    SELECT d.ip
                      FROM {local_restrict_user_exam} u
                      JOIN {local_restrict_devices} d ON u.privateip = d.id
                      join mdl_quiz q on q.id = u.examid
                     WHERE u.userid = ? and q.timeopen < UNIX_TIMESTAMP() + 1800 and q.timeclose > UNIX_TIMESTAMP()
                ', [$userid]);


                $allowed_ips = array_map(function($d) {
                    return trim($d->ip);
                }, $user_devices);

                // ? Check if current IP is in allowed list.
                if (!empty($allowed_ips) && !in_array(trim($local_ip), $allowed_ips)) {
                    \core\notification::add('You don\'t have access on this device.', \core\output\notification::NOTIFY_ERROR);
                    require_logout();
                    $microsoft_logout_url = "https://login.microsoftonline.com/common/oauth2/logout?post_logout_redirect_uri=" . urlencode("https://exams.zu.edu.jo/login/logout.php");

                    redirect($microsoft_logout_url);


            }
          }
				}
			}
		}
	}


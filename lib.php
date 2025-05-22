<?php

/**
 * @package     local_restrict
 * @author      Moayad Shloul
 * @license
 * @var stdClass $plugin
 */






function local_restrict_before_footer()
{
	global  $USER,$DB;

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
				ON ad.labid = lb.id',[]);

			foreach ($public_devices as $pdevice) {
				if($pdevice == $local_ip){
					$public_ip = true;
					break;
				}
			}

			if(!$public_ip){

				$context = context_system::instance(); // or use context_course::instance($courseid), etc.

				$roles = get_user_roles($context, $USER->id);

				$hasRole = false;
				foreach ($roles as $role) {
				if (in_array($role->shortname, ['teacher', 'manager', 'coursecreator','editingteacher'])) {
					$hasRole = true;
					break;
				}
				}

				//if(!$hasRole && !is_siteadmin($userid))
				if(true) 
        {
					$users = $DB->get_records_sql('SELECT
						u.*, d.ip
						FROM {local_restrict_user_exam} u
						JOIN {local_restrict_devices} d
						ON u.privateip = d.id
						AND u.userid = ?
							', [
						(int) $userid,
					]);
					if($users){

						foreach ($users as $user) {
							if ($user->ip == $local_ip) {
								break;
							}
							else {
								\core\notification::add('You didn\'t have access on this device.', \core\output\notification::NOTIFY_ERROR);
								require_logout();
								redirect(new moodle_url('/login/index.php'));
							}
						}

					}
					else{
						//\core\notification::add('You didn\'t have access on this device.', \core\output\notification::NOTIFY_ERROR);
						//require_logout();
						//redirect(new moodle_url('/login/index.php'));
					}
				}
			}
		}
	}
}

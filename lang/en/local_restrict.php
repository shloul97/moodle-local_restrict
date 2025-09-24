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



/* Main */
$string['pluginname'] = "Secure Exam Access";
$string['plugintitle'] = 'Secure Exam Access Plugin';
$string['check_exam_closure_task'] = 'Check for closed exams';

/* Hard Text */
$string['failed_lab_insertion'] = 'Failed to insert the lab.';
$string['lab_insertion'] = 'Inserted lab with ID:';
$string['err_iprange'] = 'The starting range must not start with (1) or (100) or (200) e.g.: 127.0.0.(1)';
$string['err_ipinsertion'] = 'IP Not Inserted check your database';
$string['ip_success'] = 'IPs Inserted Successfully';
$string['err_ipnull_admin'] = 'There\'s no device with IP you Entered';
$string['ip_range_hint'] = 'The last one or two digit from device IP should be the device number';
$string['ip_example_hint'] = 'The starting range must not start from (1) e.g.: 127.0.0.(1)';
$string['record_success'] = 'Record inserted successfully with ID:';
$string['record_err'] = 'Error inserting record.';
$string['distrputed_err'] = "Users exceeds the maximum number of devices in one of quiz in the course" . "\ndevices:";
$string['distrputed_sucess'] = 'Users Distriputed';
$string['hint_notification']= 'Course should be has a group and every group has an exam';

/* Elements */
$string['delete'] = 'Delete';
$string['selectitems'] = 'Select Labs';
$string['selectcourse'] = 'Select Course';
$string['capacity'] = 'Lab Capacity';
$string['labname'] = 'Lab short name';
$string['add_btn'] = 'Add';
$string['ipstart'] = 'IP Start';
$string['ipend'] = 'IP End';
$string['courses'] = 'courses';
$string['filter'] = 'Filter';
$string['admin_ips'] = 'Admin IPs';
$string['admin_ips_place'] = "One IP per line... Exp:\n127.0.1.1 \n127.0.0.1\n...";
$string['admin_header'] = 'Admin Devices';
$string['distrbute_btn'] = "Distrbute";
$string['auto_insertion_header'] = 'Automatic insertion';
$string['manual_insertion_header'] = 'Manual insertion';
$string['ips'] = 'IPs';
$string['manual_ip_place'] = "One IP per line (Separated) by comma with device name or number ... Exp:\n127.0.1.1,1 \n127.0.0.2,2\n...";
$string['device_ip'] = 'Device IP';

/* Headers */
$string['quiz_distrputed_header'] = 'Distpute By Quiz: ';
$string['insertlabs_header'] = 'Insert Labs';
$string['insertips_header'] = 'Insert Labs';
$string['insertgroups_header'] = 'Distrpute users';
$string['updatelabs_header'] = 'Update Labs';
$string['search'] = 'Search';


/* Templates */
$string['action'] = "Action";
$string['course_table_moodleid'] = "Course Moodle Id";
$string['course_table_id'] = "Course Id Number";
$string['course_table_name'] = "Course Name";
$string['insertranges'] = "Insert Ranges";

/* metadata */
$string['privacy:metadata:local_restrict_user_exam'] =
    'The Secure Access plugin stores details of which users were assigned to exams and on which devices.';

$string['privacy:metadata:local_restrict_user_exam:userid'] = 'The ID of the user taking the exam.';
$string['privacy:metadata:local_restrict_user_exam:examid'] = 'The exam assigned to the user.';
$string['privacy:metadata:local_restrict_user_exam:groupid'] = 'The group associated with the user in the exam.';
$string['privacy:metadata:local_restrict_user_exam:privateip'] = 'The private IP address of the device used.';
$string['privacy:metadata:local_restrict_user_exam:publicip'] = 'The public IP address recorded.';
$string['privacy:metadata:local_restrict_user_exam:statusid'] = 'The current status of the user in the exam.';





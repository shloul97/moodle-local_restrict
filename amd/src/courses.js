
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
define(
    ['jquery', 'core/ajax','core/str'], function ($, Ajax,str) {
        'use strict';
        return {
            init: function () {

                var msg;
                var status;
                var statustxt;
                var response;
                var args;
                var delconfirm;
                var failedalert;

                var strings = [
                { key: 'msg', component: 'local_restrict' },
                { key: 'status', component: 'local_restrict' },
                { key: 'statustxt', component: 'local_restrict' },
                { key: 'response', component: 'local_restrict' },
                { key: 'args', component: 'local_restrict' },
                { key: 'delconfirm', component: 'local_restrict' },
                { key: 'failedalert', component: 'local_restrict' }
                ];

                str.get_strings(strings).then(
                    function (results) {
                        msg = results[0];
                        status = results[1];
                        statustxt = results[2];
                        response = results[3];
                        args = results[4];
                        delconfirm = results[5];
                        failedalert = results[6];
                    }
                ).catch(
                    function (e) {
                        alert(failedalert, e);
                    }
                );


                $('button[name=action-btn]').click(
                    function () {
                        var courseId = $(this).attr('data-id');
                        var action = $(this).attr('data-action');

                        if (action === 'del') {
                            if (confirm(delconfirm) == true) {
                                var courseargs = {
                                    courseid: parseInt(courseId),
                                    action: action.toString()
                                };
                                Ajax.call(
                                    [{
                                        methodname: 'local_restrict_courses_records',
                                        args: courseargs
                                    }]
                                )[0].then(
                                    function () {
                                        $('#tr-' + courseId).fadeOut(300);
                                    }
                                ).catch(
                                    function (err) {
                                        let fullError = `
                                         <div style="color:red;">
                                            <strong> ${str.get_string('ajxerr','local_restrict')}</strong><br>
                                            ${msg} ${err.message || 'N/A'}<br>
                                            ${status} ${err.status || 'N/A'}<br>
                                            ${statustxt} ${err.statusText || 'N/A'}<br>
                                            ${response}
                                            ${err.responseText || JSON.stringify(err)}<br>
                                            ${args}
                                            ${JSON.stringify(courseargs)}
                                        </div>
                                        `;
                                        $('#ajx-err').html(fullError);
                                    }
                                );
                            }

                            else {

                            }
                        }

                    }
                );
            }
        };
    }
);



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
define(['jquery', 'core/ajax','core/str'], function ($, Ajax,str) {
    'use strict';
    return {
        init: function () {

            var msg;
            var status;
            var statustxt;
            var response;
            var args;

            var strings = [
                { key: 'msg', component: 'local_restrict' },
                { key: 'status', component: 'local_restrict' },
                { key: 'statustxt', component: 'local_restrict' },
                { key: 'response', component: 'local_restrict' },
                { key: 'args', component: 'local_restrict' }
            ];

             str.get_strings(strings).then(function (results) {
                msg = results[0];
                status = results[1];
                statustxt = results[2];
                response = results[3];
                args = results[4];
            }).catch(function (e) {
                alert('Failed to load strings:', e);
            });


            // -------- Form Submit.
            $('.mform').submit(function (e) {
                e.preventDefault();

                var data = $(this).serializeArray();
                var labs = [];
                var quizes = [];

                //labs and course debugging.
                let course_debug = data.find(x => x.name === "course")?.value;
                let labs_debug = data.filter(x => x.name === "labs[]").map(x => x.value);

                if(!course_debug.length|| labs_debug.length <= 0){
                    alert("Please Select Course and Labs");
                    return;
                }

                $(this).find('input[name="labs[]"]:checked').each(function () {
                    labs.push($(this).val());
                });

                $(this).find('input[name="quizes[]"]:checked').each(function () {
                    quizes.push($(this).val());
                });

                var usersArgs = {
                    courseid: parseInt(data[2].value),
                    labs: labs,
                    quiz: quizes
                };

                Ajax.call([{
                    methodname: 'local_restrict_get_users',
                    args: usersArgs
                }])[0].then(function (res) {
                    var msgDiv = $('.msg-div');
                    msgDiv.removeClass('msg-err msg-success');

                    if (res.status === 0) {
                        msgDiv.html(res.message).addClass('msg-err').fadeIn(200);
                    } else {
                        msgDiv.html(res.message).addClass('msg-success').fadeIn(200);
                    }

                }).catch(function (err) {
                    let fullError = `
                                    <div style="color:red;">
                                        <strong> ${str.get_string('ajxerr','local_restrict')}</strong><br>
                                        ${msg} ${err.message || 'N/A'}<br>
                                        ${status} ${err.status || 'N/A'}<br>
                                        ${statustxt} ${err.statusText || 'N/A'}<br>
                                        ${response}
                                        ${err.responseText || JSON.stringify(err)}<br>
                                        ${args}
                                        ${JSON.stringify(usersArgs)}
                                    </div>
                                `;
                    $('#ajx-err').html(fullError);
                });

            });

            // -------- Course Change.
            $('input[name=course]').change(function () {
                var val = parseInt($(this).val());

                // Get Quizes.
                var quizesArgs = {
                    courseid: val
                };
                Ajax.call([{
                    methodname: 'local_restrict_get_quizes',
                    args: quizesArgs
                }])[0].then(function (res) {
                    let html = '';
                    if (res.status == 1) {
                        $.each(res.message, function (index, value) {
                            html += `<div class="form-check form-check-inline" data-id="${index}">
                                <input class="form-check-input" name="quizes[]" type="checkbox"
                                id="checkbox${value.id}" value="${value.id}">
                                <label class="form-check-label" for="checkbox${value.id}">${value.name}</label>
                            </div>`;
                        });
                        $('#quizes-div').html(html);
                    }

                }).catch(function (err) {
                    let fullError = `
                                    <div style="color:red;">
                                        <strong> ${str.get_string('ajxerr','local_restrict')}</strong><br>
                                        ${msg} ${err.message || 'N/A'}<br>
                                        ${status} ${err.status || 'N/A'}<br>
                                        ${statustxt} ${err.statusText || 'N/A'}<br>
                                        ${response}
                                        ${err.responseText || JSON.stringify(err)}<br>
                                        ${args}
                                        ${JSON.stringify(quizesArgs)}
                                    </div>
                                `;
                    $('#ajx-err').html(fullError);
                });
            });
        }
    };
});
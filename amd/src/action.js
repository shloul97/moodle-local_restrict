
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
    ['jquery', 'core/ajax', 'core/str'], function ($, Ajax, str) {
        'use strict';
        return {
            init: function () {

                var ajxerr = '';
                var jserr = '';
                var args = '';
                var mkadminconfirm;
                var adminremovecofnirm;
                var delconfirm;
                var failedalert;

                var mkadmin;
                var rmadmin;
                var active;
                var activate;
                var inactive;
                var suspend;




                var long_strings = [
                    { key: 'ajxerr', component: 'local_restrict' },
                    { key: 'jserr', component: 'local_restrict' },
                    { key: 'args', component: 'local_restrict' },
                    { key: 'mkadminconfirm', component: 'local_restrict' },
                    { key: 'adminremovecofnirm', component: 'local_restrict' },
                    { key: 'delconfirm', component: 'local_restrict' },
                    { key: 'failedalert', component: 'local_restrict' }
                ];

                str.get_strings(long_strings).then(
                    function (results) {
                        ajxerr = results[0];
                        jserr = results[1];
                        args = results[2];
                        mkadminconfirm = results[3];
                        adminremovecofnirm = results[4];
                        delconfirm = results[5];
                        failedalert = results[6];
                    }
                ).catch(
                    function (e) {
                        alert(failedalert, e);
                    }
                );
                const stringKeys = [
                    'rmadmin', 'mkadmin', 'suspend',
                    'activate', 'active', 'inactive'
                ];

                const strings = stringKeys.map(key => ({ key, component: 'local_restrict' }));

                str.get_strings(strings)
                    .then(results => {
                        const localized = Object.fromEntries(stringKeys.map((key, i) => [key, results[i]]));
                        // You can now access them as:
                        // localized.rmadmin, localized.mkadmin, etc.
                        rmadmin = localized.rmadmin;
                        mkadmin = localized.mkadmin;
                        activate = localized.activate;
                        inactive = localized.inactive;
                        suspend = localized.suspend;
                        active = localized.active;
                    })
                    .catch(e => {
                        alert(failedalert, e);
                    });


                $('button[name=action-btn]').click(
                    function () {

                        var deviceId = $(this).attr('data-id');
                        var action = $(this).attr('data-action');
                        var btn = $(this);
                        var spanText = $('#span-' + deviceId);
                        var dataAction = 'sus';
                        if (action == 'sus') {
                            dataAction = 'act';
                        }

                        if (action === 'del') {
                            if (confirm(delconfirm) == true) {



                                var requestArgs = {
                                    deviceid: parseInt(deviceId),
                                    action: action.toString()
                                };

                                Ajax.call(
                                    [{
                                        methodname: 'local_restrict_update_labs',
                                        args: requestArgs
                                    }]
                                )[0].then(
                                    function () {
                                        $('#tr-' + deviceId).fadeOut(300);
                                    }
                                ).catch(
                                    function (err) {
                                        let error = `<span>${ajxerr} ${err} <span><br><span>
                                        ${jserr} ${err.message}</span><br><span>
                                        ${args} ${requestArgs}</span>`;
                                        $('#ajx-err').html(error);

                                    }
                                );

                            }
                            else {

                            }
                        }
                        else {


                            var requestArgs = {
                                deviceid: parseInt(deviceId),
                                action: action.toString()
                            };

                            Ajax.call(
                                [{
                                    methodname: 'local_restrict_update_labs',
                                    args: requestArgs
                                }]
                            )[0].then(
                                function () {
                                    if (action != 'del') {
                                        btn.attr('data-action', dataAction);
                                        btn.toggleClass('btn-warning btn-success');
                                        if (btn.html() === suspend) {
                                            btn.html(activate);
                                            spanText.html(inactive);
                                        } else {
                                            btn.html(suspend);
                                            spanText.html(active);
                                        }

                                        spanText.toggleClass('text-success text-danger');
                                    }


                                }
                            ).catch(
                                function (err) {
                                    let error = `<span>${ajxerr} ${err} <span><br><span>
                                    ${jserr} ${err.message}</span><br><span>
                                    ${args} ${requestArgs}</span>`;
                                    $('#ajx-err').html(error);
                                }
                            );

                        }

                    }
                );


                $("button[name=admin-btn]").click(
                    function () {

                        var dataAction = $(this).attr('data-action');
                        var btn = $(this);
                        var deviceId = $(this).attr('data-id');
                        var lab = btn.attr('data-lab');
                        var action = 'admin';

                        var confirmString = '';





                        if (dataAction == 'mkadmin') {
                            confirmString = mkadminconfirm;

                        }
                        else {
                            confirmString = adminremovecofnirm;
                        }

                        if (confirm(confirmString) == true) {



                            var requestArgs = {
                                deviceid: parseInt(deviceId, 10),   // ensure number.
                                action: action.toString(),
                                dataaction: dataAction.toString(),
                                lab: parseInt(lab, 10)
                            };

                            Ajax.call(
                                [{
                                    methodname: 'local_restrict_update_labs',
                                    args: requestArgs
                                }]
                            )[0].then(
                                function (e) {

                                    if (e.status == 1) {
                                        btn.toggleClass('btn-primary btn-secondary');
                                        if (dataAction === "mkadmin") {
                                            dataAction = "rmadmin";
                                            btn.html(rmadmin);
                                            btn.attr('data-action', 'rmadmin');
                                        }
                                        else if (dataAction === "rmadmin") {
                                            btn.html(mkadmin);
                                            btn.attr('data-action', 'mkadmin');
                                        }
                                    }


                                }
                            ).catch(
                                function (err) {
                                    let error = `<span>${ajxerr} ${err} <span><br><span>
                                    ${jserr} ${err.message}</span><br><span>
                                    ${args} ${requestArgs}</span>`;
                                    $('#ajx-err').html(error);
                                }
                            );

                        }
                    }
                );
            }
        };
    }
);
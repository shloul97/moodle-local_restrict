define(['jquery','core/ajax','core/str'], function ($, Ajax,str) {
    'use strict';
    return {
        init: function () {
            $('button[name=action-btn]').click(function () {

                var deviceId = $(this).attr('data-id');
                var action = $(this).attr('data-action');
                var btn = $(this);
                var spanText = $('#span-' + deviceId);
                var dataAction = 'sus';
                if (action == 'sus') {
                    dataAction = 'act';
                }

                if (action === 'del') {
                    if (confirm(str.get_string('delconfirm','local_restrict')) == true) {



                        var requestArgs = {
                            deviceid: parseInt(deviceId),
                            action: action.toString()
                        };

                        Ajax.call([{
                            methodname: 'local_restrict_update_labs',
                            args: requestArgs
                        }])[0].then(function () {
                            $('#tr-' + deviceId).fadeOut(300);
                        }).catch(function (err) {
                            let error = `<span>${str.get_string('ajxerr','local_restrict')}: ${err} <span><br><span>
                    ${str.get_string('jserr','local_restrict')} ${err.message}</span><br><span>${str.get_string('args','local_restrict')} ${requestArgs}</span>`;
                            $('#ajx-err').html(error);

                        });

                    }
                    else {

                    }
                }
                else {


                    var requestArgs = {
                        deviceid: parseInt(deviceId),
                        action: action.toString()
                    };

                    Ajax.call([{
                        methodname: 'local_restrict_update_labs',
                        args: requestArgs
                    }])[0].then(function () {
                        if (action != 'del') {
                            btn.attr('data-action', dataAction);
                            btn.toggleClass('btn-warning btn-success');
                            if (btn.html() === "Suspend") {
                                btn.html("Activate");
                                spanText.html("Inactive");
                            } else {
                                btn.html("Suspend");
                                spanText.html("Active");
                            }

                            spanText.toggleClass('text-success text-danger');
                        }


                    }).catch(function (err) {
                        let error = `<span>${str.get_string('ajxerr','local_restrict')}: ${err} <span><br><span> Error:
                      ${err.message}</span><br><span>${str.get_string('args','local_restrict')} :${requestArgs}</span>`;
                        $('#ajx-err').html(error);
                    });

                }

            });


            $("button[name=admin-btn]").click(function () {

                var dataAction = $(this).attr('data-action');
                var btn = $(this);
                var deviceId = $(this).attr('data-id');
                var lab = btn.attr('data-lab');
                var action = 'admin';

                var confirmString = '';





                if (dataAction == 'mkadmin') {
                    confirmString = str.get_string('mkadminconfirm','local_restrict');

                }
                else {
                    confirmString =str.get_string('adminremovecofnirm','local_restrict');;
                }

                if (confirm(confirmString) == true) {



                    var requestArgs = {
                        deviceid: parseInt(deviceId, 10),   // ensure number
                        action: action.toString(),
                        dataaction: dataAction.toString(),
                        lab: parseInt(lab, 10)
                    };

                    Ajax.call([{
                        methodname: 'local_restrict_update_labs',
                        args: requestArgs
                    }])[0].then(function (e) {

                        if (e.status == 1) {
                            btn.toggleClass('btn-primary btn-secondary');
                            if (dataAction === "mkadmin") {
                                dataAction = "rmadmin";
                                btn.html("Remove Admin");
                                btn.attr('data-action', 'rmadmin');
                            }
                            else if (dataAction === "rmadmin") {
                                btn.html("Make Admin");
                                btn.attr('data-action', 'mkadmin');
                            }
                        }


                    }).catch(function (err) {
                        let error = `<span>${str.get_string('ajxerr','local_restrict')}: ${err} <span><br><span>
                      ${str.get_string('jserr','local_restrict')} ${err.message}</span><br><span>${str.get_string('args','local_restrict')} ${requestArgs}</span>`;
                        $('#ajx-err').html(error);
                    });

                }
            });
        }
    };
});
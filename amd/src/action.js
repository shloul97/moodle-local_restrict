define(['jquery','core/ajax'], function ($, Ajax) {
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
                    if (confirm("Are You sure you want to delete record ?") == true) {



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
                            let error = `<span>AJAX Error: ${err} <span><br><span>
                    Error: ${err.message}</span><br><span>Args were :${requestArgs}</span>`;
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
                        let error = `<span>AJAX Error: ${err} <span><br><span> Error:
                      ${err.message}</span><br><span>Args were :${requestArgs}</span>`;
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
                    confirmString = "Are You sure you want to make this device Admin ?";

                }
                else {
                    confirmString = "Are You sure you want to Remove this device From Admin ?";
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
                        let error = `<span>AJAX Error: ${err} <span><br><span>
                      Error: ${err.message}</span><br><span>Args were :${requestArgs}</span>`;
                        $('#ajx-err').html(error);
                    });

                }
            });
        }
    };
});

/*$(document).ready(function () {
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
            if (confirm("Are You sure you want to delete record ?") == true) {

                require(['core/ajax'], function (Ajax) {

                    var requestArgs = {
                        deviceid: parseInt(deviceId),
                        action: action.toString()
                    };

                    Ajax.call([{
                        methodname: 'local_restrict_update_labs',
                        args: requestArgs
                    }])[0].then(function (e) {
                        $('#tr-' + deviceId).fadeOut(300);
                    }).catch(function (err) {
                        console.error("AJAX Error:", err);
                        console.error("Error: " + err.message);
                        console.error("Args were:", requestArgs);
                    });
                });
            }
            else {

            }
        }
        else {
            require(['core/ajax'], function (Ajax) {

                var requestArgs = {
                    deviceid: parseInt(deviceId),
                    action: action.toString()
                };

                Ajax.call([{
                    methodname: 'local_restrict_update_labs',
                    args: requestArgs
                }])[0].then(function (e) {
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
                    console.error("AJAX Error:", err);
                    console.error("Error: " + err.message);
                    console.error("Args were:", requestArgs);
                });
            });
        }

    });


    $("button[name=admin-btn]").click(function () {

        var dataAction = $(this).attr('data-action');
        var btn = $(this);
        var deviceId = $(this).attr('data-id');
        var lab = btn.attr('data-lab');
        var action = 'admin';
        var sesskey = $('#sesskey').val();
        var confirmString = '';


        console.log('dataAction: ' + dataAction + '\n' + 'deviceId: '
            + deviceId + '\n' + 'lab: ' + lab + '\n' + 'action: ' + action);


        if (dataAction == 'mkadmin') {
            confirmString = "Are You sure you want to make this device Admin ?";

        }
        else {
            confirmString = "Are You sure you want to Remove this device From Admin ?";
        }

        if (confirm(confirmString) == true) {

            require(['core/ajax'], function (Ajax) {

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
                    console.log(e);
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
                    console.error("AJAX Error:", err);
                    console.error("Error: " + err.message);
                    console.error("Args were:", requestArgs);
                });
            });
        }
    });
});*/
define(['jquery'], function($) {
    'use strict';
    return {
        init: function() {
            $('button[name=action-btn]').click(function(){

                var deviceId = $(this).attr('data-id');
                var action = $(this).attr('data-action');
                var sesskey = $('#sesskey').val();
                var btn = $(this);
                var spanText = $('#span-'+ deviceId);
                var dataAction = 'sus';
                if(action == 'sus'){
                    dataAction = 'act';
                }
                if(action === 'del'){
                    if (confirm("Are You sure you want to delete record ?") == true) {
                        $.ajax({
                            url: M.cfg.wwwroot + "/local/restrict/classes/route/action.php",
                            method: 'POST',
                            data: {
                                deviceId : deviceId,
                                action: action,
                                sesskey: sesskey},
                            dataType: 'json',
                            success: function() {
                                $('#tr-'+deviceId).fadeOut(300);
                            }
                        });
                    }

                    else {

                    }
                }
                else{
                    $.ajax({
                        url: M.cfg.wwwroot + "/local/restrict/classes/route/action.php",
                        method: 'POST',
                        data: {
                            deviceId : deviceId,
                            action: action,
                            sesskey: sesskey},
                        dataType: 'json',
                        success: function(e) {
                            var json = JSON.stringify(e);
                            if(action != 'del'){
                                btn.attr('data-action',dataAction);
                                btn.toggleClass('btn-warning btn-success');
                                if (btn.html() === "Suspend") {
                                    btn.html("Activate");
                                    spanText.html("Inactive");
                                } else {
                                    btn.html("Suspend");
                                    spanText.html("Active");
                                }

                                spanText.toggleClass('text-success text-danger');
                                $('#logs-area').html(json);
                                //console.log(json);
                            }

                        }
                    });
                }
            });
        }
    };
});

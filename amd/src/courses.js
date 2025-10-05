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


            $('button[name=action-btn]').click(function () {
                var courseId = $(this).attr('data-id');
                var action = $(this).attr('data-action');

                if (action === 'del') {
                    if (confirm("Are You sure you want to delete record ?") == true) {
                        var courseargs = {
                            courseid: parseInt(courseId),
                            action: action.toString()
                        };
                        Ajax.call([{
                            methodname: 'local_restrict_courses_records',
                            args: courseargs
                        }])[0].then(function () {
                            $('#tr-' + courseId).fadeOut(300);
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
                                        ${JSON.stringify(courseargs)}
                                    </div>
                                `;
                            $('#ajx-err').html(fullError);
                        });
                    }

                    else {

                    }
                }

            });
        }
    };
});


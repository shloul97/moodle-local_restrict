define(['jquery', 'core/ajax','core/str'], function ($, Ajax,str) {
    'use strict';
    return {
        init: function () {
            $('button[name=action-btn]').click(function () {
                var courseId = $(this).attr('data-id');
                var action = $(this).attr('data-action');

                if (action === 'del') {
                    if (confirm("Are You sure you want to delete record ?") == true) {
                        var args = {
                            courseid: parseInt(courseId),
                            action: action.toString()
                        };
                        Ajax.call([{
                            methodname: 'local_restrict_courses_records',
                            args: args
                        }])[0].then(function () {
                            $('#tr-' + courseId).fadeOut(300);
                        }).catch(function (err) {
                            let fullError = `
                                    <div style="color:red;">
                                        <strong>AJAX Error:</strong><br>
                                       ${str.get_string('msg', 'local_restrict')} ${err.message || 'N/A'}<br>
                                        ${str.get_string('status', 'local_restrict')} ${err.status || 'N/A'}<br>
                                        ${str.get_string('statustxt', 'local_restrict')} ${err.statusText || 'N/A'}<br>
                                        ${str.get_string('response', 'local_restrict')}
                                        ${err.responseText || JSON.stringify(err)}<br>
                                        ${str.get_string('args', 'local_restrict')} ${JSON.stringify(args)}
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


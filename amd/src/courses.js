define(['jquery', 'core/ajax'], function ($, Ajax) {
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
                                        Message: ${err.message || 'N/A'}<br>
                                        Status: ${err.status || 'N/A'}<br>
                                        Status Text: ${err.statusText || 'N/A'}<br>
                                        Response: ${err.responseText || JSON.stringify(err)}<br>
                                        Arguments: ${JSON.stringify(args)}
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


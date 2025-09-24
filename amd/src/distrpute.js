define(['jquery', 'core/ajax'], function ($, Ajax) {
    'use strict';
    return {
        init: function () {


            // -------- Form Submit
            $('.mform').submit(function (e) {
                e.preventDefault();

                var data = $(this).serializeArray();
                var labs = [];
                var quizes = [];

                //labs and course debugging
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
                                        <strong>AJAX Error:</strong><br>
                                        Message: ${err.message || 'N/A'}<br>
                                        Status: ${err.status || 'N/A'}<br>
                                        Status Text: ${err.statusText || 'N/A'}<br>
                                        Response: ${err.responseText || JSON.stringify(err)}<br>
                                        Arguments: ${JSON.stringify(usersArgs)}
                                    </div>
                                `;
                    $('#ajx-err').html(fullError);
                });

            });

            // -------- Course Change
            $('input[name=course]').change(function () {
                var val = parseInt($(this).val());

                // Get Quizes
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
                                        <strong>AJAX Error:</strong><br>
                                        Message: ${err.message || 'N/A'}<br>
                                        Status: ${err.status || 'N/A'}<br>
                                        Status Text: ${err.statusText || 'N/A'}<br>
                                        Response: ${err.responseText || JSON.stringify(err)}<br>
                                        Arguments: ${JSON.stringify(quizesArgs)}
                                    </div>
                                `;
                    $('#ajx-err').html(fullError);
                });
            });
        }
    };
});
$(document).ready(function () {
    $('.mform').submit(function (e) {
        e.preventDefault();


        var data = $(this).serializeArray();
        var labs = [];

        $(this).find('input[name="labs[]"]:checked').each(function () {
            labs.push($(this).val());

        });


        if (data.length <= 3) {
            alert("Please Select Course and Labs");
        }
        else {

            require(['core/ajax'], function (Ajax) {

                var requestArgs = {
                    courseid: parseInt(data[2].value),
                    labs: labs,
                };

                Ajax.call([{
                    methodname: 'local_secureaccess_get_users',
                    args: requestArgs
                }])[0].then(function (e) {
                    console.log(e.status);
                    if (e.status == 0) {
                        var msgDiv = $('.msg-div');

                        msgDiv.html(e.message);
                        msgDiv.addClass('msg-err');
                        msgDiv.fadeIn(200);


                        console.log(e.message); // Will show: "Students distributed successfully"
                        console.log(e.results); // Optional: see distribution details
                    }
                    else {
                        var msgDiv = $('.msg-div');

                        msgDiv.html(e.message);
                        msgDiv.addClass('msg-success');
                        msgDiv.fadeIn(200);

                    }
                }).catch(function (err) {
                    console.error("AJAX Error:", err);
                    console.error("Error: " + err.message);
                    console.error("Args were:", requestArgs);
                });
            });
        }

    });

    $('input[name=course]').change(function () {
        var val = $(this).val();
        var action = "quizes";


        //-------- Get Quizes of the Course
        require(['core/ajax'], function (Ajax) {

            var requestArgs = {
                courseid: parseInt(val)
            };

            Ajax.call([{
                methodname: 'local_secureaccess_get_quizes',
                args: requestArgs
            }])[0].then(function (e) {
                console.log(e);
                let html = ``;
                $.each(e.message, function (index, value) {
                    console.log("value: "+value);
                    console.log("index: "+index);
                    html += `<div class="form-check form-check-inline">
                <input class="form-check-input" name="quizes[]" type="checkbox" id="checkbox${value['id']}" value="${value['id']}">
                <label class="form-check-label" for="checkbox${value['id']}">${value['name']}</label>
                    </div>`;
                });

                $('#quizes-div').html(html);




                console.log(e.message);
            }).catch(function (err) {
                console.error("AJAX Error:", err);
                console.error("Error: " + err.message);
                console.error("Args were:", requestArgs);
            });
        });


        //-------- Get Groups of the Course
        require(['core/ajax'], function (Ajax) {

            var requestArgs = {
                courseid: parseInt(val)
            };

            Ajax.call([{
                methodname: 'local_secureaccess_get_groups',
                args: requestArgs
            }])[0].then(function (e) {
                console.log(e);
                let html = ``;
                $.each(e.message, function (index, value) {
                    console.log("value: "+value);
                    console.log("index: "+index);
                    html += `<div class="form-check form-check-inline">
                <input class="form-check-input" name="quizes[]" type="checkbox" id="checkbox${value['id']}" value="${value['id']}">
                <label class="form-check-label" for="checkbox${value['id']}">${value['name']}</label>
                    </div>`;
                });

                $('#quizes-div').html(html);




                console.log(e.message);
            }).catch(function (err) {
                console.error("AJAX Error:", err);
                console.error("Error: " + err.message);
                console.error("Args were:", requestArgs);
            });
        });

    });


});
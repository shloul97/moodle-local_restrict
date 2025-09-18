define(['jquery'], function($) {
    'use strict';
    return {
        init: function() {
            $('.mform').submit(function(e){
        e.preventDefault();


        var data = $(this).serializeArray();
        var labs = [];

        $(this).find('input[name="labs[]"]:checked').each(function() {
            labs.push($(this).val());

        });


        if(data.length <= 3){
            alert("Please Select Course and Labs");
        }
        else{
            $.ajax({

                url: "classes/route/get_user.php",
                method: 'POST',
                data: {courseId : data[2].value, labs: labs, sesskey: data[0].value},

                success: function() {
                    alert("Students Distrputed to labs successfully");
                },
            });
        }

    });
        }
    };
});

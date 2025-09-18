

$(document).ready(function () {

    $('button[name=action-btn]').click(function () {

        var courseId = $(this).attr('data-id');
        var action = $(this).attr('data-action');
        var sesskey = $('#sesskey').val();
        var btn = $(this);
        var spanText = $('#span-' + courseId);
        var dataAction = 'sus';
        if (action == 'sus') {
            dataAction = 'act';
        }

        console.log(action);
        if (action === 'del') {
            if (confirm("Are You sure you want to delete record ?") == true) {
                $.ajax({
                    url: M.cfg.wwwroot + "/local/secureaccess/classes/route/courses_records.php",
                    method: 'POST',
                    data: {
                        courseId: courseId,
                        action: action,
                        sesskey: sesskey
                    },
                    dataType: 'json',
                    success: function (e) {

                        console.log(e.data);
                        $('#tr-' + courseId).fadeOut(300);
                    }, error: function (jqXHR, textStatus, errorThrown) {
                        console.error("AJAX Error:");
                        console.error("Status:", textStatus);
                        console.error("Thrown Error:", errorThrown);
                        console.error("Response Text:", jqXHR.responseText);

                    }
                });
            }

            else {

            }
        }

    });
});

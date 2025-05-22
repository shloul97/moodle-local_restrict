define(['jquery'], function($) {
    'use strict';
    return {
        init: function() {
            $('#dropdownSearch').on('keyup', function() {
                var searchTerm = $(this).val().toLowerCase();
                $('.dropdown-menu .dropdown-item').each(function() {
                    var text = $(this).text().toLowerCase();
                    if (text.indexOf(searchTerm) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
            $('input[name=course]').on('change',function(){
                if ($(this).is(':checked')) {
                    $('#dropdownMenu').html($('#'+$(this).val()).val());
                }
            });
        }
    };
});

/*$(document).ready(function(){
    $('#dropdownSearch').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.dropdown-menu .dropdown-item').each(function() {
            var text = $(this).text().toLowerCase();
            if (text.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    $('input[name=course]').on('change',function(){
        if ($(this).is(':checked')) {
            $('#dropdownMenu').html($('#'+$(this).val()).val());
        }
    });

});*/
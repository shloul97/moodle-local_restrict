
// This file is part of Moodle - http://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify.
// it under the terms of the GNU General Public License as published by.
// the Free Software Foundation, either version 3 of the License, or.
// This file is part of Moodle - http://moodle.org/.
// Moodle is distributed in the hope that it will be useful,.
// but WITHOUT ANY WARRANTY; without even the implied warranty of.
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the.
// This file is part of Moodle - http://moodle.org/.
// You should have received a copy of the GNU General Public License.
// This file is part of Moodle - http://moodle.org/.
/*
 * @package   local_restrict
 * @copyright 2025 Moayad Shloul <shloul97@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
define(
    ['jquery'], function ($) {
        'use strict';
        return {
            init: function () {
                $('#dropdownSearch').on(
                    'keyup', function () {
                        var searchTerm = $(this).val().toLowerCase();
                        $('.dropdown-menu .dropdown-item').each(
                            function () {
                                var text = $(this).text().toLowerCase();
                                if (text.indexOf(searchTerm) > -1) {
                                    $(this).show();
                                } else {
                                    $(this).hide();
                                }
                            }
                        );
                    }
                );
                $('input[name=course]').on(
                    'change',function () {
                        if ($(this).is(':checked')) {
                            $('#dropdownMenu').html($('#'+$(this).val()).val());
                        }
                    }
                );
            }
        };
    }
);

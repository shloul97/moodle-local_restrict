<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package local_restrict
 * @author Moayad Shloul
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');



class insert_groups extends moodleform {

    protected function definition() {

        global $OUTPUT, $PAGE, $CFG,$DB;

        $mform = $this->_form;

        $records = $DB->get_records('course');


        $courseArr = array();

        $html = '<div class="dropdown-checkbox">
            <button type="button" class="btn btn-secondary dropdown-toggle" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                '.get_string('selectcourse','local_restrict').'
            </button>
            <div class="dropdown-menu p-3" aria-labelledby="dropdownMenu">
                <!-- Search input for filtering options -->
                <input type="text" class="form-control" id="dropdownSearch" placeholder="Search...">

                <!-- Scrollable area for checkboxes -->
                <div class="checkbox-container" style="max-height: 200px; overflow-y: auto;">

                ';

        $i = 0;
        foreach ($records as $choice) {

            $html .= '<label class="dropdown-item">
                        <input name="course" type="radio" value="' . $choice->id . '"> ' . $choice->fullname . '
                        <input type="hidden" id="'.$choice->id.'" value="'.$choice->fullname.'">
                        </label>
                        ';

        }






        $html .= '   </div> <!-- End of scrollable area -->
                    </div>
                </div>
                ';

        $mform->addElement('html',$html);


        //$htmlLabs =''
        $htmlLabs = ' <div class="labs-div mt-3">
            <div class="labs-label-div">
                <h4>Select Labs: </h4>
            </div>
            <div class="labs-select-div">
            ';

        $records_labs = $DB->get_records('local_restrict_labs');

        foreach ($records_labs as $choice) {
            $htmlLabs .= '<div class="form-check d-flex flex-column justify-content-right">
                   <input class="form-check-input" name="labs[]" type="checkbox" value="'.$choice->id.'" id="lab-'.$choice->id.'">
                <label class="form-check-label" for="lab-'.$choice->id.'">
                  '.$choice->lab_name.'
                </label>
                </div>';

        }



        $htmlLabs .= '</div>
        </div>';

        $mform->addElement('html',$htmlLabs);



        $buttonarray[] = $mform->createElement('submit', 'submitbutton', "Distrbute");

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

    }


    function validation($data, $files)
    {
        return array();
    }

}

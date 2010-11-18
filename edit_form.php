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
 * Defines the form for editing slides
 *
 * @package    mod
 * @subpackage slideshow
 * @copyright  2010 onwards Mark Johnson  {@link http://barrenfrozenwasteland.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/formslib.php');

class slide_edit_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->addElement('hidden', 'slide');
        $mform->addElement('header', 'editslide', get_string('editslides', 'slideshow'));
        $mform->addElement('htmleditor', 'slidehtml', get_string('slide', 'slideshow'));

        $buttongroup = array();
        $buttongroup[] = $mform->createElement('submit', 'submit', get_string('saveandedit', 'slideshow'));
        $buttongroup[] = $mform->createElement('submit', 'submit', get_string('saveandview', 'slideshow'));
        $mform->addGroup($buttongroup, 'submits', '&nbsp;');
    }

    public function process($data) {
        
    }
}
?>

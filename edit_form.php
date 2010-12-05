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
 * @subpackage standardslideshow
 * @copyright  2010 onwards Mark Johnson  {@link http://barrenfrozenwasteland.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/formslib.php');

class slide_edit_form extends moodleform {
    public function definition() {
        global $id, $slideshow, $slide;
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->addElement('hidden', 'slide');
        $mform->addElement('header', 'editslide', get_string('editslides', 'standardslideshow'));
        $mform->addElement('htmleditor', 'slidehtml', get_string('slide', 'standardslideshow'));
        if ($slide > 0) {
            $previewslide = 1;
        } else {
            $previewslide = $slide;
        }
        $previewurl = new moodle_url('/mod/standardslideshow/preview.php#slide'.$previewslide, array('id' => $id, 'slide' => $slide));
        $mform->addElement('static', 'preview', get_string('preview', 'standardslideshow'), html_writer::tag('iframe', '', array('src' => $previewurl->out(false), 'height' => $slideshow->height*0.75, 'width' => $slideshow->width*0.75)));
        $buttongroup = array();
        $buttongroup[] = $mform->createElement('submit', 'submit', get_string('saveandedit', 'standardslideshow'));
        $buttongroup[] = $mform->createElement('submit', 'submit', get_string('saveandview', 'standardslideshow'));
        $mform->addGroup($buttongroup, 'submits', '&nbsp;');
    }

}
?>

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
 * Confirms the deletion of a slide, then removes it from the appropriate HTML file.
 *
 * @package    mod
 * @subpackage slideshow
 * @copyright  2010 onwards Mark Johnson  {@link http://barrenfrozenwasteland.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once("../../config.php");
require_once($CFG->dirroot.'/mod/slideshow/edit_form.php');

$id = optional_param('id',0,PARAM_INT);    // Course Module ID, or
$s = optional_param('s',0,PARAM_INT);     // Slideshow ID
$slide = required_param('slide', 0, PARAM_INT);     // Slide number
$confirm = optional_param('confirm', 0, PARAM_BOOL);     // Is deletion confirmed?

if ($id) {
    $PAGE->set_url('/mod/slideshow/edit.php', array('id'=>$id));
    if (! $cm = get_coursemodule_from_id('slideshow', $id)) {
        print_error('invalidcoursemodule');
    }

    if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
        print_error('coursemisconf');
    }

    if (! $slideshow = $DB->get_record("slideshow", array("id"=>$cm->instance))) {
        print_error('invalidcoursemodule');
    }

} else {
    $PAGE->set_url('/mod/slideshow/edit.php', array('s'=>$s));
    if (! $slideshow = $DB->get_record("slideshow", array("id"=>$s))) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id"=>$slideshow->course)) ){
        print_error('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance("slideshow", $slideshow->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}

require_login($course);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/slideshow:edit', $context);

$redirecturl = new moodle_url('/mod/slideshow/edit.php', array('id' => $cm->id));

$dom = new DOMDocument();
$dom->loadHTMLFile($CFG->dataroot.'/s5/'.$slideshow->id.'.html');
$xpath = new DOMXPath($dom);
$slides = $xpath->query("//div[contains(@class, 'slide')]");

if (!$current_slide = $slides->item($slide)) {
    print_error('invalidslide', 'slideshow', $redirecturl);
}

if ($slides->length == 1) {
    print_error('lastslide', 'slideshow', $redirecturl);
}

if ($confirm) {
    $parent = $xpath->query("//div[contains(@class, 'presentation')]")->item(0);
    $parent->removeChild($current_slide);
    $dom->saveHTMLFile($CFG->dataroot.'/s5/'.$slideshow->id.'.html');
    redirect($redirecturl);
    exit();
} else {
    $confirmmessage = html_writer::tag('p', get_string('deleteconfirm', 'slideshow', $slide), array('class' => 'deleteconfirm'));
    $confirmurl = new moodle_url('/mod/slideshow/delete_slide.php', array('id' => $cm->id, 'slide' => $slide, 'confirm' => true));
    $confirmbutton = $OUTPUT->single_button($confirmurl, get_string('confirm'), 'get');
    $cancelurl = new moodle_url('/mod/slideshow/edit.php', array('id' => $cm->id, 'slide' => $slide));
    $cancelbutton = $OUTPUT->single_button($cancelurl, get_string('cancel'), 'get');

    echo $OUTPUT->header($course);
    echo $confirmmessage;
    echo $confirmbutton;
    echo $cancelbutton;
    echo $OUTPUT->footer();
}
?>

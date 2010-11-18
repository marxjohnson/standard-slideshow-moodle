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
 * Displays and handles form for editing slides
 *
 * @package    mod
 * @subpackage slideshow
 * @copyright  2010 onwards Mark Johnson  {@link http://barrenfrozenwasteland.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/slideshow/edit_form.php');

$id = optional_param('id',0,PARAM_INT);    // Course Module ID, or
$l = optional_param('l',0,PARAM_INT);     // Label ID
$slide = optional_param('slide', 0, PARAM_CLEAN);     // Slide number


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
    $PAGE->set_url('/mod/slideshow/edit.php', array('l'=>$l));
    if (! $slideshow = $DB->get_record("slideshow", array("id"=>$l))) {
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

$context = get_context_instance(CONTEXT_MODULE, $slideshow->id);
require_capability('mod/slideshow:edit', $context);

$dom = new DOMDocument();
$dom->loadHTMLFile($CFG->dataroot.'/s5/'.$slideshow->id.'.html');

$slide_html = '';
$xpath = new DOMXPath($dom);
$slides = $xpath->query("//div[contains(@class, 'slide')]");

if ($slide === 'new') {
    $slide_html = '';
} else {

    $current_slide = $slides->item($slide);
    $slide_dom = new DOMDocument('1.0', 'UTF-8');
    $slide_node = $slide_dom->importNode($current_slide, TRUE);
    $slide_dom->appendChild($slide_node);
    $slide_html = $slide_dom->saveHTML();
}

$form = new slide_edit_form();
if ($data = $form->get_data()) {

    $newslide_dom = new DOMDocument('1.0', 'UTF-8');
    if (isset($current_slide)) {
        $newslide_dom->loadHTML($data->slidehtml);
    } else {
        $newslide_dom->loadHTML(html_writer::tag('div', $data->slidehtml, array('class' => 'slide')));
    }
    $new_xpath = new DOMXPath($newslide_dom);
    $newslide_node = $new_xpath->query("//div[contains(@class, 'slide')]");
    $newslide_node = $newslide_node->item(0);

    $replacement_node = $dom->importNode($newslide_node, TRUE);
    if (isset($current_slide)) {
        $current_slide->parentNode->insertBefore($replacement_node, $current_slide);
        $current_slide->parentNode->removeChild($current_slide);
    } else {
        $slides->item(0)->parentNode->appendChild($replacement_node);
    }
    $dom->saveHTMLFile($CFG->dataroot.'/s5/'.$slideshow->id.'.html');
    if ($data->submits['submit'] == get_string('saveandview', 'slideshow')) {
        $redirect_url = new moodle_url('/mod/slideshow/view.php', array('id' => $cm->id));
    } else {
        if ($slide == 'new') {
            $slidenumber = $slides->length;
        } else {
            $slidenumber = $slide;
        }
        $redirect_url = new moodle_url('/mod/slideshow/edit.php', array('id' => $cm->id, 'slide' => $slidenumber));
    }
    redirect($redirect_url);
    die();
}

$data = new stdClass();
$data->id = $cm->id;
$data->slide = $slide;
$data->slidehtml = $slide_html;
$form->set_data($data);

$slidelist = '';
for ($i = 0; $i < $slides->length; $i++) {
    if ($i === $slide) {
        $link = get_string('slide', 'slideshow').' '.$i;
    } else {
        $url = new moodle_url('/mod/slideshow/edit.php', array('id' => $cm->id, 'slide' => $i));
        $link = html_writer::tag('a', get_string('slide', 'slideshow').' '.$i, array('href' => $url->out(false)));
    }
    $li = html_writer::tag('li', $link);
    $slidelist .= $li;
}

if ($slide === 'new') {
    $link = get_string('new').' '.get_string('slide', 'slideshow');
} else {
    $url = new moodle_url('/mod/slideshow/edit.php', array('id' => $cm->id, 'slide' => 'new'));
    $link = html_writer::tag('a', get_string('new').' '.get_string('slide', 'slideshow'), array('href' => $url->out(false)));
}
$li = html_writer::tag('li', $link);
$slidelist .= $li;

echo $OUTPUT->header();
echo html_writer::nonempty_tag('ul', $slidelist);
$form->display();
echo $OUTPUT->footer($course);
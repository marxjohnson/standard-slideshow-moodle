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
 * Display the slideshow
 *
 * @package    mod
 * @subpackage slideshow
 * @copyright  2010 onwards Mark Johnson  {@link http://barrenfrozenwasteland.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

$id = optional_param('id',0,PARAM_INT);    // Course Module ID, or
$l = optional_param('l',0,PARAM_INT);     // Label ID

if ($id) {
    $PAGE->set_url('/mod/slideshow/index.php', array('id'=>$id));
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
    $PAGE->set_url('/mod/slideshow/index.php', array('l'=>$l));
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
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_login($course, true, $cm);
require_capability('mod/slideshow:view', $context);

echo $OUTPUT->header();

$url = new moodle_url('/mod/slideshow/display.php', array('id' => $cm->id));
$html = html_writer::tag('iframe', '', array('src' => $url->out(), 'width' => $slideshow->width, 'height' => $slideshow->height));
echo $html;
if (has_capability('mod/slideshow:edit', $context)) {
    $url = new moodle_url('/mod/slideshow/edit.php', array('id' => $cm->id));
    echo $OUTPUT->single_button($url, get_string('editslides', 'slideshow'), 'get');
}

echo $OUTPUT->footer($course);
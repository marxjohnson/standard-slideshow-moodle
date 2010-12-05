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
 * Generates a single-frame slideshow page to preview the specified slide.
 *
 * @package    mod
 * @subpackage slideshow
 * @copyright  2010 onwards Mark Johnson  {@link http://barrenfrozenwasteland.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

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

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
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
$fullname = fullname($USER);
$date = time();

echo <<< EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>$slideshow->name</title>
<!-- metadata -->
<meta name="generator" content="S5" />
<meta name="version" content="S5 1.1" />
<meta name="presdate" content="$date" />
<meta name="author" content="$fullname" />
<!-- configuration parameters -->
<meta name="defaultView" content="slideshow" />
<meta name="controlVis" content="hidden" />
<!-- style sheet links -->
<link rel="stylesheet" href="$CFG->wwwroot/mod/slideshow/s5/ui/$slideshow->theme/slides.css" type="text/css" media="projection" id="slideProj" />
<link rel="stylesheet" href="$CFG->wwwroot/mod/slideshow/s5/ui/$slideshow->theme/outline.css" type="text/css" media="screen" id="outlineStyle" />
<link rel="stylesheet" href="$CFG->wwwroot/mod/slideshow/s5/ui/$slideshow->theme/print.css" type="text/css" media="print" id="slidePrint" />
<link rel="stylesheet" href="$CFG->wwwroot/mod/slideshow/s5/ui/$slideshow->theme/opera.css" type="text/css" media="projection" id="operaFix" />
<!-- S5 JS -->
<script src="$CFG->wwwroot/mod/slideshow/s5/ui/$slideshow->theme/slides.js" type="text/javascript"></script>
</head>
<body>

<div class="layout">
<div id="controls"><!-- DO NOT EDIT --></div>
<div id="currentSlide"><!-- DO NOT EDIT --></div>
<div id="header"></div>
<div id="footer">
<h1>[location/date of presentation]</h1>
<h2>$slideshow->name</h2>
</div>

</div>


<div class="presentation">

$slide_html
        
$slide_html

</div>

</body>
</html>
EOT
?>

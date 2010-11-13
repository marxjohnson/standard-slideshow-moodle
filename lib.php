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
 * Library of functions and constants for module slideshow
 *
 * @package    mod
 * @subpackage slideshow
 * @copyright  2010 onwards Mark Johnson  {@link http://barrenfrozenwasteland.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $slideshow
 * @return bool|int
 */
function slideshow_add_instance($slideshow) {
    global $DB, $CFG, $USER;

    $slideshow->id = $DB->insert_record('slideshow', $slideshow);
    $success = $slideshow->id;
    if (!file_exists($CFG->dataroot.'/s5')) {
        mkdir($CFG->dataroot.'/s5');
    }
    if (!file_exists($CFG->dataroot.'/s5/'.$slideshow->id.'.html')) {
        $template = file_get_contents($CFG->dirroot.'/mod/slideshow/s5/s5-template.html');
        $skeleton = str_replace('{name}', $slideshow->name, 
                    str_replace('{fullname}', fullname($USER),
                    str_replace('{theme}', $slideshow->theme,
                    str_replace('{date}', time(),
                    str_replace('{wwwroot}', $CFG->wwwroot,
                    $template)))));
        $success = $success && file_put_contents($CFG->dataroot.'/s5/'.$slideshow->id.'.html', $skeleton);
    }

    if ($success) {
        return $slideshow->id;
    } else {
        return false;
    }
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $slideshow
 * @return bool
 */
function slideshow_update_instance($slideshow) {
    global $DB;

    $slideshow->id = $slideshow->instance;

    return $DB->update_record("slideshow", $slideshow);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function slideshow_delete_instance($id) {
    global $DB;

    if (! $slideshow = $DB->get_record("slideshow", array("id"=>$id))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("slideshow", array("id"=>$slideshow->id))) {
        $result = false;
    }

    return $result;
}

/**
 * Returns the users with data in one resource
 * (NONE, but must exist on EVERY mod !!)
 *
 * @param int $slideshowid
 */
function slideshow_get_participants($slideshowid) {

    return false;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 * See get_array_of_activities() in course/lib.php
 *
 * @global object
 * @param object $coursemodule
 * @return object|null
 */
function slideshow_get_coursemodule_info($coursemodule) {
    global $DB;

    if ($slideshow = $DB->get_record('slideshow', array('id'=>$coursemodule->instance), 'id, name, intro, introformat')) {
        if (empty($slideshow->name)) {
            // slideshow name missing, fix it
            $slideshow->name = "slideshow{$slideshow->id}";
            $DB->set_field('slideshow', 'name', $slideshow->name, array('id'=>$slideshow->id));
        }
        $info = new stdClass();
        // no filtering hre because this info is cached and filtered later
        $info->extra = format_module_intro('slideshow', $slideshow, $coursemodule->id, false);
        $info->name  = $slideshow->name;
        return $info;
    } else {
        return null;
    }
}

/**
 * @return array
 */
function slideshow_get_view_actions() {
    return array();
}

/**
 * @return array
 */
function slideshow_get_post_actions() {
    return array();
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 *
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function slideshow_reset_userdata($data) {
    return array();
}

/**
 * Returns all other caps used in module
 *
 * @return array
 */
function slideshow_get_extra_capabilities() {
    return array();
}

/**
 * @uses FEATURE_IDNUMBER
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return bool|null True if module supports feature, false if not, null if doesn't know
 */
function slideshow_supports($feature) {
    switch($feature) {
        case FEATURE_IDNUMBER:                return false;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:          return true;

        default: return null;
    }
}

 

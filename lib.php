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
 * Library of interface functions and constants for module philosophers
 *
 * All the core Moodle functions, needed to integrate this plugin into Moodle at all,
 * should be placed here.
 *
 * All the philosophers specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_philosophers
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_completion\api;
use mod_philosophers\model\gamesession;
use mod_philosophers\model\level;
use mod_philosophers\util;

defined('MOODLE_INTERNAL') || die();

// tile heights
define('MOD_PHILOSOPHERS_LEVEL_TILE_HEIGHT_SMALL', 0);
define('MOD_PHILOSOPHERS_LEVEL_TILE_HEIGHT_MEDIUM', 1);
define('MOD_PHILOSOPHERS_LEVEL_TILE_HEIGHT_LARGE', 2);
define('MOD_PHILOSOPHERS_LEVEL_TILE_HEIGHTS', [
    MOD_PHILOSOPHERS_LEVEL_TILE_HEIGHT_SMALL,
    MOD_PHILOSOPHERS_LEVEL_TILE_HEIGHT_MEDIUM,
    MOD_PHILOSOPHERS_LEVEL_TILE_HEIGHT_LARGE,
]);

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function philosophers_supports($feature) {
    switch ($feature) {
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_COMPLETION_HAS_RULES:
        case FEATURE_MOD_INTRO:
        case FEATURE_USES_QUESTIONS:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the philosophers quiz into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $philosophers Submitted data from the form in mod_form.php
 * @param mod_philosophers_mod_form $mform The form instance itself (if needed)
 *
 * @return int The id of the newly inserted philosophers record
 * @throws dml_exception
 */
function philosophers_add_instance(stdClass $philosophers, mod_philosophers_mod_form $mform = null) {
    global $DB;
    // pre-processing
    $philosophers->timecreated = time();
    philosophers_preprocess_form_data($philosophers);
    // insert into db
    $philosophers->id = $DB->insert_record('philosophers', $philosophers);
    // some additional stuff
    philosophers_after_add_or_update($philosophers);
    return $philosophers->id;
}

/**
 * Updates an instance of the philosophers in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $philosophers An object from the form in mod_form.php
 * @param mod_philosophers_mod_form $mform The form instance itself (if needed)
 *
 * @return boolean Success/Fail
 * @throws dml_exception
 */
function philosophers_update_instance(stdClass $philosophers, mod_philosophers_mod_form $mform = null) {
    global $DB;
    // pre-processing
    $philosophers->id = $philosophers->instance;
    philosophers_preprocess_form_data($philosophers);
    // update in db
    $result = $DB->update_record('philosophers', $philosophers);
    // some additional stuff
    philosophers_after_add_or_update($philosophers);
    return $result;
}


/**
 * Pre-process the philosophers options form data, making any necessary adjustments.
 * Called by add/update instance in this file.
 *
 * @param stdClass $philosophers The variables set on the form.
 */
function philosophers_preprocess_form_data(stdClass $philosophers) {
    // update timestamp
    $philosophers->timemodified = time();
    // trim name.
    if (!empty($philosophers->name)) {
        $philosophers->name = trim($philosophers->name);
    }
}

/**
 * This function is called at the end of philosophers_add_instance
 * and philosophers_update_instance, to do the common processing.
 *
 * @param stdClass $philosophers the quiz object.
 */
function philosophers_after_add_or_update(stdClass $philosophers) {
    // nothing to do for now...
}

/**
 * Removes an instance of the philosophers from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 *
 * @return boolean Success/Failure
 * @throws dml_exception
 * @throws coding_exception
 */
function philosophers_delete_instance($id) {
    global $DB;
    if (!$philosophers = $DB->get_record('philosophers', ['id' => $id])) {
        return false;
    }
    if (!$cm = get_coursemodule_from_instance('philosophers', $philosophers->id)) {
        return false;
    }
    if (!$course = $DB->get_record('course', array('id'=>$cm->course))) {
        return false;
    }
    $context = context_module::instance($cm->id);

    // get rid of all files
    $fs = get_file_storage();
    $fs->delete_area_files($context->id);

    // delete completion data
    api::update_completion_date_event($cm->id, 'philosophers', $philosophers->id, null);

    // now delete actual game data
    $result = true;
    $gamesession_ids = $DB->get_fieldset_select('philosophers_gamesessions', 'id', 'game = :game', ['game' => $philosophers->id]);
    if ($gamesession_ids) {
        $result &= $DB->delete_records_list('philosophers_questions', 'gamesession', $gamesession_ids);
    }
    $result &= $DB->delete_records('philosophers_gamesessions', ['game' => $philosophers->id]);
    // levels and categories
    $levels_ids = $DB->get_fieldset_select('philosophers_levels', 'id', 'game = :game', ['game' => $philosophers->id]);
    if ($levels_ids) {
        $result &= $DB->delete_records_list('philosophers_categories', 'level', $levels_ids);
    }
    $result &= $DB->delete_records('philosophers_levels', ['game' => $philosophers->id]);
    $result &= $DB->delete_records('philosophers', ['id' => $philosophers->id]);
    return $result;
}

/**
 * Obtains the automatic completion state for this forum based on any conditions
 * in main activity settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 * @throws dml_exception
 * @throws moodle_exception
 */
function philosophers_get_completion_state($course, $cm, $userid, $type) {
    list($course, $coursemodule) = get_course_and_cm_from_cmid($cm->id, 'philosophers');
    if (!($philosophers = util::get_game($coursemodule))) {
        throw new Exception("Can't find activity instance {$cm->instance}");
    }
    $result = $type;
    if ($philosophers->get_completionrounds()) {
        $value = $philosophers->get_completionrounds() <= $philosophers->count_finished_gamesessions($userid);
        if ($type == COMPLETION_AND) {
            $result &= $value;
        } else {
            $result |= $value;
        }
    }
    if ($philosophers->get_completionpoints()) {
        $value = $philosophers->get_completionpoints() <= $philosophers->calculate_total_score($userid);
        if ($type == COMPLETION_AND) {
            $result &= $value;
        } else {
            $result |= $value;
        }
    }
    return $result;
}

function philosophers_perform_completion($course, $cm, $philosophers) {
    global $USER;
    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) == COMPLETION_TRACKING_AUTOMATIC && ($philosophers->completionrounds || $philosophers->completionpoints)) {
        $completion->update_state($cm, COMPLETION_COMPLETE, $USER->id);
    }
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

function philosophers_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== level::FILE_AREA) {
        return false;
    }

    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    require_login($course, true, $cm);

    // Check the relevant capabilities - these may vary depending on the filearea being accessed.
    if (!has_capability('mod/philosophers:view', $context)) {
        return false;
    }

    // Get the item id
    $itemid = array_shift($args); // The first item in the $args array.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/' . implode('/', $args) . '/'; // $args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_philosophers', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}

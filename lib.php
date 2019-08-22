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

use mod_philosophers\form\form_controller;
use mod_philosophers\model\gamesession;
use mod_philosophers\util;

defined('MOODLE_INTERNAL') || die();

// different highscore modes
define('MOD_PHILOSOPHERS_HIGHSCORE_MODE_BEST', 'best');
define('MOD_PHILOSOPHERS_HIGHSCORE_MODE_LAST', 'last');
define('MOD_PHILOSOPHERS_HIGHSCORE_MODE_AVERAGE', 'average');
define('MOD_PHILOSOPHERS_HIGHSCORE_MODES', [
    MOD_PHILOSOPHERS_HIGHSCORE_MODE_BEST,
    MOD_PHILOSOPHERS_HIGHSCORE_MODE_LAST,
    MOD_PHILOSOPHERS_HIGHSCORE_MODE_AVERAGE
]);

// implemented question types
define('MOD_PHILOSOPHERS_QTYPE_SINGLE_CHOICE_DB', 'multichoice');
define('MOD_PHILOSOPHERS_VALID_QTYPES_DB', [
    MOD_PHILOSOPHERS_QTYPE_SINGLE_CHOICE_DB,
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
 */
function philosophers_delete_instance($id) {
    global $DB;
    $result = true;
    $philosophers = $DB->get_record('philosophers', ['id' => $id], '*', MUST_EXIST);
    // game sessions, including chosen questions
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
 * in forum settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 * @throws dml_exception
 */
function philosophers_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    if (!($philosophers = $DB->get_record('philosophers', ['id' => $cm->instance]))) {
        throw new Exception("Can't find philosophers {$cm->instance}");
    }
    $result = $type;
    if ($philosophers->completionrounds) {
        $sqlParams = ['game' => $philosophers->id, 'mdl_user' => $userid, 'state' => gamesession::STATE_FINISHED];
        $value = $philosophers->completionrounds <= $DB->count_records('philosophers_gamesessions', $sqlParams);
        if ($type == COMPLETION_AND) {
            $result &= $value;
        } else {
            $result |= $value;
        }
    }
    if ($philosophers->completionpoints) {
        $value = $philosophers->completionpoints <= highscore_utils::calculate_score($philosophers, $userid);
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

/**
 * View or submit an mform.
 *
 * Returns the HTML to view an mform.
 * If form data is delivered and the data is valid, this returns 'ok'.
 *
 * @param array $args
 * @return string
 * @throws moodle_exception
 */
function philosophers_output_fragment_mform($args) {
    $context = $args['context'];
    if ($context->contextlevel != CONTEXT_MODULE) {
        throw new \moodle_exception('fragment_mform_wrong_context', 'philosophers');
    }

    list($course, $coursemodule) = \get_course_and_cm_from_cmid($context->instanceid, 'philosophers');
    $game = util::get_game($coursemodule);

    $formdata = [];
    if (!empty($args['jsonformdata'])) {
        $serializeddata = \json_decode($args['jsonformdata']);
        if (\is_string($serializeddata)) {
            \parse_str($serializeddata, $formdata);
        }
    }

    $moreargs = (isset($args['moreargs'])) ? \json_decode($args['moreargs']) : new stdClass();
    $formname = $args['form'] ?? '';

    $controller = form_controller::get_controller($formname, $game, $context, $formdata, $moreargs);

    if ($controller->success()) {
        $ret = 'ok';
        if ($msg = $controller->get_message()) {
            $ret .= ' ' . $msg;
        }
        return $ret;
    } else {
        return $controller->render();
    }
}

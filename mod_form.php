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
 * The main philosophers configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_philosophers
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/lib/questionlib.php');

/**
 * Module instance settings form
 */
class mod_philosophers_mod_form extends moodleform_mod {

    private $completionModes = ['completionrounds', 'completionpoints'];

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('philosophersname', 'philosophers'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'philosophersname', 'philosophers');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements(get_string('introduction', 'philosophers'));

        // Game options
        $mform->addElement('header', 'game_options_fieldset', get_string('game_options_fieldset', 'philosophers'));
        // ... question duration/score
        $mform->addElement('text', 'question_duration', get_string('question_duration', 'philosophers'), ['size' => 5]);
        $mform->setType('question_duration', PARAM_INT);
        $mform->setDefault('question_duration', 30);
        $mform->addHelpButton('question_duration', 'question_duration', 'philosophers');
        // ... question reading speed
        $question_reading_speed_options = [];
        for ($i = -1; $i <= 1; $i++) {
            $question_reading_speed_options["$i"] = get_string('question_reading_speed_' . $i, 'philosophers');
        }
        $mform->addElement('select', 'question_reading_speed', get_string('question_reading_speed', 'philosophers'), $question_reading_speed_options);
        $mform->setDefault('question_reading_speed', "0");
        $mform->addHelpButton('question_reading_speed', 'question_reading_speed', 'philosophers');
        // ... review duration
        $mform->addElement('text', 'review_duration', get_string('review_duration', 'philosophers'), ['size' => 5]);
        $mform->setType('review_duration', PARAM_INT);
        $mform->setDefault('review_duration', 2);
        $mform->addHelpButton('review_duration', 'review_duration', 'philosophers');
        // ... shuffle levels in overview?
        $mform->addElement('advcheckbox', 'shuffle_levels', get_string('shuffle_levels', 'philosophers'), '&nbsp;');
        $mform->setDefault('shuffle_levels', 0);
        $mform->addHelpButton('shuffle_levels', 'shuffle_levels', 'philosophers');
        // ... shuffle answers?
        $mform->addElement('advcheckbox', 'question_shuffle_answers', get_string('question_shuffle_answers', 'philosophers'), '&nbsp;');
        $mform->setDefault('question_shuffle_answers', 1);
        $mform->addHelpButton('question_shuffle_answers', 'question_shuffle_answers', 'philosophers');
        // ... highscore count
        $mform->addElement('text', 'highscore_count', get_string('highscore_count', 'philosophers'), ['size' => 5]);
        $mform->setType('highscore_count', PARAM_INT);
        $mform->setDefault('highscore_count', 5);
        $mform->addHelpButton('highscore_count', 'highscore_count', 'philosophers');
        // ... whether teachers are shown in highscore
        $mform->addElement('advcheckbox', 'highscore_teachers', get_string('highscore_teachers', 'philosophers'), '&nbsp;');
        $mform->setDefault('highscore_teachers', 0);
        $mform->addHelpButton('highscore_teachers', 'highscore_teachers', 'philosophers');

        // ... tile height for level cards
        $level_tile_heights = [];
        foreach (MOD_PHILOSOPHERS_LEVEL_TILE_HEIGHTS as $height) {
            $level_tile_heights[$height] = get_string('level_tile_height_' . $height, 'philosophers');
        }
        $mform->addElement('select', 'level_tile_height', get_string('level_tile_height', 'philosophers'), $level_tile_heights);
        $mform->setDefault('level_tile_height', MOD_PHILOSOPHERS_LEVEL_TILE_HEIGHT_LARGE);
        $mform->addHelpButton('level_tile_height', 'level_tile_height', 'philosophers');
        // ... tile overlay alpha
        $level_tile_alphas = [];
        for($i=0; $i<=10; $i++) {
            $level_tile_alphas[$i * 10] = ($i * 10) . "%";
        }
        $mform->addElement('select', 'level_tile_alpha', get_string('level_tile_alpha', 'philosophers'), $level_tile_alphas);
        $mform->setDefault('level_tile_alpha', 50);
        $mform->addHelpButton('level_tile_alpha', 'level_tile_alpha', 'philosophers');

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values) {
        parent::data_preprocessing($default_values);
        foreach ($this->completionModes as $mode) {
            $default_values[$mode . 'enabled'] = !empty($default_values[$mode]) ? 1 : 0;
            if (empty($default_values[$mode])) {
                $default_values[$mode] = 1;
            }
        }
    }

    function add_completion_rules() {
        $mform = $this->_form;
        $result = [];
        foreach ($this->completionModes as $mode) {
            $group = array();
            $group[] = $mform->createElement('checkbox', $mode . 'enabled', '', get_string($mode, 'philosophers'));
            $group[] = $mform->createElement('text', $mode, '', array('size' => 3));
            $mform->setType($mode, PARAM_INT);
            $mform->addGroup($group, $mode . 'group', get_string($mode . 'label', 'philosophers'), array(' '), false);
            $mform->disabledIf($mode, $mode . 'enabled', 'notchecked');
            $result[] = $mode . 'group';
        }
        return $result;
    }

    function completion_rule_enabled($data) {
        foreach ($this->completionModes as $mode) {
            if (!empty($data[$mode . 'enabled']) && $data[$mode] !== 0) {
                return true;
            }
        }
        return false;
    }

    function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        // Turn off completion settings if the checkboxes aren't ticked
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionroundsenabled) || !$autocompletion) {
                $data->completionrounds = 0;
            }
            if (empty($data->completionpointsenabled) || !$autocompletion) {
                $data->completionpoints = 0;
            }

        }
        return $data;
    }


}

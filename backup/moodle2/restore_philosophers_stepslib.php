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
 * Restore structure step for philosophers content
 *
 * @package    mod_philosophers
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_philosophers\model\level;

defined('MOODLE_INTERNAL') || die();

/**
 * Structure step to restore one philosophers activity
 */
class restore_philosophers_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines restore element's structure
     *
     * @return array
     * @throws base_step_exception
     */
    protected function define_structure() {
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('philosopher', '/activity/philosopher');
        $paths[] = new restore_path_element('philosopher_level', '/activity/philosopher/levels/level');
        $paths[] = new restore_path_element('philosopher_category', '/activity/philosopher/levels/level/categories/category');
        if ($userinfo) {
            $paths[] = new restore_path_element('philosopher_gamesession', '/activity/philosopher/gamesessions/gamesession');
            $paths[] = new restore_path_element('philosopher_question', '/activity/philosopher/gamesessions/gamesession/questions/question');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process philosopher, inserting the record into the database.
     *
     * @param $data
     *
     * @throws base_step_exception
     * @throws dml_exception
     */
    protected function process_philosopher($data) {
        global $DB;
        $data = (object) $data;
        $oldid = $data->id;

        $data->course = $this->get_courseid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the philosopher record.
        $newitemid = $DB->insert_record('philosophers', $data);
        $this->set_mapping('philosopher', $oldid, $newitemid, true);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * @param $data
     * @throws dml_exception
     * @throws restore_step_exception
     */
    protected function process_philosopher_level($data) {
        global $DB;
        $data = (object) $data;
        $oldid = $data->id;

        $data->game = $this->get_new_parentid('philosopher');

        $newitemid = $DB->insert_record('philosophers_levels', $data);
        $this->set_mapping('philosopher_level', $oldid, $newitemid, true);
    }

    /**
     * @param $data
     * @throws dml_exception
     * @throws restore_step_exception
     */
    protected function process_philosopher_category($data) {
        global $DB;
        $data = (object) $data;
        $oldid = $data->id;

        $data->level = $this->get_new_parentid('philosopher_level');

        $newitemid = $DB->insert_record('philosophers_categories', $data);
        $this->set_mapping('philosopher_category', $oldid, $newitemid);
    }

    /**
     * @param $data
     * @throws dml_exception
     * @throws restore_step_exception
     */
    protected function process_philosopher_gamesession($data) {
        global $DB;
        $data = (object) $data;
        $oldid = $data->id;

        $data->game = $this->get_new_parentid('philosopher');
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->mdl_user = $this->get_mappingid('user', $data->mdl_user);

        $newitemid = $DB->insert_record('philosophers_gamesessions', $data);
        $this->set_mapping('philosopher_gamesession', $oldid, $newitemid);
    }

    /**
     * @param $data
     * @throws dml_exception
     * @throws restore_step_exception
     */
    protected function process_philosopher_question($data) {
        global $DB;
        $data = (object) $data;
        $oldid = $data->id;

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->level = $this->get_mappingid('philosopher_level', $data->level);

        $newitemid = $DB->insert_record('philosophers_questions', $data);
        $this->set_mapping('philosopher_question', $oldid, $newitemid);
    }

    /**
     * Additional work that needs to be done after steps executions.
     */
    protected function after_execute() {
        // Add files for intro field.
        $this->add_related_files('mod_philosophers', 'intro', null);

        // Add level related files, matching by itemname = 'philosophers_level'.
        $this->add_related_files('mod_philosophers', level::FILE_AREA, 'philosophers_level');
    }
}

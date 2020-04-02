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
 * Defines backup structure steps for philosophers content.
 *
 * @package    mod_philosophers
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_philosophers\model\level;

defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete philosophers structure for backup, with file and id annotations
 */
class backup_philosophers_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines backup element's structure
     *
     * @return backup_nested_element
     * @throws base_element_struct_exception
     * @throws base_step_exception
     */
    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define main activity element
        $activity = new backup_nested_element('philosopher', ['id'], [
            'timecreated', 'timemodified', 'course', 'name', 'intro', 'introformat', 'grade',
            'question_duration', 'review_duration', 'question_shuffle_answers', 'question_reading_speed',
            'question_chances', 'highscore_count', 'highscore_teachers', 'completionrounds', 'completionpoints',
            'shuffle_levels', 'level_tile_height', 'level_tile_alpha'
        ]);
        $activity->set_source_table('philosophers', ['id' => backup::VAR_ACTIVITYID]);

        // define game structure: levels and categories
        $levels = new backup_nested_element('levels');
        $activity->add_child($levels);
        $level = new backup_nested_element('level', ['id'], [
            'game', 'state', 'name', 'position', 'image', 'bgcolor'
        ]);
        $levels->add_child($level);
        $categories = new backup_nested_element('categories');
        $level->add_child($categories);
        $category = new backup_nested_element('category', ['id'], [
            'level', 'mdl_category', 'subcategories'
        ]);
        $categories->add_child($category);

        // define sources for structural game data
        $level->set_source_table('philosophers_levels', ['game' => backup::VAR_ACTIVITYID], 'id ASC');
        $category->set_source_table('philosophers_categories', ['level' => backup::VAR_PARENTID], 'id ASC');

        // define user data structure: gamesessions and questions
        $gamesessions = new backup_nested_element('gamesessions');
        $activity->add_child($gamesessions);
        $gamesession = new backup_nested_element('gamesession', ['id'], [
            'timecreated', 'timemodified', 'game', 'mdl_user', 'score', 'answers_total', 'answers_correct',
            'state', 'levels_order'
        ]);
        $gamesessions->add_child($gamesession);
        $questions = new backup_nested_element('questions');
        $gamesession->add_child($questions);
        $question = new backup_nested_element('question', ['id'], [
            'timecreated', 'timemodified', 'gamesession', 'level', 'mdl_question', 'mdl_answers_order', 'mdl_answer_given',
            'score', 'correct', 'finished', 'timeremaining'
        ]);
        $questions->add_child($question);

        // define sources for user data
        if ($userinfo) {
            $gamesession->set_source_table('philosophers_gamesessions', ['game' => backup::VAR_ACTIVITYID], 'id ASC');
            $question->set_source_table('philosophers_questions', ['gamesession' => backup::VAR_PARENTID], 'id ASC');
        }

        // Define id annotations
        $gamesession->annotate_ids('user', 'mdl_user');

        // Define file annotations
        $activity->annotate_files('mod_philosophers', 'intro', null, null);
        $activity->annotate_files('mod_philosophers', level::FILE_AREA, null, null);

        // Return the root element (philosopher), wrapped into standard activity structure.
        return $this->prepare_activity_structure($activity);
    }
}

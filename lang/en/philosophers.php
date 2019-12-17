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
 * English strings for philosophers
 *
 * @package    mod_philosophers
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* system */
$string['modulename'] = 'Philosophers\' Quiz';
$string['modulenameplural'] = 'Philosophers\' Quizzes';
$string['modulename_help'] = 'This is a quiz game with customizable question categories. Faster answers on questions result in higher scores. Course participants get ranked in a leader board by their high scores.';
$string['pluginadministration'] = '»Philosophers\' Quiz« Administration';
$string['pluginname'] = 'Philosophers\' Quiz';
$string['philosophers'] = 'Philosophers\' Quiz';
$string['philosophers:addinstance'] = 'Add a new »Philosophers\' Quiz«';
$string['philosophers:submit'] = 'Submit »Philosophers\' Quiz«';
$string['philosophers:manage'] = 'Manage »Philosophers\' Quiz«';
$string['philosophers:view'] = 'View »Philosophers\' Quiz«';
$string['philosophersname'] = 'Name';
$string['philosophersname_help'] = 'Please provide a name for this »Philosophers\' Quiz«.';
$string['introduction'] = 'Description';
$string['route_not_found'] = 'The page you tried to open doesn\'t exist.';

/* main admin form: game options */
$string['game_options_fieldset'] = 'Game options';
$string['question_duration'] = 'Question duration (seconds)';
$string['question_duration_help'] = 'Available time (in seconds) for each question. This is also the maximum reachable score per question.';
$string['question_reading_speed'] = 'Reading speed';
$string['question_reading_speed_help'] = 'Please select a reading speed which fits your participants\' capabilities. The time which gets added to the question countdown gets modified accordingly.';
$string['question_reading_speed_-1'] = 'Slow (x0,5)';
$string['question_reading_speed_0'] = 'Normal';
$string['question_reading_speed_1'] = 'Fast (x1,5)';
$string['review_duration'] = 'Time to display solution (seconds)';
$string['review_duration_help'] = 'The duration how long the solution of a question is being displayed (in seconds) before continuing to the level overview automatically.';
$string['shuffle_levels'] = 'Shuffle Levels';
$string['shuffle_levels_help'] = 'If enabled, the levels in the overview will be shuffled instead of displaying them in their configured order.';
$string['question_shuffle_answers'] = 'Shuffle answers';
$string['question_shuffle_answers_help'] = 'If enabled, the answers of questions will be shuffled.';
$string['highscore_count'] = 'Leader board entries';
$string['highscore_count_help'] = 'Please decide how many rows the leader board will show (default: 5).';
$string['completionrounds'] = 'Student has to play a number of rounds';
$string['completionroundslabel'] = 'Played rounds';
$string['completionpoints'] = 'Student has to achieve a certain highscore';
$string['completionpointslabel'] = 'Achieved highscore';
$string['highscore_teachers'] = 'Teachers in leader board';
$string['highscore_teachers_help'] = 'If enabled teachers\' scores will appear in the leader board.';
$string['level_tile_height'] = 'Level tile height';
$string['level_tile_height_help'] = 'Please select the height for level tile representation.';
$string['level_tile_height_0'] = 'Flat';
$string['level_tile_height_1'] = 'Regular';
$string['level_tile_height_2'] = 'Tall';
$string['level_tile_alpha'] = 'Level overlay alpha';
$string['level_tile_alpha_help'] = 'For improved readability there will be an overlay on the level tiles. Please select the how dark (0%) or light (100%) this overlay should be.';

/* activity edit page: control */
$string['control_edit'] = 'Control';
$string['control_edit_title'] = 'Control Options';
$string['reset_progress_heading'] = 'Reset Progress';
$string['reset_progress_button'] = 'Reset Progress';
$string['reset_progress_confirm_title'] = 'Confirm Reset Progress';
$string['reset_progress_confirm_question'] = 'Are you sure you want to reset the progress? this will delete all the results and is irreversible';

/* course reset */
$string['course_reset_include_progress'] = 'Reset progress (Highscores etc.)';
$string['course_reset_include_topics'] = 'Reset topics etc. (Everything will be deleted!)';

/* admin screen in vue app */
$string['admin_screen_title'] = 'Edit game content';
$string['admin_not_allowed'] = 'You have insufficient permissions to view this page.';
$string['admin_levels_title'] = 'Edit levels';
$string['admin_levels_none'] = 'You didn\'t add any levels, yet.';
$string['admin_levels_intro'] = 'You have already created the following levels for this game. You may edit their data and order, or even delete them. Please note, that editing levels which already has had participants might render existing highscores worthless.';
$string['admin_btn_save'] = 'Save';
$string['admin_btn_cancel'] = 'Cancel';
$string['admin_btn_add'] = 'Add';
$string['admin_btn_confirm_delete'] = 'Confirm Delete';
$string['admin_level_delete_confirm'] = 'Do you really want to delete the level »{$a}«?';
$string['admin_level_title_add'] = 'Create level {$a}';
$string['admin_level_title_edit'] = 'Edit level {$a}';
$string['admin_level_loading'] = 'Loading level data';
$string['admin_level_lbl_name'] = 'Name';
$string['admin_level_lbl_bgcolor'] = 'Background Color';
$string['admin_level_lbl_bgcolor_help'] = 'HEX format, with or without #, as 3 or 6 chars. Example: #cc0033 or #c03';
$string['admin_level_lbl_image'] = 'Background Image';
$string['admin_level_lbl_image_drag'] = 'Upload via drag&drop or select';
$string['admin_level_lbl_image_change'] = 'Change';
$string['admin_level_lbl_image_remove'] = 'Remove';
$string['admin_level_lbl_categories'] = 'Question assignments';
$string['admin_level_lbl_category'] = 'Category {$a}';
$string['admin_level_lbl_category_please_select'] = 'Select category';
$string['admin_level_msg_saving'] = 'Saving the level, please wait';

/* game gui */
$string['game_screen_title'] = 'Play »Philosophers\' Quiz«';
$string['game_qtype_not_supported'] = 'The question type »{$a}« is not supported.';
$string['game_loading_question'] = 'Loading question details';
$string['game_loading_highscore'] = 'Loading leader board';
$string['game_loading_highscore_failed'] = 'An error occurred while loading the leader board.';
$string['game_btn_restart'] = 'New Game';
$string['game_btn_continue'] = 'Continue';
$string['game_btn_highscore'] = 'Leader Board';
$string['game_btn_quit'] = 'Quit';
$string['game_btn_start'] = 'Start Game';
$string['game_btn_game'] = 'Show Game';
$string['game_progress_current_score'] = 'Score:';
$string['game_progress_point'] = '1 Point';
$string['game_progress_points'] = '{$a} Points';
$string['game_progress_answered_level'] = '1 out of {$a} questions answered';
$string['game_progress_answered_levels'] = '{$a->completed} out of {$a->total} questions answered';
$string['game_progress_answered_levels_all'] = 'All {$a} questions answered';
$string['game_intro_message'] = 'We need to place a logo, text, etc, here.';
$string['game_help_headline'] = 'Game introduction';
$string['game_help_message'] = '<ul><li>You can start a new game at any time by clicking »New Game«.</li><li>You can switch between the Leader Board and the actual Game at any time.</li><li>You can end the game with your current score by clicking »Quit« (button below the question).</li></ul>';
$string['game_stats_empty'] = 'No one is on this leader board, yet.';
$string['game_stats_day'] = 'Day';
$string['game_stats_week'] = 'Week';
$string['game_stats_month'] = 'Month';
$string['game_stats_all'] = 'Overall';
$string['game_stats_rank'] = 'Rank';
$string['game_stats_user'] = 'User';
$string['game_stats_score'] = 'Score';
$string['game_stats_maxscore'] = 'Best Round';
$string['game_stats_sessions'] = 'Attempts';

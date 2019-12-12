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

namespace mod_philosophers\external;

use coding_exception;
use dml_exception;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_value;
use invalid_parameter_exception;
use mod_philosophers\external\exporter\score_dto;
use mod_philosophers\model\gamesession;
use mod_philosophers\util;
use moodle_exception;
use restricted_context_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Class scores
 *
 * @package    mod_philosophers\external
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class scores extends external_api {

    /**
     * Definition of parameters for {@see get_scores_global}.
     *
     * @return external_function_parameters
     */
    public static function get_scores_global_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'span' => new external_value(PARAM_TEXT, 'one out of day, week, month or total', false),
        ]);
    }

    /**
     * Definition of return type for {@see get_scores_global}.
     *
     * @return external_multiple_structure
     */
    public static function get_scores_global_returns() {
        return new external_multiple_structure(
            score_dto::get_read_structure()
        );
    }

    /**
     * Get scores among all users.
     *
     * @param int $coursemoduleid
     * @param string $span
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function get_scores_global($coursemoduleid, $span) {
        $params = ['coursemoduleid' => $coursemoduleid];
        self::validate_parameters(self::get_scores_global_parameters(), $params);

        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'philosophers');
        self::validate_context($coursemodule->context);

        global $PAGE, $DB;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);

        list($andwhere, $timestamp) = self::get_scores_global_where_params($span);
        $sql = "SELECT gs.mdl_user, SUM(gs.score) AS score, MAX(gs.score) AS maxscore, COUNT(gs.score) AS sessions, CONCAT(u.firstname, ' ', u.lastname) AS mdl_user_name
                  FROM {philosophers_gamesessions} AS gs
                  JOIN {user} AS u ON mdl_user=u.id
                 WHERE game = :game AND state = :state $andwhere
              GROUP BY gs.mdl_user
              ORDER BY score DESC";
        $params = ['game' => $game->get_id(), 'state' => gamesession::STATE_FINISHED, 'timemodified' => $timestamp];
        $records = $DB->get_records_sql($sql, $params);
        $result = [];
        if ($records) {
            $rank = 1;
            foreach ($records as $record) {
                $teacher = has_capability('mod/philosophers:manage', $ctx, $record->mdl_user);
                if (!$teacher || $game->is_highscore_teachers()) {
                    $dto = new score_dto($rank++, $record->score, $record->maxscore, $record->sessions, $record->mdl_user, $record->mdl_user_name, $teacher, $ctx);
                    $result[] = $dto->export($renderer);
                }
            }
        }
        return $result;
    }

    /**
     * @param string $span One out of 'day', 'week' and 'month'. Otherwise non-restrictive params will be returned.
     *
     * @return array[string, int]
     */
    private static function get_scores_global_where_params($span) {
        $andwhere = "AND gs.timemodified >= :timemodified";
        switch ($span) {
            case 'day':
                $time = \time() - (24 * 60 * 60);
                return [$andwhere, $time];
            case 'week':
                $time = \time() - (7 * 24 * 60 * 60);
                return [$andwhere, $time];
            case 'month':
                $time = \time() - (30 * 24 * 60 * 60);
                return [$andwhere, $time];
            default:
                return [$andwhere, 0];
        }
    }
}

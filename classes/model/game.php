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

namespace mod_philosophers\model;

use function assert;
use function usort;

defined('MOODLE_INTERNAL') || die();

/**
 * Class game
 *
 * @package    mod_philosophers\model
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class game extends abstract_model {

    /**
     * @var int Timestamp of creation of this game.
     */
    protected $timecreated;
    /**
     * @var int Timestamp of last update of this game.
     */
    protected $timemodified;
    /**
     * @var int Id of course.
     */
    protected $course;
    /**
     * @var string Name of this game activity.
     */
    protected $name;
    /**
     * @var int The number of seconds a question is shown for answering and at the same time the max score when answering correct.
     */
    protected $question_duration;
    /**
     * @var int The number of seconds the solution is displayed before the user is returned to the level selection.
     */
    protected $review_duration;
    /**
     * @var bool Whether or not answers should be shuffled when displaying a question.
     */
    protected $question_shuffle_answers;
    /**
     * @var int The reading speed of the user.
     */
    protected $question_reading_speed;
    /**
     * @var int The number of (previously wrong answered questions) a user is allowed to re-answer at a certain point.
     */
    protected $question_chances;
    /**
     * @var int Number of highscore entries shown in highscore list.
     */
    protected $highscore_count;
    /**
     * @var bool Whether or not teachers will be shown in the highscore list.
     */
    protected $highscore_teachers;
    /**
     * @var int Number of rounds a user has to finish for successful completion.
     */
    protected $completionrounds;
    /**
     * @var int Total score a user has to reach for successful completion.
     */
    protected $completionpoints;
    /**
     * @var bool Whether or not the levels should be shuffled in the level overview.
     */
    protected $shuffle_levels;
    /**
     * @var int One of the tile height categories (see lib.php).
     */
    protected $level_tile_height;
    /**
     * @var int The alpha value of the level tile overlay (out of [0,100]).
     */
    protected $level_tile_alpha;

    /**
     * game constructor.
     */
    function __construct() {
        parent::__construct('philosophers', 0);
        $this->timecreated = \time();
        $this->timemodified = \time();
        $this->course = 0;
        $this->name = '';
        $this->question_duration = 30;
        $this->review_duration = 2;
        $this->question_shuffle_answers = true;
        $this->question_reading_speed = 0;
        $this->question_chances = 0;
        $this->highscore_count = 5;
        $this->highscore_teachers = false;
        $this->completionrounds = 0;
        $this->completionpoints = 0;
        $this->shuffle_levels = false;
        $this->level_tile_height = MOD_PHILOSOPHERS_LEVEL_TILE_HEIGHT_MEDIUM;
        $this->level_tile_alpha = 50;
    }

    /**
     * Apply data to this object from an associative array or an object.
     *
     * @param mixed $data
     *
     * @return void
     */
    public function apply($data) {
        if (\is_object($data)) {
            $data = get_object_vars($data);
        }
        $this->id = isset($data['id']) ? $data['id'] : 0;
        $this->timecreated = isset($data['timecreated']) ? $data['timecreated'] : \time();
        $this->timemodified = isset($data['timemodified']) ? $data['timemodified'] : \time();
        $this->course = isset($data['course']) ? $data['course'] : 0;
        $this->name = isset($data['name']) ? $data['name'] : '';
        $this->question_duration = isset($data['question_duration']) ? $data['question_duration'] : 30;
        $this->review_duration = isset($data['review_duration']) ? $data['review_duration'] : 2;
        $this->question_shuffle_answers = isset($data['question_shuffle_answers']) ? ($data['question_shuffle_answers'] == 1) : true;
        $this->question_reading_speed = isset($data['question_reading_speed']) ? $data['question_reading_speed'] : 0;
        $this->question_chances = isset($data['question_chances']) ? $data['question_chances'] : 0;
        $this->highscore_count = isset($data['highscore_count']) ? $data['highscore_count'] : 5;
        $this->highscore_teachers = isset($data['highscore_teachers']) ? ($data['highscore_teachers'] == 1) : false;
        $this->completionrounds = isset($data['completionrounds']) ? $data['completionrounds'] : 0;
        $this->completionpoints = isset($data['completionpoints']) ? $data['completionpoints'] : 0;
        $this->shuffle_levels = isset($data['shuffle_levels']) ? ($data['shuffle_levels'] == 1) : false;
        $this->level_tile_height = isset($data['level_tile_height']) ? $data['level_tile_height'] : MOD_PHILOSOPHERS_LEVEL_TILE_HEIGHT_MEDIUM;
        $this->level_tile_alpha = isset($data['level_tile_alpha']) ? $data['level_tile_alpha'] : 50;
    }

    /**
     * Calculates the total score of the given user.
     *
     * @param int $userid
     * @return int
     * @throws \dml_exception
     */
    public function calculate_total_score($userid) {
        global $DB;
        $sql = "SELECT SUM(score) AS score
                  FROM {philosophers_gamesessions}
                 WHERE game = :game AND state = :state AND mdl_user = :user";
        $params = ['game' => $this->get_id(), 'state' => gamesession::STATE_FINISHED, 'user' => $userid];
        $record = $DB->get_record_sql($sql, $params);
        if ($record !== false) {
            return $record->score;
        } else {
            return 0;
        }
    }

    /**
     * Counts the number of finished gamesessions of the given user.
     *
     * @param int $userid
     * @return int
     * @throws \dml_exception
     */
    public function count_finished_gamesessions($userid) {
        global $DB;
        $sqlParams = ['game' => $this->get_id(), 'mdl_user' => $userid, 'state' => gamesession::STATE_FINISHED];
        return $DB->count_records('philosophers_gamesessions', $sqlParams);
    }

    /**
     * Counts the active levels of this game.
     *
     * @return int
     * @throws \dml_exception
     */
    public function count_active_levels(): int {
        global $DB;
        $sql = "
            SELECT COUNT(id)
              FROM {philosophers_levels}
             WHERE game = :game AND state = :state
        ";
        $count = $DB->get_field_sql($sql, ['game' => $this->get_id(), 'state' => level::STATE_ACTIVE]);
        return $count === false ? 0 : $count;
    }

    /**
     * Gets an active level which belongs to this game and has the given $position value. Will return null
     * if no such level exists.
     *
     * @param int $position
     *
     * @return level|null
     * @throws \dml_exception
     */
    public function get_active_level_by_position($position) {
        global $DB;
        $sql = "
            SELECT *
              FROM {philosophers_levels}
             WHERE game = :game AND state = :state AND position = :position
        ";
        $record = $DB->get_record_sql($sql, ['game' => $this->get_id(), 'state' => level::STATE_ACTIVE, 'position' => $position]);
        if ($record === false) {
            return null;
        } else {
            $level = new level();
            $level->apply($record);
            return $level;
        }
    }

    /**
     * Gets all active levels for this game from the DB.
     *
     * @return level[]
     * @throws \dml_exception
     */
    public function get_active_levels() {
        global $DB;
        $sql_params = ['game' => $this->get_id(), 'state' => level::STATE_ACTIVE];
        $records = $DB->get_records('philosophers_levels', $sql_params, 'position ASC');
        $result = [];
        foreach ($records as $level_data) {
            $level = new level();
            $level->apply($level_data);
            $result[] = $level;
        }
        return $result;
    }

    /**
     * Goes through all active levels, fixing their individual position.
     *
     * @return void
     * @throws \dml_exception
     */
    public function fix_level_positions() {
        $levels = $this->get_active_levels();
        // sort levels ascending
        usort($levels, function (level $level1, level $level2) {
            $pos1 = $level1->get_position();
            $pos2 = $level2->get_position();
            if ($pos1 === $pos2) {
                return 0;
            }
            return ($pos1 < $pos2) ? -1 : 1;
        });
        // walk through sorted list and set new positions
        $pos = 0;
        foreach ($levels as $level) {
            assert($level instanceof level);
            $level->set_position($pos++);
            $level->save();
        }
    }

    /**
     * Based on the configured reading speed category, this returns the number
     * of words, a user is expected to be able to read in one minute.
     *
     * @return int
     */
    public function get_expected_words_per_minute(): int {
        switch ($this->question_reading_speed) {
            case -1:
                return 100;
            case 1:
                return 350;
            default:
                return 220;
        }
    }

    /**
     * @return int
     */
    public function get_timecreated(): int {
        return $this->timecreated;
    }

    /**
     * @return int
     */
    public function get_timemodified(): int {
        return $this->timemodified;
    }

    /**
     * @param int $timemodified
     */
    public function set_timemodified(int $timemodified) {
        $this->timemodified = $timemodified;
    }

    /**
     * @return int
     */
    public function get_course(): int {
        return $this->course;
    }

    /**
     * @param int $course
     */
    public function set_course(int $course) {
        $this->course = $course;
    }

    /**
     * @return string
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function set_name(string $name) {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function get_question_duration(): int {
        return $this->question_duration;
    }

    /**
     * @return int
     */
    public function get_review_duration(): int {
        return $this->review_duration;
    }

    /**
     * @return bool
     */
    public function is_question_shuffle_answers(): bool {
        return $this->question_shuffle_answers;
    }

    /**
     * @return int
     */
    public function get_highscore_count(): int {
        return $this->highscore_count;
    }

    /**
     * @return bool
     */
    public function is_highscore_teachers(): bool {
        return $this->highscore_teachers;
    }

    /**
     * @return int
     */
    public function get_completionrounds(): int {
        return $this->completionrounds;
    }

    /**
     * @return int
     */
    public function get_completionpoints(): int {
        return $this->completionpoints;
    }

    /**
     * @return int
     */
    public function get_question_reading_speed(): int {
        return $this->question_reading_speed;
    }

    /**
     * @return int
     */
    public function get_question_chances(): int {
        return $this->question_chances;
    }

    /**
     * @return bool
     */
    public function is_shuffle_levels(): bool {
        return $this->shuffle_levels;
    }

    /**
     * @return int
     */
    public function get_level_tile_height(): int {
        return $this->level_tile_height;
    }

    /**
     * @return int
     */
    public function get_level_tile_height_px() {
        switch ($this->get_level_tile_height()) {
            case MOD_PHILOSOPHERS_LEVEL_TILE_HEIGHT_SMALL:
                return 60;
            case MOD_PHILOSOPHERS_LEVEL_TILE_HEIGHT_LARGE:
                return 200;
            case MOD_PHILOSOPHERS_LEVEL_TILE_HEIGHT_MEDIUM:
            default:
                return 120;
        }
    }

    /**
     * @return int
     */
    public function get_level_tile_alpha() {
        return $this->level_tile_alpha;
    }
}

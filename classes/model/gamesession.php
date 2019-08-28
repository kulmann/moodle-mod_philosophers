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

use mod_philosophers\util;

defined('MOODLE_INTERNAL') || die();

/**
 * Class gamesession
 *
 * @package    mod_philosophers\model
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gamesession extends abstract_model {

    const STATE_FINISHED = 'finished';
    const STATE_DUMPED = 'dumped';
    const STATE_PROGRESS = 'progress';

    /**
     * @var int Timestamp of the creation of this gamesession
     */
    protected $timecreated;
    /**
     * @var int Timestamp of the last db update of this gamesession
     */
    protected $timemodified;
    /**
     * @var int The id of the philosophers instance this gamesession belongs to
     */
    protected $game;
    /**
     * @var int The id of the moodle user this gamesession belongs to
     */
    protected $mdl_user;
    /**
     * @var int Score the user reached so far
     */
    protected $score;
    /**
     * @var int Total number of answers in this gamesession
     */
    protected $answers_total;
    /**
     * @var int Number of correct answers in this gamesession
     */
    protected $answers_correct;
    /**
     * @var string The state of the gamesession, out of [progress, finished, dumped].
     */
    protected $state;
    /**
     * @var string The ids of levels in their order, like they will be displayed in the level overview of the game.
     *             This is relevant for when levels are shuffled. We want to display the levels in the same order after a reload.
     */
    protected $levels_order;

    /**
     * gamesession constructor.
     */
    function __construct() {
        parent::__construct('philosophers_gamesessions', 0);
        $this->timecreated = \time();
        $this->timemodified = \time();
        $this->game = 0;
        $this->mdl_user = 0;
        $this->score = 0;
        $this->answers_total = 0;
        $this->answers_correct = 0;
        $this->state = self::STATE_PROGRESS;
        $this->levels_order = '';
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
        $this->game = $data['game'];
        $this->mdl_user = $data['mdl_user'];
        $this->score = isset($data['score']) ? $data['score'] : 0;
        $this->answers_total = isset($data['answers_total']) ? $data['answers_total'] : 0;
        $this->answers_correct = isset($data['answers_correct']) ? $data['answers_correct'] : 0;
        $this->state = isset($data['state']) ? $data['state'] : self::STATE_PROGRESS;
        $this->levels_order = isset($data['levels_order']) ? $data['levels_order'] : '';
    }

    /**
     * Looks up in the db, whether the given level is already finished.
     *
     * @param int $id_level
     *
     * @return bool
     * @throws \dml_exception
     */
    public function is_level_finished($id_level) {
        $question = $this->get_question_by_level($id_level);
        return ($question !== null && $question->is_finished());
    }

    /**
     * Tries to find a question for the given $id_level. Returns null if none found.
     *
     * @param int $id_level
     * @return question|null
     * @throws \dml_exception
     */
    public function get_question_by_level($id_level) {
        global $DB;
        $question = new question();
        $record = $DB->get_record_select(
            $question->get_table_name(),
            'gamesession = :gamesession AND level = :level',
            ['gamesession' => $this->get_id(), 'level' => $id_level]
        );
        if ($record) {
            $question->apply($record);
            return $question;
        } else {
            return null;
        }
    }

    /**
     * @return int
     */
    public function get_timecreated(): int {
        return $this->timecreated;
    }

    /**
     * @param int $timecreated
     */
    public function set_timecreated(int $timecreated) {
        $this->timecreated = $timecreated;
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
    public function get_game(): int {
        return $this->game;
    }

    /**
     * @param int $game
     */
    public function set_game(int $game) {
        $this->game = $game;
    }

    /**
     * @return int
     */
    public function get_mdl_user(): int {
        return $this->mdl_user;
    }

    /**
     * @param int $mdl_user
     */
    public function set_mdl_user(int $mdl_user) {
        $this->mdl_user = $mdl_user;
    }

    /**
     * @return int
     */
    public function get_score(): int {
        return $this->score;
    }

    /**
     * @param int $score
     */
    public function set_score(int $score) {
        $this->score = $score;
    }

    /**
     * @param int $delta
     */
    public function increase_score(int $delta) {
        $this->score += $delta;
    }

    /**
     * @return int
     */
    public function get_answers_total(): int {
        return $this->answers_total;
    }

    /**
     * @param int $answers_total
     */
    public function set_answers_total(int $answers_total) {
        $this->answers_total = $answers_total;
    }

    /**
     * @return void
     */
    public function increment_answers_total() {
        $this->answers_total++;
    }

    /**
     * @return int
     */
    public function get_answers_correct(): int {
        return $this->answers_correct;
    }

    /**
     * @param int $answers_correct
     */
    public function set_answers_correct(int $answers_correct) {
        $this->answers_correct = $answers_correct;
    }

    /**
     * @return void
     */
    public function increment_answers_correct() {
        $this->answers_correct++;
    }

    /**
     * @return string
     */
    public function get_state(): string {
        return $this->state;
    }

    /**
     * @return bool
     */
    public function is_finished(): bool {
        return $this->state === self::STATE_FINISHED;
    }

    /**
     * @return bool
     */
    public function is_dumped(): bool {
        return $this->state === self::STATE_DUMPED;
    }

    /**
     * @return bool
     */
    public function is_in_progress(): bool {
        return $this->state === self::STATE_PROGRESS;
    }

    /**
     * @param string $state
     */
    public function set_state(string $state) {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function get_levels_order(): string {
        return $this->levels_order;
    }

    /**
     * @param string $levels_order
     */
    public function set_levels_order(string $levels_order): void {
        $this->levels_order = $levels_order;
    }
}

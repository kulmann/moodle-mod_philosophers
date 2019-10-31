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

namespace mod_philosophers\external\exporter;

use context;
use core\external\exporter;
use mod_philosophers\model\game;
use mod_philosophers\model\level;
use mod_philosophers\model\question;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

/**
 * Class level_dto
 *
 * @package    mod_philosophers\external\exporter
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class level_dto extends exporter {

    /**
     * @var level
     */
    protected $level;
    /**
     * @var question
     */
    protected $question;
    /**
     * @var game
     */
    protected $game;

    /**
     * level_dto constructor.
     *
     * @param level $level
     * @param question|null $question
     * @param game $game
     * @param context $context
     *
     * @throws \coding_exception
     */
    public function __construct(level $level, $question, game $game, context $context) {
        $this->level = $level;
        $this->question = $question;
        $this->game = $game;
        parent::__construct([], ['context' => $context]);
    }

    protected static function define_other_properties() {
        return [
            'id' => [
                'type' => PARAM_INT,
                'description' => 'level id',
            ],
            'game' => [
                'type' => PARAM_INT,
                'description' => 'philosophers instance id',
            ],
            'state' => [
                'type' => PARAM_TEXT,
                'description' => 'active, deleted',
            ],
            'name' => [
                'type' => PARAM_TEXT,
                'description' => 'name of the level',
            ],
            'position' => [
                'type' => PARAM_INT,
                'description' => 'order of the levels within a game session is defined by their indices.'
            ],
            'bgcolor' => [
                'type' => PARAM_TEXT,
                'description' => 'background color hex code for level representation',
            ],
            'image' => [
                'type' => PARAM_TEXT,
                'description' => 'filename of background image for level representation',
            ],
            'imageurl' => [
                'type' => PARAM_TEXT,
                'description' => 'url of background image for level representation',
            ],
            'finished' => [
                'type' => PARAM_BOOL,
                'description' => 'whether or not the level is already finished',
            ],
            'correct' => [
                'type' => PARAM_BOOL,
                'description' => 'whether or not the question for this level was answered correctly',
            ],
            'score' => [
                'type' => PARAM_INT,
                'description' => 'the score that was reached by answering this question',
            ],
            'seen' => [
                'type' => PARAM_BOOL,
                'description' => 'whether or not this level has been seen by the user (i.e. if a question was shown)',
            ],
            'tile_height_px' => [
                'type' => PARAM_INT,
                'description' => 'the height of the level tiles in pixels',
            ],
        ];
    }

    protected static function define_related() {
        return [
            'context' => 'context',
        ];
    }

    protected function get_other_values(renderer_base $output) {
        $result = \array_merge(
            $this->level->to_array(),
            [
                'imageurl' => $this->level->get_image_url($this->related['context']),
                'finished' => $this->question ? $this->question->is_finished() : false,
                'correct' => $this->question ? $this->question->is_correct() : false,
                'score' => $this->question ? $this->question->get_score() : 0,
                'seen' => $this->question !== null,
                'tile_height_px' => $this->game->get_level_tile_height_px(),
            ]
        );
        return $result;
    }
}

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

use coding_exception;
use context;
use core\external\exporter;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

/**
 * Class bool_dto
 *
 * @package    mod_philosophers\external\exporter
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bool_dto extends exporter {

    /**
     * @var bool
     */
    private $value;

    /**
     * bool_dto constructor.
     *
     * @param bool $value
     * @param context $context
     *
     * @throws coding_exception
     */
    public function __construct(bool $value, context $context) {
        $this->value = $value;
        parent::__construct([], ['context' => $context]);
    }

    protected static function define_other_properties() {
        return [
            'result' => [
                'type' => PARAM_BOOL,
                'description' => 'a boolean value.'
            ],
        ];
    }

    protected static function define_related() {
        return ['context' => 'context'];
    }

    protected function get_other_values(renderer_base $output) {
        return ['result' => $this->value];
    }
}

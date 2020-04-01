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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/bank.php');

/**
 * Class level
 *
 * @package    mod_philosophers\model
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class level extends abstract_model {

    const FILE_AREA = 'levels';
    const STATE_ACTIVE = 'active';
    const STATE_DELETED = 'deleted';

    /**
     * @var int The id of the philosophers instance this level belongs to.
     */
    protected $game;
    /**
     * @var string The state of the level, out of [active, deleted].
     */
    protected $state;
    /**
     * @var string The name of the level.
     */
    protected $name;
    /**
     * @var int Position for ordering levels.
     */
    protected $position;
    /**
     * @var string The filename of the image of the level.
     */
    protected $image;
    /**
     * @var string The hex code of the bg color of the level.
     */
    protected $bgcolor;

    /**
     * level constructor.
     */
    function __construct() {
        parent::__construct('philosophers_levels', 0);
        $this->game = 0;
        $this->state = self::STATE_ACTIVE;
        $this->name = '';
        $this->position = -1;
        $this->image = '';
        $this->bgcolor = '#ccc';
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
        $this->game = $data['game'];
        $this->state = isset($data['state']) ? $data['state'] : self::STATE_ACTIVE;
        $this->name = isset($data['name']) ? $data['name'] : '';
        $this->position = isset($data['position']) ? $data['position'] : 0;
        $this->image = isset($data['image']) ? $data['image'] : '';
        $this->bgcolor = isset($data['bgcolor']) ? $data['bgcolor'] : '#ccc';
    }

    /**
     * Fetches all categories from the DB which belong to this level.
     *
     * @return category[]
     * @throws \dml_exception
     */
    public function get_categories(): array {
        global $DB;
        $sql_params = ['level' => $this->get_id()];
        $records = $DB->get_records('philosophers_categories', $sql_params);
        return \array_map(function ($record) {
            $category = new category();
            $category->apply($record);
            return $category;
        }, $records);
    }

    /**
     * Returns one random question out of the categories that are assigned to this level.
     *
     * @return \question_definition
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public function get_random_question(): \question_definition {
        global $DB;

        // collect all moodle question categories
        $mdl_category_ids = [];
        foreach($this->get_categories() as $category) {
            $mdl_category_ids = \array_merge($mdl_category_ids, $category->get_mdl_category_ids());
        }
        list($cat_sql, $cat_params) = $DB->get_in_or_equal($mdl_category_ids);

        // build query for moodle question selection
        $sql = "
            SELECT q.id
              FROM {question} q 
        INNER JOIN {qtype_multichoice_options} qmo ON q.id=qmo.questionid
             WHERE q.qtype = ? AND qmo.single = ? AND q.category $cat_sql 
        ";
        $params = \array_merge(["multichoice", 1], $cat_params);

        // Get all available questions.
        $available_ids = $DB->get_records_sql($sql, $params);
        if (!empty($available_ids)) {
            // Shuffle here because SQL RAND() can't be used.
            shuffle($available_ids);
            // Take the first one in the array.
            $id = \reset($available_ids)->id;
            return \question_bank::load_question($id, false);
        } else {
            throw new \dml_exception('no question available');
        }
    }

    /**
     * Takes an image, provided as a base64 encoded string, and stores it as a file. The filename will be set
     * in this level entity, so that it can be referenced later.
     *
     * @param \context $context
     * @param string $mimetype
     * @param string $base64content
     *
     * @throws \file_exception
     * @throws \stored_file_creation_exception
     */
    public function store_image($context, $mimetype, $base64content) {
        // delete possibly already existing image
        $this->delete_image($context);
        // save as file
        $fileinfo = [
            'contextid' => $context->id,
            'component' => 'mod_philosophers',
            'filearea' => self::FILE_AREA,
            'itemid' => $this->get_id(),
            'filepath' => '/',
            'filename' => \uniqid(),
            'mimetype' => $mimetype,
            'timecreated' => \time(),
            'timemodified' => \time(),
        ];
        $fs = get_file_storage();
        $file = $fs->create_file_from_string($fileinfo, \base64_decode($base64content));
        $this->set_image($file->get_filename());
    }

    /**
     * Deletes the image of this level entity, if one is set at all.
     *
     * @param \context $context
     */
    public function delete_image($context) {
        if ($this->get_image()) {
            $fs = get_file_storage();
            if ($file = $fs->get_file($context->id, 'mod_philosophers', self::FILE_AREA, $this->get_id(), '/', $this->get_image())) {
                $file->delete();
            }
            $this->set_image('');
        }
    }

    /**
     * If an image is set, constructs and returns an absolute url to the file.
     *
     * @param $context
     * @return \moodle_url|string
     */
    public function get_image_url($context) {
        if ($this->get_image()) {
            $fs = get_file_storage();
            if ($file = $fs->get_file($context->id, 'mod_philosophers', self::FILE_AREA, $this->get_id(), '/', $this->get_image())) {
                $url = \moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
                return $url->out();
            }
        }
        return '';
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
     * @return string
     */
    public function get_state(): string {
        return $this->state;
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
    public function get_position(): int {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function set_position(int $position) {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function get_image(): string {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function set_image(string $image) {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function get_bgcolor(): string {
        return $this->bgcolor;
    }

    /**
     * @param string $bgcolor
     */
    public function set_bgcolor(string $bgcolor) {
        $this->bgcolor = $this->to_valid_hex_string($bgcolor);
    }

    /**
     * @param string $color
     *
     * @return string
     */
    private function to_valid_hex_string(string $color) {
        $color = \str_replace('#', '', $color);
        return '#' . $color;

    }
}

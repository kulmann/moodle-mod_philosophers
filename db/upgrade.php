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
 * This file keeps track of upgrades to mod_philosophers.
 *
 * @package    mod_philosophers
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Upgrade code for mod_philosophers.
 *
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_philosophers_upgrade($oldversion = 0) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2019083001) {
        // we added a service. need to add it to the db as well.
        $servicerecord = new \stdClass();
        $servicerecord->name = 'mod_philosophers_cancel_gamesession';
        $servicerecord->classname = 'mod_philosophers\\external\\gamesessions';
        $servicerecord->methodname = 'cancel_gamesession';
        $servicerecord->component = 'mod_philosophers';
        $DB->insert_record('external_functions', $servicerecord);
    }


    return true;
}

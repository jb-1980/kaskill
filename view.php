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
 * View page for the kaskills module
 *
 * This will just display a link to the skill on www.khanacademy.org, as well as
 * show an example image and videos if available
 *
 * @package    mod_kaskill
 * @copyright  2015 Joseph Gilgen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT);

if ($id) {
    $cm         = get_coursemodule_from_id('kaskill', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $kaskill  = $DB->get_record('kaskill', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID');
}

require_login($course, true, $cm);

$event = \mod_kaskill\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $kaskill);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/kaskill/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($kaskill->name));
$PAGE->set_heading(format_string($course->fullname));
$output = $PAGE->get_renderer('mod_kaskill');
/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('kaskill-'.$somevar);
 */

// Output starts here.
echo $output->header();

$skill_data = json_decode(file_get_contents($CFG->dirroot.'/mod/kaskill/skills.json'));
$name = $kaskill->kaslug;

echo $output->heading($skill_data->$name->title);
echo $output->render_skill_view_page($skill_data->$name);
echo $output->footer();

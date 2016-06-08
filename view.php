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
$skill_url = 'http://www.khanacademy.org/api/v1/exercises/'.$kaskill->kaslug;
$video_url = $skill_url.'/videos';
$skill_data = json_decode(file_get_contents($skill_url));
$video_data = json_decode(file_get_contents($video_url));
$videos = array();
foreach ($video_data as $key => $video) {
  $videos[] = "http://www.khanacademy.org/embed_video?v=".$video->youtube_id;
}
$renderable_data = new stdClass();
$renderable_data->url      = $skill_data->ka_url;
$renderable_data->title    = $skill_data->display_name;
$renderable_data->idnumber = $skill_data->name;
$renderable_data->imageurl = $skill_data->image_url;
$renderable_data->videos   = $videos;

echo $output->heading($renderable_data->title);
echo $output->render_skill_view_page($renderable_data);
echo $output->footer();

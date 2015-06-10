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
 * Library of interface functions and constants for module kaskill
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the kaskill specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_kaskill
 * @copyright  2015 Joseph Gilgen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function kaskill_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_INTRO:
            return false;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_ADVANCED_GRADING:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the kaskill into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $kaskill Submitted data from the form in mod_form.php
 * @param mod_kaskill_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted kaskill record
 */
function kaskill_add_instance(stdClass $kaskill, mod_kaskill_mod_form $mform = null) {
    global $CFG,$DB;
    $skill_data = json_decode(file_get_contents($CFG->dirroot.'/mod/kaskill/skills.json'));

    $slug = $kaskill->skillname;
    $kaskill->kaslug = $slug;
    $kaskill->timecreated = time();
    $kaskill->name = $skill_data->$slug->title;


    $kaskill->id = $DB->insert_record('kaskill', $kaskill);

    kaskill_grade_item_update($kaskill);

    return $kaskill->id;
}

/**
 * Updates an instance of the kaskill in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $kaskill An object from the form in mod_form.php
 * @param mod_kaskill_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function kaskill_update_instance(stdClass $kaskill, mod_kaskill_mod_form $mform = null) {
    global $CFG,$DB;
    $kaskill->timemodified = time();
    $skill_data = json_decode(file_get_contents($CFG->dirroot.'/mod/kaskill/skills.json'));

    $slug = $kaskill->skillname;
    $kaskill->kaslug = $slug;
    $kaskill->name = $skill_data->$slug->title;
    $kaskill->id = $kaskill->instance;

    $result = $DB->update_record('kaskill', $kaskill);

    kaskill_grade_item_update($kaskill);

    return $result;
}

/**
 * Removes an instance of the kaskill from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function kaskill_delete_instance($id) {
    global $DB;

    if (!$kaskill = $DB->get_record('kaskill', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.

    $DB->delete_records('kaskill', array('id' => $kaskill->id));

    kaskill_grade_item_delete($kaskill);

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $kaskill The kaskill instance record
 * @return stdClass|null
 */
function kaskill_user_outline($course, $user, $mod, $kaskill) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * It is supposed to echo directly without returning a value.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $kaskill the module instance record
 */
function kaskill_user_complete($course, $user, $mod, $kaskill) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in kaskill activities and print it out.
 *
 * @param stdClass $course The course record
 * @param bool $viewfullnames Should we display full names
 * @param int $timestart Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function kaskill_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link kaskill_print_recent_mod_activity()}.
 *
 * Returns void, it adds items into $activities and increases $index.
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 */
function kaskill_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@link kaskill_get_recent_mod_activity()}
 *
 * @param stdClass $activity activity record with added 'cmid' property
 * @param int $courseid the id of the course we produce the report for
 * @param bool $detail print detailed report
 * @param array $modnames as returned by {@link get_module_types_names()}
 * @param bool $viewfullnames display users' full names
 */
function kaskill_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 *
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * Note that this has been deprecated in favour of scheduled task API.
 *
 * @return boolean
 */
function kaskill_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array
 */
function kaskill_get_extra_capabilities() {
    return array();
}

/* Gradebook API */

/**
 * Is a given scale used by the instance of kaskill?
 *
 * This function returns if a scale is being used by one kaskill
 * if it has support for grading and scales.
 *
 * @param int $kaskillid ID of an instance of this module
 * @param int $scaleid ID of the scale
 * @return bool true if the scale is used by the given kaskill instance
 */
function kaskill_scale_used($kaskillid, $scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('kaskill', array('id' => $kaskillid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of kaskill.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale
 * @return boolean true if the scale is used by any kaskill instance
 */
function kaskill_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('kaskill', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given kaskill instance
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $kaskill instance object with extra cmidnumber and modname property
 * @param bool $reset reset grades in the gradebook
 * @return void
 */
function kaskill_grade_item_update(stdClass $kaskill, $reset=false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($kaskill->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($kaskill->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $kaskill->grade;
        $item['grademin']  = 0;
    } else if ($kaskill->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$kaskill->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('mod/kaskill', $kaskill->course, 'mod', 'kaskill',
            $kaskill->id, 0, null, $item);
}

/**
 * Delete grade item for given kaskill instance
 *
 * @param stdClass $kaskill instance object
 * @return grade_item
 */
function kaskill_grade_item_delete($kaskill) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/kaskill', $kaskill->course, 'mod', 'kaskill',
            $kaskill->id, 0, null, array('deleted' => 1));
}

/**
 * Update kaskill grades in the gradebook
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $kaskill instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 */
function kaskill_update_grades(stdClass $kaskill, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();

    grade_update('mod/kaskill', $kaskill->course, 'mod', 'kaskill', $kaskill->id, 0, $grades);
}


/**
 * Update the json file of the KA skills, which is used to choose and create activities
 */
function kaskill_update_skills(){
    $contents = file_get_contents('http://www.khanacademy.org/api/v1/exercises');
    $data = json_decode($contents);
    $skills = array();
    foreach($data as $datum){
        $datum->name = str_replace(' ','%20',$datum->name);
        //get video urls to embed in skill
        $video_content = file_get_contents("http://www.khanacademy.org/api/v1/exercises/{$datum->name}/videos");
        $video_data = json_decode($video_content);
        $video_urls = array();
        foreach($video_data as $video_datum){
            $video_urls[]="http://www.khanacademy.org/embed_video?v={$video_datum->youtube_id}";
        }
        
        //parse json data to keep just essential skill data
        $skills[$datum->name]=array(
            'url'=>$datum->ka_url,
            'title'=>$datum->title,
            'idnumber'=>$datum->name,
            'imageurl'=>$datum->image_url,
            'videos'=>$video_urls
        );
        
    }
    $json_file = fopen('skills.json','w');
    fwrite($json_file,json_encode($skills));
    fclose($json_file);
    print_object($skills);
}
#global $PAGE;
#$PAGE->requires->jquery_plugin('kaskill-jquerymodule', 'mod_kaskill');

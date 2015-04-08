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
 * The main kaskill configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_kaskill
 * @copyright  2015 Joseph Gilgen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');


/**
 * Module instance settings form
 *
 * @package    mod_kaskill
 * @copyright  2015 Joseph Gilgen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_kaskill_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG,$DB;
        
        $mform = $this->_form;
        
        
        // Adding the skill selector
        $skill_data = json_decode(file_get_contents($CFG->dirroot.'/mod/kaskill/skills.json'));
        foreach($skill_data as $skill=>$data){
            $skill_select[$skill]=$data->title;
        }
        asort($skill_select);
        if($update = optional_param('update', 0, PARAM_INT)){
            $instance = $DB->get_record('course_modules',array('id'=>$update))->instance;
            $skill = $DB->get_record('kaskill', array('id' => $instance));
            $name = $skill->kaslug;
            $mform->setDefault('skillname',$name);
        } else{
            $name = '';
        }
        $mform->addElement('select', 'skillname', get_string('kaskillname', 'kaskill'),$skill_select ,array('height'=>'64px','overflow'=>'hidden','width'=>'240px','data-placeholder'=>$name));
        $mform->addHelpButton('skillname', 'kaskillname', 'kaskill');
        
        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
        
        $mform->addElement('html','<script>$("#id_skillname").chosen()</script>');
    }
}

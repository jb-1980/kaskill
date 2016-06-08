<?php

defined('MOODLE_INTERNAL') || die();
require_once('lib.php');

/**
 * Renderer for the flexdates plugin
 *
 * @copyright  2014 Joseph Gilgen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_kaskill_renderer extends plugin_renderer_base {

    /**
     * Render selection box and selected box of khan skills
     * @param stdClass object $skill_data parsed json object that describe the
     *        skill (e.g. parsed from http://www.khanacademy.org/api/v1/exercises/addition_1)
     * @return string html to output.
     */
    function render_skills_container($skills_data){

    }

    /**
     * Render the skill row for a single Khan Academy skill
     * @param stdClass object $skill_data parsed json object that describe the skill (e.g. parsed from http://www.khanacademy.org/api/v1/exercises/addition_1)
     * @return string html to output.
     */
    function render_skill_row($skill_data){
        $checkbox_glyph = html_writer::span('','glyphicon glyphicon-unchecked', array("aria-hidden"=>"true"))."\n";
        $skill_name = html_writer::span($skill_data->title,'kaskills-exercise-name')."\n";
        $skill_name_container = html_writer::div($checkbox_glyph.$skill_name,'kaskills-exercise-name-container',array('data-kaidnumber'=>$skill_data->idnumber))."\n";

        $popover = html_writer::span('','glyphicon glyphicon-info-sign kaskills-info',array(
                                        'data-content'=>$this->render_skill_popover($skill_data->image,$skill_data->url),
                                        'tabindex'=>'0')
                                     )."\n";
        $skill_row = html_writer::div($skill_name_container.$popover,'kaskills-exercise-picker-row');
        return $skill_row;
    }

    /**
     * Render the html for image popover for a single Khan Academy skill
     * @param string $image_256 url src of image (i.e https://ka-exercise-screenshots.s3.amazonaws.com/counting-out-1-20-objects_256.png)
     * @param string $skill_url url href of skill (i.e. http://www.khanacademy.org/exercise/counting-out-1-20-objects)
     * @return string html to output.
     */
    function render_skill_popover($image_256,$skill_url){
        if(strpos($image_256,'_256.png' === False)){
            $image_256 = str_replace('.png','_256.png',$image_256);
        }

        $image = html_writer::img($image_256, $alt=null);
        $link = html_writer::link($skill_url, get_string('openskill','mod_kaskill'), array('target'=>'_blank'));

        $popover = html_writer::div($image,$class = '', array('style'=>'height:262;width:260;'))."\n";
        $popover = html_writer::div($link,$class = '', array('style'=>'text-align:center;padding-top:5px;'))."\n";

        return $popover;
    }

    /**
     * Renders the view page for a given skill
     * @param stdObj $skill an object containing the Khan Academy skill info
     * @return string html to output
     */
    function render_skill_view_page($skill){
        //print_object($skill);

        $img = html_writer::img($skill->imageurl, $skill->title.' image');
        if(!empty($skill->videos)){
            $iframe = html_writer::tag('iframe', '',
                          array('id'=>'kaskill-ka-player',
                              'style'=>'width:853px;height:480px;border:none;background-color:ghostwhite;margin:auto;',
                              'scrolling'=>'no',
                              'src'=>$skill->videos[0],
                              'allowfullscreen'=>'',
                              'webkitallowfullscreen'=>'',
                              'mozallowfullscreen'=>''
                              )
                      )."\n";
            $buttons = '';
            foreach($skill->videos as $k=>$video){
                $buttons.= html_writer::tag('button','Video '.($k+1), array('onclick'=>"changeVideo('{$video}')",'class'=>'kaskills-btn'))."\n";
            }
            $iframe.=html_writer::tag('p',$buttons)."\n";
        } else{
            $iframe='';
        }
        $intro = '';
        $html = html_writer::tag('p',$img,array('style'=>'text-align:center;'))."\n";
        if($iframe){
            $html.= html_writer::tag('p',get_string('videointro','kaskill'))."\n";
        } else{
            $html.= html_writer::tag('p',get_string('novideointro','kaskill'))."\n";
        }
        $html.= html_writer::tag('p',$iframe)."\n";
        $link = html_writer::link($skill->url, get_string('skilllink','kaskill'), array('class'=>'kaskill-button-link','target'=>'_blank'));
        $html.= html_writer::div($link,'',array('style'=>'margin:auto;text-align:center;'))."\n";
        $jscode = '
    function changeVideo(url){
        document.getElementById("kaskill-ka-player").src = url;
    }';
        $html.= html_writer::script($jscode, $url=null);
        return $html;
    }
}

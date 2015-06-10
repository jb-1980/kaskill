<?php

namespace mod_kaskill\task;

class updateskills extends \core\task\scheduled_task {      
    public function get_name() {
        // Shown in admin screens
        return get_string('updateskills', 'mod_kaskill');
    }
                                                                     
    public function execute() {       
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
        
        $json_file = fopen(dirname(__FILE__).'../../skills.json','w');
        fwrite($json_file,json_encode($skills));
        fclose($json_file);
    }
} 

<?php

/**
 * Update the json file of the KA skills, which is used to choose and create activities
 */

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
echo "Script completed\n";

?>

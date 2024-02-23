<?php
	if (isset($_GET['id']))
	    $videoID = $_GET['id'];

    $APIKey = "Api Key Here";

    $videoData = @file_get_contents("https://www.googleapis.com/youtube/v3/videos?id=$videoID&part=contentDetails&key=$APIKey");
    if ($videoData !== FALSE) {
    	$videoData = json_decode($videoData, true);
    	
    	$videoTime = $videoData["items"][0]["contentDetails"]["duration"];
    	
    	$videoMinutes = 0;
    	$videoSeconds = 0;
    	
    	$videoTime = str_replace("PT", "", $videoTime);
    	$videoTime = str_replace("S", "", $videoTime);
    	
    	$videoInfo = explode("M", $videoTime);
    	
    	if($videoTime == "") {
    	    $videoReal = "false";
    	}else{
    	    $videoReal = "true";
    	}
    	
    	if(!isset($videoInfo[1])) {
    	    $videoMinutes = 0;
    	    $videoSeconds = intval($videoInfo[0]);
    	} else {
    	    $videoInfo[0] = explode("H", $videoInfo[0]);
    	    if(isset($videoInfo[0][1])) {
    	        $videoMinutes = intval($videoInfo[0][1]+($videoInfo[0][0]*60));
    	        $videoSeconds = intval($videoInfo[1]);
    	    } else {
    	        $videoMinutes = intval($videoInfo[0][0]);
    	        $videoSeconds = intval($videoInfo[1]);
    	    }
    	}
    	
    	$videoSecondsAll = $videoMinutes*60+$videoSeconds;
    	$videoMinSec = $videoMinutes . ":" . $videoSeconds;
    	
    	echo "{\"videoID\":\"$videoID\",\"videoExists\":$videoReal,\"videoLength\":$videoSecondsAll,\"videoLengthString\":\"$videoMinSec\"}";
    }
?>

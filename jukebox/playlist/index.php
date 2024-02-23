<?php
    if (isset($_GET['id']))
	    $playlistID = urlencode($_GET['id']);

    $APIKey = "AIzaSyCS3H_1Q8k6YsNTo_rfTgQo4yiEVqLC4AA";
    $baseURL = "https://www.googleapis.com/youtube/v3/playlistItems?playlistId=$playlistID&maxResults=50&part=snippet,contentDetails&key=$APIKey";
    
    function getPlaylistSongs($token, $total) {
        global $baseURL;
        
        $url = $baseURL;
        if ( $token != false ) {
            $url = $url . "&pageToken=$token";
        }
        if ( $total >= 200 ) {
            return [];
        }
        
        $headers = get_headers($url);
        
        if ( substr($headers[0], 9, 3) == "200" ) {
            $playlistSongs = @file_get_contents($url);
            if ($playlistSongs !== FALSE) {
                $playlistSongs = json_decode($playlistSongs, true);
                
                $songs = [];
                
                foreach( $playlistSongs["items"] as $id=>$song ) {
                    if (count($songs)+$total >= 200) {
                        break;
                    };
                    $data = [
                        "title" => $song["snippet"]["title"],
                        "id" => $song["contentDetails"]["videoId"],
                        "length" => $song["contentDetails"]["endAt"],
                        "image" => $song["snippet"]["thumbnails"]["medium"]["url"]
                    ];
                    array_push($songs, $data);
                }
                
                if ( isset($playlistSongs["nextPageToken"]) ) {
                    // More sure we're not exceeding 250 results, as this is painful for the server
                    if ($total+count($songs) < 200) {
                        $moreSongs = getPlaylistSongs($playlistSongs["nextPageToken"], count($songs)+$total);
                        foreach( $moreSongs as $id=>$song ) {
                            array_push($songs, $song);   
                        }
                    }
                }
                
                return $songs;
            } else {
                return [];
            }
        } else {
            return [];
        }
    }
    
    function getSongLengths($songs) {
        global $APIKey;
        
        $len = count($songs);
        // Loop through in sets of 50 while possible
        for( $i=0; $i <= floor( $len / 50 ); $i++ ) {
            $ids = implode(",", array_slice(array_column($songs, "id"), $i*50, min($len-($i*50), 50)));
            
            $videoData = @file_get_contents("https://www.googleapis.com/youtube/v3/videos?id=$ids&part=contentDetails&key=$APIKey");
            if ($videoData !== FALSE) {
            	$videoData = json_decode($videoData, true);
            	
            	for( $j=0; $j < count($videoData["items"]); $j++) {
                	$videoTime = $videoData["items"][$j]["contentDetails"]["duration"];
                	
                	$videoMinutes = 0;
                	$videoSeconds = 0;
                	
                	$videoTime = str_replace("PT", "", $videoTime);
                	$videoTime = str_replace("S", "", $videoTime);
                	
                	$videoInfo = explode("M", $videoTime);
                	
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
                	
                	$songs[$i*50+$j]["length"] = $videoSecondsAll;
            	}
            }
        }
        
        return $songs;
    }
    
    $results = getPlaylistSongs(false, 0);
    
    // YouTube's API is stupid and doesn't return the length of each song, so we need to manually do it...
    
    $results = getSongLengths($results);
    
    /*
    foreach( $results as $id=>$song ) {
        echo $song['title'] . " -> " . $song['length'] . "<br>";
    }
    echo count($results);
    */
    
    
    echo json_encode($results);
    
    
?>
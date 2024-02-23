<?php
	if (isset($_GET['search']))
	    $searchTerm = urlencode($_GET['search']);

    $APIKey = "AIzaSyCS3H_1Q8k6YsNTo_rfTgQo4yiEVqLC4AA";
	
	echo '[';
    
    $searchData = @file_get_contents("https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&key=$APIKey&q=$searchTerm&maxResults=20");
    if ($searchData !== FALSE) {
        $searchData = json_decode($searchData, true);
        
        foreach( $searchData["items"] as $searchResultNum=>$searchResult ) {
            if ( ($searchResultNum + 1) === count($searchData["items"]) ) {
                echo '{"url":"' . str_replace( '"', '\"', $searchResult["id"]["videoId"] ) . '", "image":"' . str_replace( '"', '\"', $searchResult["snippet"]["thumbnails"]["medium"]["url"] ) . '", "title":"' . str_replace( '"', '\"', $searchResult["snippet"]["title"] ) . '", "desc":"' . str_replace( '"', '\"', $searchResult["snippet"]["description"] ) . '" } ';
            } else {
                echo '{"url":"' . str_replace( '"', '\"', $searchResult["id"]["videoId"] ) . '", "image":"' . str_replace( '"', '\"', $searchResult["snippet"]["thumbnails"]["medium"]["url"] ) . '", "title":"' . str_replace( '"', '\"', $searchResult["snippet"]["title"] ) . '", "desc":"' . str_replace( '"', '\"', $searchResult["snippet"]["description"] ) . '" }, ';
            };
        }
    }
	
	echo "]";
?>
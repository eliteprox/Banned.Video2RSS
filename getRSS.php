<?php
date_default_timezone_set("America/Chicago");
header("Content-Type: application/rss+xml; charset=ISO-8859-1");

$theXML = "";
$filename = "";
if (isset($_GET["channel"])) {
    $channel = $_GET["channel"];
    $json_a = json_decode(GetChannelVideos($channel, 0, 200), true)["data"]["getChannel"]["videos"];
    if (count($json_a) > 0) {
        $filename = "channel.rss";
        $theXML = processXML($json_a, 0, 200,"channel.rss", false);
    }
} else {
    $json_a = json_decode(GetLatestVideos(0, 200), true)["data"]["getNewVideos"];
    if (count($json_a) > 0) {
        $filename = "latest.rss";
        $theXML = processXML($json_a, 0, 200,"latest.rss", true);
    }
}

header("Content-Disposition: attachment; filename=" . $filename . "");
echo $theXML;

function processXML($videos, $start, $end, $filename, $latest)
{
    //Create XML File in memory
    $xml = new DomDocument("1.0", "UTF-8");
    $xml->version  = "1.0";
    $xml->encoding = "UTF-8";
    $xml->formatOutput = true;
    $rss_node = $xml->appendChild($xml->createElement("rss")); //add RSS element to XML node
    $rss_node->setAttribute("version","2.0"); //set RSS version
    $rss_node->setAttribute("xmlns:media", "http://search.yahoo.com/mrss/");
    
    //Prevents breaking when less than selected results exist, grabs what it can.
    if ($start > count($videos) || $start + $end > count($videos)) {
        if ($start < count($videos)) {
            $videos = array_slice($videos, $start, ($end + $start) - count($videos));
        } else {
            return;
        }
    } else {
        $videos = array_slice($videos, $start, $end);
    }

    for($i=0; $i<count($videos); $i++) 
    {
        $theXML = $xml->saveXML();
        $xpath = new DOMXPath($xml);

        // TODO: There's probably a better way to write this than using a loop, but it's all I could figure out
        $search = "";
        $latestTitle = "Latest Videos";
        if ($latest) {
            $search = $latestTitle;
        } else {
            $search = htmlspecialchars($videos[$i]["channel"]["title"]);
        }

        $query = "//channel[title='" . $search . "']";
        $elements = $xpath->query($query);
        $found = false;
        $thischannel = null;
        foreach ($elements as $channel) {
            $found = true;
            $thischannel = $channel;
            break;
            // echo $title->nodeValue, "\n";
        }
        //TODO: END CODE REFACTOR OPPORTUNITY

        if ($found) {
            //Add video to channel
            $xml = addVideoToChannel($thischannel, $xml,  $videos[$i]);
       } else {
            //Create channel
            $channel = $xml->createElement('channel');
            $channel = $rss_node->appendChild($channel);
            if ($latest) {
                $title = $latestTitle;
                $description = "Because there is a war on for your mind!";
                $link = "https://banned.video";
                $imageurl = "https://assets.infowarsmedia.com/images/9a211cdf-bdbf-443f-83d6-333a5f02e104-large.jpg";
            } else {
                $title = htmlspecialchars($videos[$i]["channel"]["title"]);
                $description = htmlspecialchars($videos[$i]["channel"]["title"]);
                $link = "https://banned.video/channel/" . $videos[$i]["channel"]["_id"];
                $imageurl = $videos[$i]["channel"]["avatar"];
            }

            $channel_title = $channel->appendChild($xml->createElement('title', $title));
            $channel_title = $channel->appendChild($xml->createElement('description', $description));
            $channel_link = $channel->appendChild($xml->createElement('link', $link));
            $xml->saveXML();
                $image = $channel->appendChild($xml->createElement('image'));
                $imgurl = $image->appendChild($xml->createElement('title', $latestTitle));        
                $imgurl = $image->appendChild($xml->createElement('url', $imageurl));
                $imglink = $image->appendChild($xml->createElement('link', $link));
            $xml->saveXML();

            //Add video to it
            $xml = addVideoToChannel($channel, $xml, $videos[$i]);
        }
    }

    $theXML = $xml->saveXML();
    
    // // Save the contents to a file (you could alternatively return them in response!)
    // $file = fopen($filename, "w");
    // fwrite($file, $theXML);
    // fclose($file);
    
    return $theXML;
}

function addVideoToChannel($channel, $xml, $video)
{
    if ($video["directUrl"] == null) {
        $val_streamFormat = "video/mp4";
        $val_streamUrl = $video["streamUrl"];
    } else {
        $val_streamFormat = "video/mp4";
        $val_streamUrl = $video["directUrl"];
    }

    $item = $channel->appendChild($xml->createElement('item'));
    $item_title = $item->appendChild($xml->createElement('title', utf8_encode(htmlspecialchars($video["title"]))));
    $item_link = $item->appendChild($xml->createElement('link', "https://banned.video/watch?id=" . $video["_id"]));
    $item_guid = $item->appendChild($xml->createElement('guid', "https://banned.video/watch?id=" . $video["_id"]));
    $item_pubDate = $item->appendChild($xml->createElement('pubDate', date('r', strtotime($video["createdAt"]))));
    
    $enclosure = $item->appendChild($xml->createElement('enclosure'));
    $enclosure->setAttribute("url",htmlspecialchars($val_streamUrl));
    $enclosure->setAttribute("length",htmlspecialchars(round($video["videoDuration"])));
    $enclosure->setAttribute("type", $val_streamFormat);
    $description = $item->appendChild($xml->createElement('description', utf8_encode(htmlspecialchars($video["summary"])) ));

    $media = $item->appendChild($xml->createElement("media:content"));
    $media->setAttribute("url",$val_streamUrl);
    $media->setAttribute("type",$val_streamFormat);
    $mediaThumb = $media->appendChild($xml->createElement('media:thumbnail'));
    $mediaThumb->setAttribute("url", $video["largeImage"]);

    return $xml;
}

function formatJsonDate($jsondate) {
    return date_format(date_create(date('Y-m-d H:i:s', strtotime($jsondate))), "M j, Y");
}

function GetChannelVideos($channelid, $offset, $limit) {
    $params = array(
        "operationName" => "GetChannelVideos",
        "variables" =>(object) [
            'id' => $channelid ,
            'limit' => $limit,
            'offset' => $offset,
        ],
        "query" => "query GetChannelVideos(\$id: String!, \$limit: Float, \$offset: Float) {
                getChannel(id: \$id) {
                  _id
                  videos(limit: \$limit, offset: \$offset) {
                    ...DisplayVideoFields
                    __typename
                  }
                  __typename
                }
              }
              
              fragment DisplayVideoFields on Video {
                _id
                title
                summary
                playCount
                largeImage
                embedUrl
                published
                directUrl
                summary
                videoDuration
                streamUrl
                live
                channel {
                  _id
                  title
                  avatar
                  __typename
                }
                createdAt
                __typename
              }"
    );
    $params = json_encode($params);
    $results = httpPost("https://api.infowarsmedia.com/graphql", $params);
    return $results;
}

function GetLatestVideos($offset, $limit) {
    $params = array(
        "operationName" => "GetNewVideos",
        "variables" =>(object) [
            'limit' => $limit,
            'offset' => $offset,
        ],
        "query" => "query GetNewVideos(\$limit: Float, \$offset: Float) {
                getNewVideos(limit: \$limit, offset: \$offset ) {
                    ...DisplayVideoFields
                    __typename
                  }
              }
              
              fragment DisplayVideoFields on Video {
                _id
                title
                summary
                playCount
                largeImage
                thumbnailImage
                embedUrl
                published
                summary
                videoDuration
                directUrl
                streamUrl
                live
                channel {
                  _id
                  title
                  avatar
                  __typename
                }
                createdAt
                __typename
              }"
    );
    $params = json_encode($params);
    $results = httpPost("https://api.infowarsmedia.com/graphql", $params);
    return $results;
}

function httpPost($url,$params)
{
    $ch = curl_init();  
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HEADER, false); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);    
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($params))
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Skip SSL Verification

    $output=curl_exec($ch);
    if(curl_error($ch)) {
        var_dump(curl_error($ch) . "url:" . $url, true);
        die();
    }
    curl_close($ch);
    return $output;
}



?>
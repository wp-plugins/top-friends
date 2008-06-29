<?php
/*
Plugin Name: Top Friends
Version: 0.1
Plugin URI: http://fairyfish.net/2008/06/02/top-friends/
Description: Top Friends
Author: Denis
Author URI: http://fairyfish.net/
*/

$google_ajax_feed_apikey="";

$top_friends_feeds = array (
  'http://feed.honeypiggy.com/',
  'http://teanaelf.com/feed/',
  'http://feed.feedsky.com/denisblog',
  'http://feeds.feedburner.com/fairyfish'
);


function fetch_google_ajax_feed($feed) {
  if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $feed);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_REFERER, "http://www.mysite.com/index.html");
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
  } else {
    if ($result = file_get_contents($feed)) {
      return $result;
    }			
  }
}

function top_friends(){
  $top_friends_string = wp_cache_get('top_friends', 'top_friends');

  if ($top_friends_string === false){
  
    global $google_ajax_feed_apikey, $top_friends_feeds;
    
    foreach($top_friends_feeds as $feed){
      $google_ajax_feed_url =  'http://ajax.googleapis.com/ajax/services/feed/load?v=1.0&q='.urlencode($feed);
      if($google_ajax_feed_apikey) $google_ajax_feed_url .= '&key='.$google_ajax_feed_apikey;
      
      $result = fetch_google_ajax_feed($google_ajax_feed_url);
      
      $result = json_decode($result);
      
      $feed = $result -> responseData-> feed;
      
      $entries = $feed -> entries;
      
      if($entries){
        $top_friends_string .= '<li>'. "\n";
        
        $top_friends_string .= '<a href="'.$feed -> link.'" title="'.$feed -> description.'" target="_blank" >'.$feed -> title.'</a> '. "\n";
        
        $last_update_time = strtotime($entries[0] -> publishedDate);
        
        $time_now = time();
        $cc = $time_now - $last_update_time;
        $days = floor($cc/(3600*24));
        $hours = floor(($cc%(3600*24))/3600);
        $mins = floor($cc%3600/60);
        
        if ($days > 30) {
          $top_friends_string .= '<a href="'.$feed.'" title="Last Update: 1 month ago" target="_blank" >'.'<img src="'.get_option('siteurl') . '/'. PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) . '/images/sig_01.gif" />'.'</a> <br />'. "\n";
        } elseif ($days > 15) {
          $top_friends_string .= '<a href="'.$feed.'" title="Last Update: '.$days.' days ago" target="_blank" >'.'<img src="'.get_option('siteurl'). '/'.PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) . '/images/sig_02.gif" />'.'</a> <br />'. "\n";
        } elseif ($days > 10) {
          $top_friends_string .= '<a href="'.$feed.'" title="Last Update: '.$days.' days ago" target="_blank" >'.'<img src="'.get_option('siteurl'). '/'.PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) . '/images/sig_03.gif" />'.'</a> <br />'. "\n";
        } elseif ($days > 5) {
          $top_friends_string .= '<a href="'.$feed.'" title="Last Update: '.$days.' days ago" target="_blank" >'.'<img src="'.get_option('siteurl'). '/'.PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) . '/images/sig_04.gif" />'.'</a> <br />'. "\n";
        } elseif ($days > 1) {
          $top_friends_string .= '<a href="'.$feed.'" title="Last Update: '.$days.' days ';
          if($hours) $top_friends_string .= $hours.' hours ';
          $top_friends_string .= 'ago"  target="_blank" >'.'<img src="'.get_option('siteurl'). '/'.PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) . '/images/sig_05.gif" />'.'</a> <br />'. "\n";
        } else {
          $top_friends_string .= '<a href="'.$feed.'" title="Last Update: '.$hours.' hours ';
          if($mins) $top_friends_string .= $mins.' minutes ';
          $top_friends_string .= 'ago"  target="_blank" >'.'<img src="'.get_option('siteurl'). '/'.PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) . '/images/sig_06.gif" />'.'</a> <br />'. "\n";
        }
        
        $i = 0;
        foreach($entries as $entry){
          if($i>1) continue;
          $top_friends_string .= '<a href="'.$entry -> link.'" title="'.$entry -> title.'"  target="_blank" >'.$entry -> title.'</a> / '. "\n";
          $i++;
        }
        $top_friends_string .= '... </li>'. "\n";
      }
    }
    
    wp_cache_set('top_friends', $top_friends_string, 'top_friends', 3600);
  }
  
  echo $top_friends_string;
}
?>
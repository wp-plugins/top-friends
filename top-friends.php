<?php
/*
Plugin Name: Top Friends
Version: 0.3
Plugin URI: http://fairyfish.net/2008/06/02/top-friends/
Description: Top Friends
Author: Denis
Author URI: http://fairyfish.net/
*/

function fetch_google_ajax_feed($feed) {
  if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $feed);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_REFERER, get_option("home"));
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
  } else {
    if ($result = file_get_contents($feed)) {
      return $result;
    }			
  }
}

function update_top_friends(){  
  $top_friends_option = get_option("top_friends_option");
  
  $google_ajax_feed_apikey = $top_friends_option["google_ajax_feed_apikey"];
  
  //global $top_friends_feeds;
  
  $top_friends_feeds = $top_friends_option["top_friends_feeds"];  
  
  if(!$top_friends_feeds || $top_friends_feeds == ""){
	return;
  }
  
  $top_friends_feeds = explode("\n", $top_friends_feeds);
  
  foreach($top_friends_feeds as $top_friends_feed){
	$top_friends_feed = trim($top_friends_feed);
	if(!$top_friends_feed || $top_friends_feed == ""){
		continue;
	}
    $google_ajax_feed_url =  'http://ajax.googleapis.com/ajax/services/feed/load?v=1.0&q='.urlencode($top_friends_feed);
    if($google_ajax_feed_apikey) $google_ajax_feed_url .= '&key='.$google_ajax_feed_apikey;
    
    $result = fetch_google_ajax_feed($google_ajax_feed_url);
    
	if (function_exists("json_decode")) {
	    $result = json_decode($result);
	} else {
	    require_once("JSON.php");
	    $json = new Services_JSON();
	    $result = $json->decode($str);
	}
        
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
        $top_friends_string .= '<a href="'.$top_friends_feed.'" title="Last Update: 1 month ago" target="_blank" >'.'<img src="'.get_option('siteurl') . '/'. PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) . '/images/sig_01.gif" />'.'</a> <br />'. "\n";
      } elseif ($days > 15) {
        $top_friends_string .= '<a href="'.$top_friends_feed.'" title="Last Update: '.$days.' days ago" target="_blank" >'.'<img src="'.get_option('siteurl'). '/'.PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) . '/images/sig_02.gif" />'.'</a> <br />'. "\n";
      } elseif ($days > 10) {
        $top_friends_string .= '<a href="'.$top_friends_feed.'" title="Last Update: '.$days.' days ago" target="_blank" >'.'<img src="'.get_option('siteurl'). '/'.PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) . '/images/sig_03.gif" />'.'</a> <br />'. "\n";
      } elseif ($days > 5) {
        $top_friends_string .= '<a href="'.$top_friends_feed.'" title="Last Update: '.$days.' days ago" target="_blank" >'.'<img src="'.get_option('siteurl'). '/'.PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) . '/images/sig_04.gif" />'.'</a> <br />'. "\n";
      } elseif ($days > 1) {
        $top_friends_string .= '<a href="'.$top_friends_feed.'" title="Last Update: '.$days.' days ';
        if($hours) $top_friends_string .= $hours.' hours ';
        $top_friends_string .= 'ago"  target="_blank" >'.'<img src="'.get_option('siteurl'). '/'.PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) . '/images/sig_05.gif" />'.'</a> <br />'. "\n";
      } else {
        $top_friends_string .= '<a href="'.$top_friends_feed.'" title="Last Update: '.$hours.' hours ';
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
  
	$top_friends = array("time"=>time(),"top_friends"=>$top_friends_string);
	update_option('top_friends', $top_friends);
  
  return $top_friends_string;
}

function get_top_friends(){
	$top_friends = get_option("top_friends");
	
	if($top_friends){
		$time = time();
		if(($time - $top_friends["time"])<1800){
			return $top_friends["top_friends"];
		}
	}
	
	$top_friends_string = update_top_friends();
	
	return $top_friends_string;
}

function top_friends(){
	echo get_top_friends();
}


add_action('plugins_loaded', 'widget_sidebar_top_friends');
function widget_sidebar_top_friends() {
	function widget_top_friends($args) {
	    extract($args);
		echo $before_widget;
		echo $before_title . 'Top Friends' . $after_title;
			$output = "<ul>".get_top_friends() ."</ul>";
			echo $output;
		echo $after_widget;
	}
	register_sidebar_widget('Top-Friends', 'widget_top_friends');
}



add_action('admin_menu', 'top_friends_options_page');

function top_friends_options_page() {
	if (function_exists('add_options_page')) {
		add_options_page( __('Top Friends','top_friends'), __('Top Friends','top_friends'), 8, basename(__FILE__), 'top_friends_options_subpanel');
	}
}

function top_friends_options_subpanel() {
	if($_POST["top_friends_Submit"]){
		$message = "Top Friends Setting Updated";
	
		$top_friends_option_saved = get_option("top_friends_option");
		
		$google_ajax_feed_apikey = $_POST['google_ajax_feed_apikey_option'];
		$top_friends_feeds = $_POST['top_friends_feeds_option'];
		
		$top_friends_option = array (
			"google_ajax_feed_apikey" 	=> $google_ajax_feed_apikey,
			"top_friends_feeds"		=> $top_friends_feeds
		);
		
		if ($top_friends_option_saved != $top_friends_option){
			if(!update_option("top_friends_option",$top_friends_option)){
				$message = "Update Failed";
			}
			update_top_friends();
		}
		
		echo '<div id="message" class="updated fade"><p>'.$message.'.</p></div>';
	}
	
	$top_friends_option = get_option("top_friends_option");
?>
    <div class="wrap">
        <h2 id="write-post"><?php _e("Top Friends Options&hellip;",'top_friends');?></h2>
        <p><?php _e("Top Friends is a WordPress blogroll enhancement plugin. The plugin will fetch your friends' feeds, and then display the feed's name and status icon base on last update time and latest two posts of the feed.",'top_friends');?></p>
        <div style="float:right;">
          <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
          <input type="hidden" name="cmd" value="_donations">
          <input type="hidden" name="business" value="honghua.deng@gmail.com">
          <input type="hidden" name="item_name" value="Donate to fairyfish.net">
          <input type="hidden" name="no_shipping" value="0">
          <input type="hidden" name="no_note" value="1">
          <input type="hidden" name="currency_code" value="USD">
          <input type="hidden" name="tax" value="0">
          <input type="hidden" name="lc" value="US">
          <input type="hidden" name="bn" value="PP-DonationsBF">
          <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
          <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1"><br />
          </form>
        </div>
        <h3><?php _e("Top Friends Preference",'top_friends');?></h3>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo basename(__FILE__); ?>">
        
        <table class="form-table">
          <tr>
            <th><?php _e("Google AJAX Feed API key:",'top_friends'); ?></th>
            <td>
              <input type="text" name="google_ajax_feed_apikey_option"  value="<?php echo $top_friends_option["google_ajax_feed_apikey"]; ?>" size="40" />
            </td>
          </tr>
          <tr>
            <th><?php _e("Top Friends Feeds:",'top_friends'); ?></th>
            <td>
			  <textarea name="top_friends_feeds_option" rows="10" id="top_friends_feeds_option" style="width: 98%; font-size: 12px;" class="code"><?php echo $top_friends_option["top_friends_feeds"];?></textarea>
			  <br /> <?php _e("Input the feeds of your top friends, One feed one line",'top_friends'); ?>
            </td>
          </tr>
		</table>
        <p class="submit"><input type="submit" value="<?php _e("Update Preferences &raquo;",'top_friends');?>" name="top_friends_Submit" /></p>
        </form>
      </div>
<?php }?>
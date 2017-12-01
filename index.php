<?php
/*
Plugin Name: WP Coffee
Description: Finds coffee shops near you
Version: 0.0.1
Author: Taryn Greer | Jason Stallings
*/
add_action('wp_dashboard_setup', 'wp_coffee_dashboard_widgets');
add_action( 'admin_enqueue_scripts', 'wp_enqueue_coffee_scripts' );
add_action('admin_post_wp_coffee_save_zip','wp_coffee_save_zip');

function wp_coffee_save_zip() {
  update_option( 'wp_coffee_zipcode', $_POST['zipcode'], false );
  update_option( 'wp_coffee_opennow', $_POST['opennow'], false );
  wp_redirect(admin_url('index.php'));
}

function wp_enqueue_coffee_scripts() {
  wp_enqueue_style( 'wp-coffee-css', plugins_url( 'wp-coffee.css', __FILE__ ));
}

function wp_coffee_dashboard_widgets() {
  global $wp_meta_boxes;

  wp_add_dashboard_widget('wp_coffee', 'WP Coffee', 'wp_coffee_dashboard_widget');
}

function wp_coffee_dashboard_widget() {
  date_default_timezone_set( get_option('timezone_string'));
  $zipcode = get_option('wp_coffee_zipcode');
  $opennow = get_option('wp_coffee_opennow', false );
  $url = "https://api.foursquare.com/v2/venues/search?v=20161016&near=$zipcode&query=coffee&intent=checkin&limit=5&sortByDistance=1&client_id=MWI1A5GEEYFGDY5ZO23DUFO4NEFJE1XUG3FIUMMKOEORBFKH&client_secret=DUQKLSMGTN5TYWWGSK5F5KOMLX4VME0XKJY3RKFHXS15EGGA";
$response = get_transient( "wp_coffee_search_results_$zipcode" );
  if ( false === $response ) {
  // It wasn't there, so regenerate the data and save the transient
  $response = wp_remote_get($url);
  set_transient( "wp_coffee_search_results_$zipcode", $response, DAY_IN_SECONDS );
}
  $results = $response['body'];
  $parsed_results = json_decode($results, true);
  $shops = $parsed_results['response']['venues'];
?>
<p align="center"><b>Welcome to WP Coffee!</b> <br> <small><i>Find coffee shops near you.</small></i></p>
<div align="center">
  <form action="<?php echo admin_url( 'admin-post.php' );?>" method='POST'>
  <input type='hidden' name='action' value='wp_coffee_save_zip' />
  Zip Code: <input type='text' name='zipcode' value="<?php echo $zipcode; ?>"/>
  Open Now: <input type='checkbox' name='opennow' value="1" <?php checked( $opennow ); ?>/>

  <input type='submit' value='Save'/>
</form>
</div>
<div class="shops">
<?php
  if (count($shops) < 1){
    echo "Sorry, no coffee shops found :(";
    $shops = array();
  }
  else if (isset($shops['name'])) {
    $shops = array($shops);
  }
  foreach ($shops as $shop) {
    $map_url = "https://www.google.com/maps/search/{$shop['name']}+{$shop['location']['address']}";
    $hours_url = "https://api.foursquare.com/v2/venues/{$shop['id']}/hours?v=20161016&client_id=MWI1A5GEEYFGDY5ZO23DUFO4NEFJE1XUG3FIUMMKOEORBFKH&client_secret=DUQKLSMGTN5TYWWGSK5F5KOMLX4VME0XKJY3RKFHXS15EGGA";
    $hours_response = get_transient( "wp_coffee_hours_{$shop['id']}" );
      if ( false === $hours_response ) {
      // It wasn't there, so regenerate the data and save the transient
      $hours_response = wp_remote_get($hours_url);
      set_transient( "wp_coffee_hours_{$shop['id']}", $hours_response, 7 * DAY_IN_SECONDS );
    }
    $api_response = json_decode( wp_remote_retrieve_body( $hours_response ), true );
    $time_format = 'g:ia';
    $hours_string = "None available :(";
    $hours = get_hours($api_response);
    if ($hours !== false){
      $open = $hours['open'][0];
      $start_timestamp = strtotime($open['start']);
      $end_timestamp = strtotime(str_replace("+","",$open['end']));
      // $end_timestamp = strtotime(str_replace("+","",$open['end']));
      // fix issue of end time being parsed as the same day if after midnight
      if ($open['end'][0] === "+"){
        $end_timestamp += 86400;
        var_dump("end time fix");
      }
      $start = date($time_format, $start_timestamp);
      $end = date($time_format, $end_timestamp);
      $hours_string = "$start - $end";
      $time =   time();
      if ($opennow && ($time < $start_timestamp || $time > $end_timestamp)){
        continue;
        //echo "CLOSED";
      }


      var_dump($start_timestamp);
      var_dump($time);
      var_dump($end_timestamp);
    }

    ?>
    <div class="shop">
      <span class="header">
        <a href="<?php echo $shop['url']; ?>" target="_blank">
          <?php echo $shop['name']; ?>
        </a>
      </span>
      <div>
      <span class="header">  Address: </span>
        <a href="<?php echo $map_url; ?>" target="_blank">
          <?php echo $shop['location']['address']; ?>
        </a>
      </div>
      <div>
        <span class="header">Hours:</span>
        <?php echo $hours_string; ?>
      </div>
    </div>
    <?php
  }

  ?>
</div>
  <p> A map would probably go here. </p>
  <p> There are __ Coffee shops nearby. Here is the closest one: </p>
  <p> I can be the little google header with the rating maybe? </p>
  <marquee> COFFEE</marquee>
  <?php
}

function get_hours($body) {
  if (empty($body['response']['hours'])) {
    return false;
  }
  $timeframes = $body['response']['hours']['timeframes'];
  foreach ($timeframes as $timeframe){
    if (isset($timeframe['includesToday'])) {
      return $timeframe;
    }
  }
  return false;
}

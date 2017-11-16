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
  $zipcode = get_option('wp_coffee_zipcode');
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
    $hours = get_hours($api_response)['open'][0];
    $time_format = 'g:ia';
    $start = date($time_format, strtotime($hours['start']));
    $end = date($time_format, strtotime(str_replace("+","",$hours['end'])));
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
        <?php echo $start; ?> - <?php echo $end; ?>
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
  $timeframes = $body['response']['hours']['timeframes'];
  foreach ($timeframes as $timeframe){
    if (isset($timeframe['includesToday'])){
      return $timeframe;
    }
  }
  return false;
}

<?php
/*
Plugin Name: WP Coffee
Description: Finds coffee shops near you
Version: 0.0.1
Author: Taryn Greer | Jason Stallings
*/
add_action('wp_dashboard_setup', 'wp_coffee_dashboard_widgets');

function wp_coffee_dashboard_widgets() {
global $wp_meta_boxes;

wp_add_dashboard_widget('wp_coffee', 'WP Coffee', 'wp_coffee_dashboard_widget');
}

function wp_coffee_dashboard_widget() {
  $results = file_get_contents('https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20local.search%20where%20query=%22coffee%22%20and%20location=%22Austin,%20TX%22%20and%20Rating.AverageRating%3E=3&format=json');
$parsed_results = json_decode($results, true);
$shops = $parsed_results['query']['results']['Result'];
foreach ($shops as $shop) {
  ?>
  <span style="font-weight:600;">
    <a href="<?php echo $shop['BusinessUrl'];?>" target="_blank">
      <?php echo $shop['Title'];?>
    </a>
  </span>
  <div>
    Rating: <?php echo $shop['Rating']['AverageRating'];?>
<br/>
    Address: <a href="<?php echo $shop['MapUrl'];?>" target="_blank">
<?php echo $shop['Address'];?>
</a>
  </div>
  <hr/>
  <?php
}

  ?>
<p align="center"><b>Welcome to WP Coffee!</b> <br> <small><i>Find coffee shops near you.</small></i></p>
<p> A map would probably go here. </p>
<p> There are __ Coffee shops nearby. Here is the closest one: </p>
<p> I can be the little google header with the rating maybe? </p>
<marquee> COFFEE</marquee>
<?php
}

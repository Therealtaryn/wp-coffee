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
  $results = file_get_contents('https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20local.search%20where%20query=%22coffee%22%20and%20location=%22Austin,%20TX%22%20and%20Rating.AverageRating%3E=3&format=json');
  $zipcode = get_option('wp_coffee_zipcode');
  $parsed_results = json_decode($results, true);
  $shops = $parsed_results['query']['results']['Result'];
  foreach ($shops as $shop) {
    ?>
    <div class="wp-coffee">
      <span style="font-weight:600;">
        <a href="<?php echo $shop['BusinessUrl']; ?>" target="_blank">
          <?php echo $shop['Title']; ?>
        </a>
      </span>
      <div>
        Rating: <?php echo $shop['Rating']['AverageRating'];?>
        <br />
        Address:
        <a href="<?php echo $shop['MapUrl']; ?>" target="_blank">
          <?php echo $shop['Address']; ?>
        </a>
      </div>
    </div>
    <?php
  }

  ?>
  <p align="center"><b>Welcome to WP Coffee!</b> <br> <small><i>Find coffee shops near you.</small></i></p>
  <form action="<?php echo admin_url( 'admin-post.php' );?>" method='POST'>
    <input type='hidden' name='action' value='wp_coffee_save_zip' />
    Zip Code: <input type='text' name='zipcode' value="<?php echo $zipcode; ?>"/>
    <input type='submit' value='Save'/>
  </form>

  <p> A map would probably go here. </p>
  <p> There are __ Coffee shops nearby. Here is the closest one: </p>
  <p> I can be the little google header with the rating maybe? </p>
  <marquee> COFFEE</marquee>
  <?php
}

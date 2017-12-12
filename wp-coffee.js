jQuery(function($){
  $("#wp-coffee-geo").on("click", function(e) {
    e.preventDefault();
    $("#wp-coffee-loading").html("Finding Location...");

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(a) {
        $("#wp-coffee-ll").val(a.coords.latitude+","+a.coords.longitude);
        $("#wp-coffee-zip").val("Current Location");
        $("#wp-coffee-loading").html("");
        $("#wp-coffee-submit").submit();
      });
    } else {
      alert('navigator.geolocation not supported.');
    }

  });
})

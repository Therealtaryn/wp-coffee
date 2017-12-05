jQuery(function($){
  $("#wp-coffee-geo").on("click", function(e) {
    e.preventDefault();
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(a) {
        console.log(a);
        $("#wp-coffee-ll").val(a.coords.latitude+","+a.coords.longitude);
        $("#wp-coffee-zip").val("Current Location");
      });
    } else {
      alert('navigator.geolocation not supported.');
    }

  });
})

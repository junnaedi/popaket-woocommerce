// Create the script tag, set the appropriate attributes
if (popaket_maps.api_key) {
    var script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${popaket_maps.api_key}&callback=initMap`;
    script.async = true;
    
    // Attach your callback function to the `window` object
    window.initMap = function() {
        const geocoder = new google.maps.Geocoder();
        var $input = jQuery('#shipping_address_1');
    
        //user is "finished typing," do something
        $input.on('blur', function() {
            geocodeAddress(geocoder, $input.val());
        })
    };
    
    // Append the 'script' element to 'head'
    document.head.appendChild(script);
    
    // This function is called when the user clicks the UI button requesting
    // a geocode of a place ID.
    function geocodeAddress(geocoder, location) {
      geocoder
        .geocode({ address: location })
        .then(({ results }) => {
          if (results[0]) {
            jQuery('#shipping_latitude').val(results[0].geometry.location.lat());
            jQuery('#shipping_longitude').val(results[0].geometry.location.lng());
          }
        })
        .catch((e) => console.log(e));
    }
}
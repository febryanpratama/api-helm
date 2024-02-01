var map;
var myLatLng;
$(document).ready(function() {
    geoLocationInit();
});
    function geoLocationInit() {
        console.log(navigator.geolocation);
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(success, fail,{maximumAge:60000, timeout:5000, enableHighAccuracy:true});
        } else {
            alert("Browser not supported");
        }
    }

    function success(position) {
        // console.log(position);
        var latval = position.coords.latitude;
        var lngval = position.coords.longitude;
        myLatLng = new google.maps.LatLng(latval, lngval);
        createMap(myLatLng);

        var url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" + latval + "," + lngval + "&sensor=false&key=AIzaSyBxbZxhzKBe5g9ZGLwM6STYnfNPU3ithjE";

        $.getJSON(url, function (data) {
                var address = data.results[0].formatted_address;
                document.getElementById("location").value = address;

        });
        // nearbySearch(myLatLng, "school");
        // searchGirls(latval,lngval);
    }

    function fail() {
        // alert("please allow location from browser to using location");
    }
    //Create Map
    function createMap(myLatLng) {
        // map = new google.maps.Map(document.getElementById('map'), {
        //     center: myLatLng,
        //     zoom: 12
        // });
        // var marker = new google.maps.Marker({
        //     position: myLatLng,
        //     map: map
        // });
    }
    //Create marker
    function createMarker(latlng, icn, name) {
        var marker = new google.maps.Marker({
            position: latlng,
            map: map,
            icon: icn,
            title: name
        });
    }



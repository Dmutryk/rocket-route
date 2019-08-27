<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<body>

<h1>My Test Google Map</h1>

<div id="googleMap" style="width:100%;height:400px;"></div>
ICAO:<br>
<input type="text" name="ItemQ" id ="icao"><br>
<input type="button" value="Search" onclick="search()">
<script>
    function myMap() {
        var mapProp= {
            center:new google.maps.LatLng(51.508742,-0.120850),
            zoom:5,
        };
        var map = new google.maps.Map(document.getElementById("googleMap"),mapProp);
    }

    function search() {

        var icao = document.getElementById("icao").value;

        console.log(icao);

        var notam = [];
        var geoLocation = notam['ItemQ'];
        var result = geoLocation.split("/").pop();
        console.log(result);
    }

    function login() {
        const Http = new XMLHttpRequest();
        const url='https://flydev.rocketroute.com/api/';
        var params = 'email=dmutrykv@gmail.com&password=Dm:Z5LJ9mtE&app_key=GW6xu64dTJT7wRk3B8WD';
        http.open('POST', url, true);

//Send the proper header information along with the request
        http.setRequestHeader('Content-type', 'application/json');

        http.onreadystatechange = function() {//Call a function when the state changes.
            if(http.readyState == 4 && http.status == 200) {
                alert(http.responseText);
            }
        }
        http.send(params);
    }
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCjIn6KXclYWxY6PW0WryVDDB8lhdNRUvM&callback=myMap"></script>

</body>
</html>

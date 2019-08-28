<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<body>

<h1>My Test Google Map</h1>

<div id="googleMap" style="width:100%;height:400px;"></div>
ICAO:<br>
<input type="text" name="ItemQ" id ="icao"><br>
<input type="button" value="Search" onclick="getAirportInfo()">

<script>
    var map, mapProp, points;
    function myMap() {
        mapProp= {
            center:new google.maps.LatLng(51.508742,-0.120850),
            zoom:5,
        };
        map = new google.maps.Map(document.getElementById("googleMap"),mapProp);
    }

    function getAirportInfo() {
        var icao = $("#icao").val();
        $.ajax({
            url: '/get-airport-data',
            type: "POST",
            data: JSON.stringify({
                'icao': icao,
            }),
            contentType: "application/json",
            success: function (result) {
                var notam = JSON.parse(result.notam);
                var weather = JSON.parse(result.weather);
                console.log(notam);
                console.log(weather);
                points = getAllPoints(notam.data[0].notams);
                addPointToMap();
                console.log(points);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(ajaxOptions);
                console.log(thrownError);
            }
        });
    }

    function getAllPoints(notams){
        var notamsAddresses = [];
        console.log(notams);
        for (var i = 0; i < notams.length; i++) {
            itemQ = notams[i].ItemQ;
            var address = itemQ.split("/").pop();
            var coordinates = address.split("N");
            var lat = coordinates[1].split("W");
            latitude = lat[0].replace('W','');
            console.log(coordinates);
            console.log(coordinates[0], lat[0]);
            console.log(latitude);
            notamsAddresses[i] = {
                'long': coordinates[0],
                'lat': latitude,
            }
        };

        return notamsAddresses;
    }

    function addPointToMap() {
        var myLatLng = new google.maps.LatLng('51.17', '0.47');
        var marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            icon: 'http://www.clker.com/cliparts/H/Z/0/R/f/S/warning-icon-th.png'
        });
    }
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCjIn6KXclYWxY6PW0WryVDDB8lhdNRUvM&callback=myMap"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</body>
</html>

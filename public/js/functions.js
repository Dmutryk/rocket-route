var map, mapProp, weather;

function myMap() {
    mapProp = {
        center: new google.maps.LatLng(51.508742, -0.120850),
        zoom: 5,
    };
    map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
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
            weather = JSON.parse(result.weather);
            notam.forEach(addPointToMap);
        },
        error: function (xhr) {
            alert(xhr.responseText);
        }
    });
}

function addPointToMap(item) {
    var myLatLng = new google.maps.LatLng(item['x'], item['y']);
    var marker = new google.maps.Marker({
        position: myLatLng,
        map: map,
        icon: 'http://www.clker.com/cliparts/H/Z/0/R/f/S/warning-icon-th.png'
    });

    var infowindow = new google.maps.InfoWindow({
        content: '<p> NOTAM:' + JSON.stringify(item.notam) + '</p>'
            + '<p> Weather info:' + JSON.stringify(weather) + '</p>'
    });

    google.maps.event.addListener(marker, 'click', function () {
        infowindow.open(map, marker);
    });

    map.panTo(marker.getPosition());
}

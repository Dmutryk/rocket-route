<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<body>

<h1>My Test Rocket Route Task</h1>

ICAO:<br>
<input type="text" name="ItemQ" id="icao"><br>
<input type="button" value="Search" onclick="getAirportInfo()">

<div id="googleMap" style="width:100%;height:400px;"></div>

<script type="text/javascript" src="{{ asset('js/functions.js') }}"></script>
<script
    src="https://maps.googleapis.com/maps/api/js?key={{$googleAppKey}}&callback=myMap"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</body>
</html>

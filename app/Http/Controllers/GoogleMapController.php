<?php

namespace App\Http\Controllers;

class GoogleMapController
{
    public function index()
    {
        return response()->view(
            'map',
            ['googleAppKey' => env('GOOGLE_MAPS_APP_KEY')]
        );
    }
}

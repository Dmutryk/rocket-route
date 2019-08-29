<?php

namespace App\Http\Controllers;

class GoogleMapController
{
    public function index()
    {
        return response()->view('map');
    }
}

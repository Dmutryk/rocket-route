<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GoogleMapController extends Controller
{
    public function index()
    {
        return response()->view('map');
    }
}

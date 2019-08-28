<?php

namespace App\Http\Validators;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait ValidatesIcaoRequests
{
    protected function validateIcao(Request $request)
    {
        return Validator::make($request->all(), ['icao' => 'required|string|min:4|max:4']);
    }
}

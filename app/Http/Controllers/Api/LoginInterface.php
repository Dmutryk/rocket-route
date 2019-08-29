<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

interface LoginInterface
{
    public function login(): void;

    public function getToken(Request $request);
}

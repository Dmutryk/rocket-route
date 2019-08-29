<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

interface ReLoginInterface
{
    public function relogin(Request $request): void;
}

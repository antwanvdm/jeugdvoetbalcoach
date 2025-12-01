<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PrivacyController extends Controller
{
    public function show(): View
    {
        return view('privacy');
    }
}

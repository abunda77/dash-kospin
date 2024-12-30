<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class WelcomeController extends Controller
{
    public function index()
    {
        $quote = Artisan::call('inspire');
        $quote = Artisan::output();

        return view('welcome', compact('quote'));
    }
}

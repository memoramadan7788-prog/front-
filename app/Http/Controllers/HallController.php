<?php

namespace App\Http\Controllers;

class HallController extends Controller
{
    public function exterior()
    {
        // The entrance scene is for visitors only: a logged-in user goes straight to their dashboard.
        if (auth()->check()) {
            return redirect()->route('frontend.workspace');
        }

        return view('hall.exterior');
    }

    public function landing()
    {
        return view('hall.landing');
    }

    public function lobby()
    {
        return view('hall.lobby');
    }

    public function exhibit()
    {
        return view('hall.exhibit');
    }
}
